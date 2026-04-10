import React from 'react';
import { cn } from '@/lib/utils';

export default function TimerButton({ isPiP, onClick, IconComp, children, className = "" }) {

  return (
    <button
      className={cn("flex items-center gap-2 rounded-full",
                    "px-5 py-3",
                    "text-text-main items-center justify-center",
                    "bg-text-main/7 hover:bg-text-main/10",
                    "hover:-translate-y-0.5 active:translate-y-0.5",
                    "hover:shadow-lg active:shadow-none active:inset-shadow-sm inset-shadow-text-main/20",
                    "transition-all duration-100", className)}
      onClick={onClick}
      >
      <IconComp
        size={20}
        className="cursor-pointer"
      />
      {!isPiP && 
      <span>
        {children}
      </span>}
    </button>
  );
}