<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\User;
use App\Models\Undangan;
use App\Models\Kirim_Document;
use App\Models\Risalah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KirimApiController extends Controller {
    public function index($id) {
        $memo = Memo::find($id);
        $undangan = Undangan::find($id);
        $risalah = Risalah::find($id);

        if (!$memo && !$undangan && !$risalah) {
            return response()->json(['error' => 'Dokumen tidak ditemukan.'], 404);
        }

        $divisi = Divisi::all();
        $position = Position::all();
        $user = User::whereIn('role_id_role', ['2', '3'])->get();  
        $userId = Auth::id();

        if ($memo) {
        $status = $memo->divisi_id_divisi === Auth::user()->divisi_id_divisi
            ? $memo->status
            : optional(Kirim_Document::where('id_document', $memo->id_memo)
                    ->where('jenis_document', 'memo')
                    ->where('id_penerima', $userId)
                    ->first())->status ?? '-';

            return response()->json([
                'type' => 'memo',
                'data' => $memo,
                'status' => $status,
                'divisi' => $divisi,
                'position' => $position,
                'users' => $user
            ]); 
        }

        if ($undangan) {
            $status = $undangan->divisi_id_divisi === Auth::user()->divisi_id_divisi
                ? $undangan->status
                : optional(Kirim_Document::where('id_document', $undangan->id_undangan)
                        ->where('jenis_document', 'undangan')
                        ->where('id_penerima', $userId)
                        ->first())->status ?? '-';

            return response()->json([
                'type' => 'undangan',
                'data' => $undangan,
                'status' => $status,
                'divisi' => $divisi,
                'position' => $position,
                'users' => $user
            ]);
        }

        if ($risalah) {
            $status = $risalah->divisi_id_divisi === Auth::user()->divisi_id_divisi
                ? $risalah->status
                : optional(Kirim_Document::where('id_document', $risalah->id_risalah)
                        ->where('jenis_document', 'risalah')
                        ->where('id_penerima', $userId)
                        ->first())->status ?? '-';

            return response()->json([
                'type' => 'risalah',
                'data' => $risalah,
                'status' => $status,
                'divisi' => $divisi,
                'position' => $position,
                'users' => $user
            ]);
        }
    }

    public function viewManager($type, $id)
    {
        $divisi = Divisi::all();
        $position = Position::all();
        $user = User::whereIn('role_id_role', ['2', '3'])->get();  
        $userId = Auth::id();

        switch ($type) {
            case 'memo':
                $doc = Memo::find($id);
                $docIdName = 'id_memo';
                break;
            case 'undangan':
                $doc = Undangan::find($id);
                $docIdName = 'id_undangan';
                break;
            case 'risalah':
                $doc = Risalah::find($id);
                $docIdName = 'id_risalah';
                break;
            default:
                return response()->json(['error' => 'Jenis dokumen tidak valid.'], 400);
        }

        if (!$doc) {
            return response()->json(['error' => 'Dokumen tidak ditemukan.'], 404);
        }

        $status = $doc->divisi_id_divisi === Auth::user()->divisi_id_divisi
            ? $doc->status
            : optional(Kirim_Document::where('id_document', $doc->$docIdName)
                    ->where('jenis_document', $type)
                    ->where('id_penerima', $userId)
                    ->first())->status ?? '-';

        return response()->json([
            'type' => $type,
            'data' => $doc,
            'status' => $status,
            'divisi' => $divisi,
            'position' => $position,
            'users' => $user
        ]);
    }

    public function sendDocument(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'id_document' => 'required',
            'posisi_penerima' => 'required|exists:position,id_position', // Validasi posisi
            'divisi_penerima' => 'required|exists:divisi,id_divisi', // Pastikan divisi ada
            'jenis_document' => 'required|in:memo,undangan,risalah',
        ]);
        

        $documentid = $request->id_document;
        $posisiPenerima = $request->posisi_penerima;
        $divisiPenerima = $request->divisi_penerima;
    
        // Cari semua user dengan posisi dan divisi yang dipilih
        $penerimaUsers = User::where('position_id_position', $posisiPenerima)
                              ->where('divisi_id_divisi', $divisiPenerima)
                              ->get();
    
        if ($penerimaUsers->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada user yang sesuai dengan kriteria penerima.'
            ], 404);
        }

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileData = base64_encode(file_get_contents($file->getRealPath()));
            // Simpan file base64 ke tabel sesuai jenis dokumen
            // Simpan ke model dokumen sesuai jenis
            switch ($request->jenis_document) {
                case 'memo':
                    $memo = Memo::findOrFail($documentid);
                    $memo->lampiran = $fileData;
                    $memo->save();
                    break;
                case 'undangan':
                    $undangan = Undangan::findOrFail($documentid);
                    $undangan->lampiran = $fileData;
                    $undangan->save();
                    break;
                case 'risalah':
                    $risalah = Risalah::findOrFail($documentid);
                    $risalah->lampiran = $fileData;
                    $risalah->save();
                    break;
            }
        }
    
        // Simpan pengiriman undangan ke setiap penerima
        foreach ($penerimaUsers as $user) {
            Log::info('Mengirim dokumen ke user', [
                'document_id' => $documentid,
                'jenis_document' => $request->jenis_document,
                'id_pengirim' => Auth::id(),
                'id_penerima' => $user->id,
                'status' => 'pending',
            ]);

            $result = Kirim_Document::create([
                'id_document' => $documentid,
                'jenis_document' => $request->jenis_document,
                'id_pengirim' => Auth::id(),
                'id_penerima' => $user->id,
                'status' => 'pending',
            ]);

            Log::info('Berhasil menyimpan kirim_document', ['id' => $result->id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Dokumen berhasil dikirim.',
            'total_penerima' => $penerimaUsers->count()
        ]);
    }

    // API: Daftar dokumen yang dikirim oleh user yang sedang login
    public function sentDocuments()
    {
        $documents = Kirim_Document::where('id_pengirim', Auth::id())->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar dokumen yang dikirim',
            'data' => $documents
        ]);
    }

    // ambil data undangan untuk manager
    // public function Undangan(Request $request)
    // {
    //     $user = auth()->user();
    //     $userId = $user->id;

    //     $undangans = Kirim_Document::where('jenis_document', 'undangan')
    //         ->where('id_penerima', $userId)
    //         ->where('status', 'pending')
    //         ->whereHas('undangan')
    //         ->with('undangan') // Pastikan relasi 'undangan' ada di model Kirim_Document
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Data undangan berhasil diambil',
    //         'data' => $undangans
    //     ]);
    // }

        public function Undangan(Request $request)
{
    $user = auth()->user();
    $userId = $user->id;

    $kiriman = Kirim_Document::where('jenis_document', 'undangan')
        ->where('id_penerima', $userId)
        ->where('status', 'pending')
        ->whereHas('undangan')
        ->with('undangan') // relasi harus sudah ada
        ->get();

    // Transformasi agar Android dapet final_status
    $data = $kiriman->map(function ($item) {
        $undangan = $item->undangan;
        $undangan->final_status = $item->status; // Inject manual
        return $undangan;
    });

    return response()->json([
        'success' => true,
        'message' => 'Data undangan berhasil diambil',
        'data' => $data
    ]);
}

    public function Risalah(Request $request)
{
    $user = auth()->user();
    $userId = $user->id;

    $kiriman = Kirim_Document::where('jenis_document', 'risalah')
        ->where('id_penerima', $userId)
        ->where('status', 'pending')
        ->whereHas('risalah')
        ->with('risalah') // relasi harus sudah ada
        ->get();

    // Transformasi agar Android dapet final_status
    $data = $kiriman->map(function ($item) {
        $risalah = $item->risalah;
        $risalah->final_status = $item->status; // Inject manual
        return $risalah;
    });

    return response()->json([
        'success' => true,
        'message' => 'Data risalah berhasil diambil',
        'data' => $data
    ]);
}

    public function view($id)
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $userId = $user->id;

        $undangan = Undangan::where('id_undangan', $id)
            ->with('divisi') // jika kamu perlu relasi divisi
            ->firstOrFail();

        // Cek apakah user berasal dari divisi yang sama
        if ($undangan->divisi_id_divisi === $user->divisi_id_divisi) {
            $undangan->final_status = $undangan->status;
        } else {
            // Ambil status dari tabel kirim_document
            $statusKirim = Kirim_Document::where('id_document', $undangan->id_undangan)
                ->where('jenis_document', 'undangan')
                ->where('id_penerima', $userId)
                ->first();

            $undangan->final_status = $statusKirim ? $statusKirim->status : '-';
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail undangan berhasil diambil',
            'data' => $undangan
        ]);
    }

    public function updateStatus(Request $request, $type, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:approve,reject,pending',
                'catatan' => 'nullable|string',
            ]);

            $user = Auth::user();
            $userId = $user->id;
            $userDivisiId = $user->divisi_id_divisi;

            // Identifikasi model dan primary key
            switch ($type) {
                case 'memo':
                    $doc = Memo::findOrFail($id);
                    $idKey = 'id_memo';
                    break;
                case 'undangan':
                    $doc = Undangan::findOrFail($id);
                    $idKey = 'id_undangan';
                    break;
                case 'risalah':
                    $doc = Risalah::findOrFail($id);
                    $idKey = 'id_risalah';
                    break;
                default:
                    return response()->json(['error' => 'Jenis dokumen tidak valid.'], 400);
            }

            $kirim = Kirim_Document::where('id_document', $doc->$idKey)
                ->where('jenis_document', $type)
                ->where('id_penerima', $userId)
                ->first();

            if ($userDivisiId == $doc->divisi_id_divisi) {

                $doc->status = $request->status;
                $doc->catatan = $request->catatan;

                if ($request->status === 'approve') {
                    $doc->tgl_disahkan = now();
                    $qrText = "Disetujui oleh: " . $user->firstname . ' ' . $user->lastname . "\nTanggal: " . now()->translatedFormat('l, d F Y');
                    $qrImage = \QrCode::format('svg')->generate($qrText);
                    $doc->qr_approved_by = base64_encode($qrImage);
                } elseif ($request->status === 'reject') {
                    $doc->tgl_disahkan = now();
                } else {
                    $doc->tgl_disahkan = null;
                }
                $doc->save();

                if ($kirim) {
                    $kirim->status = $request->status;
                    $kirim->updated_at = now();
                    $kirim->save();
                }
            } else {
                if ($kirim) {
                    $kirim->status = $request->status;
                    $kirim->updated_at = now();
                    $kirim->save();

                    Kirim_Document::where('id_document', $doc->$idKey)
                        ->where('jenis_document', $type)
                        ->where('id_penerima', $kirim->id_pengirim)
                        ->where('status', 'pending')
                        ->update([
                            'status' => $request->status,
                            'updated_at' => now()
                        ]);
                } else {
                    Log::warning("Kirim document tidak ditemukan untuk user non-divisi pemilik.");
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Status dokumen berhasil diperbarui.',
                'data' => [
                    'undangan' => $doc,
                    'kirim' => $kirim,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Exception updateStatus: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}