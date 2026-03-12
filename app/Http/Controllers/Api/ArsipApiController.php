<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Arsip;
use App\Models\Undangan;
use App\Models\Risalah;
use App\Models\RisalahDetail;


class ArsipApiController extends Controller
{
    public function indexMemo()
    {
        $user_id = Auth::id();

        $arsipMemo = Arsip::where('user_id', $user_id)
            ->where('jenis_document', 'App\Models\Memo')
            ->get();

        $memoIds = $arsipMemo->pluck('document_id');

        // Ambil semua memo yang dibutuhkan + relasi
        $memoMap = Memo::with('divisi') // pastikan relasi 'divisi' ada
            ->whereIn('id_memo', $memoIds)
            ->get()
            ->keyBy('id_memo');

        // Gabungkan memo ke setiap arsip
        foreach ($arsipMemo as $arsip) {
            $arsip->document = $memoMap[$arsip->document_id] ?? null;
        }

        return response()->json($arsipMemo);
    }

    
    public function indexUndangan()
    {
        $user_id = Auth::id();

        $arsipUndangan = Arsip::where('user_id', $user_id)
            ->where('jenis_document', 'App\Models\Undangan')
            ->get();

        $undanganIds = $arsipUndangan->pluck('document_id');

        $undanganMap = Undangan::with('divisi')
            ->whereIn('id_undangan', $undanganIds)
            ->get()
            ->keyBy('id_undangan');

        foreach ($arsipUndangan as $arsip) {
            $arsip->document = $undanganMap[$arsip->document_id] ?? null;
        }

        return response()->json($arsipUndangan);
    }

    public function indexRisalah()
    {
        $user_id = Auth::id();

        $arsipRisalah = Arsip::where('user_id', $user_id)
            ->where('jenis_document', 'App\Models\Risalah')
            ->get();

        $risalahIds = $arsipRisalah->pluck('document_id');

        $risalahMap = Risalah::with('divisi', 'risalahDetails')
            ->whereIn('id_risalah', $risalahIds)
            ->get()
            ->keyBy('id_risalah');

        foreach ($arsipRisalah as $arsip) {
            $arsip->document = $risalahMap[$arsip->document_id] ?? null;
        }

        return response()->json($arsipRisalah);
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_id' => 'required|integer',
            'jenis_document' => 'required|string',
        ]);
        $userid=Auth::user()->id;

        $jenis = $request->jenis_document;
        if ($jenis === 'App\Models\Memo') {
            // Menggunakan primary key 'id_memo'
            $doc = Memo::find($request->document_id);
        } elseif ($jenis === 'App\Models\Undangan') {
            // Menggunakan primary key 'id_undangan'
            $doc = Undangan::find($request->document_id);
        } elseif ($jenis === 'App\Models\Risalah') {
            // Menggunakan primary key 'id_risalah'
            $doc = Risalah::find($request->document_id);
        } else {
            return response()->json(['message' => 'Jenis dokumen tidak valid'], 400);
        }

        if (!$doc) {
            return response()->json(['message' => 'Dokumen tidak ditemukan'], 404);
        }

        // Menyimpan arsip
        $arsip = new Arsip();
        $arsip->user_id = $userid;
        $arsip->document_id = $request->document_id;
        $arsip->jenis_document = $jenis;
        $arsip->save();

        return response()->json(['message' => 'Dokumen berhasil diarsipkan']);
    }

    // Route: POST /api/arsip/restore
    public function restore(Request $request)
    {
        $request->validate([
            'document_id' => 'required|integer',
            'jenis_document' => 'required|string',
        ]);

        $user_id = Auth::id();
        $modelClass = "App\\Models\\" . ucfirst($request->jenis_document);


        // Hapus arsip hanya untuk user yang login
        $deleted = Arsip::where('user_id', $user_id)
            ->where('document_id', $request->document_id)
            ->where('jenis_document', $modelClass)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => ucfirst($request->jenis_document) . ' berhasil dikembalikan!']);
        } else {
            return response()->json(['message' => 'Dokumen tidak ditemukan dalam arsip'], 404);
        }
    }


    

    public function showByUser()
    {
        $user_id=Auth::user()->id;
        // Menampilkan arsip berdasarkan user_id
        $arsip = Arsip::where('user_id', $user_id)->get();
        
        if ($arsip->isEmpty()) {
            return response()->json(['message' => 'Dokumen arsip tidak ditemukan untuk user ini'], 404);
        }

        return response()->json($arsip);
    }
}
