import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';

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
            right: ['close'],
        },
    },
    Carousel: {
        transition: false,
        friction: 0,
    },
};

const bindFancybox = () => Fancybox.bind('#gallery [data-fancybox]', fancyboxOptions);

document.addEventListener('DOMContentLoaded', bindFancybox);
document.addEventListener('livewire:navigated', bindFancybox);
window.addEventListener('photo-added', bindFancybox);
