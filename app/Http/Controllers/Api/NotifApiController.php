<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Notifikasi, NotifTokenModel};
use App\Http\Resources\NotifResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifApiController extends Controller
{
    protected string $expoUrl = 'https://exp.host/--/api/v2/push/send';

    // ======================================================
    // GET: Daftar Notifikasi User
    // ======================================================
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        // Pastikan kolom updated_at memang ada di tabel notifikasi
        // Kalau ada -> orderBy(updated_at)
        // Kalau tidak ada -> fallback ke id_notifikasi
        $q = Notifikasi::where('id_user', $user->id);

        // aman: coba order by updated_at, kalau error DB kamu akan ketahuan di log
        // (umumnya tabel notifikasi memang punya kolom updated_at)
        $notifications = $q->orderBy('updated_at', 'desc')->get();

        $notificationsFilter = collect(NotifResource::collection($notifications)->resolve())
            ->filter(fn($notif) => ($notif['id_document'] ?? null) !== null)
            ->values();

        [$unread, $read] = $notificationsFilter->partition(fn($n) => ($n['dibaca'] ?? false) === false);

        return response()->json([
            'status' => true,
            'message' => 'Daftar notifikasi',
            'data' => [
                'unread' => $unread->values(),
                'read' => $read->values(),
            ],
        ]);
    }

    // ======================================================
    // GET: Jumlah Notifikasi Belum Dibaca
    // ======================================================
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'count' => 0], 401);
        }

        $count = Notifikasi::where('id_user', $user->id)
            ->where('dibaca', 0)
            ->count();

        return response()->json(['status' => true, 'count' => $count]);
    }

    // ======================================================
    // PATCH/POST: Tandai Sebagai Dibaca (AMAN)
    // ======================================================
    public function markAsRead($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        // ✅ penting: pastikan notif milik user ini
        $notification = Notifikasi::where('id_notifikasi', $id)
            ->where('id_user', $user->id)
            ->first();

        if (!$notification) {
            return response()->json(['status' => false, 'message' => 'Notifikasi tidak ditemukan'], 404);
        }

        if ((int)$notification->dibaca === 1) {
            return response()->json(['status' => true, 'message' => 'Sudah dibaca']);
        }

        $notification->dibaca = 1;

        // jika model timestamps=false, updated_at tidak auto keisi
        if (property_exists($notification, 'timestamps') && $notification->timestamps === false) {
            $notification->updated_at = now();
        }

        $notification->save();

        return response()->json(['status' => true, 'message' => 'Berhasil ditandai sebagai dibaca']);
    }

    // ======================================================
    // PATCH/POST: Tandai Semua Sebagai Dibaca
    // ======================================================
    public function markAllAsRead()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $updated = Notifikasi::where('id_user', $user->id)
            ->where('dibaca', 0)
            ->update([
                'dibaca' => 1,
                'updated_at' => now(), // tetap update agar sorting di mobile benar
            ]);

        return response()->json([
            'status' => true,
            'message' => "Semua notifikasi ($updated) ditandai sebagai dibaca",
        ]);
    }

    // ======================================================
    // POST: Simpan Token Notifikasi
    // ======================================================
    public function saveToken(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string|in:android,ios',
        ]);

        // hilangkan token duplikat device lain
        NotifTokenModel::where('token', $request->token)->delete();

        $notifToken = NotifTokenModel::updateOrCreate(
            [
                'id_user' => $user->id,
                'platform' => $request->platform,
            ],
            [
                'token' => $request->token,
            ],
        );

        return response()->json([
            'status' => true,
            'message' => 'Token berhasil disimpan',
            'data' => $notifToken,
        ]);
    }

    // ======================================================
    // HELPER: Kirim Notifikasi ke user (BALIKKAN ARRAY, bukan response json)
    // ======================================================
    public function sendToUser(int $id_user, string $title, string $body, array $data = []): array
    {
        $tokens = NotifTokenModel::where('id_user', $id_user)->pluck('token')->toArray();

        if (empty($tokens)) {
            return [
                'ok' => false,
                'message' => 'Token tidak ditemukan',
                'tokens' => [],
                'expo_response' => null,
            ];
        }

        $messages = collect($tokens)->map(function ($token) use ($title, $body, $data) {
            return [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => array_merge(['click_action' => 'OPEN_APP'], $data),
            ];
        })->toArray();

        try {
            $response = Http::asJson()
                ->timeout(10)
                ->post($this->expoUrl, $messages);

            $json = $response->json();

            Log::info('📬 Notifikasi Expo Dikirim', [
                'user_id' => $id_user,
                'tokens' => $tokens,
                'expo_response' => $json,
                'http_status' => $response->status(),
            ]);

            return [
                'ok' => $response->successful(),
                'message' => $response->successful()
                    ? '✅ Notifikasi berhasil dikirim'
                    : '⚠️ Expo merespon tapi status bukan 2xx',
                'tokens' => $tokens,
                'expo_response' => $json,
                'http_status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('❌ Gagal kirim notif (Expo)', [
                'user_id' => $id_user,
                'tokens' => $tokens,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => '❌ Gagal mengirim notifikasi',
                'tokens' => $tokens,
                'expo_response' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ======================================================
    // TEST: Kirim Notifikasi Manual (via token atau id_user)
    // ======================================================
    public function tesNotif(Request $request)
    {
        $id_user = $request->input('id_user');
        $token = $request->input('token');

        if ($id_user && !$token) {
            $tokens = NotifTokenModel::where('id_user', $id_user)->pluck('token')->toArray();
        } elseif ($token) {
            $tokens = [$token];
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Harap sertakan id_user atau token untuk dites.',
            ]);
        }

        if (empty($tokens)) {
            return response()->json([
                'status' => false,
                'message' => 'Token device tidak ditemukan untuk user ini.',
            ]);
        }

        try {
            $messages = collect($tokens)->map(fn($t) => [
                'to' => $t,
                'sound' => 'default',
                'title' => '🔔 Tes Notifikasi dari SIPO',
                'body' => 'Halo Master, ini pesan dari server Expo!',
                'data' => ['customKey' => 'example'],
            ])->toArray();

            $response = Http::asJson()->timeout(10)->post($this->expoUrl, $messages);

            return response()->json([
                'status' => true,
                'message' => '✅ Notifikasi berhasil dikirim (Expo)',
                'tokens' => $tokens,
                'expo_response' => $response->json(),
                'http_status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Gagal kirim tes notif (Expo)', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => '❌ Gagal mengirim notifikasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ======================================================
    // POST: Simpan Token Manual
    // ======================================================
    public function saveTokenManual(Request $request)
    {
        $request->validate([
            'id_user' => 'required|integer|exists:users,id',
            'token' => 'required|string',
            'platform' => 'nullable|string|in:android,ios',
        ]);

        $notifToken = NotifTokenModel::updateOrCreate(
            ['id_user' => $request->id_user],
            [
                'token' => $request->token,
                'platform' => $request->platform ?? 'android',
            ],
        );

        return response()->json([
            'status' => true,
            'message' => 'Token manual berhasil disimpan',
            'data' => $notifToken,
        ]);
    }

    // ======================================================
    // GET: Cek Apakah Ada Notifikasi Belum Dibaca
    // ======================================================
    public function notifAvailable()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $notif = Notifikasi::where('id_user', $user->id)
            ->where('dibaca', 0)
            ->exists();

        return response()->json([
            'status' => $notif,
        ]);
    }
}
