import Alpine from 'alpinejs';
import sort from '@alpinejs/sort';

window.Alpine = Alpine;

Alpine.plugin(sort);
Alpine.start();

// QR scanner library, loaded only when the check-in page asks for it
window.loadJsQR = () => import('jsqr').then((module) => module.default);