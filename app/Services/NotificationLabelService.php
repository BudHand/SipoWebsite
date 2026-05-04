<?php

namespace App\Services;

use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;
use App\Models\Notifikasi;
use App\Models\Disposisi;

class NotificationLabelService
{
    public function resolve(Notifikasi $notification, $user): array
    {
        $jenis = $notification->jenis_document;
        $documentId = $notification->id_document;

        if (!$documentId) {
            return $this->default();
        }

        // ================= MEMO =================
        if ($this->isMemo($jenis, $notification->judul)) {
            $memo = Memo::find($documentId);

            if (!$memo) {
                return $this->build('Memo', 'memo', null);
            }

            return $this->resolveMemo($memo, $user);
        }

        // ================= UNDANGAN =================
        if ($this->isUndangan($jenis, $notification->judul)) {
            $undangan = Undangan::find($documentId);

            if (!$undangan) {
                return $this->build('Undangan', 'undangan', null);
            }

            return $this->resolveUndangan($undangan, $user);
        }

        // ================= RISALAH =================
        if ($this->isRisalah($jenis, $notification->judul)) {
            $risalah = Risalah::find($documentId);

            if (!$risalah) {
                return $this->build('Risalah', 'risalah', null);
            }

            return $this->resolveRisalah($risalah);
        }

        //================== DISPOSISI =================
        if ($this->isDisposisi($jenis, $notification->judul)) {
            $disposisi = Disposisi::find($documentId);

            if (!$disposisi) {
                return $this->build('Disposisi', 'disposisi', null);
            }

            return $this->resolveDisposisi($disposisi, $user);
        }

        return $this->default();
    }

    // ======================================================
    // MEMO LOGIC
    // ======================================================
    private function resolveMemo($memo, $user): array
    {
        return $this->build(
            match (true) {
                $this->isMemoMasuk($memo, $user) => 'Memo Masuk',
                $this->isMemoKeluar($memo, $user) => 'Memo Keluar',
                default => 'Memo',
            },
            'memo',
            $memo->id_memo
        );
    }

    private function isMemoMasuk($memo, $user): bool
    {
        if ($memo->status !== 'approve') {
            return false;
        }

        return $this->userInList($memo->tujuan, $user->id)
            || $this->userInList($memo->tembusan, $user->id)
            || $this->userInList($memo->bcc, $user->id);
    }

    private function isMemoKeluar($memo, $user): bool
    {
        // SUPERADMIN → all document
        if ((int) $user->role_id_role === 1) {
            return true;
        }

        $matchKodeBagian = $this->matchKodeBagian($memo->kode_bagian, $user->kode_bagian);

        // ADMIN (2) → kode_bagian sama + pembuat
        if ((int) $user->role_id_role === 2) {
            return $matchKodeBagian && $memo->pembuat == $user->id;
        }

        // MANAGER (3) → kode_bagian sama
        if ((int) $user->role_id_role === 3) {
            return $matchKodeBagian;
        }

        return false;
    }

    // ======================================================
    // UNDANGAN LOGIC
    // ======================================================
    private function resolveUndangan($undangan, $user): array
    {
        return $this->build(
            match (true) {
                $this->isUndanganMasuk($undangan, $user) => 'Undangan Masuk',
                $this->isUndanganKeluar($undangan, $user) => 'Undangan Keluar',
                default => 'Undangan',
            },
            'undangan',
            $undangan->id_undangan
        );
    }

    private function isUndanganMasuk($undangan, $user): bool
    {
        if ($undangan->status !== 'approve') {
            return false;
        }

        return $this->userInList($undangan->tujuan, $user->id)
            || $this->userInList($undangan->tembusan, $user->id)
            || $this->userInList($undangan->bcc, $user->id);
    }

    private function isUndanganKeluar($undangan, $user): bool
    {
        // SUPERADMIN → lihat semua
        if ((int) $user->role_id_role === 1) {
            return true;
        }

        $matchKodeBagian = $this->matchKodeBagian($undangan->kode_bagian, $user->kode_bagian);

        // ADMIN (2) → kode_bagian sama + pembuat
        if ((int) $user->role_id_role === 2) {
            return $matchKodeBagian && $undangan->pembuat == $user->id;
        }

        // MANAGER (3) → kode_bagian sama
        if ((int) $user->role_id_role === 3) {
            return $matchKodeBagian;
        }

        return false;
    }

    private function resolveRisalah($risalah): array
    {
        return $this->build(
            $this->isRisalahDenganUndangan($risalah)
                ? 'Risalah Dengan Undangan'
                : 'Risalah Tanpa Undangan',
            'risalah',
            $risalah->id_risalah
        );
    }

    private function isRisalahDenganUndangan($risalah): bool
    {
        return !empty($risalah->with_undangan);
    }

    // ======================================================
    // HELPER
    // ======================================================
    private function isMemo(?string $jenis, ?string $judul): bool
    {
        return $jenis === 'memo'
            || $jenis === 'App\Models\Memo'
            || str_contains(strtolower((string)$judul), 'memo');
    }

    private function isUndangan(?string $jenis, ?string $judul): bool
    {
        return $jenis === 'undangan'
            || $jenis === 'App\Models\Undangan'
            || str_contains(strtolower((string)$judul), 'undangan');
    }

    private function isRisalah(?string $jenis, ?string $judul): bool
    {
        return $jenis === 'risalah'
            || $jenis === 'App\Models\Risalah'
            || str_contains(strtolower((string)$judul), 'risalah');
    }

    private function userInList(?string $value, int $userId): bool
    {
        return collect(explode(';', (string)$value))
            ->map(fn($id) => trim($id))
            ->filter()
            ->contains((string)$userId);
    }

    private function matchKodeBagian(?string $docKode, ?string $userKode): bool
    {
        $doc = collect(explode(';', (string)$docKode))
            ->map(fn($v) => trim($v))
            ->filter();

        $user = collect(explode(';', (string)$userKode))
            ->map(fn($v) => trim($v))
            ->filter();

        return $doc->intersect($user)->isNotEmpty();
    }

    private function resolveDisposisi($disposisi, $user): array
    {
        return $this->build(
            match (true) {
                $this->isDisposisiMasuk($disposisi, $user) => 'Disposisi Masuk',
                $this->isDisposisiKeluar($disposisi, $user) => 'Disposisi Keluar',
                default => 'Disposisi',
            },
            'disposisi',
            $disposisi->id
        );
    }

    private function isDisposisi(?string $jenis, ?string $judul): bool
    {
        return $jenis === 'disposisi'
            || $jenis === 'App\Models\Disposisi'
            || str_contains(strtolower((string) $judul), 'disposisi');
    }

    private function isDisposisiMasuk($disposisi, $user): bool
    {
        return Disposisi::masuk($user->id)
            ->whereKey($disposisi->getKey())
            ->exists();
    }

    private function isDisposisiKeluar($disposisi, $user): bool
    {
        return Disposisi::keluar($user->id)
            ->whereKey($disposisi->getKey())
            ->exists();
    }

    private function build(string $label, string $tipe, $id): array
    {
        return [
            'label' => $label,
            'tipe_document' => $tipe,
            'id_document' => $id,
        ];
    }

    private function default(): array
    {
        return [
            'label' => 'Notifikasi',
            'tipe_document' => 'unknown',
            'id_document' => null,
        ];
    }
}
