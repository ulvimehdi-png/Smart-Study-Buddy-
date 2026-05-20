import React from 'react';
import { cn } from '@/lib/utils';

export default function AnimatedBlobs() {
  return (
    <div className={cn("absolute inset-0 -z-10 bg-linear-to-br",
                       "from-amber-50 via-yellow-50 to-orange-50",
                       "dark:from-gray-800 dark:via-gray-900 dark:to-gray-800",
                       "overflow-hidden ambient-blobs")} />
  );
}
