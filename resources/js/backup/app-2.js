import './bootstrap';
import { animate, stagger, createTimeline, splitText } from 'animejs';
// import { animate, stagger, createTimeline } from 'https://esm.sh/animejs';
// import { animate, stagger, splitText } from 'https://esm.sh/animejs@4.3.5';

// =============================================================================
// LOGIN PAGE
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
    const loginPage = document.querySelector('.login-page');

    if (!loginPage) return;

    animate('.login-card', {
        opacity: [0, 1],
        scale: [0.96, 1],
        y: [30, 0],
        duration: 900,
        easing: 'easeOutExpo',
    });

    animate('.login-left-animate', {
        opacity: [0, 1],
        x: [-35, 0],
        delay: stagger(120, { start: 250 }),
        duration: 900,
        easing: 'easeOutExpo',
    });

    animate('.login-stat-card', {
        opacity: [0, 1],
        y: [30, 0],
        scale: [0.92, 1],
        delay: stagger(120, { start: 650 }),
        duration: 800,
        easing: 'easeOutBack',
    });

    animate('.login-form-item', {
        opacity: [0, 1],
        x: [35, 0],
        delay: stagger(90, { start: 350 }),
        duration: 750,
        easing: 'easeOutExpo',
    });

    animate('.login-bg-orb', {
        scale: [0.8, 1.15],
        opacity: [0.35, 0.7],
        duration: 2200,
        alternate: true,
        loop: true,
        easing: 'easeInOutSine',
    });
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('.page-transition-link');

    if (!link) return;

    event.preventDefault();

    animate('.login-card', {
        opacity: [1, 0],
        scale: [1, 0.97],
        y: [0, 20],
        duration: 350,
        easing: 'easeInExpo',
        complete: () => {
            window.location.href = link.href;
        },
    });
});

// =============================================================================
// SIDEBAR
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    // ── 1. Hover icon ─────────────────────────────────────────────────────────
    sidebar.querySelectorAll('.nav-item > a').forEach((link) => {
        const icon = link.querySelector('i');
        if (!icon) return;
        link.addEventListener('mouseenter', () => {
            animate(icon, { scale: [1, 1.25], rotate: [0, -8, 0], duration: 320, easing: 'easeOutBack' });
        });
        link.addEventListener('mouseleave', () => {
            animate(icon, { scale: [null, 1], duration: 200, easing: 'easeOutSine' });
        });
    });

    // ── 2. Submenu ────────────────────────────────────────────────────────────
    sidebar.querySelectorAll('[data-sidebar-toggle]').forEach((trigger) => {
        const targetId = trigger.getAttribute('data-sidebar-toggle');
        const target   = document.getElementById(targetId);
        if (!target) return;

        let busy = false;

        // Simpan teks asli sekali saja
        const anchors = Array.from(target.querySelectorAll('li a'));
        anchors.forEach(a => {
            a.dataset.originalText = a.textContent.trim();
            a.style.overflow       = 'hidden';
            a.style.display        = 'block';
        });

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            if (busy) return;

            const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
            const navItem    = trigger.closest('.nav-item');
            const caret      = trigger.querySelector('.caret');
            const items      = Array.from(target.querySelectorAll('li'));

            if (!isExpanded) {
                // ── BUKA ──
                busy = true;

                // 1. Tampilkan container, sembunyikan semua li
                target.style.display = 'block';
                target.classList.add('is-open');
                items.forEach(li => { li.style.opacity = '0'; });

                // 2. Tunggu DOM render
                requestAnimationFrame(() => requestAnimationFrame(() => {

                    let completedCount = 0;

                    anchors.forEach((a, i) => {
                        // Restore teks plain dulu
                        a.innerHTML = a.dataset.originalText;

                        // splitText — chars langsung ada di DOM
                        const { chars } = splitText(a, { words: false, chars: true });

                        // Set initial state SETELAH split (elemen baru dari splitText)
                        chars.forEach(c => {
                            c.style.display   = 'inline-block';
                            c.style.opacity   = '0';
                            c.style.transform = 'translateY(14px)';
                        });

                        // Tampilkan li-nya
                        const liEl = a.closest('li');
                        setTimeout(() => {
                            liEl.style.opacity = '1';
                        }, i * 180);

                        // Animasi chars satu per satu
                        chars.forEach((c, ci) => {
                            const delay = i * 180 + ci * 50;
                            setTimeout(() => {
                                // Bounce up
                                c.style.transition = 'none';
                                c.style.opacity    = '1';
                                c.style.transform  = 'translateY(-8px)';

                                setTimeout(() => {
                                    c.style.transition = 'transform 450ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 200ms ease';
                                    c.style.transform  = 'translateY(0px)';
                                }, 80);

                            }, delay);
                        });

                        // Hitung selesai dari anchor terakhir, char terakhir
                        if (i === anchors.length - 1) {
                            const totalDelay = (anchors.length - 1) * 180 + (chars.length - 1) * 50 + 80 + 450 + 100;
                            setTimeout(() => {
                                // Restore innerHTML plain
                                anchors.forEach(a2 => { a2.innerHTML = a2.dataset.originalText; });
                                busy = false;
                            }, totalDelay);
                        }
                    });

                }));

                if (caret) animate(caret, { rotate: [0, 90], duration: 350, easing: 'easeOutBack' });
                trigger.setAttribute('aria-expanded', 'true');
                navItem?.classList.add('active');

            } else {
                // ── TUTUP ──
                busy = true;

                requestAnimationFrame(() => requestAnimationFrame(() => {

                    anchors.forEach((a, i) => {
                        a.innerHTML = a.dataset.originalText;
                        const { chars } = splitText(a, { words: false, chars: true });

                        chars.forEach(c => { c.style.display = 'inline-block'; });

                        const liEl   = a.closest('li');
                        const isLast = i === anchors.length - 1;

                        // Chars terbang ke bawah dengan stagger terbalik
                        const reversed = [...chars].reverse();
                        reversed.forEach((c, ci) => {
                            const delay = i * 80 + ci * 30;
                            setTimeout(() => {
                                c.style.transition = 'transform 220ms ease-in, opacity 180ms ease-in';
                                c.style.transform  = 'translateY(12px)';
                                c.style.opacity    = '0';
                            }, delay);
                        });

                        // Li fade out
                        const liDelay = i * 80 + chars.length * 30 + 80;
                        setTimeout(() => {
                            liEl.style.transition = 'opacity 250ms ease-in';
                            liEl.style.opacity    = '0';

                            if (isLast) {
                                setTimeout(() => {
                                    target.style.display = 'none';
                                    target.classList.remove('is-open');
                                    // Reset semua
                                    items.forEach(li => {
                                        li.style.opacity    = '';
                                        li.style.transition = '';
                                    });
                                    anchors.forEach(a2 => {
                                        a2.innerHTML = a2.dataset.originalText;
                                    });
                                    busy = false;
                                }, 300);
                            }
                        }, liDelay);
                    });

                }));

                if (caret) animate(caret, { rotate: [90, 0], duration: 300, easing: 'easeOutExpo' });
                trigger.setAttribute('aria-expanded', 'false');
                navItem?.classList.remove('active');
            }
        });
    });
});
