{{-- resources/views/disposisi/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Disposisi')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    .show-wrap * { font-family: 'Plus Jakarta Sans', sans-serif; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .anim-1 { animation: fadeUp .35s ease both; }
    .anim-2 { animation: fadeUp .35s .08s ease both; }
    .anim-3 { animation: fadeUp .35s .16s ease both; }
    .anim-4 { animation: fadeUp .35s .24s ease both; }
    .anim-5 { animation: fadeUp .35s .32s ease both; }

    /* ── Select2 override ── */
    .show-wrap .select2-container--default .select2-selection--single {
        height: 40px;
        border-radius: .5rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        align-items: center;
        padding: 0 10px;
        transition: border-color .15s, box-shadow .15s;
    }
    .show-wrap .select2-container--default.select2-container--open .select2-selection--single,
    .show-wrap .select2-container--default .select2-selection--single:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,.15);
        outline: none;
    }
    .show-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b; font-size: 13px; padding: 0; line-height: 1;
    }
    .show-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #94a3b8; }
    .show-wrap .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; right: 8px; }
    /* Dropdown portal – pakai body agar tidak terpotong overflow:hidden */
    .select2-teruskan-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: .75rem !important;
        box-shadow: 0 10px 30px rgba(0,0,0,.12) !important;
        overflow: hidden;
        margin-top: 4px;
        z-index: 9999 !important;
    }
    .select2-teruskan-dropdown .select2-search--dropdown .select2-search__field {
        border-radius: .5rem; border: 1px solid #e2e8f0;
        padding: 7px 10px; font-size: 13px;
    }
    .select2-teruskan-dropdown .select2-search--dropdown .select2-search__field:focus {
        border-color: #6366f1; outline: none;
    }
    .select2-teruskan-dropdown .select2-results__option--highlighted { background: #6366f1 !important; }

    /* focus ring textarea & date */
    .show-wrap textarea:focus,
    .show-wrap input[type="date"]:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,.15) !important;
        outline: none;
    }
</style>
@endpush

@section('content')

@php
    $rantai = $disposisi->allChildren()->with('allChildren.allChildren')->get();
    $kepadaUsers = $kepadaUsers ?? $disposisi->kepadaUsers();
@endphp

