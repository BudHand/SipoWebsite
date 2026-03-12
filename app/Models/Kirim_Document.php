<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Kirim_Document extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kirim_document';
    protected $primaryKey = 'id_kirim_document';

    protected $fillable = [
        'id_document',
        'jenis_document',
        'id_pengirim',
        'id_penerima',
        'status',
        'updated_at',
    ];
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    // Relasi ke User (Pengirim)
    public function pengirim()
    {
        return $this->belongsTo(User::class, 'id_pengirim');
    }

    public function memo()
    {
        return $this->belongsTo(Memo::class, 'id_document', 'id_memo'); // Sesuaikan dengan foreign key yang benar
    }

    // public function undangan()
    // {
    //     return $this->belongsTo(Undangan::class, 'id_document'); // Sesuaikan dengan foreign key yang benar
    // }
    public function undangan()
    {
        return $this->belongsTo(\App\Models\Undangan::class, 'id_document', 'id_undangan');
    }


    public function risalah()
    {
        return $this->belongsTo(Risalah::class, 'id_document', 'id_risalah'); // Sesuaikan dengan foreign key yang benar
    }

    // Relasi ke User (Penerima)
    public function penerima()
    {
        return $this->belongsTo(User::class, 'id_penerima');
    }

    public function up()
    {
        Schema::table('kirim_document', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('kirim_document', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
