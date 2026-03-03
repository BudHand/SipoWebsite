<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Risalah;
use App\Models\Undangan;
use App\Models\Kirim_Document;
use App\Models\Arsip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $roleName = $user->role->nm_role; // 'superadmin' | 'admin' | 'manager'
        $userId = (int) $user->id;

        if ($roleName === 'superadmin') {
            return $this->superadminDashboard($userId);
        }

        // role 2 & 3 -> shared dashboard
        return $this->sharedDashboard($userId, $roleName);
    }

    /**
     * Dashboard gabungan untuk role 2 & 3
     * - Memo & Undangan: ada Keluar dan Masuk
     * - Risalah: hanya 1 angka (tanpa keluar/masuk)
     */
    private function sharedDashboard(int $userId, string $roleName)
    {
        // ===== Arsip user (exclude) =====
        $memoDiarsipkan = $this->arsipIds($userId, Memo::class);
        $undanganDiarsipkan = $this->arsipIds($userId, Undangan::class);
        $risalahDiarsipkan = $this->arsipIds($userId, Risalah::class);

        // ===== KELUAR (hitung dari tabel dokumen, bukan dari Kirim_Document biar tidak dobel) =====
        $jumlahMemoKeluar = Memo::query()
            ->where('pembuat', (string) $userId) // memo: pembuat varchar
            ->whereNull('deleted_at')
            ->whereNotIn('id_memo', $memoDiarsipkan)
            ->count();

        $jumlahUndanganKeluar = Undangan::query()
            ->where('pembuat', $userId)
            ->whereNull('deleted_at')
            ->whereNotIn('id_undangan', $undanganDiarsipkan)
            ->count();

        // ===== MASUK (ambil id dari Kirim_Document, distinct supaya tidak dobel) =====
        $memoMasukIds = Kirim_Document::query()
            ->where('jenis_document', 'memo')
            ->where('id_penerima', $userId)
            ->distinct()
            ->pluck('id_document');

        $jumlahMemoMasuk = Memo::query()
            ->whereIn('id_memo', $memoMasukIds)
            ->whereNull('deleted_at')
            ->whereNotIn('id_memo', $memoDiarsipkan)
            ->count();

        $undanganMasukIds = Kirim_Document::query()
            ->where('jenis_document', 'undangan')
            ->where('id_penerima', $userId)
            ->distinct()
            ->pluck('id_document');

        $jumlahUndanganMasuk = Undangan::query()
            ->whereIn('id_undangan', $undanganMasukIds)
            ->whereNull('deleted_at')
            ->whereNotIn('id_undangan', $undanganDiarsipkan)
            ->count();

        // ===== RISALAH (tanpa keluar/masuk) =====
        // “terkait user” = pembuat ATAU ada di Kirim_Document (pengirim/penerima).
        $risalahIdsFromKirim = Kirim_Document::query()
            ->where('jenis_document', 'risalah')
            ->where(function ($q) use ($userId) {
                $q->where('id_pengirim', $userId)
                    ->orWhere('id_penerima', $userId);
            })
            ->distinct()
            ->pluck('id_document');

        $jumlahRisalah = Risalah::query()
            ->whereNull('deleted_at')
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
            ->where(function ($q) use ($userId, $risalahIdsFromKirim) {
                $q->where('pembuat', $userId)
                  ->orWhereIn('id_risalah', $risalahIdsFromKirim);
            })
            ->count();

        // ===== Notifikasi =====
        $notifikasiByDate = $this->getNotifikasiByDate($userId);

        // ===== Chart (6 bulan) -> untuk role 2 & 3 berdasarkan dokumen yang “terkait user” =====
        $chartData = $this->getChartDataForUser($userId);

        /**
         * VIEW:
         * - disarankan pakai 1 view "dashboard.shared"
         * - tapi kalau kamu masih punya folder admin/dashboard & manager/dashboard,
         *   kita bikin wrapper view supaya kompatibel.
         */
        return view('dashboard.shared', [
            'roleName' => $roleName,

            'jumlahMemoKeluar' => $jumlahMemoKeluar,
            'jumlahMemoMasuk' => $jumlahMemoMasuk,

            'jumlahUndanganKeluar' => $jumlahUndanganKeluar,
            'jumlahUndanganMasuk' => $jumlahUndanganMasuk,

            'jumlahRisalah' => $jumlahRisalah,

            'notifikasiByDate' => $notifikasiByDate,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Dashboard khusus Superadmin – agregat seluruh sistem
     */
    private function superadminDashboard(int $userId)
    {
        $jumlahMemoKeluar = Memo::whereNull('deleted_at')->count();
        $jumlahUndanganKeluar = Undangan::whereNull('deleted_at')->count();
        $jumlahRisalah = Risalah::whereNull('deleted_at')->count();

        $jumlahMemoMasuk = 0;
        $jumlahUndanganMasuk = 0;

        $notifikasiByDate = $this->getNotifikasiByDate($userId);

        $chartData = $this->getChartDataSystem();

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

    // =========================
    // Helpers
    // =========================

    private function arsipIds(int $userId, string $modelClass)
    {
        return Arsip::query()
            ->where('user_id', $userId)
            ->where('jenis_document', $modelClass)
            ->pluck('document_id');
    }

    private function getNotifikasiByDate(int $userId)
    {
        $notifikasi = DB::table('notifikasi')
            ->where('id_user', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return $notifikasi->groupBy(function ($item) {
            return Carbon::parse($item->updated_at)->locale('id')->translatedFormat('l, d F');
        });
    }

    /**
     * Chart khusus user (role 2 & 3):
     * - memo/undangan: "terkait user" => pembuat atau penerima di Kirim_Document
     * - risalah: "terkait user" => pembuat atau pengirim/penerima di Kirim_Document
     * Exclude arsip user
     */
    private function getChartDataForUser(int $userId)
    {
        $labels = [];
        $memoData = [];
        $undanganData = [];
        $risalahData = [];

        $memoDiarsipkan = $this->arsipIds($userId, Memo::class);
        $undanganDiarsipkan = $this->arsipIds($userId, Undangan::class);
        $risalahDiarsipkan = $this->arsipIds($userId, Risalah::class);

        // cache id dokumen terkait user dari kirim_document (biar query monthly tidak berat)
        $memoIdsFromKirim = Kirim_Document::query()
            ->where('jenis_document', 'memo')
            ->where(function ($q) use ($userId) {
                $q->where('id_pengirim', $userId)->orWhere('id_penerima', $userId);
            })
            ->distinct()
            ->pluck('id_document');

        $undanganIdsFromKirim = Kirim_Document::query()
            ->where('jenis_document', 'undangan')
            ->where(function ($q) use ($userId) {
                $q->where('id_pengirim', $userId)->orWhere('id_penerima', $userId);
            })
            ->distinct()
            ->pluck('id_document');

        $risalahIdsFromKirim = Kirim_Document::query()
            ->where('jenis_document', 'risalah')
            ->where(function ($q) use ($userId) {
                $q->where('id_pengirim', $userId)->orWhere('id_penerima', $userId);
            })
            ->distinct()
            ->pluck('id_document');

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $labels[] = $date->locale('id')->translatedFormat('M Y');

            // MEMO (terkait user)
            $memoCount = Memo::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNull('deleted_at')
                ->whereNotIn('id_memo', $memoDiarsipkan)
                ->where(function ($q) use ($userId, $memoIdsFromKirim) {
                    $q->where('pembuat', (string) $userId)
                      ->orWhereIn('id_memo', $memoIdsFromKirim);
                })
                ->count();

            // UNDANGAN (terkait user)
            $undanganCount = Undangan::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNull('deleted_at')
                ->whereNotIn('id_undangan', $undanganDiarsipkan)
                ->where(function ($q) use ($userId, $undanganIdsFromKirim) {
                    $q->where('pembuat', $userId)
                      ->orWhereIn('id_undangan', $undanganIdsFromKirim);
                })
                ->count();

            // RISALAH (terkait user, tanpa masuk/keluar)
            $risalahCount = Risalah::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNull('deleted_at')
                ->whereNotIn('id_risalah', $risalahDiarsipkan)
                ->where(function ($q) use ($userId, $risalahIdsFromKirim) {
                    $q->where('pembuat', $userId)
                      ->orWhereIn('id_risalah', $risalahIdsFromKirim);
                })
                ->count();

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

    /**
     * Chart sistem (superadmin)
     */
    private function getChartDataSystem()
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

            $memoData[] = (int) Memo::whereYear('created_at', $year)->whereMonth('created_at', $month)->whereNull('deleted_at')->count();
            $undanganData[] = (int) Undangan::whereYear('created_at', $year)->whereMonth('created_at', $month)->whereNull('deleted_at')->count();
            $risalahData[] = (int) Risalah::whereYear('created_at', $year)->whereMonth('created_at', $month)->whereNull('deleted_at')->count();
        }

        return [
            'labels' => $labels,
            'memo' => $memoData,
            'undangan' => $undanganData,
            'risalah' => $risalahData,
        ];
    }
}
