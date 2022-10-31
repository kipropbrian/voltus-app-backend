import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();



window.handleClick = function(e) {
    // Now you can access the event object (e) directly
    console.log('working...')
}