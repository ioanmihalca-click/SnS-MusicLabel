import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';

const fancyboxOptions = {
    compact: false,
    idle: false,
    animated: false,
    showClass: false,
    hideClass: false,
    dragToClose: false,
    Images: { zoom: false },
    Toolbar: {
        display: {
            left: [],
            middle: ['prev', 'next'],
            right: ['close'],
        },
    },
    Carousel: { transition: false, friction: 0 },
};

const bindFancybox = () => Fancybox.bind('#gallery [data-fancybox]', fancyboxOptions);

document.addEventListener('DOMContentLoaded', bindFancybox);
document.addEventListener('livewire:navigated', bindFancybox);
window.addEventListener('photo-added', bindFancybox);

/**
 * Scroll-triggered reveals.
 * Any element with [data-reveal] gets [data-revealed] when it enters the viewport.
 * Paired with the [data-reveal] CSS in app.css.
 */
const revealObserver = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries, observer) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.setAttribute('data-revealed', 'true');
                observer.unobserve(entry.target);
            }
        }
    }, { threshold: 0.12, rootMargin: '0px 0px -10% 0px' })
    : null;

const observeReveals = () => {
    if (!revealObserver) {
        // Fallback: just reveal everything on browsers without IntersectionObserver.
        document.querySelectorAll('[data-reveal]:not([data-revealed])').forEach(el => {
            el.setAttribute('data-revealed', 'true');
        });
        return;
    }
    document.querySelectorAll('[data-reveal]:not([data-revealed])').forEach(el => {
        revealObserver.observe(el);
    });
};

document.addEventListener('DOMContentLoaded', observeReveals);
document.addEventListener('livewire:navigated', observeReveals);
