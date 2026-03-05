<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Http\Controllers\Api\NotifApiController;

class NotifService
{
    public function createAndPush(int $userId, string $judul, ?string $judulDocument = null) : Notifikasi
    {
        // Simpan / update notifikasi DB
        $notif = Notifikasi::updateOrCreate(
            [
                'id_user' => $userId,
                'judul' => $judul,
                'judul_document' => $judulDocument,
            ],
            [
                'dibaca' => 0,
                'updated_at' => now()
            ]
        );

        // Push notification
        $push = app(NotifApiController::class);

        $push->sendToUser(
            $userId,
            $judul,
            $judulDocument ?? 'Ada pembaruan dokumen'
        );

        return $notif;
    }
}
