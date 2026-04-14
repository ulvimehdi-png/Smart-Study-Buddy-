import React from 'react';
import { cn } from '@/lib/utils';

export default function Badge({ icon: Icon, children, className }) {

  return (
    <div className={cn(
      'inline-flex items-center px-4 py-2 rounded-full',
      'bg-badge-text/10 text-badge-text border border-badge-text/20',
      className
    )}>
      {Icon && <Icon className="w-4 h-4 mr-2" />}
      <span className="text-sm font-medium">{children}</span>
    </div>
  );
}
