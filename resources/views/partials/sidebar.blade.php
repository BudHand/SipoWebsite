{{--
    resources/views/partials/sidebar.blade.php
--}}

{{-- ============================================================ --}}
{{-- Helper functions — hanya dideklarasi sekali                  --}}
{{-- ============================================================ --}}
@once
    @php
        function sidebarCanAccess(array $item, string $role): bool
        {
            if (! isset($item['roles'])) {
                return true;
            }
            return in_array($role, $item['roles'], strict: true);
        }

        function sidebarIsActive(string|array $patterns): bool
        {
            foreach ((array) $patterns as $pattern) {
                if (request()->routeIs($pattern) || request()->is($pattern)) {
                    return true;
                }
            }
            return false;
        }

        function sidebarCollapseId(string $label, int $sectionIndex, int $itemIndex): string
        {
            return 'sidebar-' . md5("{$label}-{$sectionIndex}-{$itemIndex}");
        }
    @endphp
@endonce

{{-- ============================================================ --}}
{{-- Data & role user                                             --}}
{{-- ============================================================ --}}
@php
    $role = Auth::user()->role->nm_role;

    $belumDibacaDisposisi = \App\Models\Disposisi::whereJsonContains('kepada_user_id', Auth::id())
        ->whereNull('dibaca_at')
        ->count();

    $menuSections = [

        // ----------------------------------------------------------------
        // SECTION: MENU
        // ----------------------------------------------------------------
        [
            'title' => 'MENU',
            'items' => [

                [
                    'label'  => 'Counter Nomor Surat',
                    'icon'   => 'fas fa-sort-numeric-up',
                    'route'  => 'counter-nomor-surat.index',
                    'roles'  => ['superadmin', 'admin', 'manager'],
                    'active' => ['counter-nomor-surat.*'],
                ],

                // Dashboard — superadmin
                [
                    'label'  => 'Dashboard',
                    'icon'   => 'fas fa-home',
                    'route'  => 'superadmin.dashboard',
                    'roles'  => ['superadmin'],
                    'active' => ['superadmin.dashboard'],
                ],

                // Dashboard — admin & manager
                [
                    'label'  => 'Dashboard',
                    'icon'   => 'fas fa-home',
                    'route'  => 'dashboard',
                    'roles'  => ['admin', 'manager'],
                    'active' => ['dashboard', 'admin.dashboard'],
                ],

                // Memo — superadmin (flat)
                [
                    'label'  => 'Memo',
                    'icon'   => 'fas fa-file-alt',
                    'route'  => 'superadmin.memo.index',
                    'roles'  => ['superadmin'],
                    'active' => ['superadmin.memo.index'],
                ],

                // Memo — admin & manager (dengan sub-menu)
                [
                    'label'  => 'Memo',
                    'icon'   => 'fas fa-file-alt',
                    'roles'  => ['admin', 'manager'],
                    'active' => [
                        'memo.terkirim',
                        'memo.diterima',
                        'memo.create',
                        'memo.edit',
                        'view.memo-terkirim',
                        'view.memo-diterima',
                    ],
                    'children' => [
                        [
                            'label'  => 'Memo Keluar',
                            'route'  => 'memo.terkirim',
                            'active' => ['memo.terkirim', 'memo.create', 'memo.edit', 'view.memo-terkirim'],
                        ],
                        [
                            'label'  => 'Memo Masuk',
                            'route'  => 'memo.diterima',
                            'active' => ['memo.diterima', 'view.memo-diterima'],
                        ],
                    ],
                ],

                // Undangan Rapat — superadmin (flat)
                [
                    'label'  => 'Undangan Rapat',
                    'icon'   => 'fas fa-calendar-alt',
                    'route'  => 'superadmin.undangan.index',
                    'roles'  => ['superadmin'],
                    'active' => ['superadmin.undangan.index'],
                ],

                // Undangan Rapat — admin & manager (dengan sub-menu)
                [
                    'label'  => 'Undangan Rapat',
                    'icon'   => 'fas fa-calendar-alt',
                    'roles'  => ['admin', 'manager'],
                    'active' => [
                        'undangan.terkirim',
                        'undangan.diterima',
                        'admin.undangan.terkirim',
                        'admin.undangan.diterima',
                    ],
                    'children' => [
                        [
                            'label'  => 'Undangan Keluar',
                            'route'  => 'undangan.terkirim',
                            'active' => ['undangan.terkirim', 'admin.undangan.terkirim'],
                        ],
                        [
                            'label'  => 'Undangan Masuk',
                            'route'  => 'undangan.diterima',
                            'active' => ['undangan.diterima', 'admin.undangan.diterima'],
                        ],
                    ],
                ],

                // Risalah Rapat — superadmin (flat)
                [
                    'label'  => 'Risalah Rapat',
                    'icon'   => 'fas fa-clipboard-list',
                    'route'  => 'superadmin.risalah.index',
                    'roles'  => ['superadmin'],
                    'active' => ['superadmin.risalah.index'],
                ],

                // Risalah Rapat — admin & manager (flat)
                [
                    'label'  => 'Risalah Rapat',
                    'icon'   => 'fas fa-clipboard-list',
                    'route'  => 'risalah.index',
                    'roles'  => ['admin', 'manager'],
                    'active' => ['risalah.index'],
                ],

                // ── Disposisi — semua role, flat, badge belum dibaca ──────────
                [
                    'label'       => 'Disposisi',
                    'icon'        => 'fas fa-paper-plane',
                    'route'       => 'disposisi.index',
                    'roles'       => ['superadmin', 'admin', 'manager'],
                    'active'      => ['disposisi.*'],
                    'badge'       => $belumDibacaDisposisi > 0 ? $belumDibacaDisposisi : null,
                    'badge_class' => 'badge bg-danger ms-auto',
                ],

                // Arsip — semua role
                [
                    'label'  => 'Arsip',
                    'icon'   => 'fas fa-archive',
                    'roles'  => ['superadmin', 'admin', 'manager'],
                    'active' => ['arsip.*', 'arsip.memo', 'arsip.undangan', 'arsip.risalah'],
                    'children' => [
                        [
                            'label'  => 'Memo',
                            'route'  => 'arsip.memo',
                            'active' => ['arsip.memo'],
                        ],
                        [
                            'label'  => 'Undangan Rapat',
                            'route'  => 'arsip.undangan',
                            'active' => ['arsip.undangan'],
                        ],
                        [
                            'label'  => 'Risalah Rapat',
                            'route'  => 'arsip.risalah',
                            'active' => ['arsip.risalah'],
                        ],
                    ],
                ],

                // Laporan — superadmin saja
                [
                    'label'  => 'Laporan',
                    'icon'   => 'fas fa-book',
                    'roles'  => ['superadmin'],
                    'active' => [
                        'laporan*',
                        'laporan-memo.superadmin',
                        'laporan-undangan.superadmin',
                        'laporan-risalah.superadmin',
                    ],
                    'children' => [
                        [
                            'label'  => 'Memo',
                            'route'  => 'laporan-memo.superadmin',
                            'active' => ['laporan-memo.superadmin'],
                        ],
                        [
                            'label'  => 'Undangan Rapat',
                            'route'  => 'laporan-undangan.superadmin',
                            'active' => ['laporan-undangan.superadmin'],
                        ],
                        [
                            'label'  => 'Risalah Rapat',
                            'route'  => 'laporan-risalah.superadmin',
                            'active' => ['laporan-risalah.superadmin'],
                        ],
                    ],
                ],

            ],
        ],

        // ----------------------------------------------------------------
        // SECTION: LAINNYA
        // ----------------------------------------------------------------
        [
            'title' => 'LAINNYA',
            'items' => [

                [
                    'label'  => 'Pengaturan',
                    'icon'   => 'fas fa-cogs',
                    'roles'  => ['superadmin', 'admin'],
                    'active' => [
                        'pengaturan*',
                        'data-perusahaan',
                        'user.manage',
                        'organization.manageOrganization',
                        'kode-bagian.index',
                    ],
                    'children' => [
                        [
                            'label'  => 'Data Perusahaan',
                            'route'  => 'data-perusahaan',
                            'roles'  => ['superadmin', 'admin'],
                            'active' => ['data-perusahaan'],
                        ],
                        [
                            'label'  => 'Manajemen Kode Bagian Kerja',
                            'route'  => 'kode-bagian.index',
                            'roles'  => ['superadmin'],
                            'active' => ['kode-bagian.index'],
                        ],
                        [
                            'label'  => 'Manajemen Pengguna',
                            'route'  => 'user.manage',
                            'roles'  => ['superadmin'],
                            'active' => ['user.manage'],
                        ],
                        [
                            'label'  => 'Manajemen Struktur Organisasi',
                            'route'  => 'organization.manageOrganization',
                            'roles'  => ['superadmin'],
                            'active' => ['organization.manageOrganization'],
                        ],
                    ],
                ],

                [
                    'label'  => 'Pemulihan',
                    'icon'   => 'fas fa-recycle',
                    'roles'  => ['superadmin'],
                    'active' => ['pemulihan*', 'memo.backup', 'undangan.backup', 'risalah.backup'],
                    'children' => [
                        [
                            'label'  => 'Memo',
                            'route'  => 'memo.backup',
                            'active' => ['memo.backup'],
                        ],
                        [
                            'label'  => 'Undangan Rapat',
                            'route'  => 'undangan.backup',
                            'active' => ['undangan.backup'],
                        ],
                        [
                            'label'  => 'Risalah Rapat',
                            'route'  => 'risalah.backup',
                            'active' => ['risalah.backup'],
                        ],
                    ],
                ],

                [
                    'label'  => 'Info',
                    'icon'   => 'fas fa-info-circle',
                    'route'  => 'info',
                    'roles'  => ['superadmin', 'admin', 'manager'],
                    'active' => ['info'],
                ],

            ],
        ],

    ];
