import './bootstrap';

const fancyboxOptions = {
    compact: false,
    idle: false,
    animated: false,
    showClass: false,
    hideClass: false,
    dragToClose: false,
    Images: {
        zoom: false,
    },
    Toolbar: {
        display: {
            left: [],
            middle: ['prev', 'next'],
            right: ['close']
        }
    },
    Carousel: {
        transition: false,
        friction: 0,
    }
};

document.addEventListener('DOMContentLoaded', function() {
    Fancybox.bind('#gallery [data-fancybox]', fancyboxOptions);
});

document.addEventListener('livewire:navigated', () => {
    Fancybox.bind('#gallery [data-fancybox]', fancyboxOptions);
});

window.addEventListener('photo-added', event => {
    Fancybox.bind('#gallery [data-fancybox]', fancyboxOptions);
});