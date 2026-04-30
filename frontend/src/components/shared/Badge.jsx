import React from 'react';
import { cn } from '@/lib/utils';

export default function Badge({ children, icon: Icon, variant = 'primary', className }) {
  return (
    <span className={cn(
      'inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-medium border',
      variant === 'accent'
        ? 'bg-accent/10 text-accent border-accent/20'
        : 'bg-primary/10 text-primary border-primary/20',
      className
    )}>
      {Icon && <Icon className="w-4 h-4" />}
      {children}
    </span>
  );
}
