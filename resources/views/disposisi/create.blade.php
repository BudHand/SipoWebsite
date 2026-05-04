{{-- resources/views/disposisi/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Buat Disposisi')

@push('styles')
<style>
    /* ── Google Font ── */
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    .disposisi-wrap * {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Select2 – selaraskan dengan Tailwind */
    .disposisi-wrap .select2-container--default .select2-selection--single {
        height: 42px;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        padding: 0 12px;
        transition: border-color .15s, box-shadow .15s;
    }
    .disposisi-wrap .select2-container--default .select2-selection--single:focus-within,
    .disposisi-wrap .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,.15);
        outline: none;
    }
    .disposisi-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b;
        font-size: 14px;
        padding: 0;
        line-height: 1;
    }
    .disposisi-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8;
    }
    .disposisi-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 10px;
    }
    .disposisi-wrap .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        box-shadow: 0 10px 30px rgba(0,0,0,.1);
        overflow: hidden;
        margin-top: 4px;
    }
    .disposisi-wrap .select2-container--default .select2-results__option--highlighted {
        background: #6366f1;
    }
    .disposisi-wrap .select2-search--dropdown .select2-search__field {
        border-radius: .5rem;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        font-size: 13px;
    }
    .disposisi-wrap .select2-search--dropdown .select2-search__field:focus {
        border-color: #6366f1;
        outline: none;
    }

    /* Animasi masuk */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .anim-1 { animation: fadeUp .4s ease both; }
    .anim-2 { animation: fadeUp .4s .1s ease both; }
    .anim-3 { animation: fadeUp .4s .2s ease both; }

    /* Focus ring custom untuk textarea & input */
    .disposisi-wrap textarea:focus,
    .disposisi-wrap input[type="date"]:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,.15) !important;
        outline: none;
    }

    /* is-invalid override agar tetap merah */
    .disposisi-wrap .is-invalid {
        border-color: #ef4444 !important;
    }
    .disposisi-wrap .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
    }
</style>
@endpush