@endphp

{{-- ============================================================ --}}
{{-- Logo                                                         --}}
{{-- ============================================================ --}}
<div class="sidebar-logo">
    <div class="logo-header d-flex align-items-center justify-content-center p-3 pt-4 pb-4">
        <a href="{{ url('dashboard') }}" class="logo d-block w-100 text-decoration-none">
            <div class="bg-white d-flex align-items-center justify-content-center overflow-hidden w-100 p-2 px-3">
                <img
                    src="{{ asset('assets/img/Logo-SIPO-Text.svg') }}"
                    alt="SIPO"
                    class="d-block img-fluid"
                    style="max-height: 76px;"
                />
            </div>
        </a>
    </div>
</div>

{{-- ============================================================ --}}
{{-- Navigasi                                                     --}}
{{-- ============================================================ --}}
<div class="sidebar-wrapper">
    <div class="sidebar-content">
        <ul class="nav nav-secondary" style="margin-top: 50px;">

            @foreach ($menuSections as $sectionIndex => $section)

                @php
                    $visibleItems = collect($section['items'])
                        ->filter(fn ($item) => sidebarCanAccess($item, $role))
                        ->values();
                @endphp

                @if ($visibleItems->isNotEmpty())

                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">{{ $section['title'] }}</h4>
                    </li>

                    @foreach ($visibleItems as $itemIndex => $menu)

                        @php
                            $hasChildren = ! empty($menu['children']);

                            $visibleChildren = $hasChildren
                                ? collect($menu['children'])
                                    ->filter(function ($child) use ($role, $menu) {
                                        $childRoles = $child['roles'] ?? $menu['roles'] ?? null;
                                        return $childRoles === null
                                            || in_array($role, $childRoles, strict: true);
                                    })
                                    ->values()
                                : collect();

                            $isActive = sidebarIsActive($menu['active'] ?? [$menu['route'] ?? '']);

                            if ($hasChildren) {
                                $isActive = $isActive || $visibleChildren->contains(
                                    fn ($child) => sidebarIsActive($child['active'] ?? [$child['route'] ?? ''])
                                );
                            }

                            $collapseId = sidebarCollapseId($menu['label'], $sectionIndex, $itemIndex);
                        @endphp

                        {{-- A) Item TANPA sub-menu --}}
                        @if (! $hasChildren && isset($menu['route']))

                            <li class="nav-item {{ $isActive ? 'active' : '' }}">
                                <a href="{{ route($menu['route']) }}" class="nav-link">
                                    <i class="{{ $menu['icon'] }}"></i>
                                    <p>{{ $menu['label'] }}</p>
                                    @if (! empty($menu['badge']))
                                        <span class="{{ $menu['badge_class'] ?? 'badge bg-danger ms-auto' }}">
                                            {{ $menu['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            </li>

                        {{-- B) Item DENGAN sub-menu yang visible --}}
                        @elseif ($hasChildren && $visibleChildren->isNotEmpty())

                            <li class="nav-item {{ $isActive ? 'active' : '' }}">
                                <a
                                    href="#{{ $collapseId }}"
                                    data-sidebar-toggle="{{ $collapseId }}"
                                    aria-expanded="{{ $isActive ? 'true' : 'false' }}"
                                    aria-controls="{{ $collapseId }}"
                                    role="button"
                                >
                                    <i class="{{ $menu['icon'] }}"></i>
                                    <p>{{ $menu['label'] }}</p>
                                    <span class="caret"></span>
                                </a>

                                <div
                                    id="{{ $collapseId }}"
                                    class="sidebar-submenu {{ $isActive ? 'is-open' : '' }}"
                                    style="{{ $isActive ? '' : 'display:none;' }}"
                                >
                                    <ul class="nav nav-collapse">
                                        @foreach ($visibleChildren as $child)
                                            @php
                                                $childActive = sidebarIsActive(
                                                    $child['active'] ?? [$child['route'] ?? '']
                                                );
                                            @endphp

                                            @if (isset($child['route']))
                                                <li class="{{ $childActive ? 'active' : '' }}">
                                                    <a href="{{ route($child['route']) }}">
                                                        <span class="sub-item">{{ $child['label'] }}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>

                            </li>

                        @endif

                    @endforeach

                @endif

            @endforeach

        </ul>
    </div>
</div>
