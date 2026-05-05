import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echo: Echo<'reverb'> | null = null;

if (typeof window !== 'undefined') {
    // Echo looks for window.Pusher to instantiate — must be set BEFORE new Echo()
    (window as any).Pusher = Pusher;

    echo = new Echo({
        broadcaster: 'reverb',
        key: process.env.NEXT_PUBLIC_REVERB_APP_KEY || 'li6mkhtjbx9nqewvbbzn',
        wsHost: process.env.NEXT_PUBLIC_REVERB_HOST || 'localhost',
        wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT) || 8080,
        wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT) || 8080,
        forceTLS: (process.env.NEXT_PUBLIC_REVERB_SCHEME || 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

export default echo;
