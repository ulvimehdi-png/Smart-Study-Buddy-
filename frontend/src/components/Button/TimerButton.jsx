import React from 'react';
import { motion as Motion } from 'framer-motion';
import { cn } from '@/lib/utils';

export default function TimerButton({ isPiP, onClick, IconComp, children, className = "" }) {

  return (
    <Motion.button
      key="skip-button"
      initial={{ opacity: 0, scale: 0.7, height: 0, y: -8 }}
      animate={{ opacity: 1, scale: 1, height: 'auto', y: 0 }}
      exit={{ opacity: 0, scale: 0.7, height: 0, y: -8 }}
      transition={{ duration: 0.5, ease: 'easeInOut' }}
      className={cn("flex items-center gap-2 px-4 py-2 rounded-full",
                    "bg-gray-100 hover:bg-gray-100",
                    "hover:-translate-y-0.5 active:translate-y-0.5",
                    "shadow-sm hover:shadow-md active:shadow-sm active:inset-shadow-sm/10 inset-shadow-white",
                    "transition-all duration-100", className)}
      onClick={onClick}
      >
      <IconComp
        size={20}
        className="cursor-pointer"
      />
      {!isPiP && 
      <span className="text-sm font-medium">
        {children}
      </span>}
    </Motion.button>
  );
}