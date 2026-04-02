@extends('layouts.app')

@section('title', 'Undangan Diterima')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                <h3 class="fw-bold mb-3">Undangan Diterima</h3>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('manager.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Undangan Diterima</span>
                        </div>
                    </div>
                </div>

                {{-- Filter --}}
                <form class="row g-2 align-items-center" method="GET" action="{{ route('undangan.diterima') }}">
                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;"
                            onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto d-flex align-items-center">
                        <input value="{{ request('tgl_dibuat_awal') }}" type="date" class="form-control rounded-3"
                            name="tgl_dibuat_awal" placeholder="Tanggal Awal">
                        <span class="mx-1">→</span>
                        <input value="{{ request('tgl_dibuat_akhir') }}" type="date" class="form-control rounded-3"
                            name="tgl_dibuat_akhir" placeholder="Tanggal Akhir">
                    </div>

                    <div class="col-12 col-md">
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control rounded-end-3" name="search"
                                value="{{ request('search') }}" placeholder="Cari judul atau nomor undangan">
                        </div>
                    </div>

                    <div class="col-12 col-md-auto">
                        <select class="form-select rounded-3" name="kode" id="kode" aria-label="Pilih Kode"
                            onchange="this.form.submit()">
                            <option value="" {{ !request()->filled('kode') ? 'selected' : '' }}>Semua Divisi</option>
                            @foreach ($kode ?? collect() as $k)
                                <option value="{{ $k }}" {{ request('kode') == $k ? 'selected' : '' }}>
                                    {{ $k }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>

                {{-- Tabel --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered custom-table-bagian">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:20%;">Perihal</th>
                                <th class="text-center" style="width:12%;">Tanggal Rapat</th>
                                <th class="text-center" style="width:20%;">Dokumen</th>
                                <th class="text-center" style="width:12%;">Tanggal Disahkan</th>
                                <th class="text-center" style="width:10%;">Kode Bagian</th>
                                <th class="text-center" style="width:13%;">Status</th>
                                <th class="text-center" style="width:8%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($undangans as $index => $undangan)
                                @php
                                    $status = $undangan->final_status ?? $undangan->status;

                                    $statusClass = match ($status) {
                                        'reject' => 'text-danger',
                                        'correction' => 'text-warning',
                                        'approve' => 'text-success',
                                        default => '',
                                    };

                                    $statusStyle = $status === 'pending' ? 'color: #0dcaf0;' : '';

                                    $badge = match ($status) {
                                        'reject' => ['class' => 'bg-danger', 'text' => 'Ditolak'],
                                        'pending' => ['class' => 'bg-info', 'text' => 'Diproses'],
                                        'correction' => ['class' => 'bg-warning', 'text' => 'Dikoreksi'],
                                        'approve' => ['class' => 'bg-success', 'text' => 'Diterima'],
                                        default => ['class' => 'bg-secondary', 'text' => ucfirst($status ?? '-')],
                                    };

                                    $sumberLabel = match ($undangan->sumber_diterima ?? '-') {
                                        'tembusan' => 'via tembusan',
                                        'bcc' => 'via bcc',
                                        default => null,
                                    };
                                @endphp

                                <tr>
                                    <td class="nomor text-center">
                                        {{ ($undangans->firstItem() ?? 0) + $index }}
                                    </td>

                                    <td class="nama-dokumen {{ $statusClass }}" style="{{ $statusStyle }}">
                                        {{ \Illuminate\Support\Str::limit($undangan->judul, 35, '...') }}
                                    </td>

                                    <td class="text-center">
                                        {{ $undangan->tgl_rapat ? \Carbon\Carbon::parse($undangan->tgl_rapat)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ $undangan->nomor_undangan ?? '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ $undangan->tgl_disahkan ? \Carbon\Carbon::parse($undangan->tgl_disahkan)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ $undangan->kode ?? $undangan->kode_bagian ?? '-' }}
                                    </td>

                                    <td class="text-center">
                                        <span class="badge {{ $badge['class'] }} px-3 py-2">
                                            {{ $badge['text'] }}
                                        </span>

                                        @if ($sumberLabel)
                                            <div class="mt-1">
                                                <small class="text-muted">{{ $sumberLabel }}</small>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button title="Detail"
                                                class="btn btn-sm rounded-circle text-white border-0 bg-info"
                                                style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                onclick="window.location.href='{{ route('view.undangan', ['id' => $undangan->id_undangan]) }}'">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            @if (in_array($undangan->status, ['approve', 'reject']))
                                                <button type="button"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FFAD46; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#arsipModal"
                                                    data-route="{{ route('arsip.archive', ['document_id' => $undangan->id_undangan, 'jenis_document' => 'Undangan']) }}"
                                                    data-title="{{ $undangan->judul ?? $undangan->nama_dokumen }}"
                                                    title="Arsip">
                                                    <i class="fa-solid fa-archive fa-lg"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada undangan yang diterima.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $undangans->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Arsip --}}
    <div class="modal fade" id="arsipModal" tabindex="-1" aria-labelledby="arsipModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Arsipkan Undangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin mengarsipkan undangan <strong id="arsipUndanganTitle"></strong>?</p>
                    <p class="text-muted">Undangan yang diarsipkan akan dipindahkan ke arsip dan tidak akan muncul di daftar utama.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="confirmArsip">
                        <i class="fa-solid fa-archive me-1"></i>Arsip
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let currentRoute = null;

    document.addEventListener("DOMContentLoaded", function() {
        const arsipModal = document.getElementById("arsipModal");
        const confirmArsipBtn = document.getElementById("confirmArsip");

        arsipModal.addEventListener("show.bs.modal", function(event) {
            const button = event.relatedTarget;
            currentRoute = button.getAttribute("data-route");
            const undanganTitle = button.getAttribute("data-title");

            document.getElementById('arsipUndanganTitle').textContent = undanganTitle;
        });

        confirmArsipBtn.addEventListener("click", function(event) {
            event.preventDefault();

            if (!currentRoute) {
                showAlert('Route tidak ditemukan', 'error');
                return;
            }

            confirmArsipBtn.disabled = true;
            confirmArsipBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Mengarsipkan...';

            fetch(currentRoute, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Content-Type": "application/json"
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const modalInstance = bootstrap.Modal.getInstance(arsipModal);
                modalInstance.hide();

                return showAlert('Undangan berhasil diarsipkan', 'success');
            })
            .then(() => {
                window.location.reload();
            })
            .catch(error => {
                console.error("Error:", error);
                showAlert('Terjadi kesalahan saat mengarsipkan undangan', 'error');
            })
            .finally(() => {
                confirmArsipBtn.disabled = false;
                confirmArsipBtn.innerHTML = '<i class="fa-solid fa-archive me-1"></i>Arsip';
            });
        });

        @if (session('success'))
            showAlert(@json(session('success')), 'success');
        @endif

        @if (session('error'))
            showAlert(@json(session('error')), 'error');
        @endif

        @if (session('warning'))
            showAlert(@json(session('warning')), 'warning');
        @endif

        @if (session('info'))
            showAlert(@json(session('info')), 'info');
        @endif
    });

    function showAlert(message, type) {
        const config = {
            text: message,
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                confirmButton: 'btn btn-primary px-4 py-2',
            },
            buttonsStyling: false
        };

        if (type === 'success') {
            config.title = 'Berhasil!';
            config.icon = 'success';
            config.customClass.confirmButton = 'btn btn-success px-4 py-2';
        } else if (type === 'error') {
            config.title = 'Error!';
            config.icon = 'error';
            config.customClass.confirmButton = 'btn btn-danger px-4 py-2';
        } else if (type === 'warning') {
            config.title = 'Peringatan!';
            config.icon = 'warning';
            config.customClass.confirmButton = 'btn btn-warning px-4 py-2';
        } else if (type === 'info') {
            config.title = 'Informasi';
            config.icon = 'info';
            config.customClass.confirmButton = 'btn btn-info px-4 py-2';
        }

        if (typeof Swal !== 'undefined') {
            return Swal.fire(config);
        }

        alert(message);
        return Promise.resolve();
    }
</script>
@endpush
