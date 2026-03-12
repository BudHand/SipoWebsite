<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubRisalahDetail extends Model
{
    use HasFactory;

    protected $table = 'sub_risalah_details';

    protected $primaryKey = 'id_sub_risalah_detail';

    public $timestamps = true;

    protected $fillable = [
        'risalah_detail_id_risalah_detail',
        'topik',
        'pembahasan',
        'tindak_lanjut',
        'target',
        'pic',
    ];

    public function risalahDetail()
    {
        return $this->belongsTo(
            RisalahDetail::class,
            'risalah_detail_id_risalah_detail',
            'id_risalah_detail'
        );
    }
}
