<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Kirim_Document;
use App\Models\Memo;
use App\Models\Risalah;
use App\Models\Undangan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Superadmin tetap seperti sebelumnya (agregat total sistem)
        if (($user->role->nm_role ?? null) === 'superadmin' || (int)($user->role_id_role ?? 0) === 1) {
            return $this->superadminDashboard();
        }

        $userId = (int) $user->id;

        // ===== Arsip (exclude) =====
        $memoDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', Memo::class)
            ->pluck('document_id')
            ->toArray();

        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', Undangan::class)
            ->pluck('document_id')
            ->toArray();

        $risalahDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', Risalah::class)
            ->pluck('document_id')
            ->toArray();

        // ===== Kode user (dipakai untuk Opsi A) =====
        // IMPORTANT: pakai helper yang sama seperti halaman memo/undangan kamu
        $userKode = (new MemoController())->getDivDeptKode($user);
        $fullname = (string) ($user->fullname ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')));

        $isManager = ((int)($user->role_id_role ?? 0) === 3);

        // ===== HITUNG MEMO =====
        $jumlahMemoKeluar = $this->countMemoKeluar($userId, $isManager, $memoDiarsipkan, $userKode, $fullname);
        $jumlahMemoMasuk  = $this->countMemoMasuk($userId, $memoDiarsipkan, $fullname);

        // ===== HITUNG UNDANGAN =====
        $jumlahUndanganKeluar = $this->countUndanganKeluar($userId, $isManager, $undanganDiarsipkan, $userKode, $fullname);
        $jumlahUndanganMasuk  = $this->countUndanganMasuk($userId, $undanganDiarsipkan);

        // ===== HITUNG RISALAH (1 angka saja, tidak ada masuk/keluar) =====
        $jumlahRisalah = $this->countRisalahTerkait($userId, $risalahDiarsipkan);

        // ===== Notifikasi =====
        $notifikasi = DB::table('notifikasi')
            ->where('id_user', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $notifikasiByDate = $notifikasi->groupBy(function ($item) {
            return Carbon::parse($item->updated_at)->locale('id')->translatedFormat('l, d F');
        });

        // ===== Routes untuk kartu dashboard (biar 1 blade bisa dipakai role 2 & 3) =====
        $routes = $this->dashboardRoutesForRole((int)($user->role_id_role ?? 0));

        // Kamu bisa arahkan ke 1 file blade baru misalnya: resources/views/dashboard/shared.blade.php
        // (atau tetap pakai $user->role->nm_role . '.dashboard' kalau belum mau pindah)
        return view('dashboard.shared', [
            'routes' => $routes,

            'jumlahMemoKeluar' => $jumlahMemoKeluar,
            'jumlahMemoMasuk' => $jumlahMemoMasuk,
            'jumlahUndanganKeluar' => $jumlahUndanganKeluar,
            'jumlahUndanganMasuk' => $jumlahUndanganMasuk,
            'jumlahRisalah' => $jumlahRisalah,

            'notifikasiByDate' => $notifikasiByDate,
        ]);
    }

    /**
     * ====== MEMO KELUAR (Opsi A) ======
     * - Role 2: kirim_document.id_pengirim = userId
     * - Role 3: semua memo dengan (kode == userKode OR nama_bertandatangan == fullname)
     * - 1 dokumen dihitung 1x (MIN id_kirim_document)
     */
    private function countMemoKeluar(int $userId, bool $isManager, array $arsipIds, ?string $userKode, string $fullname): int
    {
        $q = Kirim_Document::query()
            ->where('jenis_document', 'memo')
            ->whereNotIn('id_document', $arsipIds)
            ->whereIn('id_kirim_document', function ($sub) {
                $sub->selectRaw('MIN(id_kirim_document)')
                    ->from('kirim_document')
                    ->where('jenis_document', 'memo')
                    ->groupBy('id_document');
            })
            ->whereHas('memo', function ($mq) use ($isManager, $userKode, $fullname, $userId) {
                // Opsi A: filter berdasarkan kode / penandatangan
                $mq->where(function ($x) use ($userKode, $fullname) {
                    if (!empty($userKode)) {
                        $x->where('kode', $userKode);
                    }
                    $x->orWhere('nama_bertandatangan', $fullname);
                });

                // Untuk role 2, biar “keluar” benar-benar yang dia buat/kirim
                if (!$isManager) {
                    // memo.pembuat di sistemmu kadang string, jadi safe cast:
                    $mq->where('pembuat', (string)$userId);
                }
            })
            ->with('memo');

        // Untuk role 2, ikut pola “keluar” = pengirim
        if (!$isManager) {
            $q->where('id_pengirim', $userId);
        }

        return (int) $q->count();
    }

    /**
     * ====== MEMO MASUK ======
     * Diambil dari kirim_document.penerima (pending/approve), exclude arsip,
     * dan exclude memo yang ditandatangani dirinya sendiri (menghindari “self inbox”)
     */
    private function countMemoMasuk(int $userId, array $arsipIds, string $fullname): int
    {
        return (int) Kirim_Document::query()
            ->where('jenis_document', 'memo')
            ->where('id_penerima', $userId)
            ->whereIn('kirim_document.status', ['pending', 'approve'])
            ->whereNotIn('id_document', $arsipIds)
            ->whereIn('id_kirim_document', function ($sub) use ($userId) {
                $sub->selectRaw('MIN(id_kirim_document)')
                    ->from('kirim_document')
                    ->where('jenis_document', 'memo')
                    ->where('id_penerima', $userId)
                    ->groupBy('id_document');
            })
            ->whereHas('memo', function ($mq) use ($fullname) {
                $mq->where('nama_bertandatangan', '!=', $fullname);
            })
            ->count();
    }

    /**
     * ====== UNDANGAN KELUAR (Opsi A) ======
     * - Role 2: id_pengirim = userId (keluar milik dia)
     * - Role 3: semua undangan dengan (kode == userKode OR nama_bertandatangan == fullname)
     */
    private function countUndanganKeluar(int $userId, bool $isManager, array $arsipIds, ?string $userKode, string $fullname): int
    {
        $q = Kirim_Document::query()
            ->where('jenis_document', 'undangan')
            ->whereNotIn('id_document', $arsipIds)
            ->whereIn('id_kirim_document', function ($sub) {
                $sub->selectRaw('MIN(id_kirim_document)')
                    ->from('kirim_document')
                    ->where('jenis_document', 'undangan')
                    ->groupBy('id_document');
            })
            ->whereHas('undangan', function ($uq) use ($isManager, $userKode, $fullname, $userId) {
                $uq->where(function ($x) use ($userKode, $fullname) {
                    if (!empty($userKode)) {
                        $x->where('kode', $userKode);
                    }
                    $x->orWhere('nama_bertandatangan', $fullname);
                });

                if (!$isManager) {
                    // undangan.pembuat di sistemmu numeric, tapi aman:
                    $uq->where('pembuat', $userId);
                }
            })
            ->with('undangan');

        if (!$isManager) {
            $q->where('id_pengirim', $userId);
        }

        return (int) $q->count();
    }

    /**
     * ====== UNDANGAN MASUK ======
     * Diambil dari kirim_document penerima, exclude arsip, 1 dokumen 1x
     */
    private function countUndanganMasuk(int $userId, array $arsipIds): int
    {
        return (int) Kirim_Document::query()
            ->where('jenis_document', 'undangan')
            ->where('id_penerima', $userId)
            ->whereIn('kirim_document.status', ['pending', 'approve'])
            ->whereNotIn('id_document', $arsipIds)
            ->whereIn('id_kirim_document', function ($sub) use ($userId) {
                $sub->selectRaw('MIN(id_kirim_document)')
                    ->from('kirim_document')
                    ->where('jenis_document', 'undangan')
                    ->where('id_penerima', $userId)
                    ->groupBy('id_document');
            })
            ->count();
    }

    /**
     * ====== RISALAH (TERKAIT USER) ======
     * 1 angka saja: risalah yang terkait lewat kirim_document (pengirim/penerima),
     * exclude arsip, 1 dokumen 1x
     */
    private function countRisalahTerkait(int $userId, array $arsipIds): int
    {
        return (int) Kirim_Document::query()
            ->where('jenis_document', 'risalah')
            ->where(function ($q) use ($userId) {
                $q->where('id_pengirim', $userId)
                  ->orWhere('id_penerima', $userId);
            })
            ->whereNotIn('id_document', $arsipIds)
            // Gunakan DISTINCT id_document agar sinkron dengan halaman index risalah
            // (satu risalah dihitung sekali walau ada banyak record kirim_document).
            ->distinct('id_document')
            ->count('id_document');
    }

    /**
     * Routes kartu dashboard untuk 1 blade (role 2 & 3)
     */
    private function dashboardRoutesForRole(int $roleId): array
    {
        // role 2 (admin/staff)
        if ($roleId === 2) {
            return [
                'memo_keluar'     => route('admin.memo.terkirim'),
                'memo_masuk'      => route('admin.memo.diterima'),
                'undangan_keluar' => route('admin.undangan.terkirim'),
                'undangan_masuk'  => route('admin.undangan.diterima'),
                'risalah'         => route('admin.risalah.index'),
            ];
        }

        // role 3 (manager)
        if ($roleId === 3) {
            return [
                'memo_keluar'     => route('memo.terkirim'),
                'memo_masuk'      => route('memo.diterima'),
                'undangan_keluar' => route('undangan.terkirim'),
                'undangan_masuk'  => route('undangan.diterima'),
                'risalah'         => route('risalah.manager'),
            ];
        }

        // fallback
        return [
            'memo_keluar'     => '#',
            'memo_masuk'      => '#',
            'undangan_keluar' => '#',
            'undangan_masuk'  => '#',
            'risalah'         => '#',
        ];
    }

    /**
     * Dashboard Superadmin – agregat seluruh sistem (tetap)
     */
    private function superadminDashboard()
    {
        $userId = Auth::id();

        $jumlahMemoKeluar = Memo::whereNull('deleted_at')->count();
        $jumlahUndanganKeluar = Undangan::whereNull('deleted_at')->count();
        $jumlahRisalah = Risalah::whereNull('deleted_at')->count();

        $jumlahMemoMasuk = 0;
        $jumlahUndanganMasuk = 0;

        $notifikasi = DB::table('notifikasi')
            ->where('id_user', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $notifikasiByDate = $notifikasi->groupBy(function ($item) {
            return Carbon::parse($item->updated_at)->locale('id')->translatedFormat('l, d F');
        });

        $chartData = $this->getChartData();

        return view('superadmin.dashboard', compact(
            'jumlahMemoKeluar',
            'jumlahMemoMasuk',
            'jumlahUndanganKeluar',
            'jumlahUndanganMasuk',
            'jumlahRisalah',
            'notifikasiByDate',
            'chartData'
        ));
    }

    private function getChartData()
    {
        $labels = [];
        $memoData = [];
        $undanganData = [];
        $risalahData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $labels[] = $date->locale('id')->translatedFormat('M Y');

            $memoCount = Memo::whereYear('created_at', $year)->whereMonth('created_at', $month)->whereNull('deleted_at')->count();
            $undanganCount = Undangan::whereYear('created_at', $year)->whereMonth('created_at', $month)->whereNull('deleted_at')->count();
            $risalahCount = Risalah::whereYear('created_at', $year)->whereMonth('created_at', $month)->whereNull('deleted_at')->count();

            $memoData[] = (int) $memoCount;
            $undanganData[] = (int) $undanganCount;
            $risalahData[] = (int) $risalahCount;
        }

        return [
            'labels' => $labels,
            'memo' => $memoData,
            'undangan' => $undanganData,
            'risalah' => $risalahData,
        ];
    }
}