<div class="card-body py-3">

    {{-- ── Breadcrumb ── --}}
    <nav class="mb-4 anim-1" aria-label="breadcrumb">
        <ol class="flex items-center gap-2 text-sm text-slate-500">
            <li>
                <a href="{{ route('disposisi.index') }}"
                   class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                    <i class="fas fa-inbox text-xs"></i>Disposisi
                </a>
            </li>
            <li class="text-slate-300"><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-slate-700 font-semibold">Detail</li>
        </ol>
    </nav>

    {{-- ── Heading ── --}}
    <div class="mb-5 anim-1">
        <h1 class="text-xl font-bold text-slate-800 leading-tight">Detail Disposisi</h1>
        <p class="text-sm text-slate-500 mt-0.5">Informasi lengkap disposisi dan rantai penerusan</p>
    </div>

    {{-- ── Alert session ── --}}
    @if(session('success'))
    <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800
                rounded-xl px-4 py-3 mb-4 text-sm anim-1" role="alert">
        <i class="fas fa-check-circle text-emerald-500 mt-0.5 shrink-0"></i>
        <span class="flex-1">{{ session('success') }}</span>
        <button onclick="this.closest('[role=alert]').remove()"
                class="text-emerald-400 hover:text-emerald-600 transition-colors ml-2">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800
                rounded-xl px-4 py-3 mb-4 text-sm anim-1" role="alert">
        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>
        <span class="flex-1">{{ session('error') }}</span>
        <button onclick="this.closest('[role=alert]').remove()"
                class="text-red-400 hover:text-red-600 transition-colors ml-2">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif

    {{-- ══ SEMUA KARTU: 1 KOLOM FULL WIDTH ══ --}}
    <div class="space-y-4">

        {{-- ── Diteruskan dari parent ── --}}
        @if($disposisi->parent)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden anim-1">
            <div class="h-1 bg-slate-300"></div>
            <div class="flex items-start gap-3 px-6 py-4">
                <div class="w-8 h-8 flex items-center justify-content-center bg-slate-100 rounded-lg shrink-0">
                    <i class="fas fa-share text-slate-400 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2">Diteruskan dari</p>
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center
                                             justify-content-center text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($disposisi->parent->dariUser?->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($disposisi->parent->dariUser?->lastname ?? '', 0, 1)) }}
                                </span>
                                <span class="text-sm font-semibold text-slate-700">
                                    {{ $disposisi->parent->dariUser?->firstname }} {{ $disposisi->parent->dariUser?->lastname }}
                                </span>
                                <i class="fas fa-arrow-right text-slate-300 text-[10px]"></i>
                                @php
                                    $parentKepadaUsers = $disposisi->parent->kepadaUsers();
                                    $parentKepadaText = $parentKepadaUsers->map(fn($u) => trim($u->firstname . ' ' . $u->lastname))->join(', ');
                                @endphp

                                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center
                                            justify-content-center text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($parentKepadaUsers->first()?->firstname ?? 'U', 0, 1)) }}
                                </span>
                                <span class="text-sm font-semibold text-slate-700">
                                    {{ $parentKepadaText ?: '-' }}
                                </span>
                            </div>
                            @if($disposisi->parent->instruksi)
                            <p class="text-xs text-slate-400 italic mt-1">
                                <i class="fas fa-quote-left text-[9px] mr-1 opacity-50"></i>
                                {{ Str::limit($disposisi->parent->instruksi, 120) }}
                            </p>
                            @endif
                            <p class="text-[11px] text-slate-400 mt-1">
                                <i class="fas fa-clock mr-1 opacity-50"></i>
                                {{ $disposisi->parent->created_at->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>
                        <a href="{{ route('disposisi.show', $disposisi->parent) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                                  text-slate-600 bg-white border border-slate-200 rounded-lg
                                  hover:bg-slate-50 transition-colors shrink-0">
                            <i class="fas fa-external-link-alt text-[9px]"></i>Lihat
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Dokumen sumber ── --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden anim-2">
            <div class="h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-400"></div>

            <div class="flex items-center justify-between px-6 py-3.5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 flex items-center justify-content-center bg-indigo-100 rounded-lg shrink-0">
                        <i class="fas {{ $disposisi->document_type === 'memo' ? 'fa-file-alt' : 'fa-calendar-alt' }}
                                  text-indigo-600 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-600">Dokumen Sumber</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                             bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ $disposisi->label_tipe }}
                </span>
            </div>

            <div class="px-6 py-4 flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <p class="font-semibold text-slate-800 mb-1">{{ $disposisi->judul_dokumen }}</p>
                    @if($disposisi->nomor_dokumen)
                    <p class="text-xs text-slate-400 font-mono">
                        <i class="fas fa-hashtag mr-1 opacity-50"></i>{{ $disposisi->nomor_dokumen }}
                    </p>
                    @endif
                </div>
                @if($dokumen)
                <div class="shrink-0">
                    @if($disposisi->document_type === 'memo')
                        <a href="{{ route('view.memo-diterima', $dokumen->id_memo) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium
                                  text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl
                                  hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-external-link-alt text-[9px]"></i>Lihat Memo
                        </a>
                    @else
                        <a href="{{ route('view.undangan', $dokumen->id_undangan) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium
                                  text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl
                                  hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-external-link-alt text-[9px]"></i>Lihat Undangan
                        </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- ── Detail disposisi ── --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden anim-3">
            <div class="h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

            <div class="flex items-center justify-between px-6 py-3.5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 flex items-center justify-content-center bg-indigo-100 rounded-lg shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-600">Detail Disposisi</span>
                </div>
                <span class="badge {{ $disposisi->badge_status }} fw-semibold rounded-pill px-3">
                    {{ $disposisi->label_status }}
                </span>
            </div>

            <div class="px-6 py-5 space-y-5">

                {{-- Dari / Kepada / Tanggal / Deadline – grid 4 kolom --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2">Dari</p>
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center
                                         justify-content-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($disposisi->dariUser?->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($disposisi->dariUser?->lastname ?? '', 0, 1)) }}
                            </span>
                            <span class="text-sm font-semibold text-slate-700 leading-tight">
                                {{ $disposisi->dariUser?->firstname }} {{ $disposisi->dariUser?->lastname }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2">Kepada</p>

                        <div class="flex flex-wrap gap-2">
                            @forelse($kepadaUsers as $penerima)
                                <span class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-full
                                            bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-semibold">
                                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(substr($penerima->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($penerima->lastname ?? '', 0, 1)) }}
                                    </span>
                                    {{ $penerima->firstname }} {{ $penerima->lastname }}
                                </span>
                            @empty
                                <span class="text-sm text-slate-400">-</span>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2">Tanggal dibuat</p>
                        <p class="text-sm text-slate-600">
                            <i class="fas fa-calendar-day mr-1 opacity-40 text-xs"></i>
                            {{ $disposisi->created_at->translatedFormat('d F Y, H:i') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2">Deadline</p>
                        @if($disposisi->deadline)
                            @if($disposisi->deadline->isPast() && $disposisi->status !== 'selesai')
                                <p class="text-sm font-semibold text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1 text-xs"></i>
                                    {{ $disposisi->deadline->translatedFormat('d F Y') }}
                                    <span class="font-normal text-red-400 text-xs ml-1">(Lewat batas)</span>
                                </p>
                            @else
                                <p class="text-sm text-slate-600">
                                    <i class="fas fa-calendar-check mr-1 opacity-40 text-xs"></i>
                                    {{ $disposisi->deadline->translatedFormat('d F Y') }}
                                </p>
                            @endif
                        @else
                            <p class="text-sm text-slate-400">Tidak ditentukan</p>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Instruksi --}}
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2">
                        Instruksi / Arahan
                    </p>
                    <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl px-4 py-3
                                text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $disposisi->instruksi }}</div>
                </div>

                {{-- Catatan --}}
                @if($disposisi->catatan)
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-2">Catatan</p>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3
                                text-sm text-slate-600 leading-relaxed whitespace-pre-wrap">{{ trim($disposisi->catatan) }}</div>
                </div>
                @endif

                {{-- Dibaca --}}
                @if($disposisi->dibaca_at)
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs text-slate-400">
                        <i class="fas fa-eye mr-1 opacity-50"></i>
                        Dibaca pada {{ $disposisi->dibaca_at->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>
                @endif

            </div>
        </div>

        {{-- ── Tindak lanjut ── --}}
        @if($disposisi->adalahPenerima(Auth::id()) && $disposisi->bisaDiubah())
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden anim-4">
            <div class="h-1 bg-gradient-to-r from-amber-400 to-orange-400"></div>

            <div class="flex items-center gap-2.5 px-6 py-3.5 border-b border-slate-100 bg-slate-50/60">
                <div class="w-7 h-7 flex items-center justify-content-center bg-amber-100 rounded-lg shrink-0">
                    <i class="fas fa-tasks text-amber-600 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-slate-600">Tindak lanjut</span>
            </div>

            <div class="px-6 py-4">
                {{-- Tombol aksi --}}
                <div class="flex flex-wrap gap-2">
                    @if($disposisi->status === 'menunggu')
                    <form action="{{ route('disposisi.updateStatus', $disposisi) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="diterima">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold
                                       text-sky-700 bg-sky-50 border border-sky-200 rounded-xl
                                       hover:bg-sky-100 transition-colors">
                            <i class="fas fa-check text-[10px]"></i>Tandai Diterima
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('disposisi.updateStatus', $disposisi) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold
                                       text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl
                                       hover:bg-emerald-100 transition-colors">
                            <i class="fas fa-check-double text-[10px]"></i>Tandai Selesai
                        </button>
                    </form>

                    @if($kandidat->isNotEmpty())
                    <button type="button" id="btnTeruskan"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold
                                   text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl
                                   hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-share text-[10px]"></i>Teruskan Disposisi
                    </button>
                    @endif
                </div>

                {{-- Form teruskan --}}
                @if($kandidat->isNotEmpty())
                <div id="formTeruskan" class="hidden mt-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                        <p class="text-xs font-semibold text-slate-500 mb-4 flex items-center gap-1.5">
                            <i class="fas fa-share text-indigo-500"></i>Teruskan ke
                        </p>
                        <form action="{{ route('disposisi.teruskan', $disposisi) }}" method="POST">
                            @csrf

                            {{-- Kepada – Select2 dengan dropdownParent ke body agar tidak terpotong --}}
                            <div class="mb-4">
                                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                    Kepada <span class="text-red-500">*</span>
                                </label>
                                <select name="kepada_user_id[]" id="selectTeruskan" class="w-full" multiple required>
                                    <option value="">-- Pilih Penerima --</option>
                                    @foreach($kandidat as $u)
                                        <option value="{{ $u->id }}"
                                                data-kode="{{ $u->kode_bagian ?? '' }}">
                                            {{ $u->firstname }} {{ $u->lastname }}
                                            @if($u->kode_bagian)({{ $u->kode_bagian }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Instruksi --}}
                            <div class="mb-4">
                                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                    Instruksi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="instruksi" rows="3" required
                                          placeholder="Instruksi untuk penerima berikutnya..."
                                          class="w-full px-3.5 py-2.5 text-sm text-slate-700 bg-white border border-slate-200
                                                 rounded-lg resize-none transition-all placeholder-slate-400">{{ old('instruksi') }}</textarea>
                            </div>

                            {{-- Catatan + Deadline --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                        Catatan <span class="text-slate-300 font-normal normal-case">(opsional)</span>
                                    </label>
                                    <textarea name="catatan" rows="2" placeholder="Opsional..."
                                              class="w-full px-3.5 py-2.5 text-sm text-slate-700 bg-white border border-slate-200
                                                     rounded-lg resize-none transition-all placeholder-slate-400">{{ old('catatan') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                        Deadline <span class="text-slate-300 font-normal normal-case">(opsional)</span>
                                    </label>
                                    <input type="date" name="deadline" min="{{ date('Y-m-d') }}"
                                           class="w-full px-3.5 py-2.5 text-sm text-slate-700 bg-white border border-slate-200
                                                  rounded-lg transition-all">
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-semibold
                                               text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                                               rounded-xl shadow-sm shadow-indigo-200 transition-all">
                                    <i class="fas fa-paper-plane text-[10px]"></i>Kirim Terusan
                                </button>
                                <button type="button" id="btnBatalTeruskan"
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-medium
                                               text-slate-600 bg-white border border-slate-200 rounded-xl
                                               hover:bg-slate-50 transition-colors">
                                    Batal
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Rantai disposisi ── --}}
        @if($rantai->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden anim-5">
            <div class="h-1 bg-gradient-to-r from-purple-400 to-indigo-500"></div>

            <div class="flex items-center justify-between px-6 py-3.5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 flex items-center justify-content-center bg-purple-100 rounded-lg shrink-0">
                        <i class="fas fa-list-ul text-purple-600 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-600">Rantai Disposisi</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                             bg-slate-100 text-slate-500 border border-slate-200">
                    {{ $rantai->count() }} terusan
                </span>
            </div>

            <div class="p-0">
                @include('disposisi._rantai', ['items' => $rantai, 'level' => 0])
            </div>
        </div>
        @endif

    </div>
    {{-- /.space-y-4 --}}

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn    = document.getElementById('btnTeruskan');
    var batal  = document.getElementById('btnBatalTeruskan');
    var formWrap = document.getElementById('formTeruskan');

    if (btn && formWrap) {
        btn.addEventListener('click', function () {
            var hidden = formWrap.classList.contains('hidden');
            if (hidden) {
                formWrap.classList.remove('hidden');
                btn.innerHTML = '<i class="fas fa-times" style="font-size:10px;margin-right:4px;"></i>Tutup';
                // Re-init select2 setiap kali form dibuka agar posisi dropdown benar
                if (window.$ && $('#selectTeruskan').data('select2')) {
                    $('#selectTeruskan').select2('destroy');
                }
                initSelectTeruskan();
            } else {
                formWrap.classList.add('hidden');
                btn.innerHTML = '<i class="fas fa-share" style="font-size:10px;margin-right:4px;"></i>Teruskan Disposisi';
            }
        });
    }

    if (batal && formWrap) {
        batal.addEventListener('click', function () {
            formWrap.classList.add('hidden');
            if (btn) btn.innerHTML = '<i class="fas fa-share" style="font-size:10px;margin-right:4px;"></i>Teruskan Disposisi';
        });
    }
});

function initSelectTeruskan() {
    if (!window.$ || !$.fn.select2) return;

    function formatNama(data) {
        if (!data.id) return data.text;
        var kode = $(data.element).data('kode') || '';
        var nama = data.text.replace(/\(.*?\)/, '').trim();
        if (!kode) return $('<span>').text(nama);
        return $('<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">')
            .append($('<span style="font-size:13px;">').text(nama))
            .append($('<span style="font-size:11px;padding:2px 8px;border-radius:4px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;">').text(kode));
    }

    function formatNamaSelected(data) {
        if (!data.id) return data.text;
        return data.text.replace(/\(.*?\)/, '').trim();
    }

    $('#selectTeruskan').select2({
        placeholder       : '-- Pilih satu atau lebih penerima --',
        allowClear        : true,
        width             : '100%',
        templateResult    : formatNama,
        templateSelection : formatNamaSelected,
        dropdownParent    : $('body'),
        dropdownCssClass  : 'select2-teruskan-dropdown',
        language: {
            noResults : function () { return 'Nama tidak ditemukan'; },
            searching : function () { return 'Mencari...'; },
        },
    });
}

// Init saat DOM siap jika form sudah terbuka (misal setelah validasi gagal)
$(function () {
    if (document.getElementById('formTeruskan') &&
        !document.getElementById('formTeruskan').classList.contains('hidden')) {
        initSelectTeruskan();
    }
});
</script>
@endpush
