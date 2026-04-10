import React from 'react';
import { CheckCircle2 } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function FeatureCard({ 
  icon: Icon, 
  title, 
  description, 
  gradient = '',
  showCheck = true,
  className 
}) {
  return (
    <div className={cn(
      'group relative bg-bg-card rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border',
      className
    )}>
      <div className={cn(
        'w-14 h-14 bg-linear-to-br rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg',
        gradient
      )}>
        <Icon className="w-7 h-7 text-white" />
      </div>
      <h3 className="text-xl font-bold text-primary mb-2">{title}</h3>
      <p className="text-text-secondary">{description}</p>
      {showCheck && (
        <div className="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
          <CheckCircle2 className="w-6 h-6 text-green-500" />
        </div>
      )}
    </div>
  );
}
