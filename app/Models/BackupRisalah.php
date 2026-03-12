<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupRisalah extends Model

{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'backup_risalah';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    'id_document', 'jenis_document', 'nomor_document', 'seri_document',
    'tgl_dibuat', 'tgl_disahkan', 'waktu_mulai', 'waktu_selesai',
    'agenda', 'tempat', 'nama_bertandatangan', 'lampiran', 'judul',
    'pembuat', 'catatan', 'status', 'created_at', 'updated_at'
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
    public function risalah_detail()
    {
        return $this->hasMany(RisalahDetail::class);
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
