@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
@php
    // routes WAJIB dikirim dari controller: $routes = ['memo_keluar'=>..., ...]
    $routes = $routes ?? [];

    // angka aman
    $jumlahMemoKeluar     = (int) ($jumlahMemoKeluar ?? 0);
    $jumlahMemoMasuk      = (int) ($jumlahMemoMasuk ?? 0);
    $jumlahUndanganKeluar = (int) ($jumlahUndanganKeluar ?? 0);
    $jumlahUndanganMasuk  = (int) ($jumlahUndanganMasuk ?? 0);
    $jumlahRisalah        = (int) ($jumlahRisalah ?? 0);

    // user display
    $first = auth()->user()->firstname ?? '';
    $last  = auth()->user()->lastname ?? '';
    $full  = trim($first.' '.$last);
    $full  = $full !== '' ? $full : 'Pengguna';

    $posName = Auth::user()->position->nm_position ?? '';
    $posName = trim(preg_replace('/\([^)]*\)/', '', $posName));

    // helper kecil untuk kartu
    $cards = [
        [
            'title' => 'Memo Keluar',
            'count' => $jumlahMemoKeluar,
            'href'  => $routes['memo_keluar'] ?? '#',
            'grad'  => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'icon'  => 'fas fa-paper-plane',
            'col'   => 'col-12 col-sm-6 col-lg-3',
        ],
        [
            'title' => 'Memo Masuk',
            'count' => $jumlahMemoMasuk,
            'href'  => $routes['memo_masuk'] ?? '#',
            'grad'  => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'icon'  => 'fas fa-inbox',
            'col'   => 'col-12 col-sm-6 col-lg-3',
        ],
        [
            'title' => 'Undangan Keluar',
            'count' => $jumlahUndanganKeluar,
            'href'  => $routes['undangan_keluar'] ?? '#',
            'grad'  => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'icon'  => 'fas fa-calendar-plus',
            'col'   => 'col-12 col-sm-6 col-lg-3',
        ],
        [
            'title' => 'Undangan Masuk',
            'count' => $jumlahUndanganMasuk,
            'href'  => $routes['undangan_masuk'] ?? '#',
            'grad'  => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            'icon'  => 'fas fa-calendar-check',
            'col'   => 'col-12 col-sm-6 col-lg-3',
        ],
        [
            'title' => 'Risalah Rapat',
            'count' => $jumlahRisalah,
            'href'  => $routes['risalah'] ?? '#',
            'grad'  => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
            'icon'  => 'fas fa-clipboard-list',
            'col'   => 'col-12 col-lg-12',
            'iconBoxP' => 'p-4',
            'icon2x' => true,
        ],
    ];

    // notif count aman
    $notifCount = isset($notifikasiByDate) ? (int) $notifikasiByDate->flatten()->count() : 0;
@endphp

