<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\NotificationLabelService;

class NotifResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $resolved = app(NotificationLabelService::class)
            ->resolve($this->resource, $request->user());

        return [
            'id_notifikasi' => $this->id_notifikasi,
            'judul' => $this->judul,
            'judul_document' => $this->judul_document,

            'label' => $resolved['label'],
            'tipe_document' => $resolved['tipe_document'],
            'id_document' => $resolved['id_document'],

            'dibaca' => (bool) $this->dibaca,
            'id_user' => $this->id_user,
            'updated_at' => $this->updated_at,
        ];
    }
}
