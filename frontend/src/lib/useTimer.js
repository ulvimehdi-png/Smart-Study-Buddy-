import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';

export const TimerContext = createContext(null);

function durationToMs({ minutes, seconds }) {
  return (minutes * 60 + seconds) * 1000;
}
const reset = { minutes: 0, seconds: 0.5 };

export function TimerProvider({ children }) {
  const [work, setWork] = useState({ minutes: 0, seconds: 1 });
  const [rest, setRest] = useState({ minutes: 0, seconds: 2 });

  const [isPlaying, setIsPlaying] = useState(false);
  const [isResting, setIsResting] = useState(false);
  const [isResetting, setIsResetting] = useState(false);
  const [msLeft, setMsLeft] = useState(durationToMs(work));

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
  }, [isPlaying, isResting, rest, work, isResetting]);

  // When user changes duration via inputs
  const setTimerDuration = useCallback((m, s) => {
    const totalMs = (m * 60 + s) * 1000;
    setMsLeft(totalMs);
    setIsPlaying(false);
  }, []);

  const resetTimer = useCallback(() => {
    setIsPlaying(false);
    const currentDuration = durationToMs(isResting ? rest : work);
    setMsLeft(currentDuration);
  }, [isResting, rest, work]);

  const skipTimer = useCallback(() => {
    setIsPlaying(false);
    const nextIsResting = !isResting;
    const nextPhase = nextIsResting ? rest : work;
    setIsResting(nextIsResting);
    setMsLeft(durationToMs(nextPhase));
  }, [isResting, rest, work]);

  const durationMs = useMemo(
    () => durationToMs(isResetting ? reset : isResting ? rest : work),
    [isResetting, isResting, rest, work]
  );

  const value = useMemo(
    () => ({
      isPlaying,
      setIsPlaying,
      isResting,
      setIsResting,
      isResetting,
      work,
      setWork,
      rest,
      setRest,
      msLeft,
      durationMs,
      setTimerDuration,
      resetTimer,
      skipTimer
    }),
    [durationMs, isPlaying, isResting, isResetting, msLeft, resetTimer, skipTimer, setTimerDuration, work, rest]
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