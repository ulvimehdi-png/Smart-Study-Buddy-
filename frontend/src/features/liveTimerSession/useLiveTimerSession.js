import {useCallback, useEffect, useRef, useState} from 'react';
import {subscribeMercureJson} from '@/lib/mercure';
import {
    buildLiveTimerJoinUrl,
    changeConfigLiveTimerSession,
    createLiveTimerSession,
    deleteLiveTimerSession,
    getLiveTimerSession,
    pauseLiveTimerSession,
    resetPhaseLiveTimerSession,
    resumeLiveTimerSession,
    skipPhaseLiveTimerSession,
    startLiveTimerSession,
    stopAndSaveLiveTimerSession,
} from '@/lib/liveTimerSessionApi';
import {normalizeLiveTimerProjection} from './normalizeLiveTimerProjection';

export function useLiveTimerSession() {
    const [sessionId, setSessionId] = useState('');
    const [error, setError] = useState('');

    const [projection, setProjection] = useState(null);
    const [display, setDisplay] = useState(null);

    const subscriptionRef = useRef(null);
    const animationFrameRef = useRef(null);

    const closeSubscription = useCallback(() => {
        if (subscriptionRef.current) {
            subscriptionRef.current.close();
            subscriptionRef.current = null;
        }
    }, []);

    const applyProjection = useCallback((nextProjection) => {
        setProjection(nextProjection);
        setDisplay(normalizeLiveTimerProjection(nextProjection));
    }, []);

    const clearProjection = useCallback(() => {
        setProjection(null);
        setDisplay(null);
    }, []);

    const subscribeToTopic = useCallback((topic) => {
        closeSubscription();

        subscriptionRef.current = subscribeMercureJson({
            topics: topic,
            onOpen: () => {
                console.log('mercure connected', topic);
            },
            onMessage: (projection) => {
                if (projection?.deleted) {
                    clearProjection();
                    return;
                }

                applyProjection(projection);
            },
            onError: (event) => {
                console.error('mercure error', event);
            },
            onParseError: (error, raw) => {
                console.error('mercure parse error', error, raw);
            },
        });
    }, [applyProjection, clearProjection, closeSubscription]);

    const run = useCallback(async (fn) => {
        setError('');

        try {
            const result = await fn();

            if (result) {
                applyProjection(result);
            }

            return result;
        } catch (err) {
            setError(String(err.message || err));
            return null;
        }
    }, [applyProjection]);

    const createSession = useCallback(async ({ workMinutes, breakMinutes }) => {
        return await run(async () => {
            const projection = await createLiveTimerSession({
                work: {minutes: workMinutes, seconds: 0},
                break: {minutes: breakMinutes, seconds: 0},
                subjectId: null,
                taskId: null,
            });

            setSessionId(projection.id);
            subscribeToTopic(projection.topic);

            console.log('joinUrl', buildLiveTimerJoinUrl(projection.id));

            return projection;
        });
    }, [run, subscribeToTopic]);

    const loadSession = useCallback(async (id = sessionId) => {
        if (!id) {
            return null;
        }

        return run(async () => {
            const projection = await getLiveTimerSession(id);
            setSessionId(id);
            subscribeToTopic(projection.topic);
            return projection;
        });
    }, [run, sessionId, subscribeToTopic]);

    const deleteSession = useCallback(async () => {
        if (!sessionId) {
            return;
        }

        await run(async () => {
            await deleteLiveTimerSession(sessionId);
            closeSubscription();
            clearProjection();
            return null;
        });
    }, [clearProjection, closeSubscription, run, sessionId]);

    const start = useCallback(() => {
        if (!sessionId) return Promise.resolve(null);
        return run(() => startLiveTimerSession(sessionId));
    }, [run, sessionId]);

    const pause = useCallback(() => {
        if (!sessionId) return Promise.resolve(null);
        return run(() => pauseLiveTimerSession(sessionId));
    }, [run, sessionId]);

    const resume = useCallback(() => {
        if (!sessionId) return Promise.resolve(null);
        return run(() => resumeLiveTimerSession(sessionId));
    }, [run, sessionId]);

    const skipPhase = useCallback(() => {
        if (!sessionId) return Promise.resolve(null);
        return run(() => skipPhaseLiveTimerSession(sessionId));
    }, [run, sessionId]);

    const resetPhase = useCallback(() => {
        if (!sessionId) return Promise.resolve(null);
        return run(() => resetPhaseLiveTimerSession(sessionId));
    }, [run, sessionId]);

    const changeConfig = useCallback(({ workMinutes, breakMinutes }) => {
        if (!sessionId) return Promise.resolve(null);

        return run(() =>
            changeConfigLiveTimerSession(sessionId, {
                work: { minutes: workMinutes, seconds: 0 },
                break: { minutes: breakMinutes, seconds: 0 },
            })
        );
    }, [run, sessionId]);

    useEffect(() => {
        if (!projection) {
            return undefined;
        }

        const animate = () => {
            setDisplay(normalizeLiveTimerProjection(projection));
            animationFrameRef.current = requestAnimationFrame(animate);
        };

        animationFrameRef.current = requestAnimationFrame(animate);

        return () => {
            if (animationFrameRef.current) {
                cancelAnimationFrame(animationFrameRef.current);
                animationFrameRef.current = null;
            }
        };
    }, [projection]);

    const fetchSession = useCallback(async (id) => {
        return await getLiveTimerSession(id);
    }, []);

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const idFromUrl = params.get('session');

        if (!idFromUrl || sessionId) return;

        fetchSession(idFromUrl).then((projection) => {
            setSessionId(idFromUrl);
            subscribeToTopic(projection.topic);
            applyProjection(projection);
        });
    }, [sessionId, fetchSession, subscribeToTopic, applyProjection]);

    /*
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const idFromUrl = params.get('session');

        if (idFromUrl && !sessionId) {
            loadSession(idFromUrl);
        }
    }, [loadSession, sessionId]);
    */

    useEffect(() => {
        return () => {
            closeSubscription();

            if (animationFrameRef.current) {
                cancelAnimationFrame(animationFrameRef.current);
            }
        };
    }, [closeSubscription]);

    const stopAndSave = useCallback(async ({ sessionNote = null, distractionCount = 0, finalStatus = 'completed' } = {}) => {
        if (!sessionId) {
            return null;
        }

        return run(async () => {
            const result = await stopAndSaveLiveTimerSession(sessionId, {
                sessionNote,
                distractionCount,
                finalStatus,
            });

            closeSubscription();
            clearProjection();
            setSessionId('');

            return result;
        });
    }, [clearProjection, closeSubscription, run, sessionId]);

    return {
        sessionId,
        setSessionId,
        projection,
        error,
        display,
        createSession,
        loadSession,
        deleteSession,
        start,
        pause,
        resume,
        skipPhase,
        resetPhase,
        changeConfig,
        stopAndSave,
    };
}