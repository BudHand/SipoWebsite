@extends('layouts.app')

@section('title', 'Tambah Risalah Rapat')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold mb-0">Tambah Risalah</h3>
                </div>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="mx-2 text-muted">/</span>
                            <a href="{{ route('admin.risalah.index') }}"
                                class="text-decoration-none text-primary">Risalah</a>
                            <span class="mx-2 text-muted">/</span>
                            <span class="text-muted">Tambah Risalah</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="col-md-12">
                        <form action="{{ route('risalah.store') }}" method="POST" enctype="multipart/form-data"
                            id="risalahForm">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-plus-circle text-primary me-2"></i>
                                            Form Tambah Risalah Rapat
                                        </h4>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row g-3">
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
                                                        {{ old('kode_bagian') == $bk->kode_bagian ? 'selected' : '' }}>
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
                                                value="{{ date('Y-m-d') }}" required>
                                            <input type="hidden" name="tgl_disahkan">
                                        </div>

                                        <input type="hidden" name="pembuat" value="{{ auth()->user()->id }}">
                                        <input type="hidden" name="risalah_id_risalah" value="{{ $risalah->id_risalah }}">

                                        <div class="col-md-6">
                                            <label for="judul" class="form-label">
                                                <i class="fas fa-tag text-primary me-1"></i>
                                                Judul <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="judul" id="judul" class="form-control"
                                                required placeholder="Masukkan judul risalah">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="agenda" class="form-label">
                                                <i class="fas fa-edit text-primary me-1"></i>
                                                Agenda <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="agenda" class="form-control" required
                                                placeholder="Masukkan agenda risalah">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="tempat" class="form-label">
                                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                Tempat Acara <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="tempat" id="tempat" class="form-control"
                                                required placeholder="Masukkan tempat acara">
                                        </div>

                                        <!-- Waktu -->
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="fas fa-clock text-primary me-1"></i>
                                                Waktu Rapat <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" name="waktu_mulai" id="waktu_mulai"
                                                    class="form-control" placeholder="09.00" required>
                                                <span class="input-group-text">s/d</span>
                                                <input type="text" name="waktu_selesai" id="waktu_selesai"
                                                    class="form-control" placeholder="Selesai" required>
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
                                        </div>

                                        <!-- Pilih Peserta -->
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="kepada" class="form-label">
                                                        <i class="fas fa-user text-primary me-1"></i>
                                                        Pilih Peserta Acara
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <small class="text-danger" style="font-size: x-small"> Pilih user atau
                                                        struktur, semua user di bawah struktur akan otomatis terpilih</small>
                                                    <div class="border rounded p-2"
                                                        style="max-height: 300px; overflow-y: auto;">
                                                        <div style="font-size: small" class="form-label" id="org-tree">
                                                        </div>
                                                        <style>
                                                            #org-tree .jstree-anchor {
                                                                color: #1f4178;
                                                                font-weight: 500;
                                                            }
                                                        </style>
                                                        <small id="tujuanError" class="text-danger"
                                                            style="display:none;">Minimal pilih satu tujuan!</small>
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
                                                    <label style="font-size: small;" class="form-label">
                                                        Daftar Penerima:
                                                    </label>
                                                    <div class="border rounded p-2"
                                                        style="max-height: 300px; overflow-y: auto;">
                                                        <ul id="selected-recipients"
                                                            style="font-size: small; padding-left: 15px; margin: 0; counter-reset: item; list-style-type: none;">
                                                        </ul>
                                                        <style>
                                                            #selected-recipients li {
                                                                display: block;
                                                                margin-bottom: 0.2em;
                                                            }
                                                            #selected-recipients li:before {
                                                                content: counter(item, decimal) ". ";
                                                                counter-increment: item;
                                                                font-weight: bold;
                                                            }
                                                        </style>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="pemimpin_acara" class="form-label">
                                                <i class="fas fa-signature text-primary me-1"></i>
                                                Pemimpin Acara <span class="text-danger">*</span>
                                            </label>
                                            <select name="pemimpin_acara" id="pemimpin_acara" class="select2" required>
                                                <option value="" disabled selected>--Pilih Pemimpin Acara--</option>
                                                @foreach ($users as $user)
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
                                                <option value="" disabled selected>--Pilih Notulis Acara--</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>

                                    <div id="risalahContainer" class="mt-4">
                                        <!-- Dynamic content will be added here -->
                                    </div>

                                    <button type="button" class="btn btn-primary mt-3 w-100" id="tambahRisalahBtn">
                                        <i class="bi bi-plus-circle me-1"></i> Tambah Isi Risalah
                                    </button>

                                    <div id="risalahAlert" class="mt-2 text-danger" style="display:none;"></div>
                                </div>

                                <div class="card-footer d-flex justify-content-end">
                                    <a href="{{ route('admin.risalah.index') }}"
                                        class="btn btn-outline-primary me-2">Batal</a>
                                    <button type="submit" id="submitBtn" class="btn btn-primary">Simpan</button>
                                </div>
                            </div>
                            <input type="hidden" id="with_undangan" name="with_undangan" value="">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Script dimulai');

    // =========================
    // SELECT2
    // =========================
    $('#pemimpin_acara').select2({
        theme: "bootstrap-5",
        placeholder: "Pilih Pemimpin Acara",
        allowClear: true,
        width: "100%"
    });
    $('#notulis_acara').select2({
        theme: "bootstrap-5",
        placeholder: "Pilih Notulis Acara",
        allowClear: true,
        width: "100%"
    });

    // =========================
    // REFERENSI CONTAINER
    // =========================
    const risalahContainer = document.getElementById('risalahContainer');
    const tambahRisalahBtn = document.getElementById('tambahRisalahBtn');

    // =========================
    // HELPER: Update nomor urut & name attribute sub risalah
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
        console.log(`Nomor diupdate untuk ${risalahContainer.querySelectorAll('.risalah-item').length} item`);
    }

    // =========================
    // TEMPLATE: Sub Risalah Row
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
                        placeholder="Sub Topik"
                        rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm text-muted mb-1">Sub Pembahasan</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_pembahasan"
                        name="sub_pembahasan[${parentIndex}][]"
                        placeholder="Sub Pembahasan"
                        rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm text-muted mb-1">Sub Tindak Lanjut</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_tindak_lanjut"
                        name="sub_tindak_lanjut[${parentIndex}][]"
                        placeholder="Sub Tindak Lanjut"
                        rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm text-muted mb-1">Sub Target</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_target"
                        name="sub_target[${parentIndex}][]"
                        placeholder="Sub Target"
                        rows="2"
                        style="resize:vertical;"></textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm text-muted mb-1">Sub PIC</label>
                    <textarea class="form-control form-control-sm"
                        data-sub-name="sub_pic"
                        name="sub_pic[${parentIndex}][]"
                        placeholder="Sub PIC"
                        rows="2"
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
    // TAMBAH ITEM UTAMA
    // =========================
    if (tambahRisalahBtn && risalahContainer) {
        tambahRisalahBtn.addEventListener('click', function(e) {
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
                            <textarea class="form-control" name="topik[]" placeholder="Topik" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">Pembahasan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="pembahasan[]" placeholder="Pembahasan" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">Tindak Lanjut <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="tindak_lanjut[]" placeholder="Tindak Lanjut" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">Target <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="target[]" placeholder="Target" rows="2" required style="flex:1;resize:vertical;"></textarea>
                        </div>
                        <div class="col-md-2" style="display:flex;flex-direction:column;">
                            <label class="form-label">PIC <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="pic[]" placeholder="PIC" rows="2" required style="flex:1;resize:vertical;"></textarea>
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
            console.log('✓ Risalah baru berhasil ditambahkan');
        });
    }

    // =========================
    // EVENT DELEGATION — hapus item, tambah/hapus sub
    // =========================
    risalahContainer.addEventListener('click', function(e) {

        // Hapus item utama
        const hapusRisalahBtn = e.target.closest('.hapus-risalah-btn');
        if (hapusRisalahBtn) {
            const item = hapusRisalahBtn.closest('.risalah-item');
            if (item) {
                item.remove();
                updateNomor();
                console.log('Baris risalah berhasil dihapus');
            }
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
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file tidak boleh lebih dari 2MB',
                    confirmButtonColor: '#1572e8'
                });
                input.value = '';
                return;
            }

            const itemWrapper = document.createElement('div');
            itemWrapper.className = 'd-flex align-items-center justify-content-between mb-2 flex-wrap gap-2';

            const infoWrapper = document.createElement('div');
            infoWrapper.className = 'flex-grow-1';

            const nameSpan = document.createElement('span');
            nameSpan.textContent = file.name;

            const progressOuter = document.createElement('div');
            progressOuter.className = 'progress mt-1';
            progressOuter.style.height = '4px';

            const progressInner = document.createElement('div');
            progressInner.className = 'progress-bar';
            progressInner.style.width = '100%';

            progressOuter.appendChild(progressInner);
            infoWrapper.appendChild(nameSpan);
            infoWrapper.appendChild(progressOuter);

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

            removeBtn.addEventListener('click', function() {
                itemWrapper.remove();
            });

            createEmptyVisibleInput();
        }

        lampiranInput.addEventListener('change', handleLampiranChange);
    }

    // =========================
    // VALIDASI SUBMIT
    // =========================
    const risalahForm = document.getElementById('risalahForm');
    const submitBtn = document.getElementById('submitBtn');

    if (risalahForm && submitBtn) {
        risalahForm.addEventListener('submit', function(e) {
            if (submitBtn.disabled) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

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
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Menyimpan...
            `;
            return true;
        });

        window.addEventListener('load', function() {
            const errorElements = document.querySelectorAll('.alert-danger, .invalid-feedback, .error, .text-danger');
            if (errorElements.length > 0) {
                setTimeout(function() {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Simpan';
                    }
                }, 500);
            }
        });
    }
});

// =========================
// JSTREE — tetap di luar DOMContentLoaded karena pakai $(document).ready
// =========================
$(document).ready(function() {
    console.log('Document ready, initializing JSTree...');

    var treeData = @json(json_decode($jsTreeData));
    if (!treeData || treeData.length === 0) {
        console.error('JSTree data is empty or invalid');
        $('#org-tree').html('<p class="text-danger">Data organisasi tidak tersedia</p>');
        return;
    }

    try {
        $('#org-tree').jstree({
            'core': {
                'data': treeData,
                'themes': {
                    'dots': true,
                }
            },
            'plugins': ['checkbox']
        }).on('ready.jstree', function() {
            console.log('JSTree initialized successfully');
        }).on('changed.jstree', function(e, data) {
            console.log('JSTree selection changed:', data.selected);

            $('#tujuan-container').empty();

            let allSelectedNodes = data.instance.get_selected(true);
            let selectedNodes = [];
            let userIds = [];

            allSelectedNodes.forEach(function(node) {
                if (node.icon && node.icon === 'fa fa-user') {
                    selectedNodes.push(node.text);
                    userIds.push(node.id);
                }
                if (data.instance.is_selected(node.id)) {
                    data.instance.open_node(node.id);
                }
            });

            userIds.forEach(function(nodeId) {
                $('#tujuan-container').append(
                    '<input type="hidden" name="tujuan[]" value="' + nodeId + '">'
                );
            });

            selectedNodes.sort(function(a, b) {
                const positionOrder = {
                    'Direktur': 1, 'GM': 2, 'General Manager': 2,
                    'SM': 3, 'Senior Manager': 3, 'M': 4, 'Manager': 4,
                    'PJ SM': 5, 'Penanggung Jawab Senior Manager': 5,
                    'PJ M': 6, 'Penanggung Jawab Manager': 6,
                    'SPV': 7, 'Supervisor': 7,
                    'PJ SPV': 8, 'Penanggung Jawab Supervisor': 8,
                    'Staff': 9
                };
                const getPriority = function(text) {
                    for (let pos in positionOrder) {
                        if (text.startsWith(pos)) return positionOrder[pos];
                    }
                    return 999;
                };
                return getPriority(a) - getPriority(b);
            });

            let list = $('#selected-recipients');
            let section = $('#selected-section');
            list.empty();

            if (selectedNodes.length) {
                selectedNodes.forEach(function(name) {
                    list.append('<li>' + name + '</li>');
                });
                section.show();
            } else {
                section.hide();
            }

            if (userIds.length > 0) {
                $('#tujuanError').hide();
            }
        }).on('error.jstree', function(e, data) {
            console.error('JSTree error:', data);
        });

    } catch (error) {
        console.error('JSTree initialization failed:', error);
        $('#org-tree').html('<p class="text-danger">Gagal memuat data organisasi. Error: ' + error.message + '</p>');
    }
});
</script>
@endpush
