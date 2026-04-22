import React, { useState } from 'react';
import { AnimatePresence, motion as Motion } from 'framer-motion';
import { Share2, FastForward, Pause, Play, RotateCcw, BellRing } from 'lucide-react';
import { CircularProgressbarWithChildren, buildStyles } from 'react-circular-progressbar';
import 'react-circular-progressbar/dist/styles.css';
import { cn } from "@/lib/utils";
import { useTimer } from '@/lib/useTimer';
import TimerDurationInputs from './TimerDurationInputs';
import TimerButton from './Button/TimerButton';

function formatTime(timeLeftMs) {
  return `${String(Math.floor(timeLeftMs / 60000)).padStart(2, '0')}:${String(Math.ceil((timeLeftMs % 60000) / 1000)).padStart(2, '0')}`;
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
    startSharing,
    stopSharing,
  } = useTimer();

  const percentage = durationMs > 0 ? (msLeft / durationMs) * 100 : 0;
  const displayTime = formatTime(msLeft);
  const [endTime, setEndTime] = useState('--:--');

  // --- HIDDEN POINT TRACKER ---
  const [sessionStartTime, setSessionStartTime] = useState(null);

  React.useEffect(() => {
    if (isPlaying) {
      setSessionStartTime(Date.now());
    } else if (sessionStartTime) {
      const elapsedMs = Date.now() - sessionStartTime;
      const minutes = Math.round(elapsedMs / 60000);

      if (minutes > 0) {
        fetch("/api/achievements/award-manual", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ minutes }),
          credentials: "include",
        });
      }
      setSessionStartTime(null);
    }
  }, [isPlaying]);
  // ----------------------------

  React.useEffect(() => {
    if (isPlaying) {
      setEndTime(formatDate(msLeft));
    }
  }, [isPlaying, msLeft]);

  return (
    <div className="flex flex-col items-center h-full justify-center" id="timer">
      <div className={cn("relative flex flex-col md:flex-row justify-center items-center mt-2", isPiP ? "w-48 h-48" : "w-full h-full")} >
        <CircularProgressbarWithChildren
          value={100 - percentage}
          counterClockwise={false}
          strokeWidth={isPiP ? 4 : 4}
          styles={buildStyles({
            pathColor: isResting ? 'var(--color-timer-rest)' : 'var(--color-timer-work)',
            trailColor: 'var(--color-timer-trail)',
            pathTransition: 'none',
          })}
        >
          <div
            className="group relative w-full h-full inset-0 flex items-center justify-center rounded-full cursor-pointer"
            onClick={togglePlaying}
          >
            <div className="flex flex-col items-center justify-center absolute w-full h-full transition-all duration-200 group-hover:opacity-20">
              <span className={cn("font-mono text-text-main", isPiP ? "text-xl" : "text-2xl")}>
                {displayTime}
              </span>
              {isPlaying && (
                <span className="absolute top-3/4 flex items-center gap-1 text-text-secondary text-sm mt-2">
                  <BellRing size={14} /> { endTime }
                </span>
              )}
            </div>
            <div className="absolute opacity-0 group-hover:opacity-100 transition-opacity">
              {isPlaying ? <Pause strokeWidth={1} size={isPiP ? 40 : 60} className="-stroke-3 fill-text-secondary text-text-secondary"/> : <Play size={isPiP ? 40 : 60} className="fill-text-secondary text-text-secondary" />}
            </div>
          </div>
        </CircularProgressbarWithChildren>
        <AnimatePresence mode="wait" initial={false}>
          {!isPlaying && !isPiP && <TimerDurationInputs />}
        </AnimatePresence>
      </div>
      <AnimatePresence mode="wait" initial={false}>
        <div className={cn("flex flex-col sm:flex-row gap-2 transition-all duration-300", isPiP ? "flex-row! m-2" : "m-8")}>
          <TimerButton isPiP={isPiP} onClick={togglePlaying} IconComp={isPlaying ? Pause : Play}>
            {isPlaying ? "Pause" : "Start"}
          </TimerButton>
          <TimerButton isPiP={isPiP} onClick={resetTimer} IconComp={RotateCcw}>
            Reset
          </TimerButton>
          <TimerButton isPiP={isPiP} onClick={skipTimer} IconComp={FastForward}>
            Skip to {isResting ? "Work" : "Rest"}
          </TimerButton>
          <TimerButton
            isPiP={isPiP}
            onClick={isShared ? stopSharing : startSharing}
            IconComp={Share2}
            className={isShared ? 'bg-primary/10 text-primary' : ''}
          >
            {isShared ? 'Stop Sharing' : 'Share'}
          </TimerButton>
        </div>
      </AnimatePresence>
    </div>
  );
}