@section('content')
<div class="disposisi-wrap pb-10">

    {{-- ── Breadcrumb ── --}}
    <nav class="mb-6 anim-1" aria-label="breadcrumb">
        <ol class="flex items-center gap-2 text-sm text-slate-500">
            <li>
                <a href="{{ route('disposisi.index') }}"
                   class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                    <i class="fas fa-inbox text-xs"></i>
                    Disposisi
                </a>
            </li>
            <li class="text-slate-300"><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-slate-700 font-semibold">Buat Baru</li>
        </ol>
    </nav>

    {{-- ── Heading ── --}}
    <div class="mb-6 anim-1">
        <h1 class="text-xl font-bold text-slate-800 leading-tight">Buat Disposisi</h1>
        <p class="text-sm text-slate-500 mt-0.5">Teruskan dokumen dengan instruksi kepada penerima yang dituju</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ══ KOLOM KIRI: Info Dokumen (4/12) ══ --}}
        <div class="lg:col-span-4 anim-2">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                {{-- Stripe header --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-400"></div>

                <div class="p-6 text-center">

                    {{-- Badge tipe --}}
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                                 bg-indigo-50 text-indigo-700 border border-indigo-100 mb-4">
                        <i class="fas {{ $documentType === 'memo' ? 'fa-file-alt' : 'fa-calendar-alt' }} text-[10px]"></i>
                        {{ ucfirst($documentType) }}
                    </span>

                    {{-- Ikon lingkaran --}}
                    <div class="mx-auto mb-4 w-14 h-14 flex items-center justify-content-center
                                bg-indigo-50 rounded-full ring-4 ring-indigo-50 ring-offset-2">
                        <i class="fas {{ $documentType === 'memo' ? 'fa-file-alt' : 'fa-calendar-alt' }}
                                  text-indigo-500 text-xl"></i>
                    </div>

                    {{-- Judul --}}
                    <h6 class="font-semibold text-slate-800 text-sm leading-snug mb-3 px-2">
                        {{ $dokumen->judul }}
                    </h6>

                    {{-- Nomor --}}
                    @php
                        $nomor = $documentType === 'memo'
                            ? ($dokumen->nomor_memo     ?? null)
                            : ($dokumen->nomor_undangan ?? null);
                    @endphp
                    @if($nomor)
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs
                                     bg-slate-50 text-slate-600 border border-slate-200 font-mono mb-1">
                            <i class="fas fa-hashtag text-slate-400 text-[10px]"></i>{{ $nomor }}
                        </span>
                    @endif

                    {{-- Tanggal --}}
                    @if($dokumen->tgl_dibuat)
                        <p class="text-slate-400 text-xs mt-2">
                            <i class="fas fa-calendar-day mr-1"></i>
                            {{ \Carbon\Carbon::parse($dokumen->tgl_dibuat)->translatedFormat('d F Y') }}
                        </p>
                    @endif

                </div>

                {{-- Metadata --}}
                <div class="border-t border-slate-100 divide-y divide-slate-100">

                    @if($dokumen->pengirim ?? null)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-slate-400 font-medium">Dibuat oleh</span>
                        <span class="text-xs font-semibold text-slate-700">{{ $dokumen->pengirim }}</span>
                    </div>
                    @endif

                    @if($dokumen->kode_bagian ?? null)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-slate-400 font-medium">Bagian</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                     bg-slate-100 text-slate-600">
                            {{ $dokumen->kode_bagian }}
                        </span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-slate-400 font-medium">Status</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold
                                     bg-emerald-50 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                            Aktif
                        </span>
                    </div>

                </div>
            </div>
        </div>

        {{-- ══ KOLOM KANAN: Form (8/12) ══ --}}
        <div class="lg:col-span-8 anim-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                {{-- Card header --}}
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <div class="w-8 h-8 flex items-center justify-content-center
                                bg-indigo-100 rounded-lg shrink-0">
                        <i class="fas fa-paper-plane text-indigo-600 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 leading-tight">Form Disposisi</p>
                        <p class="text-xs text-slate-400">Isi detail disposisi untuk diteruskan</p>
                    </div>
                </div>

                <div class="p-6">
                    <form action="{{ route('disposisi.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="document_type" value="{{ $documentType }}">
                        <input type="hidden" name="document_id"   value="{{ $documentId }}">

                        {{-- ── Kepada ── --}}
                        <div class="mb-5">
                            <label for="kepada_user_id"
                                   class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                Kepada <span class="text-red-500">*</span>
                            </label>
                            <select name="kepada_user_id[]" multiple
                                    id="kepada_user_id"
                                    class="w-full @error('kepada_user_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Cari nama atau kode bagian --</option>
                                @foreach($kandidat as $u)
                                    <option value="{{ $u->id }}"
                                            data-kode="{{ $u->kode_bagian ?? '' }}"
                                            {{ in_array($u->id, old('kepada_user_id', [])) ? 'selected' : '' }}>
                                        {{ $u->firstname }} {{ $u->lastname }}
                                        @if($u->kode_bagian)({{ $u->kode_bagian }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('kepada_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($kandidat->isEmpty())
                                <p class="text-amber-500 text-xs mt-1.5 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Tidak ada pengguna dengan kode bagian yang sama.
                                </p>
                            @endif
                        </div>

                        {{-- ── Instruksi ── --}}
                        <div class="mb-5">
                            <label for="instruksi"
                                   class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                Instruksi / Arahan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="instruksi"
                                      id="instruksi"
                                      rows="5"
                                      required
                                      placeholder="Tulis instruksi atau arahan untuk penerima..."
                                      class="w-full px-3.5 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200
                                             rounded-lg resize-none transition-all placeholder-slate-400
                                             @error('instruksi') is-invalid @enderror">{{ old('instruksi') }}</textarea>
                            @error('instruksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ── Catatan + Deadline ── --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                            <div class="md:col-span-2">
                                <label for="catatan"
                                       class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                    Catatan
                                    <span class="text-slate-300 font-normal normal-case ml-1">(opsional)</span>
                                </label>
                                <textarea name="catatan"
                                          id="catatan"
                                          rows="3"
                                          placeholder="Catatan tambahan..."
                                          class="w-full px-3.5 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200
                                                 rounded-lg resize-none transition-all placeholder-slate-400
                                                 @error('catatan') is-invalid @enderror">{{ old('catatan') }}</textarea>
                                @error('catatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="deadline"
                                       class="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5">
                                    Deadline
                                    <span class="text-slate-300 font-normal normal-case ml-1">(opsional)</span>
                                </label>
                                <input type="date"
                                       name="deadline"
                                       id="deadline"
                                       value="{{ old('deadline') }}"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-3.5 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200
                                              rounded-lg transition-all
                                              @error('deadline') is-invalid @enderror">
                                @error('deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ── Tombol ── --}}
                        <div class="border-t border-slate-100 pt-5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold
                                               text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                                               rounded-xl shadow-sm shadow-indigo-200 transition-all">
                                    <i class="fas fa-paper-plane text-xs"></i>
                                    Kirim Disposisi
                                </button>
                                <a href="{{ url()->previous() }}"
                                   class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium
                                          text-slate-600 bg-white border border-slate-200 hover:bg-slate-50
                                          rounded-xl transition-all">
                                    Batal
                                </a>
                            </div>
                            <p class="text-xs text-slate-400">
                                <span class="text-red-500">*</span> wajib diisi
                            </p>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /.grid --}}

</div>
@endsection

@push('scripts')
<script>
$(function () {

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

    $('#kepada_user_id').select2({
        placeholder      : '-- Cari nama atau kode bagian --',
        allowClear       : true,
        width            : '100%',
        templateResult   : formatNama,
        templateSelection: formatNamaSelected,
        language         : {
            noResults : function () { return 'Nama tidak ditemukan'; },
            searching : function () { return 'Mencari...'; },
        },
    });

});
</script>
@endpush
