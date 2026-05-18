import React from 'react';
import { GraduationCap } from 'lucide-react';
import { cn } from '@/lib/utils';
import LogoIcon from '/LogoIcon.png'

export default function Logo({ size = 'md', showIcon = true, showText = true, className, textClassName }) {
  const sizes = {
    sm: {
      container: 'w-8 h-8 rounded-lg',
      icon: 'w-4 h-4',
      text: 'text-lg',
    },
    md: {
      container: 'w-10 h-10 rounded-xl',
      icon: 'w-6 h-6',
      text: 'text-xl',
    },
    lg: {
      container: 'w-12 h-12 rounded-2xl',
      icon: 'w-8 h-8',
      text: 'text-2xl',
    },
  };

  const config = sizes[size];

  return (
    <div className={cn('flex items-center space-x-2', className)}>
      {showIcon && (
        <img src={LogoIcon} alt="Logo" className={cn(config.container)} />
      )}
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
