import { useEffect } from 'react';

export function MercureTest() {
    useEffect(() => {
        const hub = new URL(import.meta.env.VITE_MERCURE_PUBLIC_URL);
        hub.searchParams.append('topic', 'http://localhost/timer/test');

        const es = new EventSource(hub.toString());

        es.onopen = () => {
            console.log('mercure connected');
        };

        es.onmessage = (event) => {
            console.log('mercure event', JSON.parse(event.data));
        };

        es.onerror = (event) => {
            console.error('mercure error', event);
        };

        return () => es.close();
    }, []);

    return null;
}