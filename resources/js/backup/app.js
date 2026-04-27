import './bootstrap';
import { animate, stagger } from 'animejs';

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
