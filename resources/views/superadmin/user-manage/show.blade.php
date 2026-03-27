@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
    <div class="container-fluid px-4 py-0 mt-0">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">

                {{-- Header --}}
                <div class="row mb-3">
                    <div class="col">
                        <h3 class="fw-bold mb-0">Detail Data Pengguna</h3>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <a href="{{ route('user-manage.edit', $user->id) }}" class="btn btn-warning rounded-3">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
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
                                / Detail - {{ $user->firstname }} {{ $user->lastname }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Informasi Akun --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-id-card me-2"></i>Informasi Akun
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-hashtag text-muted me-1"></i>ID Pengguna
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-fingerprint text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0"
                                        value="{{ $user->id }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-envelope text-muted me-1"></i>Email
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-at text-primary"></i>
                                    </span>
                                    <input type="email" class="form-control bg-light border-start-0"
                                        value="{{ $user->email }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-shield-alt text-muted me-1"></i>Status Akun
                                </label>
                                @php
                                    $status      = $user->deleted_at ? 'Non-Aktif' : 'Aktif';
                                    $statusClass = $user->deleted_at ? 'danger'    : 'success';
                                    $statusIcon  = $user->deleted_at ? 'times-circle' : 'check-circle';
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-{{ $statusIcon }} text-{{ $statusClass }}"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control bg-light border-start-0 fw-bold text-{{ $statusClass }}"
                                        value="{{ $status }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Pribadi --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-user me-2"></i>Data Pribadi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-user-tag text-muted me-1"></i>Nama Depan
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-signature text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0"
                                        value="{{ $user->firstname }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-user-tag text-muted me-1"></i>Nama Belakang
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-signature text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0"
                                        value="{{ $user->lastname ?? '-' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-id-badge text-muted me-1"></i>NIP
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-address-card text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0"
                                        value="{{ $user->nip }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-phone text-muted me-1"></i>No. Telepon
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-mobile-alt text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0"
                                        value="{{ $user->phone_number }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Organisasi & Posisi --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-building me-2"></i>Organisasi & Posisi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-sitemap text-muted me-1"></i>Organisasi
                                </label>
                                @php
                                    $orgName = $user->unit->name_unit
                                        ?? ($user->section->name_section
                                        ?? ($user->department->name_department
                                        ?? ($user->divisi->nm_divisi
                                        ?? ($user->director->name_director ?? '-'))));
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-network-wired text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0"
                                        value="{{ $orgName }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-briefcase text-muted me-1"></i>Posisi
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user-tie text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light border-start-0"
                                        value="{{ $user->position->nm_position ?? '-' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hak Akses & Area Kerja --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-shield-alt me-2"></i>Hak Akses & Area Kerja
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            {{-- Hak Akses --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="fas fa-user-shield text-muted me-1"></i>Hak Akses
                                </label>
                                @php
                                    switch ($user->role_id_role) {
                                        case 1:
                                            $roleName  = 'Superadmin';
                                            $roleClass = 'primary';
                                            $roleIcon  = 'star';
                                            break;
                                        case 2:
                                            $roleName  = 'User';
                                            $roleClass = 'info';
                                            $roleIcon  = 'user';
                                            break;
                                        case 3:
                                            $roleName  = 'Admin';
                                            $roleClass = 'warning';
                                            $roleIcon  = 'cog';
                                            break;
                                        default:
                                            $roleName  = '-';
                                            $roleClass = 'secondary';
                                            $roleIcon  = 'question';
                                    }
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-{{ $roleIcon }} text-{{ $roleClass }}"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control bg-light border-start-0 fw-bold text-{{ $roleClass }}"
                                        value="{{ $roleName }}" readonly>
                                </div>
                            </div>

                            {{-- Kode Bagian --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary mb-2">
                                    <i class="fas fa-tags text-muted me-1"></i>Kode Bagian Kerja
                                </label>

                                @php
                                    $kodeBagianArray = $user->kode_bagian
                                        ? explode(';', $user->kode_bagian)
                                        : [];
                                @endphp

                                @if (count($kodeBagianArray) > 0)
                                    <div class="border rounded p-3 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted fw-semibold">
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                Total: <span class="badge bg-primary">{{ count($kodeBagianArray) }}</span> bagian
                                            </small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($kodeBagianArray as $kode)
                                                @php
                                                    $bagian = $bagianKerja->firstWhere('kode_bagian', trim($kode));
                                                @endphp
                                                <div class="badge bg-primary-subtle border border-primary text-primary px-3 py-2">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <strong>{{ trim($kode) }}</strong>
                                                    @if ($bagian)
                                                        <span class="ms-1">- {{ $bagian->nama_bagian }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="border rounded p-4 bg-white text-center">
                                        <i class="fas fa-inbox text-muted mb-2" style="font-size:2rem;"></i>
                                        <p class="text-muted mb-0">Tidak ada kode bagian yang ditugaskan</p>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('user-manage.edit', $user->id) }}" class="btn btn-warning text-dark">
                        <i class="fas fa-edit me-1"></i>Edit Data
                    </a>
                    <a href="{{ route('user.manage') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
