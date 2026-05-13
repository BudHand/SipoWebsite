<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;
use Illuminate\Http\Request;

class ApprovalApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $data = $this->waitingApproval($user);

        return $this->apiResponse(
            $data,
            $data->isEmpty()
                ? 'Belum ada dokumen menunggu approval'
                : 'Daftar dokumen menunggu approval ditemukan'
        );
    }

    private function waitingApproval($user)
    {
        $userId = $user->id;

        $memos = Memo::where('status', 'pending')
            ->where('manager_user_id', $userId)
            ->get()
            ->map(function ($doc) {
                return (object) [
                    'id' => $doc->id_memo,
                    'judul' => $doc->judul ?? 'Dokumen tidak ditemukan',
                    'tgl_dokumen' => $doc->updated_at ?? $doc->tgl_disahkan ?? $doc->tgl_dibuat,
                    'tipe' => 'memo',
                    'status' => $doc->status,
                ];
            });

        $undangans = Undangan::where('status', 'pending')
            ->where('manager_user_id', $userId)
            ->get()
            ->map(function ($doc) {
                return (object) [
                    'id' => $doc->id_undangan,
                    'judul' => $doc->judul ?? 'Dokumen tidak ditemukan',
                    'tgl_dokumen' => $doc->tgl_rapat ?? $doc->tgl_disahkan ?? $doc->tgl_dibuat,
                    'tipe' => 'undangan',
                    'status' => $doc->status,
                ];
            });

        $risalahs = Risalah::where('status', 'pending')
            ->where(function ($q) use ($userId) {
                $q->where('pemimpin_acara_user_id', $userId)
                    ->orWhere('notulis_acara_user_id', $userId);
            })
            ->get()
            ->map(function ($doc) {
                return (object) [
                    'id' => $doc->id_risalah,
                    'judul' => $doc->judul ?? 'Dokumen tidak ditemukan',
                    'tgl_dokumen' => $doc->updated_at ?? $doc->tgl_disahkan ?? $doc->tgl_dibuat,
                    'tipe' => 'risalah',
                    'status' => $doc->status,
                ];
            });

        return collect()
            ->merge(collect($memos))
            ->merge(collect($undangans))
            ->merge(collect($risalahs))
            ->sortByDesc('tgl_dokumen')
            ->values();
    }

    function apiResponse($data, $message = '', $status = 'success')
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data_count' => is_countable($data) ? count($data) : 1,
            'data' => $data,
        ]);
    }
}
