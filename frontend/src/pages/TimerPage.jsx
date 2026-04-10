import React from 'react';
import Timer from '@/components/Timer';
import TimerPip from '../components/TimerPip';
import { useTimer } from '../lib/useTimer';
import TimerDurationInputs from '../components/TimerDurationInputs';
import { AnimatePresence, motion as Motion } from 'motion/react';

export default function TimerPage() {
  const { isPlaying } = useTimer();
  return (
    <div className="min-h-screen flex flex-col">
      <Motion.div className="flex-1 flex items-center justify-center transition-all">
        <Timer />
        <AnimatePresence mode="wait" initial={false}>
          {!isPlaying && <TimerDurationInputs />}
        </AnimatePresence>
      </Motion.div>

      <div className='absolute bottom-10 right-10'>
        <TimerPip className='shadow-lg' />
      </div>

    </div>
  );
}