import { cn, formatTime } from "@/lib/utils"
import React, { useState } from "react";
import { CountdownCircleTimer } from 'react-countdown-circle-timer';
import TimerPip from "./TimerPip";
import { Pause, Play, ChevronLast, Settings, X, BellRing, Icon } from "lucide-react";
import { AnimatePresence, motion } from "motion/react";

export default function Timer({ isPiP = false, defaultDuration = 60*20, defaultRest = 60*5 }) {
  // Timer state
  const [isPlaying, setIsPlaying] = useState(false);
  const [isResetting, setIsResetting] = useState(false);
  const [timerKey, setTimerKey] = useState(0);
  const [isResting, setIsResting] = useState(false);
  const [hideDurationInputs, setHideDurationInputs] = useState(isPiP);

  const [work, setWork] = useState({
    minutes: Math.floor(defaultDuration / 60),
    seconds: defaultDuration % 60,
  });
  const [rest, setRest] = useState({
    minutes: Math.floor(defaultRest / 60),
    seconds: defaultRest % 60,
  });
  // Timer countdown state (in seconds)
  const [duration, setDuration] = useState(defaultDuration);

  // Handler for duration input
  function handleDurationChange(e) {
    const { id, value } = e.target;
    const val = Math.max(0, parseInt(value, 10) || 0);
    const isMinutes = id.endsWith('minutes');
    if (id.startsWith('duration-')) {
      setWork(prev => {
        const updated = { ...prev, [isMinutes ? 'minutes' : 'seconds']: val };
        if (!isResting) setDuration(updated.minutes * 60 + updated.seconds);
        return updated;
      });
      if (!isResting) setTimerKey(prev => prev + 1);
    } else if (id.startsWith('rest-')) {
      setRest(prev => {
        const updated = { ...prev, [isMinutes ? 'minutes' : 'seconds']: val };
        if (isResting) setDuration(updated.minutes * 60 + updated.seconds);
        return updated;
      });
      if (isResting) setTimerKey(prev => prev + 1);
    }
  }

  function setTimerDuration(min, sec) {
    setDuration(min * 60 + sec);
  }

  function resetTimer() {
    if (!isResting) {
      setTimerDuration(rest.minutes, rest.seconds);
    } else {
      setTimerDuration(work.minutes, work.seconds);
    }
    setIsResting(!isResting);
    setIsPlaying(false);
    setIsResetting(false);
    setTimerKey(prev => prev + 1);
  }

  return (
    <div className="flex flex-col items-center h-full justify-center">
      <div className="flex justify-center items-center mt-2">
        <CountdownCircleTimer
          key={timerKey}
          size={isPiP ? 200 : 400}
          strokeWidth={isPiP ? 10 : 15}
          isPlaying={isPlaying}
          duration={duration}
          // colors={isResting ? '#FF6347' : '#1E90FF'}
          colors={isResting ? '#fca5a5' : '#be123c'}
          rotation={"counterclockwise"}
          isGrowing={!isResetting}
          onComplete={() => {
            if (isResetting) {
              resetTimer();
            } else {
              // this is when the timer's line is going back to the start
              setIsResetting(true);
              setDuration(0.5);
              setTimerKey(prev => prev + 1);
            }
          }}
        >
          {({ remainingTime }) => (
            <div
              className={"group relative flex items-center justify-center w-full h-full rounded-full transition-all cursor-pointer"}
              onClick={() => setIsPlaying(!isPlaying)}
            >
              <span
                className={cn(
                  "font-mono text-gray-700 absolute transition-opacity duration-200 group-hover:opacity-10",
                  isPiP ? "text-lg" : "text-2xl"
                )}
              >
                { isResetting ? '00:00' : formatTime(remainingTime) }
              </span>
              {isPlaying && !isResetting && (
                <span className={cn(
                  "font-mono text-gray-500 absolute top-3/4 flex items-center gap-1 transition-opacity duration-200 group-hover:opacity-10",
                  isPiP ? "text-xs" : "text-sm"
                )}>
                  <BellRing size={15} /> {new Date(Date.now() + remainingTime * 1000).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                  })}
                </span>
              )}
              <div className="absolute flex justify-center items-center w-full h-full">
                { isPlaying ? (
                  <Pause size={isPiP ? 30 : 60} className="fill-gray-700 text-gray-700 opacity-0 transition-opacity duration-200 group-hover:opacity-100" />
                ) : (
                  <Play  size={isPiP ? 30 : 60} className="fill-gray-700 text-gray-700 opacity-0 transition-opacity duration-200 group-hover:opacity-100" strokeWidth={4} />
                )}
              </div>
            </div>
          )}
        </CountdownCircleTimer>
      </div>
      { !isPlaying &&
        <div className={cn("flex gap-2", isPiP ? "m-2" : "m-4")}>
          <AnimatePresence mode="wait" initial={false}>
            {hideDurationInputs ? (
              <motion.span
                key="settings"
                initial={{ opacity: 0, scale: 0.7 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.7 }}
                transition={{ duration: 0.1 }}
                style={{ display: 'inline-flex' }}
              >
                <Settings
                  size={isPiP ? 20 : 30}
                  className="text-gray-700 cursor-pointer"
                  aria-label="Show timer settings"
                  onClick={() => setHideDurationInputs(false)}
                />
              </motion.span>
            ) : (
              <motion.span
                key="x"
                initial={{ opacity: 0, scale: 0.7 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.7 }}
                transition={{ duration: 0.1 }}
                style={{ display: 'inline-flex' }}
              >
                <X
                  size={isPiP ? 20 : 30}
                  className="text-gray-700 cursor-pointer"
                  aria-label="Hide timer settings"
                  onClick={() => setHideDurationInputs(true)}
                />
              </motion.span>
            )}
          </AnimatePresence>
          <ChevronLast
            size={isPiP ? 20 : 30}
            className="text-gray-700 cursor-pointer"
            onClick={() => {
              setIsPlaying(false);
              resetTimer();
            }}
          />
          { !isPiP && (
            <TimerPip defaultDuration={work.minutes*60 + work.seconds} defaultRest={rest.minutes*60 + rest.seconds} />
          )}
        </div>
      }
      { !isPlaying && !hideDurationInputs && (
        <div className="flex flex-col items-end gap-4">
          <div className="flex items-end gap-2">
            <label htmlFor="duration-minutes" className="font-semibold">Duration:</label>
            <input
              id="duration-minutes"
              type="number"
              min="0"
              value={work.minutes}
              onChange={handleDurationChange}
              className="border rounded px-2 py-1 w-16"
              placeholder="Minutes"
            />
            <span>{isPiP ? "m" : "minutes"}</span>
            <input
              id="duration-seconds"
              type="number"
              min="0"
              value={work.seconds}
              onChange={handleDurationChange}
              className="border rounded px-2 py-1 w-16"
              placeholder="Seconds"
            />
            <span>{isPiP ? "s" : "seconds"}</span>
          </div>
          <div className="flex items-end gap-2">
            <label htmlFor="rest-minutes" className="font-semibold">Rest:</label>
            <input
              id="rest-minutes"
              type="number"
              min="0"
              value={rest.minutes}
              onChange={handleDurationChange}
              className="border rounded px-2 py-1 w-16"
              placeholder="Minutes"
            />
            <span>{isPiP ? "m" : "minutes"}</span>
            <input
              id="rest-seconds"
              type="number"
              min="0"
              value={rest.seconds}
              onChange={handleDurationChange}
              className="border rounded px-2 py-1 w-16"
              placeholder="Seconds"
            />
            <span>{isPiP ? "s" : "seconds"}</span>
          </div>
        </div>
      )}
    </div>
  );
}