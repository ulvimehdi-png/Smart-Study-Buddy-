import React from 'react';
import { GraduationCap } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function Logo({ size = 'md', showText = true, className, textClassName }) {
  const sizes = {
    sm: {
      container: 'w-8 h-8',
      icon: 'w-4 h-4',
      text: 'text-lg',
    },
    md: {
      container: 'w-10 h-10',
      icon: 'w-6 h-6',
      text: 'text-xl',
    },
    lg: {
      container: 'w-12 h-12',
      icon: 'w-8 h-8',
      text: 'text-2xl',
    },
  };

  const config = sizes[size];

  return (
    <div className={cn('flex items-center space-x-2', className)}>
      <div className={cn(
        'bg-gradient-primary rounded-xl flex items-center justify-center shadow-lg',
        config.container
      )}>
        <GraduationCap className={cn('text-white', config.icon)} />
      </div>
      {showText && (
        <span className={cn(
          'font-bold text-gradient-primary',
          config.text,
          textClassName
        )}>
          StudyBuddy
        </span>
      )}
    </div>
  );
}
