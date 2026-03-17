<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Risalah;
use App\Models\SubRisalahDetail;

class RisalahDetail extends Model
{
    use HasFactory;

    protected $table = 'risalah_details';

    protected $primaryKey = 'id_risalah_detail';

    public $timestamps = true;

    protected $fillable = [
        'risalah_id_risalah',
        'nomor',
        'project_event',
        'topik',
        'uraian_permasalahan',
        'pembahasan_tindak_lanjut',
        'target',
        'pic'
    ];

    public function risalah()
    {
        return $this->belongsTo(Risalah::class, 'risalah_id_risalah', 'id_risalah');
    }

    public function subDetails()
    {
        return $this->hasMany(
            SubRisalahDetail::class,
            'risalah_detail_id_risalah_detail',
            'id_risalah_detail'
        );
    }
}
