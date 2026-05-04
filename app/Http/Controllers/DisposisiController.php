<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Memo;
use App\Models\Notifikasi;
use App\Models\Undangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DisposisiController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $tab    = $request->get('tab', 'masuk');
        $filter = $request->get('status');

        $masuk = Disposisi::masuk($user->id)
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->latest()
            ->paginate(15, ['*'], 'masuk_page');

        $keluar = Disposisi::keluar($user->id)
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->latest()
            ->paginate(15, ['*'], 'keluar_page');

        $belumDibaca = Disposisi::masuk($user->id)->belumDibaca()->count();

        return view('disposisi.index', compact('masuk', 'keluar', 'tab', 'filter', 'belumDibaca'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'document_type' => ['required', Rule::in(['memo', 'undangan'])],
            'document_id'   => ['required', 'integer'],
        ]);

        $user         = Auth::user();
        $documentType = $request->document_type;
        $documentId   = (int) $request->document_id;

        $dokumen = $this->findDokumen($documentType, $documentId);

        if (!$dokumen) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        if (!$dokumen->bisaDisposisi($user)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $kandidat = $dokumen->kandidatPenerimaDispo($user);

        return view('disposisi.create', compact('dokumen', 'documentType', 'documentId', 'kandidat'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'document_type'    => ['required', Rule::in(['memo', 'undangan'])],
            'document_id'      => ['required', 'integer'],
            'kepada_user_id'   => ['required', 'array', 'min:1'],
            'kepada_user_id.*' => ['integer', 'distinct', 'exists:users,id'],
            'instruksi'        => ['required', 'string', 'max:2000'],
            'catatan'          => ['nullable', 'string', 'max:1000'],
            'deadline'         => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $kepadaUserIds = array_values(array_unique(array_map('intval', $validated['kepada_user_id'])));

        if (in_array((int) $user->id, $kepadaUserIds, true)) {
            return back()
                ->withErrors(['kepada_user_id' => 'Tidak bisa mendisposisi ke diri sendiri.'])
                ->withInput();
        }

        $dokumen = $this->findDokumen($validated['document_type'], (int) $validated['document_id']);

        if (!$dokumen) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        if (!$dokumen->bisaDisposisi($user)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $kandidatIds = $dokumen->kandidatPenerimaDispo($user)->pluck('id')->map(fn ($id) => (int) $id);

        $penerimaTidakValid = collect($kepadaUserIds)
            ->reject(fn ($id) => $kandidatIds->contains($id));

        if ($penerimaTidakValid->isNotEmpty()) {
            return back()
                ->withErrors(['kepada_user_id' => 'Ada penerima yang tidak valid untuk dokumen ini.'])
                ->withInput();
        }

        $disposisiBaru = Disposisi::create([
            'document_type'  => $validated['document_type'],
            'document_id'    => $validated['document_id'],
            'dari_user_id'   => $user->id,
            'kepada_user_id' => $kepadaUserIds,
            'instruksi'      => $validated['instruksi'],
            'catatan'        => $validated['catatan'] ?? null,
            'deadline'       => $validated['deadline'] ?? null,
            'status'         => 'menunggu',
        ]);

        $this->kirimNotifDisposisi(
            $disposisiBaru,
            $kepadaUserIds,
            'Disposisi Baru'
        );

        return redirect()
            ->route('disposisi.index')
            ->with('success', 'Disposisi berhasil dikirim.');
    }

    public function show(Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$this->userTerlibatDalamRantai($disposisi, (int) $user->id)) {
            abort(403);
        }

        if ($disposisi->adalahPenerima((int) $user->id)) {
            $disposisi->tandaiDibaca();
        }

        $disposisi->load([
            'dariUser',
            'parent.dariUser',
            'allChildren.dariUser',
        ]);

        $disposisi->load('allChildren.allChildren.allChildren');

        $dokumen     = $disposisi->dokumen;
        $kepadaUsers = $disposisi->kepadaUsers();
        $kandidat    = collect();

        if ($dokumen && $disposisi->adalahPenerima((int) $user->id) && $disposisi->bisaDiubah()) {
            $kandidat = $dokumen->kandidatPenerimaDispo($user);
        }

        return view('disposisi.show', compact('disposisi', 'dokumen', 'kandidat', 'kepadaUsers'));
    }

    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$disposisi->adalahPenerima((int) $user->id)) {
            abort(403, 'Hanya penerima yang bisa mengubah status disposisi.');
        }

        if (!$disposisi->bisaDiubah()) {
            return back()->with('error', 'Status disposisi sudah tidak bisa diubah.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['diterima', 'selesai'])],
        ]);

        $disposisi->update([
            'status' => $validated['status'],
        ]);

        $label = $validated['status'] === 'diterima' ? 'diterima' : 'diselesaikan';

        $judulNotif = $validated['status'] === 'diterima'
            ? 'Disposisi Diterima'
            : 'Disposisi Selesai';

        $this->kirimNotifDisposisi(
            $disposisi,
            [(int) $disposisi->dari_user_id],
            $judulNotif
        );

        return back()->with('success', "Disposisi berhasil {$label}.");
    }

    public function teruskan(Request $request, Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$disposisi->adalahPenerima((int) $user->id)) {
            abort(403, 'Hanya penerima yang bisa meneruskan disposisi.');
        }

        if (!$disposisi->bisaDiubah()) {
            return back()->with('error', 'Disposisi ini sudah tidak bisa diteruskan.');
        }

        $dokumen = $disposisi->dokumen;

        if (!$dokumen) {
            abort(404, 'Dokumen sumber tidak ditemukan.');
        }

        $validated = $request->validate([
            'kepada_user_id'   => ['required', 'array', 'min:1'],
            'kepada_user_id.*' => ['integer', 'distinct', 'exists:users,id'],
            'instruksi'        => ['required', 'string', 'max:2000'],
            'catatan'          => ['nullable', 'string', 'max:1000'],
            'deadline'         => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $kepadaUserIds = array_values(array_unique(array_map('intval', $validated['kepada_user_id'])));

        if (in_array((int) $user->id, $kepadaUserIds, true)) {
            return back()
                ->withErrors(['kepada_user_id' => 'Tidak bisa meneruskan ke diri sendiri.'])
                ->withInput();
        }

        $kandidatIds = $dokumen->kandidatPenerimaDispo($user)->pluck('id')->map(fn ($id) => (int) $id);

        $penerimaTidakValid = collect($kepadaUserIds)
            ->reject(fn ($id) => $kandidatIds->contains($id));

        if ($penerimaTidakValid->isNotEmpty()) {
            return back()
                ->withErrors(['kepada_user_id' => 'Ada penerima yang tidak valid untuk penerusan disposisi ini.'])
                ->withInput();
        }

        $disposisi->update([
            'status' => 'diteruskan',
        ]);

        $disposisiBaru = Disposisi::create([
            'document_type'  => $disposisi->document_type,
            'document_id'    => $disposisi->document_id,
            'dari_user_id'   => $user->id,
            'kepada_user_id' => $kepadaUserIds,
            'instruksi'      => $validated['instruksi'],
            'catatan'        => $validated['catatan'] ?? null,
            'deadline'       => $validated['deadline'] ?? null,
            'status'         => 'menunggu',
            'parent_id'      => $disposisi->id,
        ]);

        $this->kirimNotifDisposisi(
            $disposisiBaru,
            $kepadaUserIds,
            'Disposisi Diteruskan'
        );

        return redirect()
            ->route('disposisi.index')
            ->with('success', 'Disposisi berhasil diteruskan.');
    }

    public function cariDokumen(Request $request)
    {
        $request->validate([
            'tipe' => ['required', Rule::in(['memo', 'undangan'])],
            'q'    => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $uid  = (string) $user->id;
        $tipe = $request->get('tipe');
        $q    = trim($request->get('q', ''));

        if ($tipe === 'memo') {
            $query      = Memo::query();
            $nomorKolom = 'nomor_memo';
            $pkKolom    = 'id_memo';
        } else {
            $query      = Undangan::query();
            $nomorKolom = 'nomor_undangan';
            $pkKolom    = 'id_undangan';
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
                'id'            => $item->{$pkKolom},
                'judul'         => $item->judul ?? '-',
                'nomor'         => $item->{$nomorKolom} ?? '-',
                'tgl_dibuat'    => optional($item->tgl_dibuat)->format('d/m/Y'),
                'url_disposisi' => route('disposisi.create', [
                    'document_type' => $tipe,
                    'document_id'   => $item->{$pkKolom},
                ]),
            ]);

        return response()->json([
            'dokumen' => $dokumen,
        ]);
    }

    public function lihatDokumen(Disposisi $disposisi)
    {
        $user = Auth::user();

        if (!$this->userTerlibatDalamRantai($disposisi, (int) $user->id)) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $dokumen = $disposisi->dokumen;

        if (!$dokumen) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        if ($disposisi->document_type === 'memo') {
            return redirect()->route('view-memoPDF', $dokumen->id_memo);
        }

        if ($disposisi->document_type === 'undangan') {
            return redirect()->route('view-undanganPDF', $dokumen->id_undangan);
        }

        abort(404);
    }

    private function kirimNotifDisposisi(Disposisi $disposisi, array $userIds, string $judul): void
    {
        foreach (array_unique($userIds) as $userId) {
            $userId = (int) $userId;

            if ($userId <= 0) {
                continue;
            }

            Notifikasi::create([
                'judul'          => $judul,
                'judul_document' => $disposisi->judul_dokumen ?? 'Dokumen Disposisi',
                'id_user'        => $userId,
                'id_document'    => $disposisi->id,
                'jenis_document' => 'disposisi',
                'dibaca'         => false,
                'updated_at'     => now(),
            ]);
        }
    }

    private function findDokumen(string $type, int $id): Memo|Undangan|null
    {
        return match ($type) {
            'memo'     => Memo::find($id),
            'undangan' => Undangan::find($id),
            default    => null,
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
}
