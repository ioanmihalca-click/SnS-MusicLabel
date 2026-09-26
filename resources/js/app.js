import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import { startNavigation } from './navigation';
import { startPlayer } from './player';

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

/*
 * Set up once per page load. After a deploy, a wire:navigate visit injects
 * the new bundle into the open page and runs it without a reload: the copy
 * already running (and its player) carries on alone.
 */
if (!window.snsPlayer) {
    // livewire:navigated also fires once on the first page load.
    document.addEventListener('livewire:navigated', bindFancybox);
    window.addEventListener('photo-added', bindFancybox);

    startNavigation();
    window.snsPlayer = startPlayer();
}
