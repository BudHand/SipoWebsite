<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Disposisi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'disposisi';

    protected $fillable = [
        'document_type',
        'document_id',
        'dari_user_id',
        'kepada_user_id',
        'instruksi',
        'catatan',
        'deadline',
        'status',
        'parent_id',
        'dibaca_at',
    ];

    protected $casts = [
        'kepada_user_id' => 'array',    // JSON ↔ PHP array otomatis
        'deadline'       => 'date',
        'dibaca_at'      => 'datetime',
    ];

    // ─── Relasi ke pengguna ───────────────────────────────────────────────────

    public function dariUser()
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    /**
     * Semua penerima sebagai Collection of User.
     * Tidak bisa pakai belongsTo karena kepada_user_id adalah JSON array.
     */
    // public function kepadaUsers(): Collection
    // {
    //     $ids = $this->kepada_user_id ?? [];
    //     if (empty($ids)) return collect();

    //     return User::whereIn('id', $ids)
    //         ->orderBy('firstname')
    //         ->orderBy('lastname')
    //         ->get();
    // }
    public function kepadaUsers()
    {
        return \App\Models\User::whereIn('id', $this->kepada_user_id ?? [])->get();
    }

    /**
     * Cek apakah $userId termasuk salah satu penerima.
     */
    public function adalahPenerima(int $userId): bool
    {
        return in_array($userId, $this->kepada_user_id ?? [], true);
    }

    // ─── Relasi polymorphic ke dokumen ────────────────────────────────────────

    public function getDokumenAttribute()
    {
        if ($this->document_type === 'memo') {
            return Memo::find($this->document_id);
        }
        if ($this->document_type === 'undangan') {
            return Undangan::find($this->document_id);
        }
        return null;
    }

    public function getJudulDokumenAttribute(): string
    {
        $dok = $this->dokumen;
        return $dok?->judul ?? '(Dokumen tidak ditemukan)';
    }

    public function getNomorDokumenAttribute(): ?string
    {
        $dok = $this->dokumen;
        if (!$dok) return null;

        return $this->document_type === 'memo'
            ? $dok->nomor_memo
            : $dok->nomor_undangan;
    }

    public function getLabelTipeAttribute(): string
    {
        return match ($this->document_type) {
            'memo'     => 'Memo',
            'undangan' => 'Undangan',
            default    => ucfirst($this->document_type),
        };
    }

    // ─── Relasi rantai disposisi ──────────────────────────────────────────────

    public function parent()
    {
        return $this->belongsTo(Disposisi::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Disposisi::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->hasMany(Disposisi::class, 'parent_id')
                    ->with('allChildren');
    }

    // ─── Scope ───────────────────────────────────────────────────────────────

    public function scopeMasuk($query, int $userId)
    {
        return $query->whereJsonContains('kepada_user_id', $userId);
    }

    public function scopeKeluar($query, int $userId)
    {
        return $query->where('dari_user_id', $userId);
    }

    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_at');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    public function tandaiDibaca(): void
    {
        if (is_null($this->dibaca_at)) {
            $this->update(['dibaca_at' => now()]);
        }
    }

    public function sudahDibaca(): bool
    {
        return !is_null($this->dibaca_at);
    }

    public function bisaDiubah(): bool
    {
        return in_array($this->status, ['menunggu', 'diterima']);
    }

    public function getBadgeStatusAttribute(): string
    {
        return match ($this->status) {
            'menunggu'   => 'badge-warning',
            'diterima'   => 'badge-info',
            'selesai'    => 'badge-success',
            'diteruskan' => 'badge-secondary',
            default      => 'badge-light',
        };
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'menunggu'   => 'Menunggu',
            'diterima'   => 'Diterima',
            'selesai'    => 'Selesai',
            'diteruskan' => 'Diteruskan',
            default      => ucfirst($this->status),
        };
    }

    public function getCatatanAttribute($value): ?string
    {
        return $value ? trim($value) : null;
    }
}
