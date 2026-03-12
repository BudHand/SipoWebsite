@extends('layouts.app')

@section('title', 'Edit Risalah Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Edit Risalah Rapat</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.risalah.index') }}" class="text-decoration-none text-primary">Risalah Rapat</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Edit Risalah</span>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form action="{{ route('risalah.update', $risalah->id_risalah) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card border-0 shadow-sm rounded-3">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="card-header py-2 rounded-top-3"
                            style="background:#e3f2fd;border-bottom:1px solid #bbdefb;">
                            <i class="fa fa-edit me-2 text-primary"></i>
                            <span class="fw-semibold">Formulir Edit Risalah</span>
                        </div>

                        <div class="card-body">

                            <div class="row mb-3">
                                {{-- Kode Bagian Kerja --}}
                                <div class="col-md-6">
                                    <label for="kode_bagian" class="form-label">
                                        <i class="fas fa-building text-primary me-1"></i>
                                        Kode Bagian Kerja <span class="text-danger">*</span>
                                    </label>
                                    <select name="kode_bagian" id="kode_bagian"
                                        class="form-control @error('kode_bagian') is-invalid @enderror" required>
                                        <option value="">-- Pilih Bagian Kerja --</option>
                                        @foreach ($bagianKerja as $bk)
                                            <option value="{{ $bk->kode_bagian }}"
                                                {{ old('kode_bagian', $risalah->kode_bagian ?? '') == $bk->kode_bagian ? 'selected' : '' }}>
                                                {{ $bk->kode_bagian }} — {{ $bk->nama_bagian ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kode_bagian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="tanggal_surat" class="form-label">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i>
                                        Tanggal Surat <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tgl_dibuat" class="form-control"
                                        value="{{ $risalah->tgl_dibuat->format('Y-m-d') }}" required>
                                    <input type="hidden" name="tgl_disahkan">
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label for="perihal" class="form-label">
                                        <i class="fas fa-tag text-primary me-1"></i>
                                        Judul <span class="text-danger">*</span>
                                    </label>
                                    @if ($risalah->with_undangan)
                                        <select name="judul" id="judul" class="form-select" required disabled>
                                            <option value="{{ $risalah->judul }}" selected>{{ $risalah->judul }}</option>
                                        </select>
                                        <input type="hidden" name="judul" value="{{ $risalah->judul }}">
                                    @else
                                        <input type="text" name="judul" id="judul" class="form-control" required
                                            value="{{ $risalah->judul }}">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="agenda" class="form-label">
                                        <i class="fas fa-edit text-primary me-1"></i>
                                        Agenda <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="agenda" id="agenda" class="form-control"
                                        value="{{ $risalah->agenda }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tempat" class="form-label">
                                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                        Tempat Rapat <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="tempat" id="tempat" class="form-control"
                                        value="{{ $risalah->tempat }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="waktu" class="form-label">
                                        <i class="fas fa-clock text-primary me-1"></i>
                                        Waktu Rapat <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input type="text" name="waktu_mulai" id="waktu_mulai"
                                            class="form-control me-2" placeholder="Mulai"
                                            value="{{ $risalah->waktu_mulai }}" required>
                                        <span class="fw-bold">s/d</span>
                                        <input type="text" name="waktu_selesai" id="waktu_selesai"
                                            class="form-control ms-2" placeholder="Selesai"
                                            value="{{ $risalah->waktu_selesai }}" required>
                                    </div>
                                </div>

                                <!-- Lampiran -->
                                <div class="col-md-6">
                                    <label for="lampiran-input" class="form-label">
                                        <i class="fas fa-paperclip text-primary me-1"></i>
                                        Lampiran
                                    </label>
                                    <div id="lampiran-input-container" class="mb-2">
                                        <input type="file" id="lampiran-input"
                                            class="form-control @error('lampiran') is-invalid @enderror"
                                            accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div id="lampiran-list" class="mt-2"></div>
                                    <small class="form-text text-muted">
                                        Format yang diizinkan: PDF, JPG, JPEG, PNG (Max: 2MB).
                                        File akan dikirim saat Anda klik tombol <b>Simpan</b>.
                                    </small>
                                    @error('lampiran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    {{-- File yang sudah diupload --}}
                                    @if (!empty($lampiranData) && is_array($lampiranData))
                                        <div class="mt-3">
                                            <label class="form-label">
                                                <i class="fas fa-paperclip text-primary me-1"></i>
                                                File yang Sudah Diupload
                                            </label>
                                            <div class="row">
                                                @foreach ($lampiranData as $index => $lampiran)
                                                    <div class="col-12 mb-2">
                                                        <div class="border rounded p-2">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="flex-grow-1">
                                                                    <small class="text-truncate d-block"
                                                                        title="{{ $lampiran['name'] ?? 'File Lampiran' }}">
                                                                        <i class="fas fa-file text-primary me-1"></i>
                                                                        {{ $lampiran['name'] ?? 'File Lampiran ' . ($index + 1) }}
                                                                    </small>
                                                                </div>
                                                                <div class="ms-2">
                                                                    @if (isset($lampiran['path']) && file_exists(storage_path('app/public/' . $lampiran['path'])))
                                                                        <a href="{{ asset('storage/' . $lampiran['path']) }}"
                                                                            download="{{ $lampiran['name'] ?? 'file' }}"
                                                                            class="btn btn-sm btn-outline-success me-1"
                                                                            title="Download">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    @endif
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger delete-lampiran-existing"
                                                                        data-index="{{ $index }}"
                                                                        data-name="{{ $lampiran['name'] ?? 'File' }}"
                                                                        title="Hapus File">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="progress mt-1" style="height: 2px;">
                                                                <div class="progress-bar bg-success" style="width: 100%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Peserta Acara (hanya jika bukan dari undangan) --}}
                            @if (!$risalah->with_undangan)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="kepada" class="form-label">
                                                <i class="fas fa-user text-primary me-1"></i>
                                                Pilih Peserta Acara <span class="text-danger">*</span>
                                            </label>
                                            <small class="text-danger" style="font-size: x-small">
                                                Pilih user atau struktur, semua user di bawah struktur akan otomatis terpilih
                                            </small>
                                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                                <div style="font-size: small" class="form-label" id="org-tree"></div>
                                                <style>
                                                    #org-tree .jstree-anchor { color: #1f4178; font-weight: 500; }
                                                </style>
                                                <small id="tujuanError" class="text-danger" style="display:none;">
                                                    Minimal pilih satu tujuan!
                                                </small>
                                            </div>
                                            <div id="tujuan-container"></div>
                                            @error('kepada')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div style="display: none;" id="selected-section">
                                            <label style="font-size: small;" class="form-label">Daftar Penerima:</label>
                                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                                <ul id="selected-recipients"
                                                    style="font-size: small; padding-left: 15px; margin: 0; counter-reset: item; list-style-type: none;">
                                                </ul>
                                                <style>
                                                    #selected-recipients li { display: block; margin-bottom: 0.2em; }
                                                    #selected-recipients li:before { content: counter(item, decimal) ". "; counter-increment: item; font-weight: bold; }
                                                </style>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="with_undangan" value="{{ $risalah->with_undangan }}">
                            @endif

                            {{-- Pemimpin & Notulis --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pemimpin_acara" class="form-label">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        Pemimpin Acara <span class="text-danger">*</span>
                                    </label>
                                    <select name="pemimpin_acara" id="pemimpin_acara" class="select2" required>
                                        @if (!$risalah->pemimpin)
                                            <option value="" selected>Pilih Pemimpin Acara</option>
                                        @else
                                            <option value="{{ $risalah->pemimpin->id }}" selected>
                                                {{ $risalah->nama_pemimpin_acara }}
                                            </option>
                                        @endif
                                        @foreach ($users as $user)
                                            @if ($risalah->pemimpin && $user->id == $risalah->pemimpin->id)
                                                @continue
                                            @endif
                                            <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="notulis_acara" class="form-label">
                                        <i class="fas fa-signature text-primary me-1"></i>
                                        Notulis <span class="text-danger">*</span>
                                    </label>
                                    <select name="notulis_acara" id="notulis_acara" class="select2" required>
                                        @if (!$risalah->notulis)
                                            <option value="" selected>Pilih Notulis Acara</option>
                                        @else
                                            <option value="{{ $risalah->notulis->id }}" selected>
                                                {{ $risalah->nama_notulis_acara }}
                                            </option>
                                        @endif
                                        @foreach ($users as $user)
                                            @if ($risalah->notulis && $user->id == $risalah->notulis->id)
                                                @continue
                                            @endif
                                            <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- ===== DETAIL RISALAH ===== --}}
                            <div id="risalahContainer">
                                @foreach ($risalah->risalahDetails as $detailIndex => $detail)
                                    <div class="risalah-item card mb-3 shadow-sm" data-index="{{ $detailIndex }}">
                                        <div class="card-body">

                                            {{-- Baris utama --}}
                                            <div class="row g-2 align-items-stretch isi-surat-row">
                                                <div class="col-md-1" style="display:flex;flex-direction:column;">
                                                    <label class="form-label">No.</label>
                                                    <input type="text" class="form-control no-auto" name="nomor[]"
                                                        value="{{ $detail->nomor }}" readonly style="flex:1;">
                                                </div>
                                                <div class="col-md-2" style="display:flex;flex-direction:column;">
                                                    <label class="form-label">Topik <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="topik[]" rows="2" required
                                                        style="flex:1;resize:vertical;">{{ $detail->topik }}</textarea>
                                                </div>
                                                <div class="col-md-2" style="display:flex;flex-direction:column;">
                                                    <label class="form-label">Pembahasan <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="pembahasan[]" rows="2" required
                                                        style="flex:1;resize:vertical;">{{ $detail->pembahasan }}</textarea>
                                                </div>
                                                <div class="col-md-2" style="display:flex;flex-direction:column;">
                                                    <label class="form-label">Tindak Lanjut <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="tindak_lanjut[]" rows="2" required
                                                        style="flex:1;resize:vertical;">{{ $detail->tindak_lanjut }}</textarea>
                                                </div>
                                                <div class="col-md-2" style="display:flex;flex-direction:column;">
                                                    <label class="form-label">Target <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="target[]" rows="2" required
                                                        style="flex:1;resize:vertical;">{{ $detail->target }}</textarea>
                                                </div>
                                                <div class="col-md-2" style="display:flex;flex-direction:column;">
                                                    <label class="form-label">PIC <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="pic[]" rows="2" required
                                                        style="flex:1;resize:vertical;">{{ $detail->pic }}</textarea>
                                                </div>
                                                <div class="col-md-1 d-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-danger btn-sm hapus-risalah-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Sub Risalah --}}
                                            <div class="sub-risalah-wrapper mt-3 border-top pt-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="text-muted small fw-semibold">
                                                        <i class="fas fa-sitemap me-1 text-primary"></i> Sub Risalah
                                                    </span>
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm tambah-sub-risalah-btn">
                                                        <i class="fas fa-plus-circle me-1"></i> Tambah Sub Isi
                                                    </button>
                                                </div>
                                                <div class="sub-risalah-container">
                                                    @if ($detail->subDetails && $detail->subDetails->count() > 0)
                                                        @foreach ($detail->subDetails as $subDetail)
                                                            <div class="sub-risalah-row border border-primary border-opacity-25 rounded-2 p-3 mt-2 position-relative"
                                                                style="background:#f8f9ff;">
                                                                <span class="sub-badge position-absolute badge bg-primary"
                                                                    style="top:-10px;left:12px;font-size:.7rem;">
                                                                    Sub {{ $loop->iteration }}
                                                                </span>
                                                                <div class="row g-2 align-items-end">
                                                                    <div class="col-md-2">
                                                                        <label class="form-label form-label-sm text-muted mb-1">Sub Topik</label>
                                                                        <textarea class="form-control form-control-sm"
                                                                            data-sub-name="sub_topik"
                                                                            name="sub_topik[{{ $detailIndex }}][]"
                                                                            placeholder="Sub Topik" rows="2"
                                                                            style="resize:vertical;">{{ $subDetail->topik }}</textarea>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label form-label-sm text-muted mb-1">Sub Pembahasan</label>
                                                                        <textarea class="form-control form-control-sm"
                                                                            data-sub-name="sub_pembahasan"
                                                                            name="sub_pembahasan[{{ $detailIndex }}][]"
                                                                            placeholder="Sub Pembahasan" rows="2"
                                                                            style="resize:vertical;">{{ $subDetail->pembahasan }}</textarea>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <label class="form-label form-label-sm text-muted mb-1">Sub Tindak Lanjut</label>
                                                                        <textarea class="form-control form-control-sm"
                                                                            data-sub-name="sub_tindak_lanjut"
                                                                            name="sub_tindak_lanjut[{{ $detailIndex }}][]"
                                                                            placeholder="Sub Tindak Lanjut" rows="2"
                                                                            style="resize:vertical;">{{ $subDetail->tindak_lanjut }}</textarea>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <label class="form-label form-label-sm text-muted mb-1">Sub Target</label>
                                                                        <textarea class="form-control form-control-sm"
                                                                            data-sub-name="sub_target"
                                                                            name="sub_target[{{ $detailIndex }}][]"
                                                                            placeholder="Sub Target" rows="2"
                                                                            style="resize:vertical;">{{ $subDetail->target }}</textarea>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <label class="form-label form-label-sm text-muted mb-1">Sub PIC</label>
                                                                        <textarea class="form-control form-control-sm"
                                                                            data-sub-name="sub_pic"
                                                                            name="sub_pic[{{ $detailIndex }}][]"
                                                                            placeholder="Sub PIC" rows="2"
                                                                            style="resize:vertical;">{{ $subDetail->pic }}</textarea>
                                                                    </div>
                                                                    <div class="col-md-1 d-flex align-items-end justify-content-center">
                                                                        <button type="button"
                                                                            class="btn btn-outline-danger btn-sm hapus-sub-risalah-btn w-100">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="sub-empty-state text-center text-muted small py-2">
                                                            <i class="fas fa-layer-group me-1"></i> Belum ada sub risalah.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-primary mt-3 w-100" id="tambahRisalahBtn">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Isi Risalah
                            </button>

                            <div id="risalahAlert" class="mt-2 text-danger" style="display:none;"></div>
                        </div>

                        <div class="card-footer text-end">
                            <a href="{{ route('admin.risalah.index') }}" class="btn btn-outline-primary">Batal</a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================
    // SELECT2
    // =========================
    $('#pemimpin_acara').select2({ theme: "bootstrap-5", placeholder: "Pilih Pemimpin Acara", allowClear: true, width: "100%" });
    $('#notulis_acara').select2({ theme: "bootstrap-5", placeholder: "Pilih Notulis Acara", allowClear: true, width: "100%" });

    // =========================
    // REFERENSI CONTAINER
    // =========================
    const risalahContainer = document.getElementById('risalahContainer');
    const tambahRisalahBtn = document.getElementById('tambahRisalahBtn');

    // =========================
    // HELPER: Update nomor & name attribute sub
    // =========================
    function updateNomor() {
        const items = risalahContainer.querySelectorAll('.risalah-item');
        items.forEach((item, index) => {
            item.dataset.index = index;

            const noInput = item.querySelector('.no-auto');
            if (noInput) noInput.value = index + 1;

            item.querySelectorAll('.sub-risalah-row').forEach((subRow, subIndex) => {
                const badge = subRow.querySelector('.sub-badge');
                if (badge) badge.textContent = `Sub ${subIndex + 1}`;

                subRow.querySelectorAll('[data-sub-name]').forEach(el => {
                    el.name = `${el.dataset.subName}[${index}][]`;
                });
            });
        });
    }

    // Jalankan sekali saat load untuk set nomor & name yang benar dari data existing
    updateNomor();

    // =========================
    // TEMPLATE: Sub Risalah Row baru
    // =========================
    function createSubRisalahRow(parentIndex) {
        const subRow = document.createElement('div');
        subRow.className = 'sub-risalah-row border border-primary border-opacity-25 rounded-2 p-3 mt-2 position-relative';
        subRow.style.background = '#f8f9ff';

        subRow.innerHTML = `
            <span class="sub-badge position-absolute badge bg-primary" style="top:-10px;left:12px;font-size:.7rem;">Sub</span>
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label form-label-sm text-muted mb-1">Sub Topik</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_topik"
                        name="sub_topik[${parentIndex}][]"
                        placeholder="Sub Topik" rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm text-muted mb-1">Sub Pembahasan</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_pembahasan"
                        name="sub_pembahasan[${parentIndex}][]"
                        placeholder="Sub Pembahasan" rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm text-muted mb-1">Sub Tindak Lanjut</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_tindak_lanjut"
                        name="sub_tindak_lanjut[${parentIndex}][]"
                        placeholder="Sub Tindak Lanjut" rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm text-muted mb-1">Sub Target</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_target"
                        name="sub_target[${parentIndex}][]"
                        placeholder="Sub Target" rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm text-muted mb-1">Sub PIC</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_pic"
                        name="sub_pic[${parentIndex}][]"
                        placeholder="Sub PIC" rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-1 d-flex align-items-end justify-content-center">
                    <button type="button" class="btn btn-outline-danger btn-sm hapus-sub-risalah-btn w-100">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        return subRow;
    }

    // =========================
    // TAMBAH ITEM UTAMA BARU
    // =========================
    if (tambahRisalahBtn && risalahContainer) {
        tambahRisalahBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const itemIndex = risalahContainer.querySelectorAll('.risalah-item').length;
            const itemWrapper = document.createElement('div');
            itemWrapper.className = 'risalah-item card mb-3 shadow-sm';
            itemWrapper.dataset.index = itemIndex;

            itemWrapper.innerHTML = `
                <div class="card-body">
                    <div class="row g-2 align-items-stretch isi-surat-row">
                        <div class="col-md-1" style="display:flex;flex-direction:column;">
                            <label class="form-label">No.</label>
                            <input type="text" class="form-control no-auto" name="nomor[]" readonly style="flex:1;">
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">Topik <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="topik[]" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">Pembahasan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="pembahasan[]" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">Tindak Lanjut <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="tindak_lanjut[]" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">Target <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="target[]" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">PIC <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="pic[]" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-1 d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-danger btn-sm hapus-risalah-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="sub-risalah-wrapper mt-3 border-top pt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold">
                                <i class="fas fa-sitemap me-1 text-primary"></i> Sub Risalah
                            </span>
                            <button type="button" class="btn btn-outline-primary btn-sm tambah-sub-risalah-btn">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Sub Isi
                            </button>
                        </div>
                        <div class="sub-risalah-container">
                            <div class="sub-empty-state text-center text-muted small py-2">
                                <i class="fas fa-layer-group me-1"></i> Belum ada sub risalah.
                            </div>
                        </div>
                    </div>
                </div>
            `;

            risalahContainer.appendChild(itemWrapper);
            updateNomor();
        });
    }

    // =========================
    // EVENT DELEGATION
    // =========================
    risalahContainer.addEventListener('click', function (e) {

        // Hapus item utama
        const hapusRisalahBtn = e.target.closest('.hapus-risalah-btn');
        if (hapusRisalahBtn) {
            const item = hapusRisalahBtn.closest('.risalah-item');
            if (item) { item.remove(); updateNomor(); }
            return;
        }

        // Tambah sub risalah
        const tambahSubBtn = e.target.closest('.tambah-sub-risalah-btn');
        if (tambahSubBtn) {
            const item = tambahSubBtn.closest('.risalah-item');
            if (!item) return;
            const parentIndex = parseInt(item.dataset.index ?? 0, 10);
            const subContainer = item.querySelector('.sub-risalah-container');
            if (!subContainer) return;

            const emptyState = subContainer.querySelector('.sub-empty-state');
            if (emptyState) emptyState.remove();

            subContainer.appendChild(createSubRisalahRow(parentIndex));
            updateNomor();
            return;
        }

        // Hapus sub risalah
        const hapusSubBtn = e.target.closest('.hapus-sub-risalah-btn');
        if (hapusSubBtn) {
            const subRow = hapusSubBtn.closest('.sub-risalah-row');
            const subContainer = subRow ? subRow.closest('.sub-risalah-container') : null;
            if (subRow) {
                subRow.remove();
                updateNomor();
                if (subContainer && !subContainer.querySelector('.sub-risalah-row')) {
                    subContainer.insertAdjacentHTML('beforeend', `
                        <div class="sub-empty-state text-center text-muted small py-2">
                            <i class="fas fa-layer-group me-1"></i> Belum ada sub risalah.
                        </div>
                    `);
                }
            }
            return;
        }
    });

    // =========================
    // LAMPIRAN
    // =========================
    const lampiranInputContainer = document.getElementById('lampiran-input-container');
    const lampiranInput = document.getElementById('lampiran-input');
    const lampiranList = document.getElementById('lampiran-list');

    if (lampiranInputContainer && lampiranInput && lampiranList) {
        function createEmptyVisibleInput() {
            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.id = 'lampiran-input';
            newInput.className = 'form-control';
            newInput.setAttribute('accept', '.pdf,.jpg,.jpeg,.png');
            newInput.addEventListener('change', handleLampiranChange);
            lampiranInputContainer.innerHTML = '';
            lampiranInputContainer.appendChild(newInput);
        }

        function handleLampiranChange(e) {
            const input = e.target;
            if (!input.files || input.files.length === 0) return;
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file tidak boleh lebih dari 2MB', confirmButtonColor: '#1572e8' });
                input.value = '';
                return;
            }
            const itemWrapper = document.createElement('div');
            itemWrapper.className = 'd-flex align-items-center justify-content-between mb-2 flex-wrap gap-2';
            const infoWrapper = document.createElement('div');
            infoWrapper.className = 'flex-grow-1';
            infoWrapper.innerHTML = `<span>${file.name}</span><div class="progress mt-1" style="height:4px;"><div class="progress-bar" style="width:100%"></div></div>`;
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            input.name = 'lampiran[]';
            input.classList.add('d-none');
            input.removeEventListener('change', handleLampiranChange);
            itemWrapper.appendChild(infoWrapper);
            itemWrapper.appendChild(removeBtn);
            itemWrapper.appendChild(input);
            lampiranList.appendChild(itemWrapper);
            removeBtn.addEventListener('click', () => itemWrapper.remove());
            createEmptyVisibleInput();
        }

        lampiranInput.addEventListener('change', handleLampiranChange);
    }

    // =========================
    // HAPUS LAMPIRAN EXISTING
    // =========================
    document.querySelectorAll('.delete-lampiran-existing').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const lampiranIndex = this.dataset.index;
            const fileName = this.dataset.name;
            const element = this.closest('.col-12');

            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus file "${fileName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/risalah/lampiran-existing/{{ $risalah->id_risalah }}/${lampiranIndex}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            element.remove();
                            Swal.fire({ title: 'Berhasil!', text: 'File berhasil dihapus.', icon: 'success', confirmButtonColor: '#1572e8' });
                        } else {
                            Swal.fire({ title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus file.', icon: 'error', confirmButtonColor: '#1572e8' });
                        }
                    });
                }
            });
        });
    });

    // =========================
    // VALIDASI SUBMIT
    // =========================
    const risalahForm = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');

    if (risalahForm && submitBtn) {
        risalahForm.addEventListener('submit', function (e) {
            if (submitBtn.disabled) { e.preventDefault(); return false; }
            const jumlahRisalah = risalahContainer.querySelectorAll('.risalah-item').length;
            const risalahAlert = document.getElementById('risalahAlert');
            if (jumlahRisalah < 1) {
                e.preventDefault();
                if (risalahAlert) {
                    risalahAlert.style.display = 'block';
                    risalahAlert.innerText = 'Minimal harus mengisi 1 risalah rapat!';
                    risalahAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
            if (risalahAlert) risalahAlert.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...`;
            return true;
        });
    }
});

// =========================
// JSTREE
// =========================
$(document).ready(function () {
    var treeData = @json(json_decode($jsTreeData));
    var selectedTujuan = @json($tujuanArray);

    if (!treeData || treeData.length === 0) {
        $('#org-tree').html('<p class="text-danger">Data organisasi tidak tersedia</p>');
        return;
    }

    try {
        $('#org-tree').jstree({
            'core': { 'data': treeData, 'themes': { 'dots': true } },
            'plugins': ['checkbox']
        }).on('ready.jstree', function (e, data) {
            selectedTujuan.forEach(id => {
                $('#org-tree').jstree('check_node', '#user-' + id);
            });
            updateSelectedRecipients(data);
            data.instance.get_selected(true).forEach(function (node) {
                let parentId = data.instance.get_parent(node.id);
                while (parentId && parentId !== '#') {
                    data.instance.open_node(parentId);
                    parentId = data.instance.get_parent(parentId);
                }
            });
        }).on('changed.jstree', function (e, data) {
            $('#tujuan-container').empty();
            let allSelectedNodes = data.instance.get_selected(true);
            let selectedNodes = [];
            let userIds = [];

            allSelectedNodes.forEach(function (node) {
                if (node.icon && node.icon === 'fa fa-user') {
                    selectedNodes.push(node.text);
                    userIds.push(node.id);
                }
                if (data.instance.is_selected(node.id)) data.instance.open_node(node.id);
            });

            userIds.forEach(function (nodeId) {
                $('#tujuan-container').append('<input type="hidden" name="tujuan[]" value="' + nodeId + '">');
            });

            updateSelectedRecipients(data);
            if (userIds.length > 0) $('#tujuanError').hide();
        }).on('error.jstree', function (e, data) {
            console.error('JSTree error:', data);
        });
    } catch (error) {
        $('#org-tree').html('<p class="text-danger">Gagal memuat data organisasi. Error: ' + error.message + '</p>');
    }
});

function updateSelectedRecipients(data) {
    const positionOrder = {
        'Direktur': 1, 'GM': 2, 'General Manager': 2,
        'SM': 3, 'Senior Manager': 3, 'M': 4, 'Manager': 4,
        'PJ SM': 5, 'Penanggung Jawab Senior Manager': 5,
        'PJ M': 6, 'Penanggung Jawab Manager': 6,
        'SPV': 7, 'Supervisor': 7,
        'PJ SPV': 8, 'Penanggung Jawab Supervisor': 8, 'Staff': 9
    };
    const getPriority = text => {
        for (let pos in positionOrder) { if (text.startsWith(pos)) return positionOrder[pos]; }
        return 999;
    };

    let selectedNodes = data.instance.get_selected(true)
        .filter(n => n.icon && n.icon === 'fa fa-user')
        .map(n => n.text)
        .sort((a, b) => getPriority(a) - getPriority(b));

    let list = $('#selected-recipients');
    let section = $('#selected-section');
    list.empty();
    if (selectedNodes.length) {
        selectedNodes.forEach(name => list.append(`<li>${name}</li>`));
        section.show();
    } else {
        section.hide();
    }
}
</script>
@endpush
