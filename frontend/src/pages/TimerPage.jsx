import React from 'react';
import Timer from '@/components/Timer';
import TimerPip from '../components/TimerPip';
import { useTimer } from '../lib/useTimer';
import TimerDurationInputs from '../components/TimerDurationInputs';
import { AnimatePresence, motion as Motion } from 'motion/react';

import { MercureTest } from '../components/MercureTest';

export default function TimerPage() {

    return (
        <div className="min-h-screen flex flex-col pt-16">
            <MercureTest />

            <Motion.div className="flex-1 flex items-center justify-center transition-all">
                <Timer />
            </Motion.div>

            <div className='absolute bottom-10 right-10'>
                <TimerPip className='shadow-lg' />
            </div>

        </div>
    );
}