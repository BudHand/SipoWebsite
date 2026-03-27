@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col">
                        <h3 class="fw-bold mb-3">Manajemen Pengguna</h3>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="button" class="btn btn-success rounded-3" onclick="showUploadModal()">
                            <i class="far fa-file-excel"></i> Import File
                        </button>
                        <a href="{{ route('user-manage.create') }}" class="btn btn-black rounded-3">
                            + Tambah Pengguna
                        </a>
                    </div>
                </div>

                {{-- Breadcrumb --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Pengaturan / Manajemen Pengguna</span>
                        </div>
                    </div>
                </div>

                {{-- Search & Filter --}}
                <form method="GET" action="{{ route('user.manage') }}" class="row g-2 mb-3 align-items-center">
                    <input type="hidden" name="view" value="{{ $view }}">

                    <div class="col-auto">
                        <select name="per_page" class="form-select rounded-3" style="max-width:100px;">
                            <option value="10"  {{ request('per_page') == 10  ? 'selected' : '' }}>10</option>
                            <option value="25"  {{ request('per_page') == 25  ? 'selected' : '' }}>25</option>
                            <option value="50"  {{ request('per_page') == 50  ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-12 col-md">
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control rounded-end-3"
                                placeholder="Cari Nama atau NIP..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-12 col-md-auto">
                        <select name="role" class="form-select rounded-3">
                            <option value="">Semua Role</option>
                            <option value="1" {{ request('role') === '1' ? 'selected' : '' }}>Superadmin</option>
                            <option value="3" {{ request('role') === '3' ? 'selected' : '' }}>Admin</option>
                            <option value="2" {{ request('role') === '2' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto">
                        <select name="view" class="form-select rounded-3">
                            <option value="all"     {{ $view == 'all'     ? 'selected' : '' }}>Semua User</option>
                            <option value="active"  {{ $view == 'active'  ? 'selected' : '' }}>User Aktif</option>
                            <option value="deleted" {{ $view == 'deleted' ? 'selected' : '' }}>User Non-Aktif</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered custom-table-bagian align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Nama</th>
                                <th class="text-center">NIP</th>
                                <th class="text-center">Bagian Kerja</th>
                                <th class="text-center">Posisi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Hak Akses</th>
                                @if ($view !== 'deleted')
                                    <th class="text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    {{-- Nama --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($user->profile_image)
                                                <img src="data:image/png;base64,{{ $user->profile_image }}"
                                                    class="rounded-circle me-2" width="35" height="35">
                                            @else
                                                <i class="fas fa-user-circle fa-2x text-secondary me-2"></i>
                                            @endif
                                            {{ $user->firstname }} {{ $user->lastname }}
                                        </div>
                                    </td>

                                    {{-- NIP --}}
                                    <td>{{ $user->nip }}</td>

                                    {{-- Bagian Kerja --}}
                                    <td>
                                        @if ($user->unit)
                                            {{ $user->unit->name_unit }}
                                        @elseif ($user->section)
                                            {{ $user->section->name_section }}
                                        @elseif ($user->department)
                                            {{ $user->department->name_department }}
                                        @elseif ($user->divisi)
                                            {{ $user->divisi->nm_divisi }}
                                        @elseif ($user->director)
                                            {{ $user->director->name_director }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- Posisi --}}
                                    <td class="text-center">{{ $user->position->nm_position ?? '-' }}</td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @if ($user->deleted_at)
                                            <button type="button" class="btn btn-danger btn-sm btn-restore"
                                                style="width:80px;"
                                                data-id="{{ $user->id }}"
                                                data-firstname="{{ $user->firstname }}"
                                                data-lastname="{{ $user->lastname }}">
                                                Non-Aktif
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-success btn-sm btn-delete"
                                                style="width:80px;"
                                                data-id="{{ $user->id }}"
                                                data-firstname="{{ $user->firstname }}"
                                                data-lastname="{{ $user->lastname }}">
                                                Aktif
                                            </button>
                                        @endif
                                    </td>

                                    {{-- Hak Akses --}}
                                    <td class="text-center">
                                        @if ($user->role->id_role == 1)
                                            <span class="badge bg-primary">Superadmin</span>
                                        @elseif ($user->role->id_role == 2)
                                            <span class="badge bg-info">User</span>
                                        @elseif ($user->role->id_role == 3)
                                            <span class="badge bg-warning">Admin</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    @if ($view !== 'deleted')
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                {{-- View --}}
                                                <a href="{{ route('user-manage.show', $user->id) }}"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#51a1f1; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    title="Lihat">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                {{-- Edit --}}
                                                <a href="{{ route('user-manage.edit', $user->id) }}"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FBC02D; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Pengguna tidak ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

    {{-- Modal Notifikasi --}}
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-success text-white text-center rounded-3">
                <div class="modal-body">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p id="successModalMessage">Berhasil!</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .swal2-icon.no-border { border: none !important; }
    </style>
@endsection

@push('scripts')
<script>
    // ─── Notifikasi Session ───────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Gagal',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        @endif
    });

    // ─── Nonaktifkan User ─────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const userId    = this.dataset.id;
                const fullName  = `${this.dataset.firstname} ${this.dataset.lastname}`;

                Swal.fire({
                    title: 'Yakin ingin menonaktifkan <b style="color:red;">' + fullName + '</b>?',
                    text: 'Pengguna yang tidak aktif tidak dapat menggunakan sistem. Pengguna nonaktif dapat diaktifkan kembali.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, nonaktifkan',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        fetch(`/user-manage/delete/${userId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(function (data) {
                            Swal.fire('Berhasil!', data.success, 'success')
                                .then(() => location.reload());
                        })
                        .catch(function () {
                            Swal.fire('Error!', 'Gagal menonaktifkan pengguna', 'error');
                        });
                    }
                });
            });
        });
    });

    // ─── Aktifkan User ────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-restore').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const userId   = this.dataset.id;
                const fullName = `${this.dataset.firstname} ${this.dataset.lastname}`;

                Swal.fire({
                    title: 'Yakin ingin mengaktifkan <b style="color:green;">' + fullName + '</b>?',
                    text: 'Pengguna yang diaktifkan dapat kembali menggunakan sistem.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4bb543',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, aktifkan',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        fetch(`/user-manage/restore/${userId}`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(function (data) {
                            Swal.fire('Berhasil!', data.success, 'success')
                                .then(() => location.reload());
                        })
                        .catch(function () {
                            Swal.fire('Error!', 'Gagal mengaktifkan pengguna', 'error');
                        });
                    }
                });
            });
        });
    });

    // ─── Import Excel ─────────────────────────────────────────────────────────
    function showUploadModal() {
        Swal.fire({
            title: 'Import File?',
            html: `
                Anda dapat mengunggah file Excel untuk menambahkan pengguna baru.<br>
                Unduh format file Excel <a href="/Format Data User SIPO.xlsx" target="_blank">disini</a>.<br>
                <span class="text-danger" style="font-size:medium">
                    Hanya mendukung format <strong>.xlsx</strong>
                </span>
                <br><br>
                <input type="file" id="fileInput" class="form-control rounded-3"
                    style="padding:20px;" accept=".xlsx">
            `,
            iconHtml: `<i class="fas fa-cloud-arrow-up"></i>`,
            customClass: { icon: 'no-border' },
            showCancelButton: true,
            cancelButtonText: 'Batal',
            confirmButtonText: 'Unggah',
            preConfirm: function () {
                const file = document.getElementById('fileInput').files[0];
                if (!file) {
                    Swal.showValidationMessage('Harap pilih file Excel yang valid');
                    return false;
                }
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext !== 'xlsx') {
                    Swal.showValidationMessage('Format file tidak valid. Harap pilih file Excel (.xlsx)');
                    return false;
                }

                const formData = new FormData();
                formData.append('file_user', file);

                return fetch('{{ route('user-manage.import') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(function (data) {
                    if (!data.status) throw new Error(data.message || 'Gagal mengimpor file');
                    return data;
                })
                .catch(function (err) {
                    Swal.showValidationMessage(err.message);
                });
            }
        }).then(function (result) {
            if (result.value) {
                Swal.fire({
                    icon: result.value.status ? 'success' : 'error',
                    title: result.value.status ? 'Berhasil' : 'Gagal',
                    text: result.value.message,
                    confirmButtonText: 'OK'
                }).then(function (r) {
                    if (r.isConfirmed) location.reload();
                });
            }
        });
    }
</script>
@endpush
