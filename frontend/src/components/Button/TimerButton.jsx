import React from 'react';
import { cn } from '@/lib/utils';

export default function TimerButton({ isPiP, onClick, IconComp, children, enabled = true, className = "" }) {

  return (
    <button
      className={cn("flex items-center gap-2 rounded-full",
                    "px-5 py-3",
                    "text-text-main bg-text-main/7 items-center justify-center backdrop-blur-sm",
                    enabled ? ["hover:bg-text-main/10",
                              "hover:-translate-y-0.5 active:translate-y-0.5",
                              "hover:shadow-lg active:shadow-none active:inset-shadow-sm inset-shadow-text-main/20",
                              "transition-all duration-100",
                              "cursor-pointer"]

                            : ["opacity-50 hover:bg-text-main/7",
                              "hover:translate-y-0",
                              "hover:shadow-none hover:inset-shadow-none",
                              "cursor-not-allowed"]

                              , className)}
      onClick={onClick}
      disabled={!enabled}
      >
      <IconComp
        size={20}
      />
      {!isPiP && 
      <span>
        {children}
      </span>}
    </button>
  );
}