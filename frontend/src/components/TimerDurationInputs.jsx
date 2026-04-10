import React from 'react';
import { useTimer } from '../lib/useTimer';
import { motion as Motion } from "motion/react";
import { cn } from "@/lib/utils";

export default function TimerDurationInputs({ isPiP = false }) {
  const { 
    isResting, 
    work, setWork, 
    rest, setRest, 
    setTimerDuration // This should now update msLeft and durationMs
  } = useTimer();

  function handleDurationChange(e) {
    const { id, value } = e.target;
    const val = Math.max(0, parseInt(value, 10) || 0);
    const isMinutes = id.endsWith('minutes');
    
    if (id.startsWith('duration-')) {
      const updatedWork = { ...work, [isMinutes ? 'minutes' : 'seconds']: val };
      setWork(updatedWork);
      // If we are currently in "Work" mode, update the active timer immediately
      if (!isResting) {
        setTimerDuration(updatedWork.minutes, updatedWork.seconds);
      }
    } else if (id.startsWith('rest-')) {
      const updatedRest = { ...rest, [isMinutes ? 'minutes' : 'seconds']: val };
      setRest(updatedRest);
      // If we are currently in "Rest" mode, update the active timer immediately
      if (isResting) {
        setTimerDuration(updatedRest.minutes, updatedRest.seconds);
      }
    }
  }

  const inputClass = "border rounded px-2 py-1 w-16 bg-bg-elevated text-text-main focus:ring-2 focus:ring-primary-light outline-none transition-all";

  return (
    <Motion.div
      key="duration-inputs"
      initial={{ opacity: 0, scale: 0.3, width: 0, x: -80 }}
      animate={{ opacity: 1, scale: 1, width: 'auto', x: 0 }}
      exit={{ opacity: 0, scale: 0.3, width: 0, x: -80 }}
      transition={{ duration: 0.25, ease: 'easeInOut' }}
      className={cn("overflow-hidden mt-4 md:ml-4")}
    >
      <div className="flex flex-col items-end gap-3 py-2 text-text-main">
        {/* Work Duration Row */}
        <div className="flex items-center gap-4">
          <div className="gap-1 flex items-center">
            <label className="font-bold">Work:</label>
            <input
              id="duration-minutes"
              type="number"
              value={work.minutes}
              onChange={handleDurationChange}
              className={inputClass}
            />
            <span>{isPiP ? "m" : "min"}</span>
          </div>
          <div className="gap-1 flex items-center">
            <input
              id="duration-seconds"
              type="number"
              value={work.seconds}
              onChange={handleDurationChange}
              className={inputClass}
            />
            <span>{isPiP ? "s" : "sec"}</span>
          </div>
        </div>

        {/* Rest Duration Row */}
        <div className="flex items-center gap-4">
          <div className="gap-1 flex items-center">
            <label className="font-bold">Rest:</label>
            <input
              id="rest-minutes"
              type="number"
              value={rest.minutes}
              onChange={handleDurationChange}
              className={inputClass}
            />
            <span>{isPiP ? "m" : "min"}</span>
          </div>
          <div className="gap-1 flex items-center">
            <input
              id="rest-seconds"
              type="number"
              value={rest.seconds}
              onChange={handleDurationChange}
              className={inputClass}
            />
            <span>{isPiP ? "s" : "sec"}</span>
          </div>
        </div>
      </div>
    </Motion.div>
  );
}
