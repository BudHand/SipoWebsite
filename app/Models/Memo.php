<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memo extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'memo';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_memo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['judul', 'tujuan', 'isi_memo', 'tgl_dibuat', 'tgl_disahkan',
    'qr_approved_by', 'status', 'pembuat', 'catatan', 'nomor_memo',
    'nama_bertandatangan', 'lampiran', 'divisi_id_divisi', 'seri_surat',
    'kode', 'tujuan_string', 'feedback', 'tembusan', 'kode_bagian', 'bcc', 'manager_user_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    protected $casts = [
        'tgl_dibuat' => 'date',
        'tgl_disahkan' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the division associated with the document.
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }
    public function bagianKerja()
    {
        return $this->belongsTo(BagianKerja::class, 'kode_bagian', 'kode_bagian');
    }
    public function kategoriBarang()
    {
        return $this->hasMany(kategori_barang::class, 'memo_id_memo', 'id_memo');
    }
    // public function kirimDocument()
    // {
    //     return $this->hasMany(Kirim_Document::class, 'id_document');
    // }
    public function arsip()
    {
        return $this->morphMany(Arsip::class, 'document');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'pembuat', 'id');
    }
    public function lampirans()
    {
        return $this->hasMany(Lampiran::class, 'memo_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function pembuatUser()
    {
        return $this->belongsTo(User::class, 'pembuat');
    }

    public function kirimDocument()
    {
        return $this->hasMany(Kirim_Document::class, 'id_document', 'id_memo')
            ->where('jenis_document', 'memo');
    }

    // public function bisaDisposisi($user)
    // {
    //     // contoh sederhana: hanya penerima yang boleh disposisi
    //     $tujuanIds = collect(explode(';', (string) $this->tujuan))
    //         ->map(fn($id) => (int) trim($id))
    //         ->filter();

    //     return $tujuanIds->contains($user->id);
    // }

    // public function kandidatPenerimaDispo()
    // {
    //     return \App\Models\User::query()
    //         ->where('id', '!=', auth()->id())
    //         ->whereNull('deleted_at')
    //         ->orderBy('firstname')
    //         ->get();
    // }
    public function bisaDisposisi(User $user): bool
    {
        // Gunakan getKey() agar aman meski PK user berubah nama
        $uid = (string) $user->getKey();

        $semuaPenerima = collect(explode(';', (string) ($this->tujuan   ?? '')))
            ->merge(explode(';', (string) ($this->tembusan ?? '')))
            ->merge(explode(';', (string) ($this->bcc      ?? '')))
            ->map(fn($v) => trim($v))
            ->filter()   // buang string kosong
            ->unique();

        return $semuaPenerima->contains($uid);
    }

    public function kandidatPenerimaDispo(User $user): \Illuminate\Support\Collection
    {
        return User::where('id', '!=', $user->getKey())
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();
    }

}
