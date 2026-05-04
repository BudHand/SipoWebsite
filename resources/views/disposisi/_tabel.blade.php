{{-- resources/views/disposisi/_tabel.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Dokumen</th>
                @if ($mode === 'masuk')
                    <th>Dari</th>
                @else
                    <th>Kepada</th>
                @endif
                <th>Instruksi</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @forelse($data as $item)
                @php
                    $kepadaUsers = $item->kepadaUsers();
                    $kepadaText = $kepadaUsers
                        ->map(fn($u) => trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? '')))
                        ->filter()
                        ->join(', ');
                @endphp

                <tr class="{{ $mode === 'masuk' && !$item->sudahDibaca() ? 'fw-semibold' : '' }}">
                    <td>
                        <span class="badge bg-secondary me-1">{{ $item->label_tipe }}</span>
                        {{ $item->judul_dokumen }}

                        @if ($item->nomor_dokumen)
                            <br>
                            <small class="text-muted">{{ $item->nomor_dokumen }}</small>
                        @endif
                    </td>

                    <td>
                        @if ($mode === 'masuk')
                            {{ $item->dariUser?->firstname }} {{ $item->dariUser?->lastname }}
                        @else
                            <span title="{{ $kepadaText }}">
                                {{ Str::limit($kepadaText ?: '-', 45) }}
                            </span>

                            @if ($kepadaUsers->count() > 1)
                                <br>
                                <small class="text-muted">
                                    {{ $kepadaUsers->count() }} penerima
                                </small>
                            @endif
                        @endif
                    </td>

                    <td>
                        <span class="text-truncate d-inline-block" style="max-width:200px" title="{{ $item->instruksi }}">
                            {{ $item->instruksi }}
                        </span>
                    </td>

                    <td>
                        @if ($item->deadline)
                            <span
                                class="{{ $item->deadline->isPast() && $item->status !== 'selesai' ? 'text-danger' : '' }}">
                                {{ $item->deadline->translatedFormat('d M Y') }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td>
                        <span class="badge {{ $item->badge_status }}">
                            {{ $item->label_status }}
                        </span>
                    </td>

                    <td>
                        <small class="text-muted">
                            {{ $item->created_at->diffForHumans() }}
                        </small>
                    </td>

                    <td>
                        <a href="{{ route('disposisi.show', $item) }}" class="btn btn-sm btn-outline-primary">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Tidak ada disposisi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
