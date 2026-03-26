import { cn } from "@/lib/utils"
import React, { useState } from "react";
import { CountdownCircleTimer } from 'react-countdown-circle-timer';
import TimerPip from "./TimerPip";
import { Pause, Play, ChevronLast, Settings, BellRing } from "lucide-react";

export default function Timer({ isPiP = false, defaultDuration = 60*20, defaultRest = 60*5 }) {
  const [isPlaying, setIsPlaying] = useState(false);
  const [isResetting, setIsResetting] = useState(false);
  const [key, setKey] = useState(0);
  const [duration, setDuration] = useState(defaultDuration);
  const [durationMinutes, setDurationMinutes] = useState(Math.floor(duration / 60));
  const [durationSeconds, setDurationSeconds] = useState(duration % 60);

  const [isResting, setIsResting] = useState(false);
  const [restMinutes, setRestMinutes] = useState(Math.floor(defaultRest / 60));
  const [restSeconds, setRestSeconds] = useState(defaultRest % 60);

  const [hideDurationInputs, setHideDurationInputs] = useState(isPiP);

  // Handler for duration input
  function handleDurationChange(e) {
    const id = e.target.id;
    const val = parseInt(e.target.value, 10);
    const isMinutes = id.endsWith('minutes');
    const isForCurrentMode = (id.startsWith('duration-') && !isResting) || 
                 (id.startsWith('rest-') && isResting);
    
    // Update the appropriate state value
    if (id.startsWith('duration-')) {
      isMinutes ? setDurationMinutes(val) : setDurationSeconds(val);
      if (!isResting) {
      setTimerDuration(isMinutes ? val : durationMinutes, 
               isMinutes ? durationSeconds : val);
      }
    } else if (id.startsWith('rest-')) {
      isMinutes ? setRestMinutes(val) : setRestSeconds(val);
      if (isResting) {
      setTimerDuration(isMinutes ? val : restMinutes, 
               isMinutes ? restSeconds : val);
      }
    }
    
    // Reset timer when duration changes for current mode
    if (isForCurrentMode) {
      setKey(prev => prev + 1);
    }
  }

  function setTimerDuration(min, sec) {
    setDuration(min * 60 + sec);
  }

  function resetTimer() {
    // If switching to rest mode, set the timer to rest duration
    if (!isResting) {
      setTimerDuration(restMinutes, restSeconds);
    } else {
      // Otherwise, set to the main timer duration
      setTimerDuration(durationMinutes, durationSeconds);
    }
    setIsResting(!isResting);
    setIsPlaying(false);
    setIsResetting(false);
    setKey(prev => prev + 1);
  }

  return (
    <div className="flex flex-col items-center h-full justify-center">
      <div className="flex justify-center items-center mt-2">
        <CountdownCircleTimer
          key={key}
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
              setKey(prev => prev + 1);
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
                { isResetting ? '00:00' :
                  `${String(Math.floor(remainingTime / 60)).padStart(2, '0')}:${String(remainingTime % 60).padStart(2, '0')}`
                }
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
          <Settings
            size={isPiP ? 20 : 30}
            className="text-gray-700 cursor-pointer"
            onClick={() => setHideDurationInputs(!hideDurationInputs)}
          />
          <ChevronLast
            size={isPiP ? 20 : 30}
            className="text-gray-700 cursor-pointer"
            onClick={() => {
              setIsPlaying(false);
              resetTimer();
            }}
          />
          { !isPiP && (
            <TimerPip defaultDuration={durationMinutes*60 + durationSeconds} defaultRest={restMinutes*60 + restSeconds} />
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
              value={durationMinutes}
              onChange={handleDurationChange}
              className="border rounded px-2 py-1 w-16"
              placeholder="Minutes"
            />
            <span>{isPiP ? "m" : "minutes"}</span>
            <input
              id="duration-seconds"
              type="number"
              min="0"
              value={durationSeconds}
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
              value={restMinutes}
              onChange={handleDurationChange}
              className="border rounded px-2 py-1 w-16"
              placeholder="Minutes"
            />
            <span>{isPiP ? "m" : "minutes"}</span>
            <input
              id="rest-seconds"
              type="number"
              min="0"
              value={restSeconds}
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