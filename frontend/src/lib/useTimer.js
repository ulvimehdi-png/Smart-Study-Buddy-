import React, { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';
import { subscribeMercureJson } from '@/lib/mercure';
import {
  buildLiveTimerJoinUrl,
  changeConfigLiveTimerSession,
  createLiveTimerSession,
  durationPayload,
  getLiveTimerSession,
  pauseLiveTimerSession,
  resetPhaseLiveTimerSession,
  skipPhaseLiveTimerSession,
  startLiveTimerSession,
} from '@/lib/liveTimerSessionApi';

export const TimerContext = createContext(null);

function durationToMs({ minutes, seconds }) {
  return Math.round(Number(minutes) * 60 + Number(seconds)) * 1000;
}

function msToDuration(ms) {
  const safeMs = Math.max(0, Number(ms) || 0);
  const totalSeconds = Math.floor(safeMs / 1000);

  return {
    minutes: Math.floor(totalSeconds / 60),
    seconds: totalSeconds % 60,
  };
}

function normalizeDuration(duration, fallback) {
  if (!duration) {
    return fallback;
  }

  const minutes = Number(duration.minutes);
  const seconds = Number(duration.seconds);

  return {
    minutes: Number.isFinite(minutes) ? Math.max(0, minutes) : fallback.minutes,
    seconds: Number.isFinite(seconds) ? Math.max(0, seconds) : fallback.seconds,
  };
}

function toNonNegativeInt(value, fallback = 0) {
  const number = Number(value);
  return Number.isFinite(number) ? Math.max(0, Math.trunc(number)) : fallback;
}

function parseIsoToMs(value) {
  if (!value) {
    return null;
  }

  const ms = new Date(value).getTime();
  return Number.isFinite(ms) ? ms : null;
}

function getPhaseDurationMs(phase, workMs, breakMs) {
  return phase === 'break' ? breakMs : workMs;
}

function deriveProjectionView(projection, nowMs = Date.now()) {
  if (!projection?.state) {
    return null;
  }

  const state = projection.state;
  const status = typeof state.status === 'string' ? state.status : 'idle';
  let phase = state.phase === 'break' ? 'break' : 'focus';

  const workMs = toNonNegativeInt(state.workDurationMs);
  const breakMs = toNonNegativeInt(state.breakDurationMs);

  let remainingMs = toNonNegativeInt(
    state.remainingMs ?? getPhaseDurationMs(phase, workMs, breakMs)
  );

  const phaseEndsAtMs = parseIsoToMs(state.phaseEndsAt);

  if (status === 'running' && phaseEndsAtMs !== null) {
    if (nowMs < phaseEndsAtMs) {
      remainingMs = phaseEndsAtMs - nowMs;
    } else {
      let overflowMs = nowMs - phaseEndsAtMs;

      while (true) {
        phase = phase === 'focus' ? 'break' : 'focus';
        const durationMs = getPhaseDurationMs(phase, workMs, breakMs);

        if (durationMs <= 0) {
          remainingMs = 0;
          break;
        }

        if (overflowMs < durationMs) {
          remainingMs = durationMs - overflowMs;
          break;
        }

        overflowMs -= durationMs;
      }
    }
  }

  const phaseDurationMs = getPhaseDurationMs(phase, workMs, breakMs);
  const elapsedMs = Math.max(0, phaseDurationMs - remainingMs);
  const progress =
    phaseDurationMs > 0
      ? Math.min(1, Math.max(0, elapsedMs / phaseDurationMs))
      : 0;

  return {
    id: projection.id ?? null,
    topic: projection.topic ?? null,
    status,
    phase,
    workMs,
    breakMs,
    remainingMs: Math.max(0, remainingMs),
    phaseDurationMs,
    elapsedMs,
    progress,
    phaseEndsAt: state.phaseEndsAt ?? null,
    phaseEndsAtMs,
    startedAt: state.startedAt ?? null,
    updatedAt: state.updatedAt ?? null,
    accumulatedFocusMs: toNonNegativeInt(state.accumulatedFocusMs),
    accumulatedBreakMs: toNonNegativeInt(state.accumulatedBreakMs),
    subjectId: state.subjectId ?? null,
    taskId: state.taskId ?? null,
    isIdle: status === 'idle',
    isPaused: status === 'paused',
    isRunning: status === 'running',
    raw: projection,
  };
}

export function TimerProvider({ children }) {
  const [sessionId, setSessionId] = useState('');
  const [projection, setProjection] = useState(null);
  const [error, setError] = useState('');
  const [isPending, setIsPending] = useState(false);
  const [connectionStatus, setConnectionStatus] = useState('disconnected');

  const [workDraft, setWorkDraft] = useState({ minutes: 25, seconds: 0 });
  const [restDraft, setRestDraft] = useState({ minutes: 5, seconds: 0 });
  const [localIsPlaying, setLocalIsPlaying] = useState(false);
  const [localIsResting, setLocalIsResting] = useState(false);
  const [localMsLeft, setLocalMsLeft] = useState(durationToMs({ minutes: 25, seconds: 0 }));
  const [localDurationMs, setLocalDurationMs] = useState(durationToMs({ minutes: 25, seconds: 0 }));
  const [isShared, setIsShared] = useState(false);
  const [hasCopiedShareLink, setHasCopiedShareLink] = useState(false);
  const [isOwner, setIsOwner] = useState(true);

  const subscriptionRef = useRef(null);
  const bootstrappedFromUrlRef = useRef(false);

  const isRemote = Boolean(projection?.state);
  const display = useMemo(() => deriveProjectionView(projection, Date.now()), [projection]);
  const joinUrl = useMemo(() => {
    return sessionId ? buildLiveTimerJoinUrl(sessionId) : '';
  }, [sessionId]);

  const clearSession = useCallback(({ preserveId = false } = {}) => {
    setProjection(null);
    setConnectionStatus('disconnected');
    setIsShared(false);
    setHasCopiedShareLink(false);

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
    if (typeof nextProjection?.isOwner === 'boolean') {
      console.log(nextProjection);
      
      setIsOwner(nextProjection.isOwner);
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

  const createSession = useCallback(({
    workMinutes,
    workSeconds = 0,
    breakMinutes,
    breakSeconds = 0,
    phase = 'focus',
    status = 'idle',
    remainingMs = null,
  }) => {
    return runRequest(
      () => createLiveTimerSession({
        work: durationPayload(workMinutes, workSeconds),
        break: durationPayload(breakMinutes, breakSeconds),
        phase,
        status,
        remainingMs,
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
          setIsShared(true);

          if (result?.topic) {
            subscribeToTopic(result.topic);
          }
        },
      }
    );
  }, [runRequest, sessionId, subscribeToTopic]);

  const runAction = useCallback((action, id = sessionId) => {
    if (!id) {
      return Promise.resolve(null);
    }

    return runRequest(() => action(id));
  }, [runRequest, sessionId]);

  const start = useCallback((id) => runAction(startLiveTimerSession, id), [runAction]);
  const pause = useCallback((id) => runAction(pauseLiveTimerSession, id), [runAction]);
  const skipPhase = useCallback((id) => runAction(skipPhaseLiveTimerSession, id), [runAction]);
  const resetPhase = useCallback((id) => runAction(resetPhaseLiveTimerSession, id), [runAction]);

  const changeConfig = useCallback(({ workMinutes, workSeconds = 0, breakMinutes, breakSeconds = 0 }) => {
    if (!sessionId) {
      return Promise.resolve(null);
    }

    return runRequest(() =>
      changeConfigLiveTimerSession(sessionId, {
        work: durationPayload(workMinutes, workSeconds),
        break: durationPayload(breakMinutes, breakSeconds),
      })
    );
  }, [runRequest, sessionId]);

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

  useEffect(() => {
    if (!display) {
      return;
    }

    setWorkDraft(msToDuration(display.workMs));
    setRestDraft(msToDuration(display.breakMs));
    setLocalIsPlaying(Boolean(display.isRunning));
    setLocalIsResting(display.phase === 'break');
    setLocalMsLeft(display.remainingMs ?? 0);
    setLocalDurationMs(display.phaseDurationMs ?? 0);
  }, [display]);

  useEffect(() => {
    if (isRemote) {
      return;
    }

    const nextDurationMs = durationToMs(localIsResting ? restDraft : workDraft);
    setLocalDurationMs(nextDurationMs);
    setLocalMsLeft(nextDurationMs);
  }, [isRemote, localIsResting, restDraft, workDraft]);

  useEffect(() => {
    if (!isRemote || !localIsPlaying) {
      return undefined;
    }

    let previousTime;
    let requestId;

    const animate = (time) => {
      if (previousTime !== undefined) {
        const deltaTime = time - previousTime;

        setLocalMsLeft((previousMsLeft) => {
          const nextMsLeft = previousMsLeft - deltaTime;

          if (nextMsLeft > 0) {
            return nextMsLeft;
          }

          const nextIsResting = !localIsResting;
          const nextPhase = nextIsResting ? restDraft : workDraft;
          const nextDurationMs = durationToMs(nextPhase);

          setLocalIsResting(nextIsResting);
          setLocalDurationMs(nextDurationMs);

          return Math.max(0, nextDurationMs + nextMsLeft);
        });
      }

      previousTime = time;
      requestId = requestAnimationFrame(animate);
    };

    requestId = requestAnimationFrame(animate);

    return () => {
      cancelAnimationFrame(requestId);
    };
  }, [isRemote, localIsPlaying, localIsResting, restDraft, workDraft]);

  useEffect(() => {
    if (isRemote || !localIsPlaying) {
      return undefined;
    }

    let previousTime;
    let requestId;

    const animate = (time) => {
      if (previousTime !== undefined) {
        const deltaTime = time - previousTime;

        setLocalMsLeft((previousMsLeft) => {
          const nextMsLeft = previousMsLeft - deltaTime;

          if (nextMsLeft > 0) {
            return nextMsLeft;
          }

          const nextIsResting = !localIsResting;
          const nextPhase = nextIsResting ? restDraft : workDraft;
          const nextDurationMs = durationToMs(nextPhase);

          setLocalIsResting(nextIsResting);
          setLocalDurationMs(nextDurationMs);

          return Math.max(0, nextDurationMs + nextMsLeft);
        });
      }

      previousTime = time;
      requestId = requestAnimationFrame(animate);
    };

    requestId = requestAnimationFrame(animate);

    return () => {
      cancelAnimationFrame(requestId);
    };
  }, [isRemote, localIsPlaying, localIsResting, restDraft, workDraft]);

  const syncConfig = useCallback((nextWork, nextRest) => {
    if (!sessionId) {
      return Promise.resolve(null);
    }

    return changeConfig({
      workMinutes: nextWork.minutes,
      workSeconds: nextWork.seconds,
      breakMinutes: nextRest.minutes,
      breakSeconds: nextRest.seconds,
    });
  }, [changeConfig, sessionId]);

  const setTimerDuration = useCallback((minutes, seconds) => {
    if (sessionId) {
      return;
    }

    setLocalIsPlaying(false);

    if (localIsResting) {
      const nextDurationMs = durationToMs({ minutes, seconds });

      setRestDraft((current) => ({
        ...current,
        minutes,
        seconds,
      }));
      setLocalDurationMs(nextDurationMs);
      setLocalMsLeft(nextDurationMs);
      return;
    }

    const nextDurationMs = durationToMs({ minutes, seconds });

    setWorkDraft((current) => ({
      ...current,
      minutes,
      seconds,
    }));
    setLocalDurationMs(nextDurationMs);
    setLocalMsLeft(nextDurationMs);
  }, [localIsResting, sessionId]);

  const setWork = useCallback((nextWork) => {
    const normalized = normalizeDuration(nextWork, workDraft);
    setWorkDraft(normalized);

    if (!sessionId) {
      setLocalIsPlaying(false);

      if (!localIsResting) {
        const nextDurationMs = durationToMs(normalized);
        setLocalDurationMs(nextDurationMs);
        setLocalMsLeft(nextDurationMs);
      }

      return;
    }

    void syncConfig(normalized, restDraft);
  }, [localIsResting, restDraft, sessionId, syncConfig, workDraft]);

  const setRest = useCallback((nextRest) => {
    const normalized = normalizeDuration(nextRest, restDraft);
    setRestDraft(normalized);

    if (!sessionId) {
      setLocalIsPlaying(false);

      if (localIsResting) {
        const nextDurationMs = durationToMs(normalized);
        setLocalDurationMs(nextDurationMs);
        setLocalMsLeft(nextDurationMs);
      }

      return;
    }

    void syncConfig(workDraft, normalized);
  }, [localIsResting, restDraft, sessionId, syncConfig, workDraft]);

  const togglePlaying = useCallback(async () => {
    if (!sessionId) {
      setLocalIsPlaying((current) => !current);
      return null;
    }

    if (display?.isRunning) {
      return pause();
    }

    return start();
  }, [display?.isRunning, pause, sessionId, start]);

  const resetTimer = useCallback(async () => {
    if (!sessionId) {
      setLocalIsPlaying(false);
      const nextDurationMs = durationToMs(localIsResting ? restDraft : workDraft);
      setLocalDurationMs(nextDurationMs);
      setLocalMsLeft(nextDurationMs);
      return null;
    }

    return resetPhase();
  }, [localIsResting, resetPhase, restDraft, sessionId, workDraft]);

  const skipTimer = useCallback(async () => {
    if (!sessionId) {
      setLocalIsPlaying(false);
      setLocalIsResting((current) => {
        const nextIsResting = !current;
        const nextDurationMs = durationToMs(nextIsResting ? restDraft : workDraft);

        setLocalDurationMs(nextDurationMs);
        setLocalMsLeft(nextDurationMs);

        return nextIsResting;
      });
      return null;
    }

    return skipPhase();
  }, [restDraft, sessionId, skipPhase, workDraft]);

  const shareTimer = useCallback(async () => {
    let ensuredSessionId = sessionId;

    if (!ensuredSessionId) {
      const created = await createSession({
        workMinutes: workDraft.minutes,
        workSeconds: workDraft.seconds,
        breakMinutes: restDraft.minutes,
        breakSeconds: restDraft.seconds,
        phase: localIsResting ? 'break' : 'focus',
        status: localIsPlaying ? 'running' : localMsLeft === durationToMs(localIsResting ? restDraft : workDraft)
          ? 'idle'
          : 'paused',
        remainingMs: localMsLeft,
      });

      ensuredSessionId = created?.id ?? '';

      if (!ensuredSessionId) {
        return null;
      }
    }

    if (!ensuredSessionId) {
      return null;
    }

    setIsShared(true);
    setHasCopiedShareLink(false);

    const nextJoinUrl = joinUrl || buildLiveTimerJoinUrl(ensuredSessionId);

    if (nextJoinUrl) {
      window.history.replaceState(window.history.state, '', nextJoinUrl);
    }

    if (nextJoinUrl && navigator.clipboard?.writeText) {
      try {
        await navigator.clipboard.writeText(nextJoinUrl);
        setHasCopiedShareLink(true);
      } catch {
        setHasCopiedShareLink(false);
      }
    }

    return ensuredSessionId;
  }, [
    createSession,
    joinUrl,
    localIsPlaying,
    localIsResting,
    localMsLeft,
    restDraft,
    sessionId,
    workDraft,
  ]);

  const stopSharing = useCallback(async () => {
    setIsShared(false);
    setHasCopiedShareLink(false);
    return null;
  }, []);

  const value = useMemo(() => ({
    isPlaying: localIsPlaying,
    setIsPlaying: setLocalIsPlaying,
    togglePlaying,
    isResting: localIsResting,
    setIsResting: setLocalIsResting,
    work: workDraft,
    setWork,
    rest: restDraft,
    setRest,
    msLeft: localMsLeft,
    durationMs: localDurationMs,
    isShared,
    isOwner,
    hasCopiedShareLink,
    shareTimer,
    startSharing: shareTimer,
    stopSharing,
    setTimerDuration,
    resetTimer,
    skipTimer,
    connectionStatus,
    error,
    joinUrl,
    isPending,
  }), [
    connectionStatus,
    error,
    hasCopiedShareLink,
    isPending,
    isOwner,
    joinUrl,
    localDurationMs,
    localIsPlaying,
    localIsResting,
    localMsLeft,
    resetTimer,
    restDraft,
    setRest,
    setTimerDuration,
    setWork,
    isShared,
    shareTimer,
    skipTimer,
    stopSharing,
    togglePlaying,
    workDraft,
  ]);

  return React.createElement(TimerContext.Provider, { value }, children);
}

export function useTimer() {
  const context = useContext(TimerContext);

  if (!context) {
    throw new Error('useTimer must be used within an TimerProvider');
  }

  return context;
}
