<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\Storage;

class PerusahaanApiController extends Controller
{
    public function index()
    {
        $perusahaan = Perusahaan::first();

        if (!$perusahaan) {
            return response()->json([
                'success' => false,
                'message' => 'Data perusahaan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nama_instansi' => $perusahaan->nama_instansi,
                'alamat_web' => $perusahaan->alamat_web,
                'telepon' => $perusahaan->telepon,
                'email' => $perusahaan->email,
                'alamat' => $perusahaan->alamat,
                'logo' => 'data:image/png;base64,' . $perusahaan->logo,
            ]
        ]);
    }
}
