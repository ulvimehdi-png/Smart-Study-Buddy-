import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { subscribeMercureJson } from '@/lib/mercure';
import {
    buildLiveTimerJoinUrl,
    changeConfigLiveTimerSession,
    createLiveTimerSession,
    deleteLiveTimerSession,
    durationPayload,
    getLiveTimerSession,
    pauseLiveTimerSession,
    resetPhaseLiveTimerSession,
    resumeLiveTimerSession,
    skipPhaseLiveTimerSession,
    startLiveTimerSession,
    stopAndSaveLiveTimerSession,
} from '@/lib/liveTimerSessionApi';
import { deriveView } from './deriveView.js';
import { useClock } from './useClock.js';

export function useSession() {
    const [sessionId, setSessionId] = useState('');
    const [projection, setProjection] = useState(null);
    const [error, setError] = useState('');
    const [isPending, setIsPending] = useState(false);
    const [connectionStatus, setConnectionStatus] = useState('disconnected');

    const subscriptionRef = useRef(null);
    const bootstrappedFromUrlRef = useRef(false);

    const clearSession = useCallback(({ preserveId = false } = {}) => {
        setProjection(null);
        setConnectionStatus('disconnected');

        if (!preserveId) {
            setSessionId('');
        }
    }, []);

    const closeSubscription = useCallback(() => {
        if (subscriptionRef.current) {
            subscriptionRef.current.close();
            subscriptionRef.current = null;
        }

        setConnectionStatus('disconnected');
    }, []);

    const applyProjection = useCallback((nextProjection) => {
        setProjection(nextProjection ?? null);

        if (nextProjection?.id) {
            setSessionId(nextProjection.id);
        }
    }, []);

    const subscribeToTopic = useCallback((topic) => {
        closeSubscription();

        if (!topic) {
            return;
        }

        setConnectionStatus('connecting');

        subscriptionRef.current = subscribeMercureJson({
            topics: topic,
            onOpen: () => {
                setConnectionStatus('open');
            },
            onMessage: (message) => {
                if (message?.deleted) {
                    closeSubscription();
                    clearSession();
                    return;
                }

                applyProjection(message);
            },
            onError: (event) => {
                console.error('mercure error', event);
                setConnectionStatus('error');
            },
            onParseError: (parseError, raw) => {
                console.error('mercure parse error', parseError, raw);
                setError(`Mercure parse error: ${String(parseError?.message || parseError)}`);
            },
        });
    }, [applyProjection, clearSession, closeSubscription]);

    const runRequest = useCallback(async (request, { afterSuccess } = {}) => {
        setIsPending(true);
        setError('');

        try {
            const result = await request();

            if (result?.topic) {
                applyProjection(result);
            }

            if (afterSuccess) {
                await afterSuccess(result);
            }

            return result ?? null;
        } catch (err) {
            setError(String(err?.message || err));
            return null;
        } finally {
            setIsPending(false);
        }
    }, [applyProjection]);

    const createSession = useCallback(({ workMinutes, breakMinutes }) => {
        return runRequest(
            () =>
                createLiveTimerSession({
                    work: durationPayload(workMinutes),
                    break: durationPayload(breakMinutes),
                    subjectId: null,
                    taskId: null,
                }),
            {
                afterSuccess: (result) => {
                    if (result?.topic) {
                        subscribeToTopic(result.topic);
                    }
                },
            }
        );
    }, [runRequest, subscribeToTopic]);

    const loadSession = useCallback((id = sessionId) => {
        if (!id) {
            return Promise.resolve(null);
        }

        return runRequest(
            () => getLiveTimerSession(id),
            {
                afterSuccess: (result) => {
                    setSessionId(id);

                    if (result?.topic) {
                        subscribeToTopic(result.topic);
                    }
                },
            }
        );
    }, [runRequest, sessionId, subscribeToTopic]);

    const deleteSession = useCallback(() => {
        if (!sessionId) {
            return Promise.resolve(null);
        }

        return runRequest(
            () => deleteLiveTimerSession(sessionId),
            {
                afterSuccess: () => {
                    closeSubscription();
                    clearSession();
                },
            }
        );
    }, [clearSession, closeSubscription, runRequest, sessionId]);

    const runAction = useCallback((action) => {
        if (!sessionId) {
            return Promise.resolve(null);
        }

        return runRequest(() => action(sessionId));
    }, [runRequest, sessionId]);

    const start = useCallback(() => runAction(startLiveTimerSession), [runAction]);
    const pause = useCallback(() => runAction(pauseLiveTimerSession), [runAction]);
    const resume = useCallback(() => runAction(resumeLiveTimerSession), [runAction]);
    const skipPhase = useCallback(() => runAction(skipPhaseLiveTimerSession), [runAction]);
    const resetPhase = useCallback(() => runAction(resetPhaseLiveTimerSession), [runAction]);

    const changeConfig = useCallback(({ workMinutes, breakMinutes }) => {
        if (!sessionId) {
            return Promise.resolve(null);
        }

        return runRequest(() =>
            changeConfigLiveTimerSession(sessionId, {
                work: durationPayload(workMinutes),
                break: durationPayload(breakMinutes),
            })
        );
    }, [runRequest, sessionId]);

    const stopAndSave = useCallback(
        ({ sessionNote = null, distractionCount = 0, finalStatus = 'completed' } = {}) => {
            if (!sessionId) {
                return Promise.resolve(null);
            }

            return runRequest(
                () =>
                    stopAndSaveLiveTimerSession(sessionId, {
                        sessionNote,
                        distractionCount,
                        finalStatus,
                    }),
                {
                    afterSuccess: () => {
                        closeSubscription();
                        clearSession();
                    },
                }
            );
        },
        [clearSession, closeSubscription, runRequest, sessionId]
    );

    useEffect(() => {
        if (bootstrappedFromUrlRef.current) {
            return;
        }

        bootstrappedFromUrlRef.current = true;

        const params = new URLSearchParams(window.location.search);
        const idFromUrl = params.get('session');

        if (idFromUrl) {
            void loadSession(idFromUrl);
        }
    }, [loadSession]);

    useEffect(() => {
        return () => {
            closeSubscription();
        };
    }, [closeSubscription]);

    const shouldTick = projection?.state?.status === 'running' && Boolean(projection?.state?.phaseEndsAt);
    const nowMs = useClock(shouldTick, 250);

    const display = useMemo(() => {
        return deriveView(projection, nowMs);
    }, [projection, nowMs]);

    const joinUrl = useMemo(() => {
        return sessionId ? buildLiveTimerJoinUrl(sessionId) : '';
    }, [sessionId]);

    return {
        sessionId,
        setSessionId,

        projection,
        display,

        error,
        setError,

        isPending,
        connectionStatus,
        joinUrl,

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