import { useEffect, useState } from 'react';

export function useClock(enabled, intervalMs = 250) {
    const [nowMs, setNowMs] = useState(() => Date.now());

    useEffect(() => {
        if (!enabled) {
            return undefined;
        }

        setNowMs(Date.now());

        const intervalId = window.setInterval(() => {
            setNowMs(Date.now());
        }, intervalMs);

        return () => {
            window.clearInterval(intervalId);
        };
    }, [enabled, intervalMs]);

    return nowMs;
}