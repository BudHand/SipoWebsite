<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Risalah;
use App\Models\Undangan;
use App\Models\Kirim_Document;
use App\Models\Arsip;
use App\Models\Divisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperadmin = $user->role->nm_role === 'superadmin';

        if ($isSuperadmin) {
            return $this->superadminDashboard();
        }

        $userId = Auth::id();

        // ========== Arsip user (hanya untuk exclude) ==========
        $memoDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', Memo::class)
            ->pluck('document_id');

        $undanganDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', Undangan::class)
            ->pluck('document_id');

        $risalahDiarsipkan = Arsip::where('user_id', $userId)
            ->where('jenis_document', Risalah::class)
            ->pluck('document_id');

        // ========== KELUAR (langsung dari tabel dokumen) ==========
        // memo.pembuat = VARCHAR, jadi cast userId ke string
        $jumlahMemoKeluar = Memo::where('pembuat', (string) $userId)
            ->whereNull('deleted_at')
            ->whereNotIn('id_memo', $memoDiarsipkan)
            ->count();

        $jumlahUndanganKeluar = Undangan::where('pembuat', $userId)
            ->whereNull('deleted_at')
            ->whereNotIn('id_undangan', $undanganDiarsipkan)
            ->count();

        $jumlahRisalahKeluar = Risalah::where('pembuat', $userId)
            ->whereNull('deleted_at')
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
            ->count();

        // ========== MASUK (relasi penerima dari Kirim_Document, hitung dari tabel dokumen) ==========
        $memoMasukIds = Kirim_Document::where('jenis_document', 'memo')
            ->where('id_penerima', $userId)
            ->distinct()
            ->pluck('id_document');

        $jumlahMemoMasuk = Memo::whereIn('id_memo', $memoMasukIds)
            ->whereNull('deleted_at')
            ->whereNotIn('id_memo', $memoDiarsipkan)
            ->count();

        $undanganMasukIds = Kirim_Document::where('jenis_document', 'undangan')
            ->where('id_penerima', $userId)
            ->distinct()
            ->pluck('id_document');

        $jumlahUndanganMasuk = Undangan::whereIn('id_undangan', $undanganMasukIds)
            ->whereNull('deleted_at')
            ->whereNotIn('id_undangan', $undanganDiarsipkan)
            ->count();

        // Risalah: kamu bisa pilih mau "terkait user" atau hanya "keluar".
        // Ini versi "terkait user" (pengirim/penerima) supaya risalah yang diterima juga ikut.
        $risalahTerkaitIds = Kirim_Document::where('jenis_document', 'risalah')
            ->where(function ($q) use ($userId) {
                $q->where('id_pengirim', $userId)
                  ->orWhere('id_penerima', $userId);
            })
            ->distinct()
            ->pluck('id_document');

        $jumlahRisalah = Risalah::whereIn('id_risalah', $risalahTerkaitIds)
            ->whereNull('deleted_at')
            ->whereNotIn('id_risalah', $risalahDiarsipkan)
            ->count();

        // ========== Notifikasi ==========
        $notifikasi = DB::table('notifikasi')
            ->where('id_user', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $notifikasiByDate = $notifikasi->groupBy(function ($item) {
            return Carbon::parse($item->updated_at)->locale('id')->translatedFormat('l, d F');
        });

        // Map variabel risalah agar view lama tetap kompatibel
        // (sebelumnya variabel kamu namanya jumlahRisalah)
        return view($user->role->nm_role . '.dashboard', [
            'jumlahMemoKeluar' => $jumlahMemoKeluar,
            'jumlahMemoMasuk' => $jumlahMemoMasuk,
            'jumlahUndanganKeluar' => $jumlahUndanganKeluar,
            'jumlahUndanganMasuk' => $jumlahUndanganMasuk,
            'jumlahRisalah' => $jumlahRisalah,
            'notifikasiByDate' => $notifikasiByDate,
        ]);
    }

    /**
     * Dashboard khusus Superadmin – agregat seluruh sistem
     */
    private function superadminDashboard()
    {
        $userId = Auth::id();

        // Total dokumen seluruh sistem (exclude soft deleted kalau tabel pakai deleted_at)
        $jumlahMemoKeluar = Memo::whereNull('deleted_at')->count();
        $jumlahUndanganKeluar = Undangan::whereNull('deleted_at')->count();
        $jumlahRisalah = Risalah::whereNull('deleted_at')->count();

        // Dummy agar konsisten dengan view lain
        $jumlahMemoMasuk = 0;
        $jumlahUndanganMasuk = 0;

        // Notifikasi superadmin
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

    /**
     * Data chart aktivitas 6 bulan terakhir (exclude soft deleted)
     */
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

            try {
                $memoCount = Memo::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereNull('deleted_at')
                    ->count();

                $undanganCount = Undangan::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereNull('deleted_at')
                    ->count();

                $risalahCount = Risalah::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereNull('deleted_at')
                    ->count();
            } catch (\Exception $e) {
                $memoCount = $undanganCount = $risalahCount = 0;
            }

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
     * Get user's division/department code (opsional, tetap dipertahankan)
     */
    private function getDivDeptKode($user)
    {
        if ($user->divisi_id) {
            $divisi = Divisi::find($user->divisi_id);
            return $divisi?->kode_divisi;
        }

        if ($user->dept_id) {
            $departemen = DB::table('departemen')->where('id_departemen', $user->dept_id)->first();
            return $departemen?->kode_departemen;
        }

        return null;
    }
}
