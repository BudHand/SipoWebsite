<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup_Document extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'backup_document';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_document',              // ID dokumen asli
        'jenis_document',              // Jenis dokumen (memo, undangan, risalah)
        'judul',
        'tujuan',                            // Judul dokumen
        'isi_document',             // Isi dokumen
        'status',  
        'catatan',
        'seri_document',           // Status dokumen (pending, approve, reject)
        'tgl_dibuat',     // Tanggal dokumen dibuat
        'tgl_disahkan',   // Tanggal dokumen disahkan secara global       // Tahun dokumen dibuat
        'nama_bertandatangan',
        'nomor_document',
        'lampiran',
        'divisi_id_divisi',  
        'pembuat',
        'tgl_rapat',              // Tanggal rapat (khusus undangan)
        'tempat',                // Tempat rapat (khusus undangan)
        'waktu_mulai',           // Waktu mulai rapat (khusus undangan)
        'waktu_selesai',         // Waktu selesai rapat (khusus undangan)
        'kode',                  // Kode unik untuk undangan
        'qr_approved_by',        // QR code yang berisi informasi persetujuan (
        'tujuan_string',         // String tujuan untuk QR code
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tgl_dibuat' => 'date',
        'tgl_disahkan' => 'date',
    ];

    /**
     * Get the division associated with the document.
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi','id_divisi');
    }
    public function kategori_barang()
    {
        return $this->hasMany(kategori_barang::class);
    }
    public function updateStatus()
    {
        $recipients = $this->recipients;

        if ($recipients->every(fn($recipient) => $recipient->status === 'approve')) {
            $this->update(['status' => 'approve', 'tanggal_disahkan' => now()]);
        } elseif ($recipients->contains(fn($recipient) => $recipient->status === 'reject')) {
            $this->update(['status' => 'reject', 'tanggal_disahkan' => now()]);
        } else {
            $this->update(['status' => 'pending', 'tanggal_disahkan' => null]);
        }
    }
}
