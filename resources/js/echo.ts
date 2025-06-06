import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

Pusher.logToConsole = true; // Solo para depurar

const echo = new Echo({
    broadcaster: 'pusher',
    key: '178b2ea8593d0c3b1175',
    cluster: 'mt1',
    wsHost: window.location.hostname,
    wsPort: 6001, // o el que uses
    wssPort: 6001,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
        },
    },
});

export default echo;
