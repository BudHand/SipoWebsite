<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Laporan;
use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;
use App\Models\Role;
use App\Models\Seri;
use App\Models\User;
use App\Models\Divisi;
use Illuminate\Http\Request;

class LaporanApiController extends Controller
{
    public function getMemos(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $memos = Memo::with('divisi')
            ->where(function($query) {
                $query->where('status', 'diterima')
                    ->orWhere('status', 'approve');
            });

        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $memos->whereDate('tgl_dibuat', '>=', $request->tgl_awal)
                ->whereDate('tgl_dibuat', '<=', $request->tgl_akhir);
        }

        if ($request->filled('divisi_id_divisi')) {
            $memos->where('divisi_id_divisi', $request->divisi_id_divisi);
        }

        if ($request->filled('search')) {
            $memos->where('judul', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'memos' => $memos->orderBy('tgl_dibuat', 'desc')->get()
        ]);
    }

    public function getUndangans(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $undangans = Undangan::with('divisi')
            ->where(function($query) {
                $query->where('status', 'diterima')
                    ->orWhere('status', 'approve');
            });

        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $undangans->whereDate('tgl_dibuat', '>=', $request->tgl_awal)
                    ->whereDate('tgl_dibuat', '<=', $request->tgl_akhir);
        }

        if ($request->filled('divisi_id_divisi')) {
            $undangans->where('divisi_id_divisi', $request->divisi_id_divisi);
        }

        if ($request->filled('search')) {
            $undangans->where('judul', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'undangans' => $undangans->orderBy('tgl_dibuat', 'desc')->get()
        ]);
    }

    public function getRisalahs(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $risalahs = Risalah::with('divisi')
            ->where(function($query) {
                $query->where('status', 'diterima')
                    ->orWhere('status', 'approve');
            });

        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $risalahs->whereDate('tgl_dibuat', '>=', $request->tgl_awal)
                    ->whereDate('tgl_dibuat', '<=', $request->tgl_akhir);
        }

        if ($request->filled('divisi_id_divisi')) {
            $risalahs->where('divisi_id_divisi', $request->divisi_id_divisi);
        }

        if ($request->filled('search')) {
            $risalahs->where('judul', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'risalahs' => $risalahs->orderBy('tgl_dibuat', 'desc')->get()
        ]);
    }
}
