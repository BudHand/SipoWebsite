<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\Risalah;
use App\Models\Undangan;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotifController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(
                    [
                        'notifications' => [],
                        'message' => 'Unauthorized',
                    ],
                    401,
                );
            }

            $role = (int) $user->role_id_role;

            $notifications = Notifikasi::query()
                ->where('id_user', $user->id)
                ->orderByDesc('id_notifikasi')
                ->get()
                ->map(function ($notification) use ($role) {
                    return $this->transformNotification($notification, $role);
                })
                ->values();

            return response()->json([
                'notifications' => $notifications,
            ]);
        } catch (\Throwable $e) {
            Log::error('Notif index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'notifications' => [],
                    'message' => 'Gagal memuat notifikasi',
                ],
                500,
            );
        }
    }

    public function getUnreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(
                    [
                        'count' => 0,
                    ],
                    401,
                );
            }

            $count = Notifikasi::query()->where('id_user', $user->id)->where('dibaca', 0)->count();

            return response()->json([
                'count' => $count,
            ]);
        } catch (\Throwable $e) {
            Log::error('Notif getUnreadCount error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'count' => 0,
                ],
                500,
            );
        }
    }

    public function markAsRead(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Unauthorized',
                    ],
                    401,
                );
            }

            $notification = Notifikasi::query()->where('id_user', $user->id)->where('id_notifikasi', $id)->first();

            if (!$notification) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Notifikasi tidak ditemukan',
                    ],
                    404,
                );
            }

            if ((int) $notification->dibaca === 0) {
                $notification->update([
                    'dibaca' => 1,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai dibaca',
            ]);
        } catch (\Throwable $e) {
            Log::error('Notif markAsRead error: ' . $e->getMessage(), [
                'notification_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menandai notifikasi',
                ],
                500,
            );
        }
    }

    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Unauthorized',
                    ],
                    401,
                );
            }

            Notifikasi::query()
                ->where('id_user', $user->id)
                ->where('dibaca', 0)
                ->update([
                    'dibaca' => 1,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil ditandai dibaca',
            ]);
        } catch (\Throwable $e) {
            Log::error('Notif markAllAsRead error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menandai semua notifikasi',
                ],
                500,
            );
        }
    }

    private function transformNotification(Notifikasi $notification, int $role): array
    {
        $judulNotif = strtolower((string) ($notification->judul ?? ''));

        $type = 'unknown';
        $documentId = $notification->id_document ?? null;
        $redirectUrl = '#';

        if ($notification->jenis_document === 'App\Models\Memo' || str_contains($judulNotif, 'memo')) {
            $memo = $documentId ? Memo::find($documentId) : null;

            if ($memo) {
                if (
                    str_contains($judulNotif, 'memo masuk') ||
                    str_contains($judulNotif, 'masuk') ||
                    str_contains($judulNotif, 'disetujui') ||
                    str_contains($judulNotif, 'setujui') ||
                    str_contains($judulNotif, 'approve') ||
                    str_contains($judulNotif, 'approved')
                ) {
                    $type = 'memo-diterima';
                } elseif (
                    str_contains($judulNotif, 'ditolak') ||
                    str_contains($judulNotif, 'tolak') ||
                    str_contains($judulNotif, 'diupdate') ||
                    str_contains($judulNotif, 'update') ||
                    str_contains($judulNotif, 'berhasil di update') ||
                    str_contains($judulNotif, 'berhasil diupdate') ||
                    str_contains($judulNotif, 'revisi') ||
                    str_contains($judulNotif, 'terkirim') ||
                    str_contains($judulNotif, 'dikirim')
                ) {
                    $type = 'memo-terkirim';
                } else {
                    $type = 'memo-terkirim';
                }
            } else {
                $type = 'memo-null';
            }
        } elseif ($notification->jenis_document === 'App\Models\Undangan' || str_contains($judulNotif, 'undangan')) {
            $undangan = $documentId ? Undangan::find($documentId) : null;
            $type = $undangan ? 'undangan' : 'undangan-null';
        } elseif ($notification->jenis_document === 'App\Models\Risalah' || str_contains($judulNotif, 'risalah')) {
            $risalah = $documentId ? Risalah::find($documentId) : null;
            $type = $risalah ? 'risalah' : 'risalah-null';
        }

        if ($role === 2) {
            $redirectUrl = $this->resolveAdminUrl($type, $documentId);
        } elseif ($role === 3) {
            $redirectUrl = $this->resolveManagerUrl($type, $documentId);
        } elseif ($role === 1) {
            $redirectUrl = $this->resolveSuperadminUrl($type, $documentId);
        }

        return [
            'id_notifikasi' => $notification->id_notifikasi,
            'judul' => $notification->judul ?? 'Tanpa judul',
            'judul_document' => $notification->judul_document ?? '-',
            'dibaca' => (int) ($notification->dibaca ?? 0),
            'updated_at' => $notification->updated_at,
            'redirect_url' => $redirectUrl ?: '#',
        ];
    }

    private function resolveAdminUrl(string $type, ?int $documentId): string
    {
        if ($documentId) {
            return match ($type) {
                'memo-terkirim' => route('view.memo-terkirim', $documentId),
                'memo-diterima' => route('view.memo-diterima', $documentId),
                'undangan' => route('view.undangan', $documentId),
                'risalah' => route('risalah.view', $documentId),
                default => '#',
            };
        }

        return match ($type) {
            'memo-null', 'memo-terkirim' => route('memo.terkirim'),
            'memo-diterima' => route('memo.diterima'),
            'undangan-null' => route('undangan.terkirim'),
            'risalah-null' => route('risalah.index'),
            default => '#',
        };
    }

    private function resolveManagerUrl(string $type, ?int $documentId): string
    {
        if ($documentId) {
            return match ($type) {
                'memo-terkirim' => route('view.memo-terkirim', $documentId),
                'memo-diterima' => route('view.memo-diterima', $documentId),
                'undangan' => route('view.undangan', $documentId),
                'risalah' => route('risalah.view', $documentId),
                default => '#',
            };
        }

        return match ($type) {
            'memo-null', 'memo-terkirim' => route('memo.terkirim'),
            'memo-diterima' => route('memo.diterima'),
            'undangan-null' => route('undangan.terkirim'),
            'risalah-null' => route('risalah.index'),
            default => '#',
        };
    }

    private function resolveSuperadminUrl(string $type, ?int $documentId): string
    {
        if ($documentId) {
            return match ($type) {
                'memo' => route('superadmin.memo.index'),
                'undangan' => route('superadmin.undangan.index'),
                'risalah' => route('superadmin.risalah.index'),
                default => '#',
            };
        }

        return match ($type) {
            'memo-null' => route('superadmin.memo.index'),
            'undangan-null' => route('superadmin.undangan.index'),
            'risalah-null' => route('superadmin.risalah.index'),
            default => '#',
        };
    }
}
