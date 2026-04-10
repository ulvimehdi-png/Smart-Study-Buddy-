import React from 'react';
import { Moon, Sun } from 'lucide-react';
import { useTheme } from '@/lib/useTheme';
import { cn } from '@/lib/utils';

export default function ThemeToggle({ className }) {
  const { theme, toggleTheme } = useTheme();

  return (
    <button
      onClick={toggleTheme}
      className={cn(
        'p-2 rounded-lg hover:bg-text-main/10 transition-colors cursor-pointer',
        className
      )}
      aria-label="Toggle theme"
    >
      {theme === 'light' ? (
        <Moon className="w-5 h-5 text-text-main" />
      ) : (
        <Sun className="w-5 h-5 text-text-main" />
      )}
    </button>
  );
}
