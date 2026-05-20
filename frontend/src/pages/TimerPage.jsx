import React from 'react';
import Timer from '@/components/Timer';
import { motion as Motion } from 'motion/react';

export default function TimerPage() {
  return (
    <div className="min-h-screen flex flex-col pt-16">
        <div className="flex-1 flex items-center justify-center transition-all">
            <Timer />
        </div>
    </div>
  );
}
