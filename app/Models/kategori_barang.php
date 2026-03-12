<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kategori_barang extends Model
{
    use HasFactory;
    protected $table = 'kategori_barang';
    protected $primaryKey = 'id_kategori_barang';
    protected $fillable = [ 'nomor', 'barang', 'qty', 'satuan', 'memo_id_memo', 'memo_divisi_id_divisi'];

    public function memo()
    {
        return $this->belongsTo(Memo::class, 'memo_id_memo', 'id_memo');
    }
}
