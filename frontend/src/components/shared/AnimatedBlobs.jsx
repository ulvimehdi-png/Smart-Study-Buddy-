import React from 'react';
import { cn } from '@/lib/utils';

export default function AnimatedBlobs() {
  return (
    <div className={cn("absolute inset-0 -z-10 bg-linear-to-br",
                       "from-amber-50 via-yellow-50 to-orange-50",
                       "dark:from-gray-800 dark:via-gray-900 dark:to-gray-800",
                       "overflow-hidden")} >
      <div className="absolute top-20 left-10 w-96 h-96 bg-yellow-300 dark:bg-yellow-600 rounded-full mix-blend-multiply dark:mix-blend-normal blur-3xl opacity-30 animate-blob"></div>
      <div className="absolute top-40 right-10 w-96 h-96 bg-amber-300 dark:bg-amber-600 rounded-full mix-blend-multiply dark:mix-blend-normal blur-3xl opacity-30 animate-blob delay-2000"></div>
      <div className="absolute -bottom-8 left-1/3 w-96 h-96 bg-orange-300 dark:bg-orange-600 rounded-full mix-blend-multiply dark:mix-blend-normal blur-3xl opacity-30 animate-blob delay-4000"></div>
    </div>
  );
}
