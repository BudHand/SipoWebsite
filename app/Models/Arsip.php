<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    use HasFactory;

    protected $table = 'arsip';
    protected $fillable = ['user_id', 'document_id', 'jenis_document'];

    public $timestamps = false;

    // Relasi Polymorphic
    public function document()
    {
        return $this->morphTo();
    }
}
