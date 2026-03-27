@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col">
                        <h3 class="fw-bold mb-0">Edit Data Pengguna</h3>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('user.manage') }}" class="btn btn-outline-secondary rounded-3">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>

                {{-- Breadcrumb --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="bg-white border rounded-2 px-3 py-2 w-100 d-flex align-items-center">
                            <a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none text-primary">Beranda</a>
                            <span class="text-muted ms-1">/ Pengaturan /
                                <a href="{{ route('user.manage') }}" class="text-decoration-none text-primary">Manajemen Pengguna</a>
                                / Edit Pengguna - {{ $user->firstname }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form action="{{ route('user-manage/update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Informasi Akun --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-semibold text-warning">
                                <i class="fas fa-id-card me-2"></i>Informasi Akun
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-hashtag text-muted me-1"></i>ID Pengguna
                                    </label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $user->id }}" disabled>
                                    <small class="text-muted">ID tidak dapat diubah</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-envelope text-muted me-1"></i>Email
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}"
                                        placeholder="contoh@email.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Data Pribadi --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-semibold text-warning">
                                <i class="fas fa-user me-2"></i>Data Pribadi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Nama Depan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="firstname"
                                        class="form-control @error('firstname') is-invalid @enderror"
                                        value="{{ old('firstname', $user->firstname) }}"
                                        placeholder="Masukkan nama depan" required>
                                    @error('firstname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nama Akhir</label>
                                    <input type="text" name="lastname"
                                        class="form-control @error('lastname') is-invalid @enderror"
                                        value="{{ old('lastname', $user->lastname) }}"
                                        placeholder="Masukkan nama akhir (opsional)">
                                    @error('lastname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-id-badge text-muted me-1"></i>NIP
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nip"
                                        class="form-control @error('nip') is-invalid @enderror"
                                        value="{{ old('nip', $user->nip) }}"
                                        placeholder="Masukkan NIP" required>
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-phone text-muted me-1"></i>No. Telepon
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="phone_number"
                                        class="form-control @error('phone_number') is-invalid @enderror"
                                        value="{{ old('phone_number', $user->phone_number) }}"
                                        placeholder="08xxxxxxxxxx" required>
                                    @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Keamanan --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-semibold text-warning">
                                <i class="fas fa-lock me-2"></i>Keamanan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info py-2 mb-3">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Info:</strong> Kosongkan field kata sandi jika tidak ingin mengubah password.
                                </small>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-key text-muted me-1"></i>Kata Sandi Baru (Opsional)
                                    </label>
                                    <input type="password" name="password" id="password_edit"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Minimal 8 karakter" minlength="8">
                                    <small class="text-muted">Minimal 8 karakter</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Konfirmasi Kata Sandi</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation_edit"
                                        class="form-control"
                                        placeholder="Ulangi kata sandi" minlength="8"
                                        oninput="this.setCustomValidity(
                                            this.value !== document.getElementById('password_edit').value
                                            ? 'Konfirmasi kata sandi tidak cocok' : '')">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Organisasi & Posisi --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-semibold text-warning">
                                <i class="fas fa-building me-2"></i>Organisasi & Posisi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- Tentukan nilai awal organisasi (ambil level terdalam yang terisi) --}}
                                @php
                                    // Prioritas: unit > section > department > divisi > director
                                    if ($user->unit_id_unit) {
                                        $selectedType = 'unit';
                                        $selectedId   = (int) $user->unit_id_unit;
                                    } elseif ($user->section_id_section) {
                                        $selectedType = 'section';
                                        $selectedId   = (int) $user->section_id_section;
                                    } elseif ($user->department_id_department) {
                                        $selectedType = 'department';
                                        $selectedId   = (int) $user->department_id_department;
                                    } elseif ($user->divisi_id_divisi) {
                                        $selectedType = 'divisi';
                                        $selectedId   = (int) $user->divisi_id_divisi;
                                    } elseif ($user->director_id_director) {
                                        $selectedType = 'director';
                                        $selectedId   = (int) $user->director_id_director;
                                    } else {
                                        $selectedType = null;
                                        $selectedId   = null;
                                    }
                                @endphp

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-sitemap text-muted me-1"></i>Organisasi
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="parent_id" name="parent_id" required>
                                        <option value="">-- Pilih Organisasi --</option>
                                        @foreach ($orgOptions as $opt)
                                            <option value="{{ $opt['value'] }}"
                                                data-type="{{ $opt['type'] }}"
                                                {{ $selectedId && (int)$opt['value'] === $selectedId && $opt['type'] === $selectedType ? 'selected' : '' }}>
                                                {!! $opt['label'] !!}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="parent_type" id="parent_type"
                                        value="{{ $selectedType ?? '' }}">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Biarkan pilihan awal jika tidak ingin mengubah
                                    </small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-briefcase text-muted me-1"></i>Posisi
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="position_id_position" class="form-select" required>
                                        <option value="">-- Pilih Posisi --</option>
                                        @foreach ($positions as $p)
                                            <option value="{{ $p->id_position }}"
                                                {{ (string)$p->id_position === (string)($user->position_id_position ?? '') ? 'selected' : '' }}>
                                                {{ $p->nm_position }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hak Akses & Kode Bagian --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-semibold text-warning">
                                <i class="fas fa-shield-alt me-2"></i>Hak Akses & Area Kerja
                            </h6>
                        </div>
                        <div class="card-body">

                            {{-- Hak Akses --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold mb-3">
                                    <i class="fas fa-user-shield text-muted me-1"></i>Hak Akses
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="role_id_role" value="1" id="role1"
                                                    {{ $user->role_id_role == 1 ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="role1">
                                                    <div class="fw-bold text-primary">
                                                        <i class="fas fa-star me-1"></i>Superadmin
                                                    </div>
                                                    <small class="text-muted">Akses penuh sistem</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="role_id_role" value="2" id="role2"
                                                    {{ $user->role_id_role == 2 ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role2">
                                                    <div class="fw-bold text-info">
                                                        <i class="fas fa-user me-1"></i>User
                                                    </div>
                                                    <small class="text-muted">Akses terbatas</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="role_id_role" value="3" id="role3"
                                                    {{ $user->role_id_role == 3 ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role3">
                                                    <div class="fw-bold text-warning">
                                                        <i class="fas fa-cog me-1"></i>Admin
                                                    </div>
                                                    <small class="text-muted">Kelola data</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kode Bagian --}}
                            @php
                                $selectedKodeBagian = $user->kode_bagian
                                    ? explode(';', $user->kode_bagian)
                                    : [];
                            @endphp

                            <div>
                                <label class="form-label fw-semibold mb-2">
                                    <i class="fas fa-tags text-muted me-1"></i>Kode Bagian
                                </label>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Pilih satu atau lebih bagian kerja yang akan dikelola pengguna.
                                    <strong>Centang kotak</strong> untuk memilih.
                                </p>

                                {{-- Badge terpilih saat ini --}}
                                @if (count($selectedKodeBagian) > 0)
                                    <div class="alert alert-success py-2 mb-3">
                                        <small class="fw-semibold">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Terpilih saat ini ({{ count($selectedKodeBagian) }}):
                                        </small>
                                        <div class="mt-2">
                                            @foreach ($selectedKodeBagian as $kode)
                                                @php
                                                    $bagian = $bagianKerja->firstWhere('kode_bagian', $kode);
                                                @endphp
                                                <span class="badge bg-success me-1 mb-1">
                                                    {{ $kode }}{{ $bagian ? ' - ' . $bagian->nama_bagian : '' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Fallback jika tidak ada yang dipilih --}}
                                <input type="hidden" name="kode_bagian[]" value="">

                                <div class="border rounded bg-white" style="max-height:320px; overflow-y:auto;">
                                    @foreach ($bagianKerja as $index => $b)
                                        <div class="px-3 py-2 {{ $index > 0 ? 'border-top' : '' }}">
                                            <div class="form-check d-flex align-items-center">
                                                <input class="form-check-input mt-0" type="checkbox"
                                                    name="kode_bagian[]"
                                                    value="{{ $b->kode_bagian }}"
                                                    id="bagian_{{ $b->kode_bagian }}"
                                                    {{ in_array($b->kode_bagian, $selectedKodeBagian) ? 'checked' : '' }}>
                                                <label class="form-check-label d-flex align-items-center gap-2 ms-2"
                                                    for="bagian_{{ $b->kode_bagian }}">
                                                    <span class="badge bg-warning text-dark text-uppercase"
                                                        style="width:70px;">
                                                        {{ $b->kode_bagian }}
                                                    </span>
                                                    <span class="text-dark">{{ $b->nama_bagian ?? '-' }}</span>
                                                    @if (in_array($b->kode_bagian, $selectedKodeBagian))
                                                        <i class="fas fa-check-circle text-success small ms-1"></i>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="alert alert-info mt-3 mb-0 py-2">
                                    <small>
                                        <i class="fas fa-lightbulb me-1"></i>
                                        <strong>Tips:</strong> Scroll ke bawah untuk melihat lebih banyak pilihan.
                                        Anda bisa memilih lebih dari satu bagian.
                                    </small>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Footer Aksi --}}
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('user.manage') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-save me-1"></i>Perbarui Data
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Sync parent_type saat dropdown organisasi berubah
    document.getElementById('parent_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('parent_type').value = selected
            ? (selected.getAttribute('data-type') || '')
            : '';
    });

    // Notifikasi session
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('user.manage') }}';
                }
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
</script>
@endpush
