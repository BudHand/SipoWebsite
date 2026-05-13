<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DisposisiResource;
use App\Models\Disposisi;
use App\Models\Memo;
use App\Models\Notifikasi;
use App\Models\Undangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DisposisiApiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $filter = $request->get('status');
        $perPage = (int) $request->get('per_page', 15);

        $masuk = Disposisi::masuk($user->id)
            ->with(['dariUser'])
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->latest()
            ->paginate($perPage, ['*'], 'masuk_page');

        $keluar = Disposisi::keluar($user->id)
            ->with(['dariUser'])
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->latest()
            ->paginate($perPage, ['*'], 'keluar_page');

        $belumDibaca = Disposisi::masuk($user->id)
            ->belumDibaca()
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Daftar disposisi',
            'data' => [
                'belum_dibaca' => $belumDibaca,
                'masuk_count' => $masuk->total(),
                'keluar_count' => $keluar->total(),
                'masuk' => [
                    'data' => DisposisiResource::collection($masuk->items()),
                    'current_page' => $masuk->currentPage(),
                    'last_page' => $masuk->lastPage(),
                    'per_page' => $masuk->perPage(),
                    'total' => $masuk->total(),
                ],
                'keluar' => [
                    'data' => DisposisiResource::collection($keluar->items()),
                    'current_page' => $keluar->currentPage(),
                    'last_page' => $keluar->lastPage(),
                    'per_page' => $keluar->perPage(),
                    'total' => $keluar->total(),
                ],
            ],
        ]);
    }

    public function show(Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$this->userTerlibatDalamRantai($disposisi, (int) $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses ke disposisi ini.',
            ], 403);
        }

        if ($disposisi->adalahPenerima((int) $user->id)) {
            $disposisi->tandaiDibaca();
        }

        $disposisi->load([
            'dariUser',
            'parent.dariUser',
            'allChildren.dariUser',
        ]);

        $dokumen = $disposisi->dokumen;
        $kandidat = collect();

        if ($dokumen && $disposisi->adalahPenerima((int) $user->id) && $disposisi->bisaDiubah()) {
            $kandidat = $dokumen->kandidatPenerimaDispo($user)
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'nama' => trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? '')),
                ])
                ->values();
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail disposisi',
            'data' => [
                'disposisi' => new DisposisiResource($disposisi),
                'dokumen' => $dokumen ? [
                    'id' => $this->getDokumenId($disposisi->document_type, $dokumen),
                    'judul' => $dokumen->judul ?? '-',
                    'tipe' => $disposisi->document_type,
                ] : null,
                'kandidat_penerima' => $kandidat,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(['memo', 'undangan'])],
            'document_id' => ['required', 'integer'],
            'kepada_user_id' => ['required', 'array', 'min:1'],
            'kepada_user_id.*' => ['integer', 'distinct', 'exists:users,id'],
            'instruksi' => ['required', 'string', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $kepadaUserIds = array_values(array_unique(array_map('intval', $validated['kepada_user_id'])));

        if (in_array((int) $user->id, $kepadaUserIds, true)) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak bisa mendisposisi ke diri sendiri.',
            ], 422);
        }

        $dokumen = $this->findDokumen($validated['document_type'], (int) $validated['document_id']);

        if (!$dokumen) {
            return response()->json([
                'status' => false,
                'message' => 'Dokumen tidak ditemukan.',
            ], 404);
        }

        if (!$dokumen->bisaDisposisi($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $kandidatIds = $dokumen->kandidatPenerimaDispo($user)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $penerimaTidakValid = collect($kepadaUserIds)
            ->reject(fn ($id) => $kandidatIds->contains($id));

        if ($penerimaTidakValid->isNotEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Ada penerima yang tidak valid untuk dokumen ini.',
            ], 422);
        }

        $disposisi = Disposisi::create([
            'document_type' => $validated['document_type'],
            'document_id' => $validated['document_id'],
            'dari_user_id' => $user->id,
            'kepada_user_id' => $kepadaUserIds,
            'instruksi' => $validated['instruksi'],
            'catatan' => $validated['catatan'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'status' => 'menunggu',
        ]);

        $this->kirimNotifDisposisi($disposisi, $kepadaUserIds, 'Disposisi Baru');

        return response()->json([
            'status' => true,
            'message' => 'Disposisi berhasil dikirim.',
            'data' => new DisposisiResource($disposisi),
        ], 201);
    }

    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$disposisi->adalahPenerima((int) $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Hanya penerima yang bisa mengubah status disposisi.',
            ], 403);
        }

        if (!$disposisi->bisaDiubah()) {
            return response()->json([
                'status' => false,
                'message' => 'Status disposisi sudah tidak bisa diubah.',
            ], 422);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['diterima', 'selesai'])],
        ]);

        $disposisi->update([
            'status' => $validated['status'],
        ]);

        $judulNotif = $validated['status'] === 'diterima'
            ? 'Disposisi Diterima'
            : 'Disposisi Selesai';

        $this->kirimNotifDisposisi($disposisi, [(int) $disposisi->dari_user_id], $judulNotif);

        return response()->json([
            'status' => true,
            'message' => $validated['status'] === 'diterima'
                ? 'Disposisi berhasil diterima.'
                : 'Disposisi berhasil diselesaikan.',
            'data' => new DisposisiResource($disposisi->fresh()),
        ]);
    }

    public function teruskan(Request $request, Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$disposisi->adalahPenerima((int) $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Hanya penerima yang bisa meneruskan disposisi.',
            ], 403);
        }

        if (!$disposisi->bisaDiubah()) {
            return response()->json([
                'status' => false,
                'message' => 'Disposisi ini sudah tidak bisa diteruskan.',
            ], 422);
        }

        $dokumen = $disposisi->dokumen;

        if (!$dokumen) {
            return response()->json([
                'status' => false,
                'message' => 'Dokumen sumber tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'kepada_user_id' => ['required', 'array', 'min:1'],
            'kepada_user_id.*' => ['integer', 'distinct', 'exists:users,id'],
            'instruksi' => ['required', 'string', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $kepadaUserIds = array_values(array_unique(array_map('intval', $validated['kepada_user_id'])));

        if (in_array((int) $user->id, $kepadaUserIds, true)) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak bisa meneruskan ke diri sendiri.',
            ], 422);
        }

        $kandidatIds = $dokumen->kandidatPenerimaDispo($user)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $penerimaTidakValid = collect($kepadaUserIds)
            ->reject(fn ($id) => $kandidatIds->contains($id));

        if ($penerimaTidakValid->isNotEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Ada penerima yang tidak valid untuk penerusan disposisi ini.',
            ], 422);
        }

        $disposisi->update([
            'status' => 'diteruskan',
        ]);

        $disposisiBaru = Disposisi::create([
            'document_type' => $disposisi->document_type,
            'document_id' => $disposisi->document_id,
            'dari_user_id' => $user->id,
            'kepada_user_id' => $kepadaUserIds,
            'instruksi' => $validated['instruksi'],
            'catatan' => $validated['catatan'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'status' => 'menunggu',
            'parent_id' => $disposisi->id,
        ]);

        $this->kirimNotifDisposisi($disposisiBaru, $kepadaUserIds, 'Disposisi Diteruskan');

        return response()->json([
            'status' => true,
            'message' => 'Disposisi berhasil diteruskan.',
            'data' => new DisposisiResource($disposisiBaru),
        ], 201);
    }

    public function cariDokumen(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'tipe' => ['required', Rule::in(['memo', 'undangan'])],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $uid = (string) $user->id;
        $tipe = $request->get('tipe');
        $q = trim($request->get('q', ''));

        if ($tipe === 'memo') {
            $query = Memo::query();
            $nomorKolom = 'nomor_memo';
            $pkKolom = 'id_memo';
        } else {
            $query = Undangan::query();
            $nomorKolom = 'nomor_undangan';
            $pkKolom = 'id_undangan';
        }

        $dokumen = $query
            ->where('status', 'approve')
            ->where(function ($sub) use ($uid) {
                $sub->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
            })
            ->when($q !== '', function ($query) use ($q, $nomorKolom) {
                $query->where(function ($sub) use ($q, $nomorKolom) {
                    $sub->where('judul', 'like', "%{$q}%")
                        ->orWhere($nomorKolom, 'like', "%{$q}%");
                });
            })
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->{$pkKolom},
                'judul' => $item->judul ?? '-',
                'nomor' => $item->{$nomorKolom} ?? '-',
                'tgl_dibuat' => optional($item->tgl_dibuat)->format('d/m/Y'),
                'document_type' => $tipe,
            ])
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Daftar dokumen',
            'data' => $dokumen,
        ]);
    }

    public function lihatDokumen(Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$this->userTerlibatDalamRantai($disposisi, (int) $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses ke dokumen ini.',
            ], 403);
        }

        $dokumen = $disposisi->dokumen;

        if (!$dokumen) {
            return response()->json([
                'status' => false,
                'message' => 'Dokumen tidak ditemukan.',
            ], 404);
        }

        if ($disposisi->document_type === 'memo') {
            $url = route('view-memoPDF', $dokumen->id_memo);
        } elseif ($disposisi->document_type === 'undangan') {
            $url = route('view-undanganPDF', $dokumen->id_undangan);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Tipe dokumen tidak valid.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'URL dokumen',
            'data' => [
                'url' => $url,
            ],
        ]);
    }

    private function kirimNotifDisposisi(Disposisi $disposisi, array $userIds, string $judul): void
    {
        foreach (array_unique($userIds) as $userId) {
            $userId = (int) $userId;

            if ($userId <= 0) {
                continue;
            }

            Notifikasi::create([
                'judul' => $judul,
                'judul_document' => $disposisi->judul_dokumen ?? 'Dokumen Disposisi',
                'id_user' => $userId,
                'id_document' => $disposisi->id,
                'jenis_document' => 'disposisi',
                'dibaca' => false,
                'updated_at' => now(),
            ]);
        }
    }

    private function findDokumen(string $type, int $id): Memo|Undangan|null
    {
        return match ($type) {
            'memo' => Memo::find($id),
            'undangan' => Undangan::find($id),
            default => null,
        };
    }

    private function getDokumenId(string $type, $dokumen): ?int
    {
        return match ($type) {
            'memo' => $dokumen->id_memo ?? null,
            'undangan' => $dokumen->id_undangan ?? null,
            default => null,
        };
    }

    private function userTerlibatDalamRantai(Disposisi $disposisi, int $userId): bool
    {
        if ($disposisi->dari_user_id === $userId || $disposisi->adalahPenerima($userId)) {
            return true;
        }

        $parent = $disposisi->parent;

        while ($parent) {
            if ($parent->dari_user_id === $userId || $parent->adalahPenerima($userId)) {
                return true;
            }

            $parent = $parent->parent;
        }

        return $this->userAdaDiChildren($disposisi->children, $userId);
    }

    private function userAdaDiChildren($children, int $userId): bool
    {
        foreach ($children as $child) {
            if ($child->dari_user_id === $userId || $child->adalahPenerima($userId)) {
                return true;
            }

            if ($child->children->isNotEmpty() && $this->userAdaDiChildren($child->children, $userId)) {
                return true;
            }
        }

        return false;
    }

    public function kandidatPenerima(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(['memo', 'undangan'])],
            'document_id' => ['required', 'integer'],
        ]);

        $dokumen = $this->findDokumen(
            $validated['document_type'],
            (int) $validated['document_id']
        );

        if (!$dokumen) {
            return response()->json([
                'status' => false,
                'message' => 'Dokumen tidak ditemukan.',
            ], 404);
        }

        if (!$dokumen->bisaDisposisi($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $kandidat = $dokumen->kandidatPenerimaDispo($user)
            ->map(fn ($u) => [
                'id' => $u->id,
                'nama' => trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? '')),
            ])
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Daftar kandidat penerima disposisi',
            'data' => $kandidat,
        ]);
    }
}
