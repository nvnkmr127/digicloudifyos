import './bootstrap';

// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

document.addEventListener('click', (event) => {
    const el = event.target.closest('[wire\\:confirm]');
    if (!el) {
        return;
    }

    const message = el.getAttribute('wire:confirm') || 'Are you sure?';
    if (!window.confirm(message)) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }
});
