import React, { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';

export const TimerContext = createContext(null);

function durationToMs({ minutes, seconds }) {
  return (Number(minutes) * 60 + Number(seconds)) * 1000;
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

export function TimerProvider({ children }) {
  const [work, setWorkState] = useState({ minutes: 25, seconds: 0 });
  const [rest, setRestState] = useState({ minutes: 5, seconds: 0 });

  const [isPlaying, setIsPlaying] = useState(false);
  const [isResting, setIsResting] = useState(false);
  const [msLeft, setMsLeft] = useState(durationToMs(work));
  const [isShared, setIsShared] = useState(false);

  const wsRef = useRef(null);

  const sendSharedMessage = useCallback((eventtype, snapshotOverrides = {}) => {
    const socket = wsRef.current;

    if (!socket || socket.readyState !== WebSocket.OPEN) {
      return;
    }

    const snapshot = {
      work,
      rest,
      isPlaying,
      isResting,
      msLeft,
      ...snapshotOverrides,
    };

    socket.send(JSON.stringify({
      type: 'timer-share',
      eventtype,
      snapshot,
    }));
  }, [isPlaying, isResting, msLeft, rest, work]);

  const applySharedSnapshot = useCallback((snapshot) => {
    if (!snapshot) {
      return;
    }

    if (snapshot.work) {
      setWorkState(normalizeDuration(snapshot.work, work));
    }

    if (snapshot.rest) {
      setRestState(normalizeDuration(snapshot.rest, rest));
    }

    if (typeof snapshot.isResting === 'boolean') {
      setIsResting(snapshot.isResting);
    }

    if (typeof snapshot.isPlaying === 'boolean') {
      setIsPlaying(snapshot.isPlaying);
    }

    if (typeof snapshot.msLeft === 'number') {
      setMsLeft(Math.max(0, snapshot.msLeft));
    }
  }, [rest, work]);

  const applySharedMessage = useCallback((message) => {
    if (!message) {
      return;
    }

    if (message.snapshot) {
      applySharedSnapshot(message.snapshot);
    }

    if (message.eventtype) {
      alert(`Received shared timer event: ${message.eventtype}`); // For debugging, can be removed later
    }
  }, [applySharedSnapshot]);

  useEffect(() => {
    if (!isPlaying) {
      return undefined;
    }

    let previousTime;
    let requestId;

    const animate = (time) => {
      if (previousTime !== undefined) {
        const deltaTime = time - previousTime;

        setMsLeft((previousMsLeft) => {
          const nextMsLeft = previousMsLeft - deltaTime;

          if (nextMsLeft > 0) {
            return nextMsLeft;
          }

          // if (!isResetting) {
          //   setIsResetting(true);

          //   return durationToMs(reset);
          // }
          // setIsResetting(false);

          const nextIsResting = !isResting;
          const nextPhase = nextIsResting ? rest : work;

          setIsResting(nextIsResting);
          return Math.max(0, durationToMs(nextPhase) + nextMsLeft);
        });
      }

      previousTime = time;
      requestId = requestAnimationFrame(animate);
    };

    requestId = requestAnimationFrame(animate);

    return () => {
      cancelAnimationFrame(requestId);
    };
  }, [isPlaying, isResting, rest, work]);

  useEffect(() => {
    return () => {
      if (wsRef.current) {
        wsRef.current.close();
        wsRef.current = null;
      }
    };
  }, []);

  const setTimerDuration = useCallback((minutes, seconds) => {
    const totalMs = (minutes * 60 + seconds) * 1000;
    setIsPlaying(false);
    setMsLeft(totalMs);

    if (isShared) {
      sendSharedMessage('duration-change', {
        isPlaying: false,
        msLeft: totalMs,
      });
    }
  }, [isShared, sendSharedMessage]);

  const setWork = useCallback((nextWork) => {
    setWorkState(nextWork);

    const nextMsLeft = !isResting ? durationToMs(nextWork) : msLeft;

    if (!isResting) {
      setMsLeft(nextMsLeft);
    }

    if (isShared) {
      sendSharedMessage('work-change', {
        work: nextWork,
        msLeft: nextMsLeft,
      });
    }
  }, [isResting, isShared, msLeft, sendSharedMessage]);

  const setRest = useCallback((nextRest) => {
    setRestState(nextRest);

    const nextMsLeft = isResting ? durationToMs(nextRest) : msLeft;

    if (isResting) {
      setMsLeft(nextMsLeft);
    }

    if (isShared) {
      sendSharedMessage('rest-change', {
        rest: nextRest,
        msLeft: nextMsLeft,
      });
    }
  }, [isResting, isShared, msLeft, sendSharedMessage]);

  const resetTimer = useCallback(() => {
    const currentDuration = durationToMs(isResting ? rest : work);

    setIsPlaying(false);
    setMsLeft(currentDuration);

    if (isShared) {
      sendSharedMessage('reset', {
        isPlaying: false,
        msLeft: currentDuration,
      });
    }
  }, [isResting, isShared, rest, sendSharedMessage, work]);

  const skipTimer = useCallback(() => {
    const nextIsResting = !isResting;
    const nextPhase = nextIsResting ? rest : work;
    const nextMsLeft = durationToMs(nextPhase);

    setIsPlaying(false);
    setIsResting(nextIsResting);
    setMsLeft(nextMsLeft);

    if (isShared) {
      sendSharedMessage('skip', {
        isPlaying: false,
        isResting: nextIsResting,
        msLeft: nextMsLeft,
      });
    }
  }, [isResting, isShared, rest, sendSharedMessage, work]);

  const togglePlaying = useCallback(() => {
    const nextPlaying = !isPlaying;
    setIsPlaying(nextPlaying);

    if (isShared) {
      sendSharedMessage(nextPlaying ? 'start' : 'pause', {
        isPlaying: nextPlaying,
      });
    }
  }, [isPlaying, isShared, sendSharedMessage]);

  const startSharing = useCallback(async () => {
    if (wsRef.current && wsRef.current.readyState === WebSocket.OPEN) {
      setIsShared(true);
      return;
    }

    const response = await fetch('/api/timer/share', { credentials: 'include' });

    if (!response.ok) {
      const errorText = await response.text();
      alert(`Unable to start sharing: ${errorText}`);
      return;
    }

    const timerResponse = await response.json();

    if (!timerResponse?.url) {
      alert('Unable to start sharing: missing websocket url');
      return;
    }

    const socket = new WebSocket(timerResponse.url);
    wsRef.current = socket;

    socket.addEventListener('open', () => {
      setIsShared(true);
      sendSharedMessage('sync');
    });

    socket.addEventListener('message', (event) => {
      let data;

      try {
        data = JSON.parse(event.data);
      } catch {
        return;
      }

      applySharedMessage(data);
    });

    socket.addEventListener('close', () => {
      if (wsRef.current === socket) {
        wsRef.current = null;
      }

      setIsShared(false);
    });

    socket.addEventListener('error', () => {
      setIsShared(false);
    });
  }, [applySharedMessage, sendSharedMessage]);

  const stopSharing = useCallback(() => {
    const socket = wsRef.current;

    if (socket) {
      wsRef.current = null;
      socket.close();
    }

    setIsShared(false);
  }, []);

  const durationMs = useMemo(
    () => durationToMs(isResting ? rest : work),
    [isResting, rest, work]
  );

  const value = useMemo(
    () => ({
      isPlaying,
      setIsPlaying,
      togglePlaying,
      isResting,
      setIsResting,
      work,
      setWork,
      rest,
      setRest,
      msLeft,
      durationMs,
      isShared,
      startSharing,
      stopSharing,
      setTimerDuration,
      resetTimer,
      skipTimer,
    }),
    [
      durationMs,
      isPlaying,
      isResting,
      isShared,
      msLeft,
      resetTimer,
      setRest,
      setTimerDuration,
      setWork,
      skipTimer,
      startSharing,
      stopSharing,
      togglePlaying,
      work,
      rest,
    ]
  );

  return React.createElement(TimerContext.Provider, { value }, children);
}

export function useTimer() {
  const context = useContext(TimerContext);

  if (!context) {
    throw new Error('useTimer must be used within an TimerProvider');
  }

  return context;
}
