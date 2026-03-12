<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\NotifApiController;
use App\Models\{Memo, Arsip,};
use App\Http\Resources\MemoResource;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Notifikasi;
use App\Models\Kirim_Document;
use Illuminate\Support\Facades\Auth;
use App\Services\QrCodeService;
use App\Models\CounterNomorSurat;
use App\Models\Seri;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MemoApiController extends Controller
{
    // GET /api/memos
    public function index(Request $request)
    {
        // eager load user
        $user = Auth::user();

        $memoDiarsipkan = Arsip::where('user_id', $user->id)
            ->where('jenis_document', 'App\Models\Memo')
            ->pluck('document_id')->toArray();

        $ownedDocs = Kirim_Document::where('jenis_document', 'memo')
            ->where(function ($q) use ($user) {
                $q->where('id_penerima', $user->id)
                    ->orWhere('id_pengirim', $user->id);
            })
            ->pluck('id_document')->unique()->toArray();

        // eager load user
        $query = Memo::with('user')->whereNotIn('id_memo', $memoDiarsipkan)->whereIn('id_memo', $ownedDocs)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('kode') && $request->kode !== 'pilih') {
            $query->where('kode', $request->kode);
        }

        $memos = $query->get();

        return MemoResource::collection($memos)->additional([
            'status' => 'success',
            'message' => $memos->isEmpty() ? 'Belum ada memo' : 'Daftar memo ditemukan',
        ]);
    }
    public function kodeFilter()
    {
        $kode = Memo::whereNotNull('kode')
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $kode,
        ], 200);
    }
    public function getAll()
    {
        $memos = Memo::with('user')->latest()->get();

        return MemoResource::collection($memos)->additional([
            'status' => 'success',
            'message' => $memos->isEmpty() ? 'Belum ada memo' : 'Daftar memo ditemukan',
        ]);
    }
    // GET /api/memos/{id}
    public function show($id)
    {
        // $memo = Memo::find($id);
        $memo = Memo::with('user')->findOrFail($id);

        if (!$memo) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Memo tidak ditemukan',
                ],
                404,
            );
        }

        return new MemoResource($memo);
    }

    public function updateStatus(Request $request, $id)
    {
        $push = new NotifApiController();

        try {
            $user = Auth::user();

            $request->validate([
                'status'  => 'required|in:approve,reject,correction',
                'catatan' => $request->status !== 'approve' ? 'required|string' : 'nullable|string',
            ]);

            return DB::transaction(function () use ($request, $id, $user, $push) {

                $memo = Memo::lockForUpdate()->findOrFail($id);

                // Ambil "kirim_document" yang sedang diproses oleh user ini (jika ada)
                $currentKirim = Kirim_Document::where('id_document', $memo->id_memo)
                    ->where('jenis_document', 'memo')
                    ->where('id_penerima', $user->id)
                    ->first();

                // Update status di memo
                $memo->status  = $request->status;
                $memo->catatan = $request->catatan ?? null;

                // Update status kirim_document milik user yang approve/reject/correction (jika ada)
                if ($currentKirim) {
                    $currentKirim->status = $request->status;
                    $currentKirim->save();
                } else {
                    // Kalau belum ada record kirim untuk user ini, buat supaya jejak persetujuan tercatat
                    Kirim_Document::create([
                        'id_document'    => $memo->id_memo,
                        'jenis_document' => 'memo',
                        'id_pengirim'    => $memo->pembuat,
                        'id_penerima'    => $user->id,
                        'status'         => $request->status,
                        'updated_at'     => now(),
                    ]);
                }

                // Helper: kode div/dept untuk kebutuhan nomor surat (biar konsisten sama web)
                $userDivDeptKode = app(\App\Http\Controllers\MemoController::class)
                    ->getDivDeptKode($user);

                if ($request->status === 'approve') {
                    // Kalau sudah approve sebelumnya, jangan proses ulang (hindari dobel kirim/notif)
                    if ($memo->getOriginal('status') === 'approve') {
                        return response()->json([
                            'status'  => 'success',
                            'message' => 'Memo sudah berstatus approve.',
                        ], 200);
                    }

                    $tglDisahkan = now();
                    $memo->tgl_disahkan = $tglDisahkan;

                    // ==========================
                    // GENERATE NOMOR SURAT (CounterNomorSurat)
                    // ==========================
                    if (empty($memo->nomor_memo) || stripos((string) $memo->nomor_memo, 'DRAFT') !== false) {

                        $bulanRomawi = \App\Models\CounterNomorSurat::getBulanRomawi($tglDisahkan->month);

                        try {
                            $counter = \App\Models\CounterNomorSurat::createNomorSurat([
                                'tanggal_permintaan' => $tglDisahkan,
                                'perusahaan'         => 'REKA',
                                'kode_tipe_surat'    => 'GEN',
                                'divisi'             => $memo->kode_bagian ?? $userDivDeptKode,
                                'bulan'              => $bulanRomawi,
                                'tahun'              => $tglDisahkan->year,
                                'pic_peminta'        => $user->fullname,
                                'jenis'              => 'Memo',
                                'perihal'            => $memo->judul,
                            ]);

                            $memo->nomor_memo = $counter->nomor_surat_generated;
                        } catch (\Throwable $e) {
                            Log::error('Generate nomor surat (API) gagal: ' . $e->getMessage());

                            // fallback format lama
                            $nextSeri = \App\Models\Seri::getNextSeri(false);

                            $memo->nomor_memo = sprintf(
                                '%02d.%02d/REKA/GEN/%s/%s/%d',
                                $nextSeri['seri_tahunan'],
                                $nextSeri['seri_bulanan'],
                                strtoupper($userDivDeptKode),
                                $bulanRomawi,
                                $tglDisahkan->year
                            );
                        }
                    }

                    // ==========================
                    // QR Code (pakai nomor memo terbaru)
                    // ==========================
                    $qrText = 'Disetujui oleh: ' . $user->firstname . ' ' . $user->lastname
                        . "\nNomor Memo: " . ($memo->nomor_memo ?? '-')
                        . "\nTanggal: " . $tglDisahkan->translatedFormat('l, d F Y H:i:s')
                        . "\nDikeluarkan oleh Website SIPO PT Rekaindo Global Jasa";

                    try {
                        $qrService = new QrCodeService();
                        $memo->qr_approved_by = $qrService->generateWithLogo($qrText);
                    } catch (\Throwable $e) {
                        Log::error('Generate QR Code (API) gagal: ' . $e->getMessage());
                    }

                    // Simpan memo dulu sebelum kirim/notif
                    $memo->save();

                    // ==========================
                    // Kirim ke tujuan + notif (hindari dobel)
                    // ==========================
                    $tujuanArray = array_values(array_filter(array_map('trim', explode(';', (string) $memo->tujuan))));
                    $tujuanArray = array_unique($tujuanArray);

                    foreach ($tujuanArray as $tujuanId) {
                        // skip kalau tujuan sama dengan pembuat / approver (opsional, sesuaikan kebutuhanmu)
                        if ((int) $tujuanId === (int) $memo->pembuat) {
                            // tetap boleh dikirim kalau kamu mau; ini hanya pencegahan spam
                            // continue;
                        }

                        // upsert kirim_document supaya tidak dobel
                        Kirim_Document::updateOrCreate(
                            [
                                'id_document'    => $memo->id_memo,
                                'jenis_document' => 'memo',
                                'id_pengirim'    => $memo->pembuat,
                                'id_penerima'    => (int) $tujuanId,
                            ],
                            [
                                'status'     => 'approve',
                                'updated_at' => now(),
                            ]
                        );

                        Notifikasi::create([
                            'judul'          => 'Memo Masuk',
                            'judul_document' => $memo->judul,
                            'id_user'        => (int) $tujuanId,
                            'updated_at'     => now(),
                        ]);

                        $push->sendToUser((int) $tujuanId, 'Memo Masuk', $memo->judul);
                    }

                    // Notif ke pembuat
                    Notifikasi::create([
                        'judul'          => 'Memo Disetujui',
                        'judul_document' => $memo->judul,
                        'id_user'        => $memo->pembuat,
                        'updated_at'     => now(),
                    ]);

                    $push->sendToUser($memo->pembuat, 'Memo Disetujui', $memo->judul);

                    return response()->json([
                        'status'  => 'success',
                        'message' => 'Status dokumen berhasil diperbarui (approve).',
                        'data'    => [
                            'id_memo'    => $memo->id_memo,
                            'nomor_memo' => $memo->nomor_memo,
                            'status'     => $memo->status,
                        ],
                    ], 200);
                }

                if ($request->status === 'reject') {
                    $memo->tgl_disahkan = now();
                    $memo->save();

                    Notifikasi::create([
                        'judul'          => 'Memo Ditolak',
                        'judul_document' => $memo->judul,
                        'id_user'        => $memo->pembuat,
                        'updated_at'     => now(),
                    ]);

                    $push->sendToUser($memo->pembuat, 'Memo Ditolak', $memo->judul);

                    return response()->json([
                        'status'  => 'success',
                        'message' => 'Status dokumen berhasil diperbarui (reject).',
                    ], 200);
                }

                // correction
                if ($request->status === 'correction') {
                    $memo->tgl_disahkan = null; // biasanya correction belum disahkan
                    $memo->save();

                    Notifikasi::create([
                        'judul'          => 'Memo Perlu Revisi',
                        'judul_document' => $memo->judul,
                        'id_user'        => $memo->pembuat,
                        'updated_at'     => now(),
                    ]);

                    $push->sendToUser($memo->pembuat, 'Memo Perlu Revisi', $memo->judul);

                    return response()->json([
                        'status'  => 'success',
                        'message' => 'Status dokumen berhasil diperbarui (correction).',
                    ], 200);
                }

                // fallback (seharusnya tidak kena karena validasi)
                $memo->save();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Status dokumen berhasil diperbarui.',
                ], 200);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
