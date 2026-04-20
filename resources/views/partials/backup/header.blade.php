<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container-fluid">

        <span class="toggle-sidebar" role="button" tabindex="0" aria-label="Toggle sidebar">
            <i class="fa fa-bars"
                style="color:#BEA6EB;background:#E9E6FB;padding:10px;border-radius:10px;display:inline-block;"></i>
        </span>

        <div class="flex-grow-1"></div>

        <ul class="navbar-nav ms-auto align-items-center gap-3">

            {{-- Notifikasi --}}
            <li class="nav-item dropdown">
                <a class="nav-link position-relative rounded-circle d-flex align-items-center justify-content-center"
                    href="#"
                    id="notifDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="width: 48px; height: 48px; background: #F3F1FB;">
                    <i class="fa fa-bell text-primary"></i>
                    <span id="notif-count"
                        class="badge badge-danger position-absolute translate-middle rounded-pill"
                        style="top: 8px; right: -2px; display: none;">
                        0
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
                    aria-labelledby="notifDropdown"
                    style="width: 360px; border-radius: 16px; overflow: hidden;">
                    <div class="px-3 py-3 border-bottom d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">Notifikasi</h6>
                            <small class="text-muted" id="notif-subtitle">Memuat...</small>
                        </div>
                        <button type="button" id="mark-all-read-btn" class="btn btn-sm btn-light border">
                            Read all
                        </button>
                    </div>

                    <div id="notif-body" class="list-group list-group-flush" style="max-height: 420px; overflow-y: auto;">
                        <div class="text-center text-muted px-3 py-4">Memuat notifikasi...</div>
                    </div>

                    <div class="text-center text-muted small px-3 py-2 border-top bg-light">
                        Notifikasi terbaru akan muncul otomatis
                    </div>
                </div>
            </li>

            {{-- Profile & Settings --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    style="background:#E9E6EB; padding:10px 22px; border-radius:16px; display:flex; align-items:center; justify-content:center; gap:10px;">
                    @if (Auth::user()->profile_image)
                        <img src="data:image/png;base64,{{ Auth::user()->profile_image }}" alt="profile"
                            class="rounded-circle" style="width: 20px; height: 20px; object-fit: cover;">
                    @else
                        <i class="fa fa-user-circle" style="color:#BEA6EB;font-size:20px;"></i>
                    @endif
                    <i class="fa fa-cog" style="color:#56C7EB;font-size:20px;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileDropdown"
                    style="min-width:260px;">
                    <li class="px-3 py-2">
                        <div class="fw-bold mb-1">Selamat Datang, {{ Auth::user()->firstname }}
                            {{ Auth::user()->lastname }}</div>
                        @if (Auth::user()->role_id_role == 1)
                            <div class="text-muted mb-2" style="font-size:14px;">Super Admin</div>
                        @else
                            <div class="text-muted mb-2" style="font-size:14px;">
                                {{ Auth::user()->position->nm_position }}
                            </div>
                        @endif
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('edit-profile') }}">
                            <i class="fas fa-user me-2"></i> Profil
                        </a>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="fas fa-sign-out-alt me-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');
        const body = document.body;

        let hadMinimize = false;
        const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

        function openSidebar() {
            hadMinimize = body.classList.contains('sidebar_minimize');
            if (hadMinimize) body.classList.remove('sidebar_minimize');
            sidebar?.classList.add('active');
            backdrop?.classList.add('show');
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar?.classList.remove('active');
            backdrop?.classList.remove('show');
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            if (hadMinimize && !isMobile()) body.classList.add('sidebar_minimize');
        }

        function toggleSidebar() {
            if (sidebar?.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        backdrop?.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && sidebar?.classList.contains('active')) {
                closeSidebar();
            }
        });

        document.querySelectorAll('.sidebar a').forEach(a => {
            a.addEventListener('click', () => {
                if (!isMobile()) return;

                if (a.hasAttribute('data-bs-toggle') && a.getAttribute('data-bs-toggle') === 'collapse') {
                    return;
                }

                if (a.getAttribute('href') && a.getAttribute('href') !== '#') {
                    closeSidebar();
                }
            });
        });

        window.addEventListener('resize', () => {
            if (!isMobile()) closeSidebar();
        });
    });
