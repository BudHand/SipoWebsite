<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Arsip, Kirim_Document, Undangan, Memo, Risalah, Notifikasi};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use PhpParser\Node\Expr\AssignOp\Concat;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $archivedMemo = Arsip::where('user_id', $user->id)->where('jenis_document', 'App\Models\Memo')->pluck('document_id')->toArray();

        $memoCount = Kirim_Document::where('jenis_document', 'memo')
            ->where(function ($query) use ($user) {
                $query->where('id_pengirim', $user->id)->orWhere('id_penerima', $user->id);
            })
            ->whereNotIn('id_document', $archivedMemo)
            ->select('id_document')
            ->groupBy('id_document')
            ->get()
            ->count();

        $archivedUndangan = Arsip::where('user_id', $user->id)->where('jenis_document', 'App\Models\Undangan')->pluck('document_id')->toArray();

        $undanganCount = Kirim_Document::where('jenis_document', 'undangan')
            ->where(function ($query) use ($user) {
                $query->where('id_pengirim', $user->id)->orWhere('id_penerima', $user->id);
            })
            ->whereNotIn('id_document', $archivedUndangan)
            ->select('id_document')
            ->groupBy('id_document')
            ->get()
            ->count();

        $archivedRisalah = Arsip::where('user_id', $user->id)->where('jenis_document', 'App\Models\Risalah')->pluck('document_id')->toArray();

        $risalahCount = Kirim_Document::where('jenis_document', 'risalah')
            ->where(function ($query) use ($user) {
                $query->where('id_pengirim', $user->id)->orWhere('id_penerima', $user->id);
            })
            ->whereNotIn('id_document', $archivedRisalah)
            ->select('id_document')
            ->groupBy('id_document')
            ->get()
            ->count();

        $now = Carbon::now();
        // $ownedUndangan = Kirim_Document::where('id_penerima', $user->id)->orWhere('id_pengirim', $user->id)->where('jenis_document', 'undangan')->pluck('id_document');

        $undangan = Undangan::visibleTo($user->id)
            ->where('status', 'approve')
            ->whereDate('tgl_rapat', '>=', $now->toDateString())
            ->selectRaw('undangan.*, DATEDIFF(tgl_rapat, ?) as selisih_hari', [$now->toDateString()])
            ->orderByRaw('selisih_hari ASC')
            ->limit(5)
            ->get();

        foreach ($undangan as $u) {
            $u->waktu = $u->waktu_mulai . ' - ' . $u->waktu_selesai;
        }

        // ===== RECENT DOCS WAITING APPROVAL -- LOGIC LAMA ======
        // $recentDocs = Kirim_Document::where('id_penerima', $user->id)->limit(10)->orderBy('id_kirim_document', 'desc')->where('status', 'pending')->get();

        // // ganti menggunakan tabel masing-masing dokumen, tidak dengan kirim_document
        // foreach ($recentDocs as $d) {
        //     switch ($d->jenis_document) {
        //         case 'memo':
        //             $doc = Memo::find($d->id_document);
        //             $d->id = $doc ? $doc->id_memo : null;
        //             $d->judul = $doc ? $doc->judul : 'Dokumen tidak ditemukan';
        //             $d->tgl_dokumen = $doc ? $doc->updated_at ?? $doc->tgl_dibuat : null;
        //             $d->tipe = 'memo';
        //             break;
        //         case 'undangan':
        //             $doc = Undangan::find($d->id_document);
        //             $d->id = $doc ? $doc->id_undangan : null;
        //             $d->judul = $doc ? $doc->judul : 'Dokumen tidak ditemukan';
        //             $d->tgl_dokumen = $doc ? $doc->tgl_rapat ?? $doc->tgl_dibuat : null;
        //             $d->tipe = 'undangan';
        //             break;
        //         case 'risalah':
        //             $doc = Risalah::find($d->id_document);
        //             $d->id = $doc ? $doc->id_risalah : null;
        //             $d->judul = $doc ? $doc->judul : 'Dokumen tidak ditemukan';
        //             $d->tgl_dokumen = $doc ? $doc->updated_at ?? $doc->tgl_dibuat : null;
        //             $d->tipe = 'risalah';
        //             break;
        //         default:
        //             $d->judul = 'Dokumen tidak ditemukan';
        //             $d->tgl_dokumen = null;
        //             break;
        //     }
        // }

        // JUMLAH MEMO
        $jumlahMemoKeluar = $this->countMemoKeluar($user, $archivedMemo);
        $jumlahMemoMasuk  = $this->countMemoMasuk($user, $archivedMemo);

        // JUMLAH UNDANGAN
        $jumlahUndanganKeluar = $this->countUndanganKeluar($user, $archivedUndangan);
        $jumlahUndanganMasuk  = $this->countUndanganMasuk($user, $archivedUndangan);

        // JUMLAH RISALAH
        $jumlahRisalah = $this->countRisalahTerkait($user, $archivedRisalah);

        $recentDocs = $this->waitingApproval($user);

        $fullname = $user->firstname . ' ' . $user->lastname;

        return response()->json([
            'status' => 'success',
            'data' => [
                'fullname' => $fullname,
                'memo_count' => $memoCount,
                'memo_keluar_count' => $jumlahMemoKeluar,
                'memo_masuk_count' => $jumlahMemoMasuk,
                'risalah_count' => $jumlahRisalah,
                'undangan_count' => $undanganCount,
                'undangan_keluar_count' => $jumlahUndanganKeluar,
                'undangan_masuk_count' => $jumlahUndanganMasuk,
                'undangan' => $undangan,
                'recent_docs' => $recentDocs,
            ],
        ]);
    }

    private function countMemoKeluar($user, array $arsipIds): int
    {
        $kodeBagianUser = collect(explode(';', (string) $user->kode_bagian))
            ->map(fn ($kode) => trim($kode))
            ->filter()
            ->unique()
            ->values();

        if ($kodeBagianUser->isEmpty()) {
            return 0;
        }

        $query = Memo::query()
            ->whereNotIn('id_memo', $arsipIds)
            ->where(function ($q) use ($kodeBagianUser) {
                foreach ($kodeBagianUser as $kodeBagian) {
                    $q->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(COALESCE(kode_bagian, ''), ';', ','))",
                        [$kodeBagian]
                    );
                }
            });

        if ((int) $user->role_id_role === 2) {
            $query->where('pembuat', $user->id);
        }

        return (int) $query->count();
    }

    private function countMemoMasuk($user, array $arsipIds): int
    {
        $uid = (string) $user->id;

        return (int) Memo::query()
            ->where('status', 'approve')
            ->where(function ($q) use ($uid) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
            })
            ->whereNotIn('id_memo', $arsipIds)
            ->count();
    }

    private function countUndanganKeluar($user, array $arsipIds): int
    {
        $kodeBagianUser = collect(explode(';', (string) $user->kode_bagian))
            ->map(fn ($kode) => trim($kode))
            ->filter()
            ->unique()
            ->values();

        if ($kodeBagianUser->isEmpty()) {
            return 0;
        }

        $query = Undangan::query()
            ->whereNotIn('id_undangan', $arsipIds)
            ->where(function ($q) use ($kodeBagianUser) {
                foreach ($kodeBagianUser as $kodeBagian) {
                    $q->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(COALESCE(kode_bagian, ''), ';', ','))",
                        [$kodeBagian]
                    );
                }
            });

        if ((int) $user->role_id_role === 2) {
            $query->where('pembuat', $user->id);
        }

        return (int) $query->count();
    }

    private function countUndanganMasuk($user, array $arsipIds): int
    {
        $uid = (string) $user->id;

        return (int) Undangan::query()
            ->whereNotIn('id_undangan', $arsipIds)
            ->where('status', 'approve')
            ->where(function ($q) use ($uid) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(tembusan, ''), ';', ','))", [$uid])
                    ->orWhereRaw("FIND_IN_SET(?, REPLACE(COALESCE(bcc, ''), ';', ','))", [$uid]);
            })
            ->count();
    }

    private function countRisalahTerkait($user, array $arsipIds): int
    {
        $uid = (string) $user->id;

        return (int) Risalah::query()
            ->whereNotIn('id_risalah', $arsipIds)
            ->where(function ($q) use ($user, $uid) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('status', '!=', 'approve')
                        ->where(function ($x) use ($user) {
                            $x->where('pembuat', $user->id)
                                ->orWhere('pemimpin_acara_user_id', $user->id)
                                ->orWhere('notulis_acara_user_id', $user->id);
                        });
                })
                ->orWhere(function ($sub) use ($user, $uid) {
                    $sub->where('status', 'approve')
                        ->where(function ($x) use ($user, $uid) {
                            $x->where('pembuat', $user->id)
                                ->orWhere('pemimpin_acara_user_id', $user->id)
                                ->orWhere('notulis_acara_user_id', $user->id)
                                ->orWhereRaw(
                                    "FIND_IN_SET(?, REPLACE(COALESCE(tujuan, ''), ';', ','))",
                                    [$uid]
                                );
                        });
                });
            })
            ->count();
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

        return $memos
            ->merge($undangans)
            ->merge($risalahs)
            ->sortByDesc('tgl_dokumen')
            ->take(10)
            ->values();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
