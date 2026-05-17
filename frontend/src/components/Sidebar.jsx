import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Menu, X, LayoutDashboard, CheckSquare, FileText, Calendar as CalendarIcon, Timer, Trophy, BarChart2, Home } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { cn } from '@/lib/utils';
import { useTimer } from '../lib/useTimer';
import { Logo, ThemeToggle } from './shared';
import UserButtons from './UserButtons';

const NAV_LINKS = [
  { name: 'Home', path: '/', icon: Home, needsAuth: false, isRoute: true },
  { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard, needsAuth: true, isRoute: true },
  { name: 'Tasks', path: '/tasks', icon: CheckSquare, needsAuth: true, isRoute: true },
  { name: 'Notes', path: '/notes', icon: FileText, needsAuth: true, isRoute: true },
  { name: 'Calendar', path: '/calendar', icon: CalendarIcon, needsAuth: true, isRoute: true },
  { name: 'Timer', path: '/timer', icon: Timer, needsAuth: true, isRoute: true },
  { name: 'Leaderboard', path: '/leaderboard', icon: Trophy, needsAuth: true, isRoute: true },
  { name: 'Reports', path: '/reports', icon: BarChart2, needsAuth: true, isRoute: true },
];

export default function Sidebar() {
  const { isPlaying } = useTimer();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const location = useLocation();
  const auth = useAuth();

  // Close mobile menu when route changes
  useEffect(() => {
    setIsMobileMenuOpen(false);
  }, [location.pathname]);

  // Prevent scrolling when mobile menu is open
  useEffect(() => {
    if (isMobileMenuOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [isMobileMenuOpen]);

  const isActive = (path) => location.pathname === path;

  const renderNavLink = ({ name, path, icon: Icon, needsAuth, isRoute }) => {
    if (needsAuth && !auth.user) return null;
    
    const active = isActive(path);
    const className = cn(
      'flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200 group',
      active 
        ? 'bg-primary/10 text-primary' 
        : 'text-text-secondary hover:bg-elevated hover:text-primary-dark'
    );

    const content = (
      <>
        <Icon className="w-5 h-5" />
        <span>{name}</span>
      </>
    );

    return isRoute ? (
      <Link key={name} to={path} className={className}>
        {content}
      </Link>
    ) : (
      <a key={name} href={path} className={className}>
        {content}
      </a>
    );
  };

  return (
    <>
      {/* Mobile Top Bar */}
      <div className={cn(
        "md:hidden fixed top-0 left-0 right-0 h-16 z-40 bg-bg-base/80 backdrop-blur-lg border-b border-base flex items-center justify-between px-4 transition-opacity duration-300",
        isPlaying ? 'opacity-0 pointer-events-none' : 'opacity-100'
      )}>
        <Link to="/" className="flex items-center">
          <Logo size="sm" />
        </Link>
        <div className="flex items-center gap-2">
          <ThemeToggle />
          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="p-2 rounded-lg text-secondary hover:bg-elevated transition-colors"
          >
            {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {/* Backdrop for mobile */}
      {isMobileMenuOpen && (
        <div 
          className="md:hidden fixed inset-0 bg-black/50 z-40 backdrop-blur-sm"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          "fixed top-0 left-0 h-screen z-50 flex flex-col bg-bg-base border-r border-base transition-all duration-300 w-64",
          // Mobile state
          isMobileMenuOpen ? "translate-x-0" : "-translate-x-full md:translate-x-0",
          // Timer state
          isPlaying ? "md:-translate-x-full opacity-0 pointer-events-none" : "opacity-100"
        )}
      >
        {/* Logo Area */}
        <div className="h-16 flex items-center justify-between px-6 border-b border-base shrink-0">
          <Link to="/" className="flex items-center">
            <Logo size="md" className="transition-transform hover:scale-105" />
          </Link>
          {/* Close button for mobile inside sidebar */}
          <button 
            className="md:hidden p-1 rounded-lg text-text-muted hover:bg-elevated"
            onClick={() => setIsMobileMenuOpen(false)}
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Navigation Links */}
        <div className="flex-1 overflow-y-auto py-6 px-4 flex flex-col gap-2 subject-select-scrollbar">
          {NAV_LINKS.map(renderNavLink)}
        </div>

        {/* Bottom Actions */}
        <div className="p-4 border-t border-base shrink-0 flex flex-col gap-4">
          <div className="flex items-center justify-between px-2">
            <span className="text-sm font-medium text-text-muted">Theme</span>
            <ThemeToggle />
          </div>
          <UserButtons />
        </div>
      </aside>
    </>
  );
}