</script>
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentCount = 0;
        const notifBody = document.getElementById('notif-body');
        const notifCount = document.getElementById('notif-count');
        const notifSubtitle = document.getElementById('notif-subtitle');
        const markAllBtn = document.getElementById('mark-all-read-btn');
        let isLoading = false;

        init();

        function init() {
            loadNotifications();
            loadNotificationCount();

            setInterval(() => {
                loadNotifications(false);
                loadNotificationCount();
            }, 15000);

            markAllBtn?.addEventListener('click', async function(e) {
                e.preventDefault();
                await markAllAsRead();
            });
        }

        function getNotifConfig(judul = '') {
            const lower = String(judul).toLowerCase();

            if (lower.includes("risalah")) {
                if (lower.includes("tolak")) return { icon: "fas fa-clipboard-list", badge: "danger" };
                if (lower.includes("koreksi")) return { icon: "fas fa-clipboard-list", badge: "warning" };
                if (lower.includes("setuju") || lower.includes("approve") || lower.includes("masuk") || lower.includes("kirim")) {
                    return { icon: "fas fa-clipboard-list", badge: "success" };
                }
                return { icon: "fas fa-clipboard-list", badge: "primary" };
            }

            if (lower.includes("undangan")) {
                if (lower.includes("tolak")) return { icon: "fas fa-calendar-check", badge: "danger" };
                if (lower.includes("koreksi")) return { icon: "fas fa-calendar-check", badge: "warning" };
                return { icon: "fas fa-calendar-check", badge: "success" };
            }

            if (lower.includes("memo")) {
                if (lower.includes("tolak")) return { icon: "fas fa-file-alt", badge: "danger" };
                if (lower.includes("koreksi") || lower.includes("revisi")) return { icon: "fas fa-file-alt", badge: "warning" };
                return { icon: "fas fa-file-alt", badge: "info" };
            }

            if (lower.includes("surat")) {
                return { icon: "fas fa-envelope", badge: "info" };
            }

            if (lower.includes("laporan")) {
                return { icon: "fas fa-chart-bar", badge: "dark" };
            }

            return { icon: "fas fa-bell", badge: "secondary" };
        }

        function formatTanggalIndo(dateString) {
            if (!dateString) return '-';

            const isoString = String(dateString).replace(' ', 'T');
            const date = new Date(isoString);

            if (isNaN(date.getTime())) return '-';

            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        }

        function truncateText(text, limit = 60) {
            if (!text) return '-';
            text = String(text);
            return text.length > limit ? text.substring(0, limit - 3) + '...' : text;
        }

        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function setLoadingState() {
            notifBody.innerHTML = `<div class="text-center text-muted px-3 py-4">Memuat notifikasi...</div>`;
            notifSubtitle.textContent = 'Memuat...';
        }

        function setEmptyState() {
            notifBody.innerHTML = `<div class="text-center text-muted px-3 py-4">Belum ada notifikasi</div>`;
            notifSubtitle.textContent = 'Tidak ada notifikasi baru';
        }

        function setErrorState(message = 'Gagal memuat notifikasi') {
            notifBody.innerHTML = `<div class="text-center text-danger px-3 py-4">${escapeHtml(message)}</div>`;
            notifSubtitle.textContent = 'Terjadi kendala';
        }

        async function loadNotifications(showLoading = true) {
            if (isLoading) return;
            isLoading = true;

            try {
                if (showLoading) setLoadingState();

                const response = await fetch("{{ route('notifications.index') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                const notifications = Array.isArray(data.notifications) ? data.notifications : [];

                renderNotifications(notifications);
            } catch (error) {
                console.error('Error loading notifications:', error);
                setErrorState('Error memuat notifikasi');
            } finally {
                isLoading = false;
            }
        }

        function renderNotifications(notifications) {
            if (!notifications.length) {
                setEmptyState();
                return;
            }

            const unreadCount = notifications.filter(item => Number(item.dibaca) === 0).length;
            notifSubtitle.textContent = unreadCount > 0
                ? `${unreadCount} belum dibaca`
                : 'Semua notifikasi sudah dibaca';

            let html = '';

            notifications.forEach(notif => {
                const judul = notif.judul ?? 'Tanpa judul';
                const judulDocument = notif.judul_document ?? '-';
                const updatedAt = notif.updated_at ?? null;
                const redirectUrl = notif.redirect_url ?? '#';
                const isUnread = Number(notif.dibaca) === 0;
                const config = getNotifConfig(judul);

                html += `
                    <a href="javascript:void(0)"
                        class="list-group-item list-group-item-action border-0 rounded-3 mb-2 ${isUnread ? 'bg-light' : ''} notif-item"
                        data-id="${notif.id_notifikasi}"
                        data-read="${notif.dibaca}"
                        data-redirect-url="${escapeHtml(redirectUrl)}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title rounded-circle bg-${config.badge}">
                                    <i class="${config.icon} text-white"></i>
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="fw-semibold text-dark mb-1">${escapeHtml(judul)}</div>
                                <div class="text-muted small mb-1">${escapeHtml(truncateText(judulDocument, 60))}</div>
                                <div class="text-muted small">${escapeHtml(formatTanggalIndo(updatedAt))}</div>
                            </div>
                            ${isUnread ? '<span class="badge badge-primary badge-dot"></span>' : ''}
                        </div>
                    </a>
                `;
            });

            notifBody.innerHTML = html;

            notifBody.querySelectorAll('.notif-item').forEach(item => {
                item.addEventListener('click', async function(e) {
                    e.preventDefault();

                    const notifId = this.getAttribute('data-id');
                    const isRead = this.getAttribute('data-read');
                    const redirectUrl = this.getAttribute('data-redirect-url');

                    if (!redirectUrl || redirectUrl === '#') return;

                    if (isRead === '0') {
                        await markNotificationAsRead(notifId);
                    }

                    window.location.href = redirectUrl;
                });
            });
        }

        async function markNotificationAsRead(id) {
            try {
                const response = await fetch(`/notifications/${id}/tanda-dibaca`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    updateCounterDirectly(-1);
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        async function markAllAsRead() {
            try {
                markAllBtn.disabled = true;
                markAllBtn.innerHTML = 'Loading...';

                const response = await fetch("{{ route('notifications.readAll') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    currentCount = 0;
                    updateCounterUI();
                    loadNotifications(false);
                    loadNotificationCount();
                }
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
                alert('Gagal menandai semua notifikasi sebagai dibaca');
            } finally {
                markAllBtn.disabled = false;
                markAllBtn.innerHTML = 'Read all';
            }
        }

        function updateCounterDirectly(change) {
            currentCount = Math.max(0, currentCount + change);
            updateCounterUI();
        }

        function updateCounterUI() {
            notifCount.innerText = currentCount;
            notifCount.style.display = currentCount > 0 ? 'inline-block' : 'none';
        }

        async function loadNotificationCount() {
            try {
                const response = await fetch("{{ route('notifications.count') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                currentCount = Number(data.count ?? 0);
                updateCounterUI();
            } catch (error) {
                console.error('Error loading notification count:', error);
            }
        }
    });
</script>
@endpush
