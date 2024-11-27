import './bootstrap';



    document.addEventListener('DOMContentLoaded', function() {
        const options = {
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

        Fancybox.bind('#gallery [data-fancybox]', options);
    });

    // Reinițializare după actualizări Livewire
    document.addEventListener('livewire:navigated', () => {
        Fancybox.bind('#gallery [data-fancybox]', options);
    });

    window.addEventListener('photo-added', event => {
        Fancybox.bind('#gallery [data-fancybox]', options);
    });

    