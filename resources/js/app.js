import './bootstrap';
import { animate, stagger, createTimeline, splitText, createLayout, spring } from 'animejs';
// import { animate, stagger, splitText, createLayout, spring } from 'https://esm.sh/animejs@4.3.5';


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
// SIDEBAR — spring bounce, lambat & smooth
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

    // ── 2. Bounce effect pada semua nav-link & sub-item ───────────────────────
    const bounceEl = (el) => {
        animate(el, {
            scale: [1, 0.93, 1.04, 1],
            duration: 420,
            easing: 'easeOutElastic(1, 0.5)',
        });
    };

    // Bounce pada parent trigger (menu dengan submenu)
    sidebar.querySelectorAll('[data-sidebar-toggle]').forEach((trigger) => {
        trigger.addEventListener('click', () => bounceEl(trigger));
    });

    // Bounce pada flat nav-link (menu tanpa submenu)
    sidebar.querySelectorAll('.nav-item > a.nav-link').forEach((link) => {
        link.addEventListener('click', () => bounceEl(link));
    });

    // Bounce pada sub-item link
    sidebar.querySelectorAll('.nav-collapse li a').forEach((link) => {
        link.addEventListener('click', () => bounceEl(link));
    });

    // ── 3. Submenu toggle ─────────────────────────────────────────────────────
    sidebar.querySelectorAll('[data-sidebar-toggle]').forEach((trigger) => {
        const targetId = trigger.getAttribute('data-sidebar-toggle');
        const target   = document.getElementById(targetId);
        if (!target) return;

        let busy = false;

        const items = Array.from(target.querySelectorAll('li'));

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            if (busy) return;
            busy = true;

            const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
            const navItem    = trigger.closest('.nav-item');
            const caret      = trigger.querySelector('.caret');

            if (!isExpanded) {
                // ── BUKA ──
                items.forEach(li => {
                    li.style.transition = 'none';
                    li.style.opacity    = '0';
                    li.style.transform  = 'translateX(-20px) scaleX(0.88)';
                });

                target.style.display = 'block';
                target.classList.add('is-open');

                requestAnimationFrame(() => requestAnimationFrame(() => {

                    items.forEach((li, i) => {
                        setTimeout(() => {
                            li.style.transition = `
                                opacity  400ms ease ${i * 100}ms,
                                transform 600ms cubic-bezier(0.34, 1.45, 0.64, 1) ${i * 100}ms
                            `;
                            li.style.opacity   = '1';
                            li.style.transform = 'translateX(0px) scaleX(1)';
                        }, 10);
                    });

                    const totalMs = (items.length - 1) * 100 + 620 + 60;
                    setTimeout(() => {
                        items.forEach(li => {
                            li.style.transition = '';
                            li.style.opacity    = '';
                            li.style.transform  = '';
                        });
                        busy = false;
                    }, totalMs);

                }));

                animate(caret, { rotate: [0, 90], duration: 450, easing: 'easeOutBack' });
                trigger.setAttribute('aria-expanded', 'true');
                navItem?.classList.add('active');

            } else {
                // ── TUTUP ──
                const reversed = [...items].reverse();
                reversed.forEach((li, i) => {
                    li.style.transition = `
                        opacity  280ms ease-in ${i * 70}ms,
                        transform 320ms cubic-bezier(0.55, 0, 1, 0.45) ${i * 70}ms
                    `;
                    li.style.opacity   = '0';
                    li.style.transform = 'translateX(-14px) scaleX(0.92)';
                });

                const totalMs = (items.length - 1) * 70 + 340;

                setTimeout(() => {
                    target.style.display = 'none';
                    target.classList.remove('is-open');
                    items.forEach(li => {
                        li.style.transition = '';
                        li.style.opacity    = '';
                        li.style.transform  = '';
                    });
                    busy = false;
                }, totalMs);

                animate(caret, { rotate: [90, 0], duration: 350, easing: 'easeOutExpo' });
                trigger.setAttribute('aria-expanded', 'false');
                navItem?.classList.remove('active');
            }
        });
    });
});
