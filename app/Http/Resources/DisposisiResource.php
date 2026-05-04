<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisposisiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dokumen = $this->dokumen;

        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'document_id' => $this->document_id,
            'judul_dokumen' => $this->judul_dokumen ?? $dokumen?->judul ?? '-',

            'dari_user_id' => $this->dari_user_id,
            'dari_user' => $this->whenLoaded('dariUser', fn () => [
                'id' => $this->dariUser?->id,
                'nama' => trim(($this->dariUser?->firstname ?? '') . ' ' . ($this->dariUser?->lastname ?? '')),
            ]),

            'kepada_user_id' => $this->kepada_user_id,
            'kepada_users' => $this->kepadaUsers()
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'nama' => trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')),
                ])
                ->values(),

            'instruksi' => $this->instruksi,
            'catatan' => $this->catatan,
            'deadline' => $this->deadline,
            'status' => $this->status,
            'dibaca' => (bool) $this->dibaca,

            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
