import React from 'react';
import Timer from '@/components/Timer';
import TimerPip from '../components/TimerPip';
import { motion as Motion } from 'motion/react';

export default function TimerPage() {
  return (
    <div className="min-h-screen flex flex-col pt-16">
        <Motion.div className="flex-1 flex items-center justify-center transition-all">
            <Timer />
        </Motion.div>

      <div className='absolute bottom-10 right-10'>
        <TimerPip className='shadow-lg' />
      </div>

    </div>
  );
}
