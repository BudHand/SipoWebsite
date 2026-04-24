<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;

class UserManageApiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter sort (default 'asc') dan search (optional)
        $sortOrder = $request->query('sort', 'asc');
        $searchTerm = $request->query('search');

        // Query awal dengan eager loading
        $query = User::with(['role', 'divisi', 'position']);

        // Jika ada pencarian, filter berdasarkan firstname atau lastname
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('firstname', 'like', '%' . $searchTerm . '%')
                  ->orWhere('lastname', 'like', '%' . $searchTerm . '%');
            });
        }

        // Urutkan berdasarkan firstname
        $query->orderBy('firstname', $sortOrder);

        // Ambil data semua user (tanpa pagination, untuk Android lebih praktis)
        $users = $query->get();

        // Kembalikan response JSON
        return response()->json([
            'status' => true,
            'message' => 'Data users berhasil diambil',
            'data' => $users
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['success' => 'User berhasil dihapus'], 200);
    }
}
