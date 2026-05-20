import React from 'react';
import { useTimer } from '../lib/useTimer';
import { motion as Motion } from "motion/react";
import { cn } from "@/lib/utils";

export default function TimerDurationInputs({ isPiP = false }) {
  const { 
    isResting,
    isOwner,
    work, setWork,
    rest, setRest,
    setTimerDuration 
  } = useTimer();

  function handleDurationChange(e) {
    const { id, value } = e.target;
    const val = Math.max(0, parseInt(value, 10) || 0);
    const isMinutes = id.includes('minutes');
    
    if (id.startsWith('duration-')) {
      const updatedWork = { ...work, [isMinutes ? 'minutes' : 'seconds']: val };
      setWork(updatedWork);
      if (!isResting) {
        setTimerDuration(updatedWork.minutes, updatedWork.seconds);
      }
    } else if (id.startsWith('rest-')) {
      const updatedRest = { ...rest, [isMinutes ? 'minutes' : 'seconds']: val };
      setRest(updatedRest);
      if (isResting) {
        setTimerDuration(updatedRest.minutes, updatedRest.seconds);
      }
    }
  }

  const inputClass = cn("w-16 rounded border bg-bg-elevated px-2 py-1 text-text-main outline-none transition-all focus:ring-2 focus:ring-primary-light",
                        isOwner ? "" : "cursor-not-allowed"
                      );

  return (
    <Motion.div
      key="duration-inputs"
      initial={{ opacity: 0, scale: 0.3, width: 0, x: -80 }}
      animate={{ opacity: 1, scale: 1, width: 'auto', x: 0 }}
      exit={{ opacity: 0, scale: 0.3, width: 0, x: -80 }}
      transition={{ duration: 0.25, ease: 'easeInOut' }}
      className="mt-4 overflow-hidden md:ml-4"
    >
      <div className={cn("flex flex-col items-end gap-3 py-2 text-text-main", isOwner ? "opacity-100" : "opacity-50")}>
        <div className="flex items-center gap-4">
          <div className="flex items-center gap-1">
            <label htmlFor="duration-minutes" className="font-bold">Work:</label>
            <input
              id="duration-minutes"
              type="number"
              value={work.minutes}
              onChange={handleDurationChange}
              className={inputClass}
              disabled={!isOwner}
              aria-label="Work minutes"
            />
            <span aria-hidden="true">{isPiP ? "m" : "min"}</span>
          </div>
          <div className="flex items-center gap-1">
            <input
              id="duration-seconds"
              type="number"
              value={work.seconds}
              onChange={handleDurationChange}
              className={inputClass}
              disabled={!isOwner}
              aria-label="Work seconds"
            />
            <span aria-hidden="true">{isPiP ? "s" : "sec"}</span>
          </div>
        </div>

        <div className="flex items-center gap-4">
          <div className="flex items-center gap-1">
            <label htmlFor="rest-minutes" className="font-bold">Rest:</label>
            <input
              id="rest-minutes"
              type="number"
              value={rest.minutes}
              onChange={handleDurationChange}
              className={inputClass}
              disabled={!isOwner}
              aria-label="Rest minutes"
            />
            <span aria-hidden="true">{isPiP ? "m" : "min"}</span>
          </div>
          <div className="flex items-center gap-1">
            <input
              id="rest-seconds"
              type="number"
              value={rest.seconds}
              onChange={handleDurationChange}
              className={inputClass}
              disabled={!isOwner}
              aria-label="Rest seconds"
            />
            <span aria-hidden="true">{isPiP ? "s" : "sec"}</span>
          </div>
        </div>
      </div>
    </Motion.div>
  );
}