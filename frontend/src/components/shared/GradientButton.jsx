import React from 'react';
import { Link } from 'react-router-dom';
import { cn } from '@/lib/utils';

export default function GradientButton({ 
  children, 
  to, 
  href, 
  onClick, 
  variant = 'primary',
  size = 'md',
  icon: Icon,
  iconPosition = 'right',
  className,
  ...props 
}) {
  const variants = {
    primary: 'bg-linear-to-r from-primary-light to-accent-light dark:from-primary dark:to-accent text-white hover:shadow-2xl',
    secondary: 'bg-bg-card shadow-2xl border border-2 border-primary hover:border-accent-light text-primary hover:text-accent-light hover:shadow-xl',
  };

  const sizes = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-8 py-4 text-base',
    lg: 'px-10 py-5 text-lg',
  };

  const baseClasses = cn(
    'inline-flex items-center justify-center font-bold rounded-full transition-all duration-300 hover:scale-105',
    variants[variant],
    sizes[size],
    className
  );

  const content = (
    <>
      {Icon && iconPosition === 'left' && <Icon className="w-5 h-5 mr-2" />}
      {children}
      {Icon && iconPosition === 'right' && <Icon className="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />}
    </>
  );

  if (to) {
    return (
      <Link to={to} className={cn(baseClasses, 'group')} {...props}>
        {content}
      </Link>
    );
  }

  if (href) {
    return (
      <a href={href} className={cn(baseClasses, 'group')} {...props}>
        {content}
      </a>
    );
  }

  return (
    <button onClick={onClick} className={cn(baseClasses, 'group')} {...props}>
      {content}
    </button>
  );
}
