<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Tentukan nama tabel sesuai dengan nama tabel di database
    protected $table = 'role';  // Nama tabel yang benar tanpa 's'

    // Kolom yang dapat diisi
    protected $fillable = ['nm_role'];
    // Relasi One-to-Many ke User
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
