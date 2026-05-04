<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Kirim_Document;
use App\Models\Memo;
use App\Models\Risalah;
use App\Models\Undangan;
use App\Models\Disposisi;
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
        $jumlahMemoKeluar = $this->countMemoKeluar($user, $memoDiarsipkan);
        $jumlahMemoMasuk  = $this->countMemoMasuk($user, $memoDiarsipkan);

        // ===== HITUNG UNDANGAN =====
        $jumlahUndanganKeluar = $this->countUndanganKeluar($user, $undanganDiarsipkan);
        $jumlahUndanganMasuk  = $this->countUndanganMasuk($user, $undanganDiarsipkan);

        // ===== HITUNG RISALAH (1 angka saja, tidak ada masuk/keluar) =====
        $jumlahRisalah = $this->countRisalahTerkait($userId, $risalahDiarsipkan);

        // ===== HITUNG DISPOSISI MASUK =====
        $jumlahDisposisi = Disposisi::masuk($userId)->count();

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
            'jumlahDisposisi' => $jumlahDisposisi,

            'notifikasiByDate' => $notifikasiByDate,
        ]);
    }

    /**
     * ====== MEMO KELUAR ======
        * utk staff/admin (role 2): dihitung berdasarkan memo yang ada di kode_bagian && dibuatnya (pembuat = userId), exclude arsip
        * utk manager (role 3): dihitung berdasarkan memo yang terkait dengan seluruh memo yang ada di kode_bagian
    */
    private function countMemoKeluar($user, array $arsipIds): int
    {
        $kodeBagianUser = collect(explode(';', (string) $user->kode_bagian))
            ->map(fn ($kode) => trim($kode))
            ->filter()
            ->unique()
            ->values();

        if ($kodeBagianUser->isEmpty()) {
            return 0;
        }

        $query = Memo::query()
            ->whereNotIn('id_memo', $arsipIds)
            ->where(function ($q) use ($kodeBagianUser) {
                foreach ($kodeBagianUser as $kodeBagian) {
                    $q->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(COALESCE(kode_bagian, ''), ';', ','))",
                        [$kodeBagian]
                    );
                }
            });

        if ((int) $user->role_id_role === 2) {
            $query->where('pembuat', $user->id);
        }

        return (int) $query->count();
    }

    /**
     * ====== MEMO MASUK ======
     * staff & manager : jika id user berada di tujuan/tembusan/bcc, status approve, exclude arsip
     */
    private function countMemoMasuk($user, array $arsipIds): int
    {
        $uid = (string) $user->id;

        return (int) Memo::query()
            ->where('status', 'approve')
            ->where(function ($q) use ($uid) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
            })
            ->whereNotIn('id_memo', $arsipIds)
            ->count();
    }

    /**
     * ====== UNDANGAN KELUAR (Opsi A) ======
     * - Role 2: id_pengirim = userId (keluar milik dia)
     * - Role 3: semua undangan dengan (kode == userKode OR nama_bertandatangan == fullname)
     */
    private function countUndanganKeluar($user, array $arsipIds): int
    {
        $kodeBagianUser = collect(explode(';', (string) $user->kode_bagian))
            ->map(fn ($kode) => trim($kode))
            ->filter()
            ->unique()
            ->values();

        if ($kodeBagianUser->isEmpty()) {
            return 0;
        }

        $query = Undangan::query()
            ->whereNotIn('id_undangan', $arsipIds)
            ->where(function ($q) use ($kodeBagianUser) {
                foreach ($kodeBagianUser as $kodeBagian) {
                    $q->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(COALESCE(kode_bagian, ''), ';', ','))",
                        [$kodeBagian]
                    );
                }
            });

        if ((int) $user->role_id_role === 2) {
            $query->where('pembuat', $user->id);
        }

        return (int) $query->count();
    }

    /**
     * ====== UNDANGAN MASUK ======
     * Diambil dari kirim_document penerima, exclude arsip, 1 dokumen 1x
     */
    private function countUndanganMasuk($user, array $arsipIds): int
    {
        $uid = (string) $user->id;

        return (int) Undangan::query()
            ->whereNotIn('id_undangan', $arsipIds)
            ->where('status', 'approve')
            ->where(function ($q) use ($uid) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
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
        $uid = (string) $userId;

        return (int) Risalah::query()
            ->whereNotIn('id_risalah', $arsipIds)
            ->where(function ($q) use ($userId, $uid) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('status', '!=', 'approve')
                        ->where(function ($x) use ($userId) {
                            $x->where('pembuat', $userId)
                                ->orWhere('pemimpin_acara_user_id', $userId)
                                ->orWhere('notulis_acara_user_id', $userId);
                        });
                })
                ->orWhere(function ($sub) use ($userId, $uid) {
                    $sub->where('status', 'approve')
                        ->where(function ($x) use ($userId, $uid) {
                            $x->where('pembuat', $userId)
                                ->orWhere('pemimpin_acara_user_id', $userId)
                                ->orWhere('notulis_acara_user_id', $userId)
                                ->orWhereRaw(
                                    "FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))",
                                    [$uid]
                                );
                        });
                });
            })
            ->count();
    }

    /**
     * Routes kartu dashboard untuk 1 blade (role 2 & 3)
     */
    private function dashboardRoutesForRole(int $roleId): array
    {
        // role 2 (admin/staff)
        if ($roleId === 2) {
            return [
                'memo_keluar'     => route('memo.terkirim'),
                'memo_masuk'      => route('memo.diterima'),
                'undangan_keluar' => route('undangan.terkirim'),
                'undangan_masuk'  => route('undangan.diterima'),
                'risalah'         => route('risalah.index'),
                'disposisi_masuk'  => route('disposisi.index'),
            ];
        }

        // role 3 (manager)
        if ($roleId === 3) {
            return [
                'memo_keluar'     => route('memo.terkirim'),
                'memo_masuk'      => route('memo.diterima'),
                'undangan_keluar' => route('undangan.terkirim'),
                'undangan_masuk'  => route('undangan.diterima'),
                'risalah'         => route('risalah.index'),
                'disposisi_masuk'  => route('disposisi.index'),
            ];
        }

        // fallback
        return [
            'memo_keluar'     => '#',
            'memo_masuk'      => '#',
            'undangan_keluar' => '#',
            'undangan_masuk'  => '#',
            'risalah'         => '#',
            'disposisi_masuk'  => '#',
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
        $jumlahDisposisi = Disposisi::count();

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
            'jumlahDisposisi',
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

    private function applySemicolonUserMatch($query, string $column, int $userId)
    {
        return $query->where(function ($q) use ($column, $userId) {
            $q->where($column, 'like', $userId . ';%')
                ->orWhere($column, 'like', '%;' . $userId . ';%')
                ->orWhere($column, 'like', '%;' . $userId)
                ->orWhere($column, '=', (string) $userId);
        });
    }
}
