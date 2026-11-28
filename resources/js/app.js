import Alpine from 'alpinejs';
import sort from '@alpinejs/sort';
import '@fontsource/figtree/latin-400.css';
import '@fontsource/figtree/latin-500.css';
import '@fontsource/figtree/latin-600.css';

window.Alpine = Alpine;

Alpine.plugin(sort);
Alpine.start();

// QR scanner library, loaded only when the check-in page asks for it
window.loadJsQR = () => import('jsqr').then((module) => module.default);


// Skeleton cards: tell the page when a search or filter is being sent
const startLoading = () => window.dispatchEvent(new CustomEvent('events-loading'));

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-skeleton]');

    if (link && link.target !== '_blank' && !event.defaultPrevented
        && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey) {
        startLoading();
    }
});

document.addEventListener('submit', (event) => {
    if (event.target.matches('form[data-skeleton]')) {
        startLoading();
    }
});