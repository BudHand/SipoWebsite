<?php

namespace App\Traits;

use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Trait ini ditempel di model Memo dan Undangan.
 *
 * Cara pakai di model:
 *   use App\Traits\HasDisposisi;
 *   class Memo extends Model {
 *       use HasDisposisi;
 *       ...
 *   }
 */
trait HasDisposisi
{
    // ─── Relasi ──────────────────────────────────────────────────────────────

    /**
     * Semua disposisi yang melekat pada dokumen ini.
     *
     * Karena polymorphic dilakukan manual (tidak pakai morphTo bawaan),
     * kita query langsung berdasarkan document_type + document_id.
     */
    public function disposisi()
    {
        $type = $this instanceof \App\Models\Memo ? 'memo' : 'undangan';
        $pk   = $this->getKey();

        return Disposisi::where('document_type', $type)
                        ->where('document_id', $pk)
                        ->whereNull('parent_id'); // hanya disposisi akar (bukan turunan)
    }

    // ─── Akses & Otorisasi ───────────────────────────────────────────────────

    /**
     * Apakah $user boleh membuat disposisi dari dokumen ini?
     *
     * Syarat: user ada di kolom tujuan, tembusan, atau bcc dokumen.
     * Format kolom: "1;2;3" (dipisah titik koma).
     */
    public function bisaDisposisi(User $user): bool
    {
        $uid = (string) $user->id;

        $tujuan   = $this->tujuan   ?? '';
        $tembusan = $this->tembusan ?? '';
        $bcc      = $this->bcc      ?? '';

        $semuaPenerima = collect(explode(';', $tujuan))
            ->merge(explode(';', $tembusan))
            ->merge(explode(';', $bcc))
            ->map(fn($v) => trim($v))
            ->filter()
            ->unique();

        return $semuaPenerima->contains($uid);
    }

    /**
     * Daftar user yang bisa dipilih sebagai penerima disposisi oleh $user.
     *
     * Logika:
     * 1. Ambil semua kode_bagian milik $user (format "DSN;ENG;S-TQCS")
     * 2. Cari semua user lain yang punya minimal 1 kode_bagian yang sama
     * 3. Kecualikan $user itu sendiri
     */
    public function kandidatPenerimaDispo(User $user): Collection
    {
        $kodeBagianUser = collect(explode(';', $user->kode_bagian ?? ''))
            ->map(fn($k) => trim($k))
            ->filter()
            ->unique();

        if ($kodeBagianUser->isEmpty()) {
            return collect();
        }

        // Ambil semua user aktif (kecuali diri sendiri)
        $candidates = User::where('id', '!=', $user->id)
            ->whereNotNull('kode_bagian')
            ->where('kode_bagian', '!=', '')
            ->get();

        // Filter: punya minimal 1 kode_bagian yang overlap
        return $candidates->filter(function (User $candidate) use ($kodeBagianUser) {
            $kodeBagianCandidate = collect(explode(';', $candidate->kode_bagian))
                ->map(fn($k) => trim($k))
                ->filter();

            return $kodeBagianCandidate->intersect($kodeBagianUser)->isNotEmpty();
        })->values();
    }
}
