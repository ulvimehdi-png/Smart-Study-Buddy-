import React, { useRef, useState, useEffect } from 'react';
import { AnimatePresence, motion as Motion } from 'framer-motion';
import { Share2, FastForward, Pause, Play, RotateCcw, BellRing } from 'lucide-react';
import { CircularProgressbarWithChildren, buildStyles } from 'react-circular-progressbar';
import 'react-circular-progressbar/dist/styles.css';
import { cn } from "@/lib/utils";
import { useTimer } from '@/lib/useTimer';
import TimerDurationInputs from './TimerDurationInputs';
import TimerButton from './Button/TimerButton';

function formatTime(timeLeftMs) {
  const minutes = Math.floor(timeLeftMs / 60000);
  const seconds = Math.ceil((timeLeftMs % 60000) / 1000);
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function formatDate(ms) {
  const endTime = new Date(Date.now() + ms);
  return `${String(endTime.getHours()).padStart(2, '0')}:${String(endTime.getMinutes()).padStart(2, '0')}`;
}

export default function Timer({ isPiP = false }) {
  const {
    isPlaying,
    togglePlaying,
    msLeft,
    durationMs,
    resetTimer,
    skipTimer,
    isResting,
    isShared,
    isOwner,
    hasCopiedShareLink,
    shareTimer,
    connectionStatus,
    error,
  } = useTimer();

  const percentage = durationMs > 0 ? (msLeft / durationMs) * 100 : 0;
  const displayTime = formatTime(msLeft);
  const [endTime, setEndTime] = useState('--:--');

  const sessionStartTimeRef = useRef(null);

  useEffect(() => {
    if (isPlaying) {
      sessionStartTimeRef.current = Date.now();
      return;
    } 
    
    if (sessionStartTimeRef.current) {
      const elapsedMs = Date.now() - sessionStartTimeRef.current;
      const minutes = Math.round(elapsedMs / 60000);

      if (minutes > 0) {
        fetch("/api/achievements/award-manual", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ minutes }),
          credentials: "include",
        }).catch(console.error);
      }
      sessionStartTimeRef.current = null;
    }
  }, [isPlaying]);

  useEffect(() => {
    if (isPlaying) {
      setEndTime(formatDate(msLeft));
    }
  }, [isPlaying, msLeft]);

  return (
    <div className="flex h-full flex-col items-center justify-center" id="timer">
      <div aria-live="polite" className="sr-only">
        {isPlaying ? `Timer running. Time left: ${displayTime}` : `Timer paused. Time left: ${displayTime}`}
      </div>

      <div className={cn("relative mt-2 flex items-center justify-center md:flex-row flex-col", isPiP ? "h-48 w-48" : "h-full w-full")} >
        <CircularProgressbarWithChildren
          value={100 - percentage}
          counterClockwise={false}
          strokeWidth={4}
          styles={buildStyles({
            pathColor: isResting ? 'var(--color-timer-rest)' : 'var(--color-timer-work)',
            trailColor: 'var(--color-timer-trail)',
            pathTransition: 'none',
          })}
        >
          <button
            type="button"
            disabled={!isOwner}
            aria-label={isPlaying ? "Pause Timer" : "Start Timer"}
            className={cn("group absolute inset-0 flex h-full w-full items-center justify-center rounded-full outline-none focus-visible:ring-4 focus-visible:ring-primary/50 border-none bg-transparent",
                          isOwner ? "cursor-pointer" : "")}
            onClick={isOwner ? togglePlaying : undefined}
          >
            <div className={cn("absolute flex h-full w-full flex-col items-center justify-center transition-all duration-200", isOwner ? "group-hover:opacity-20" : "")}>
              <span className={cn("font-mono text-text-main", isPiP ? "text-xl" : "text-2xl")}>
                {displayTime}
              </span>
              {isPlaying && (
                <span className="absolute top-3/4 mt-2 flex items-center gap-1 text-sm text-text-secondary">
                  <BellRing size={14} aria-hidden="true" /> { endTime }
                </span>
              )}
            </div>
            <div className={cn("absolute opacity-0 transition-opacity", isOwner ? "group-hover:opacity-100" : "")}>
              {isPlaying ? <Pause strokeWidth={1} size={isPiP ? 40 : 60} className="-stroke-3 fill-text-secondary text-text-secondary" aria-hidden="true" /> : <Play size={isPiP ? 40 : 60} className="fill-text-secondary text-text-secondary" aria-hidden="true" />}
            </div>
          </button>
        </CircularProgressbarWithChildren>
        <AnimatePresence mode="wait" initial={false}>
          {!isPlaying && !isPiP && <TimerDurationInputs />}
        </AnimatePresence>
      </div>
      <AnimatePresence mode="wait" initial={false}>
        <div className={cn("flex flex-col gap-2 transition-all duration-300 sm:flex-row", isPiP ? "m-2 flex-row!" : "m-8")}>
          <TimerButton isPiP={isPiP} onClick={togglePlaying} IconComp={isPlaying ? Pause : Play} enabled={isOwner}>
            {isPlaying ? "Pause" : "Start"}
          </TimerButton>
          <TimerButton isPiP={isPiP} onClick={resetTimer} IconComp={RotateCcw} enabled={isOwner}>
            Reset
          </TimerButton>
          <TimerButton isPiP={isPiP} onClick={skipTimer} IconComp={FastForward} enabled={isOwner}>
            Skip to {isResting ? "Work" : "Rest"}
          </TimerButton>
          <TimerButton
            isPiP={isPiP}
            onClick={shareTimer}
            IconComp={Share2}
            className={isShared ? 'inset-shadow-primary/30 bg-primary/20 text-primary hover:bg-primary/30' : ''}
          >
            {hasCopiedShareLink ? 'Link Copied' : isShared ? 'Copy Link' : 'Share'}
          </TimerButton>
        </div>
      </AnimatePresence>
      {!isPiP && (connectionStatus !== 'disconnected' || error) && (
        <div className="mt-2 text-xs text-text-secondary" aria-live="assertive">
          {error ? error : `Live sync: ${connectionStatus}`}
        </div>
      )}
    </div>
  );
}