<div class="container-fluid px-4 py-0 mt-0">

    {{-- Header --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <h3 class="fw-bold mb-2">Beranda</h3>
            <p class="mb-0">
                Selamat datang <strong>{{ $full }}</strong>
                di <a href="#" class="text-decoration-none fw-semibold">Sistem Persuratan</a>!
                Anda login sebagai
                <span class="badge rounded-pill text-bg-warning text-dark">
                    {{ $posName }}
                </span>
            </p>
        </div>
    </div>

    {{-- Tinjauan Dokumen --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <h4 class="fw-bold mb-3">Tinjauan Dokumen</h4>

            <div class="row g-3">
                @foreach ($cards as $c)
                    <div class="{{ $c['col'] }}">
                        <a href="{{ $c['href'] }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 card-hover"
                                 style="background: {{ $c['grad'] }}; cursor: pointer;">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <p class="mb-1 text-white small">{{ $c['title'] }}</p>
                                            <h2 class="fw-bold mb-0">{{ (int) $c['count'] }}</h2>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-3 {{ $c['iconBoxP'] ?? 'p-3' }}">
                                            <i class="{{ $c['icon'] }} {{ !empty($c['icon2x']) ? 'fa-2x' : '' }}"></i>
                                        </div>
                                    </div>
                                    <small class="text-white">
                                        <i class="fas fa-arrow-right me-1"></i> Lihat Detail
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    {{-- Chart Distribusi Dokumen --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <h4 class="fw-bold mb-3">Distribusi Dokumen</h4>
            <div class="position-relative" style="height: 280px; width: 100%;">
                <canvas id="chartDistribusi" style="width: 100%; height: 100%; display: block;"></canvas>
            </div>
        </div>
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="card shadow-sm border-0">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Aktivitas Terbaru</h4>
                <span class="badge bg-primary">{{ $notifCount }} Notifikasi</span>
            </div>

            @if(empty($notifikasiByDate) || $notifikasiByDate->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fs-1 text-muted mb-3"></i>
                    <p class="text-muted">Belum ada aktivitas terbaru</p>
                </div>
            @else
                <div class="row gy-2">
                    @foreach ($notifikasiByDate as $tanggal => $list)

                        @if (count($notifikasiByDate) > 1)
                            <div class="col-12">
                                <div class="d-flex align-items-center my-2">
                                    <h6 class="text-muted mb-0 me-2">{{ $tanggal }}</h6>
                                    <hr class="flex-grow-1 m-0">
                                </div>
                            </div>
                        @endif

                        @foreach ($list as $notif)
                            @php
                                $judul = strtolower((string)($notif->judul ?? ''));

                                $bgColor = 'secondary';
                                $icon = 'fas fa-file';

                                if (str_contains($judul, 'risalah')) {
                                    $icon = 'fas fa-clipboard-list';
                                    if (str_contains($judul, 'tolak')) $bgColor = 'danger';
                                    elseif (str_contains($judul, 'koreksi') || str_contains($judul, 'revisi')) $bgColor = 'warning';
                                    elseif (str_contains($judul, 'setuju') || str_contains($judul, 'masuk') || str_contains($judul, 'kirim')) $bgColor = 'success';
                                } elseif (str_contains($judul, 'undangan')) {
                                    $icon = 'fas fa-calendar-check';
                                    if (str_contains($judul, 'tolak')) $bgColor = 'danger';
                                    elseif (str_contains($judul, 'revisi') || str_contains($judul, 'koreksi')) $bgColor = 'warning';
                                    elseif (str_contains($judul, 'setuju') || str_contains($judul, 'masuk') || str_contains($judul, 'kirim')) $bgColor = 'success';
                                } elseif (str_contains($judul, 'memo')) {
                                    $icon = 'fas fa-file-alt';
                                    if (str_contains($judul, 'tolak')) $bgColor = 'danger';
                                    elseif (str_contains($judul, 'revisi') || str_contains($judul, 'koreksi')) $bgColor = 'warning';
                                    elseif (str_contains($judul, 'setuju') || str_contains($judul, 'masuk') || str_contains($judul, 'kirim')) $bgColor = 'success';
                                }

                                $link = $notif->link ?? '#';
                                $judulDoc = (string)($notif->judul_document ?? '');
                                $waktu = \Carbon\Carbon::parse($notif->updated_at)->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H.i');
                            @endphp

                            <div class="col-12">
                                <a href="{{ $link }}" class="text-decoration-none">
                                    <div class="card border-start border-{{ $bgColor }} border-3 shadow-sm mb-2 hover-shadow-lg"
                                         style="transition: all 0.2s; cursor: pointer;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 flex-shrink-0">
                                                    <div class="bg-{{ $bgColor }} bg-opacity-10 d-flex align-items-center justify-content-center"
                                                         style="width: 46px; height: 46px; border-radius: 12px;">
                                                        <i class="{{ $icon }} text-white" style="font-size: 20px;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-1 text-dark lh-sm">{{ $notif->judul }}</h6>
                                                    <p class="mb-0 text-muted small lh-sm">
                                                        <i class="far fa-clock me-1"></i>
                                                        {{ $waktu }}
                                                        @if($judulDoc !== '')
                                                            • <span class="text-{{ $bgColor }} fw-semibold">{{ $judulDoc }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const memoKeluar = @json($jumlahMemoKeluar);
    const memoMasuk = @json($jumlahMemoMasuk);
    const undanganKeluar = @json($jumlahUndanganKeluar);
    const undanganMasuk = @json($jumlahUndanganMasuk);
    const risalah = @json($jumlahRisalah);

    const ctxDistribusi = document.getElementById('chartDistribusi');
    if (ctxDistribusi) {
        new Chart(ctxDistribusi, {
            type: 'bar',
            data: {
                labels: ['Memo Keluar', 'Memo Masuk', 'Undangan Keluar', 'Undangan Masuk', 'Risalah Rapat'],
                datasets: [{
                    label: 'Jumlah Dokumen',
                    data: [memoKeluar, memoMasuk, undanganKeluar, undanganMasuk, risalah],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    document.querySelectorAll('.hover-shadow-lg').forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    document.querySelectorAll('.card-hover').forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 20px rgba(0,0,0,0.2)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.card-hover { transition: all 0.3s ease; }
</style>
@endpush
