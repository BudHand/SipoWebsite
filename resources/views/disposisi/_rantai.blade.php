{{-- resources/views/disposisi/_rantai.blade.php --}}
@foreach($items as $item)
    @php
        $kepadaUsers = $item->kepadaUsers();
        $kepadaText = $kepadaUsers
            ->map(fn ($u) => trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? '')))
            ->filter()
            ->join(', ');
    @endphp

    <div class="d-flex align-items-start p-3 border-bottom"
         style="padding-left: {{ ($level * 24) + 16 }}px !important">
        @if($level > 0)
            <span class="text-muted me-2" style="font-size:18px">&#8618;</span>
        @endif

        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <span class="fw-semibold">
                        {{ $item->dariUser?->firstname }} {{ $item->dariUser?->lastname }}
                    </span>

                    <span class="text-muted mx-1">→</span>

                    <span>
                        {{ $kepadaText ?: '-' }}
                    </span>
                </div>

                <span class="badge {{ $item->badge_status }} ms-2">
                    {{ $item->label_status }}
                </span>
            </div>

            <div class="text-muted small mt-1">
                {{ Str::limit($item->instruksi, 100) }}
            </div>

            <div class="d-flex gap-3 mt-1">
                <small class="text-muted">
                    {{ $item->created_at->diffForHumans() }}
                </small>

                @if($item->deadline)
                    <small class="{{ $item->deadline->isPast() && $item->status !== 'selesai' ? 'text-danger' : 'text-muted' }}">
                        Deadline: {{ $item->deadline->translatedFormat('d M Y') }}
                    </small>
                @endif

                <a href="{{ route('disposisi.show', $item) }}" class="ms-auto small">
                    Detail
                </a>
            </div>
        </div>
    </div>

    @if($item->allChildren()->exists())
        @include('disposisi._rantai', [
            'items' => $item->allChildren,
            'level' => $level + 1
        ])
    @endif
@endforeach
