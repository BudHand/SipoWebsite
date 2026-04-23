<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_memo' => $this->id_memo,
            'judul' => $this->judul,
            'isi_memo' => $this->isi_memo,
            'status' => $this->status,
            'nomor_memo' => $this->nomor_memo,

            // ===== TUJUAN =====
            'tujuan' => $this->tujuan ? explode(';', $this->tujuan) : [],
            'tujuan_string' => $this->tujuan_string ? explode(';', $this->tujuan_string) : [],

            // ===== TEMBUSAN =====
            'tembusan' => $this->tembusan ? explode(';', $this->tembusan) : [],

            // ===== BCC =====
            'bcc' => $this->bcc ? explode(';', $this->bcc) : [],

            // ===== INFO USER =====
            'pembuat' => $this->pembuat,
            'nama_pembuat' => optional($this->pembuatUser)->fullname,

            'manager_user_id' => $this->manager_user_id,
            'nama_approver' => optional($this->approver)->fullname,

            // ===== PENANDATANGAN =====
            'nama_bertandatangan' => $this->nama_bertandatangan,

            // ===== DIVISI & BAGIAN =====
            'divisi' => optional($this->divisi)->nama_divisi,
            'kode_bagian' => $this->kode_bagian,
            'nama_bagian' => optional($this->bagianKerja)->nama_bagian ?? null,

            // ===== TANGGAL =====
            'tgl_dibuat' => $this->tgl_dibuat,
            'tgl_disahkan' => $this->tgl_disahkan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // ===== LAMPIRAN =====
            'lampiran_url' => $this->lampiran
                ? url('/api/memos/' . $this->id_memo . '/lampiran')
                : null,

            // optional kalau mau semua lampiran
            'lampirans' => $this->whenLoaded('lampirans', function () {
                return $this->lampirans->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_file' => $item->nama_file ?? null,
                    ];
                });
            }),

            // ===== LAINNYA =====
            'kode' => $this->kode,
            'catatan' => $this->catatan,
            'feedback' => $this->feedback,
        ];
    }
}
