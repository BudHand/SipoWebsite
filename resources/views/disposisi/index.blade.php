{{-- resources/views/disposisi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Disposisi')

@section('content')
<style>
    .dispo-hero {
        background: linear-gradient(135deg, #1E4178 0%, #2563eb 55%, #60a5fa 100%);
        border-radius: 22px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .dispo-hero::after {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        right: -80px;
        top: -90px;
        border-radius: 999px;
        background: rgba(255,255,255,.14);
    }

    .dispo-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
    }

    .dispo-tab {
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 999px;
        padding: 5px;
        display: inline-flex;
        gap: 5px;
    }

    .dispo-tab .nav-link {
        border: 0;
        border-radius: 999px;
        color: #64748b;
        font-weight: 600;
        padding: 9px 18px;
    }

    .dispo-tab .nav-link.active {
        background: #1E4178;
        color: #fff;
    }

    .filter-chip {
        border-radius: 999px;
        padding: 7px 14px;
        font-weight: 600;
        font-size: 13px;
    }

    .stat-box {
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 16px;
        padding: 14px 16px;
        backdrop-filter: blur(8px);
    }

    .modal-dispo .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
    }

    .modal-dispo .modal-header {
        background: linear-gradient(135deg, #1E4178, #2563eb);
        color: #fff;
        border: 0;
    }

    .modal-dispo .btn-close {
        filter: invert(1);
        opacity: .9;
    }

    .tipe-card {
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 16px;
        padding: 14px;
        transition: .2s ease;
    }

    .tipe-card.active,
    .tipe-card:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .dokumen-item {
        transition: .2s ease;
    }

    .dokumen-item:hover {
        background: #f8fafc;
        transform: translateY(-1px);
    }
</style>

<div class="container-fluid px-4">

    {{-- Hero --}}
    <div class="dispo-hero p-4 p-md-5 mb-4">
        <div class="position-relative" style="z-index:2;">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-paper-plane me-1"></i> Manajemen Disposisi
                    </span>
                    <h3 class="fw-bold mb-2">Disposisi Dokumen</h3>
                    <p class="mb-0 opacity-75">
                        Kelola disposisi masuk, keluar, status tindak lanjut, dan penerusan dokumen dalam satu halaman.
                    </p>
                </div>

                <div class="d-flex align-items-start">
                    <button type="button"
                            class="btn btn-light text-primary fw-semibold rounded-pill px-4 py-2 shadow-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalPilihDokumen">
                        <i class="fas fa-plus me-1"></i> Buat Disposisi
                    </button>
                </div>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="small opacity-75">Tab Aktif</div>
                        <div class="fs-5 fw-bold">{{ ucfirst($tab) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="small opacity-75">Filter Status</div>
                        <div class="fs-5 fw-bold">{{ $filter === '' ? 'Semua' : ucfirst($filter) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="small opacity-75">Belum Dibaca</div>
                        <div class="fs-5 fw-bold">{{ $belumDibaca ?? 0 }} Disposisi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card dispo-card mb-4">
        <div class="card-body p-3 p-md-4">

            {{-- Tab --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <ul class="nav dispo-tab">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'masuk' ? 'active' : '' }}"
                           href="{{ route('disposisi.index', ['tab' => 'masuk', 'status' => $filter]) }}">
                            <i class="fas fa-inbox me-1"></i> Masuk
                            @if ($belumDibaca > 0)
                                <span class="badge bg-danger ms-1">{{ $belumDibaca }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'keluar' ? 'active' : '' }}"
                           href="{{ route('disposisi.index', ['tab' => 'keluar', 'status' => $filter]) }}">
                            <i class="fas fa-paper-plane me-1"></i> Keluar
                        </a>
                    </li>
                </ul>

                <div class="text-muted small">
                    <i class="fas fa-filter me-1"></i>
                    Menampilkan status:
                    <strong>{{ $filter === '' ? 'Semua' : ucfirst($filter) }}</strong>
                </div>
            </div>

            {{-- Filter --}}
            <div class="d-flex gap-2 flex-wrap mb-4">
                @foreach (['' => 'Semua', 'menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'selesai' => 'Selesai', 'diteruskan' => 'Diteruskan'] as $value => $label)
                    <a href="{{ route('disposisi.index', ['tab' => $tab, 'status' => $value]) }}"
                       class="btn filter-chip {{ $filter === $value ? 'btn-primary' : 'btn-outline-secondary' }}">
                        @if ($value === '')
                            <i class="fas fa-list me-1"></i>
                        @elseif ($value === 'menunggu')
                            <i class="fas fa-clock me-1"></i>
                        @elseif ($value === 'diterima')
                            <i class="fas fa-check me-1"></i>
                        @elseif ($value === 'selesai')
                            <i class="fas fa-check-double me-1"></i>
                        @else
                            <i class="fas fa-share me-1"></i>
                        @endif
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                @if ($tab === 'masuk')
                    @include('disposisi._tabel', ['data' => $masuk, 'mode' => 'masuk'])

                    <div class="mt-3">
                        {{ $masuk->appends(['tab' => 'masuk', 'status' => $filter])->links() }}
                    </div>
                @endif

                @if ($tab === 'keluar')
                    @include('disposisi._tabel', ['data' => $keluar, 'mode' => 'keluar'])

                    <div class="mt-3">
                        {{ $keluar->appends(['tab' => 'keluar', 'status' => $filter])->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Modal Pilih Dokumen --}}
<div class="modal fade modal-dispo" id="modalPilihDokumen" tabindex="-1" aria-labelledby="modalPilihDokumenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content shadow-lg">

            <div class="modal-header px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="modalPilihDokumenLabel">
                        <i class="fas fa-paper-plane me-2"></i>Buat Disposisi
                    </h5>
                    <small class="opacity-75">Pilih dokumen yang ingin kamu disposisikan.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <button type="button"
                                class="btn tipe-card w-100 text-start btn-tipe active"
                                data-tipe="memo"
                                onclick="gantiTipe('memo')">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                                     style="width:42px;height:42px;">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Memo</div>
                                    <small class="text-muted">Disposisi dari dokumen memo</small>
                                </div>
                            </div>
                        </button>
                    </div>

                    <div class="col-md-6">
                        <button type="button"
                                class="btn tipe-card w-100 text-start btn-tipe"
                                data-tipe="undangan"
                                onclick="gantiTipe('undangan')">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                                     style="width:42px;height:42px;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Undangan</div>
                                    <small class="text-muted">Disposisi dari dokumen undangan</small>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="input-group input-group-lg mb-3">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text"
                           id="searchDokumen"
                           class="form-control"
                           placeholder="Cari judul atau nomor dokumen..."
                           oninput="cariDokumen(this.value)">
                </div>

                <div id="loadingDokumen" class="text-center py-5 d-none">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    <span class="text-muted">Memuat dokumen...</span>
                </div>

                <div id="hasilDokumen"></div>

                <div id="emptyDokumen" class="text-center text-muted py-5 d-none">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:70px;height:70px;">
                        <i class="fas fa-inbox fa-2x text-secondary"></i>
                    </div>
                    <h6 class="fw-bold">Tidak ada dokumen</h6>
                    <p class="mb-0 small">Dokumen yang bisa didisposisi belum tersedia.</p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let tipeAktif  = 'memo';
    let searchTimer = null;

    const searchUrl = "{{ route('disposisi.cariDokumen') }}";

    function gantiTipe(tipe) {
        tipeAktif = tipe;

        document.querySelectorAll('.btn-tipe').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tipe === tipe);
        });

        document.getElementById('searchDokumen').value = '';
        muatDokumen('');
    }

    function cariDokumen(keyword) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => muatDokumen(keyword), 350);
    }

    async function muatDokumen(keyword) {
        const loading = document.getElementById('loadingDokumen');
        const hasil   = document.getElementById('hasilDokumen');
        const empty   = document.getElementById('emptyDokumen');

        loading.classList.remove('d-none');
        hasil.innerHTML = '';
        empty.classList.add('d-none');

        try {
            const params = new URLSearchParams({ tipe: tipeAktif, q: keyword });
            const res = await fetch(`${searchUrl}?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();

            loading.classList.add('d-none');

            if (!data.dokumen || data.dokumen.length === 0) {
                empty.classList.remove('d-none');
                return;
            }

            hasil.innerHTML = `
                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    ${data.dokumen.map(dok => `
                        <a href="${dok.url_disposisi}"
                           class="list-group-item list-group-item-action dokumen-item d-flex align-items-start gap-3 py-3">
                            <div class="flex-shrink-0 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                                 style="width:42px;height:42px;">
                                <i class="fas ${tipeAktif === 'memo' ? 'fa-file-alt' : 'fa-calendar-alt'}"></i>
                            </div>

                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-truncate">${escHtml(dok.judul)}</div>
                                <small class="text-muted">
                                    ${escHtml(dok.nomor ?? '-')}
                                    ${dok.tgl_dibuat ? ' · ' + escHtml(dok.tgl_dibuat) : ''}
                                </small>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                                    Pilih
                                </span>
                            </div>
                        </a>
                    `).join('')}
                </div>
            `;

        } catch (e) {
            loading.classList.add('d-none');
            hasil.innerHTML = `
                <div class="alert alert-danger border-0 rounded-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Gagal memuat dokumen. Silakan coba lagi.
                </div>
            `;
        }
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    document.getElementById('modalPilihDokumen')
        .addEventListener('show.bs.modal', () => muatDokumen(''));
</script>
@endpush
