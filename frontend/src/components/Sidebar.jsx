import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Menu, X, LayoutDashboard, CheckSquare, FileText, Calendar as CalendarIcon, Timer, Trophy, BarChart2, Home } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { cn } from '@/lib/utils';
import { useTimer } from '../lib/useTimer';
import { Logo, ThemeToggle } from './shared';
import UserButtons from './UserButtons';

const NAV_LINKS = [
  { name: 'Home',        path: '/',            icon: Home,            needsAuth: false },
  { name: 'Dashboard',   path: '/dashboard',   icon: LayoutDashboard, needsAuth: true },
  { name: 'Tasks',       path: '/tasks',       icon: CheckSquare,     needsAuth: true },
  { name: 'Notes',       path: '/notes',       icon: FileText,        needsAuth: true },
  { name: 'Calendar',    path: '/calendar',    icon: CalendarIcon,    needsAuth: true },
  { name: 'Timer',       path: '/timer',       icon: Timer,           needsAuth: true },
  { name: 'Leaderboard', path: '/leaderboard', icon: Trophy,          needsAuth: true },
  { name: 'Reports',     path: '/reports',     icon: BarChart2,       needsAuth: true },
];

export default function Sidebar({ isSidebarOpen = true, setIsSidebarOpen }) {
  const { isPlaying } = useTimer();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const location = useLocation();
  const auth = useAuth();

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

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

  const renderNavLink = ({ name, path, icon: Icon, needsAuth }) => {
    if (needsAuth && !auth.user) return null;

    const active = isActive(path);
    const className = cn(
      'flex items-center rounded-xl font-medium transition-all duration-200 group',
      isSidebarOpen ? 'gap-3 px-4 py-3' : 'justify-center p-3 mx-2',
      active
        ? 'bg-primary/10 text-primary'
        : 'text-text-secondary hover:bg-elevated hover:text-primary-dark'
    );

    const content = (
      <>
        <Icon className={cn("w-5 h-5 shrink-0 transition-transform group-hover:scale-110")} />
        <span className={cn(
          "whitespace-nowrap transition-all duration-300",
          isSidebarOpen ? "opacity-100 lg:w-auto" : "lg:opacity-0 lg:w-0 lg:hidden"
        )}>
          {name}
        </span>
      </>
    );

    return (
      <Link key={name} to={path} className={className} onClick={() => active && setIsMobileMenuOpen(false)} prefetch="viewport">
        {content}
      </Link>
    ); 
  };

  return (
    <>
      {/* Top Bar */}
      <div className={cn(
        "lg:hidden fixed top-0 left-0 right-0 h-16 z-40 flex items-center justify-between px-4 transition-all duration-300",
        isScrolled
          ? "bg-bg-base/30 backdrop-blur-lg border-b border-base shadow-sm"
          : "bg-transparent border-transparent",
        (isPlaying && location.pathname.startsWith('/timer')) ? 'opacity-0 pointer-events-none' : 'opacity-100'
      )}>

        <button
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          className="p-2 rounded-lg text-text-secondary hover:bg-elevated transition-colors"
        >
          {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
        </button>

        <Link to="/" className="flex items-center">
          <Logo size="sm" />
        </Link>

        <span /> {/* So that the Logo stays in the center */}
      </div>

      {/* Backdrop for mobile */}
      {isMobileMenuOpen && (
        <div
          className="lg:hidden fixed inset-0 bg-black/50 z-40 backdrop-blur-sm"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          "fixed top-0 left-0 h-screen z-50 flex flex-col bg-bg-base border-r border-base transition-all duration-300",
          "w-64", // default mobile width
          isSidebarOpen ? "lg:w-64" : "lg:w-20", // dynamic desktop width
          // Mobile state
          isMobileMenuOpen ? "translate-x-0" : "-translate-x-full lg:translate-x-0",
          // Timer state
          (isPlaying && location.pathname.startsWith('/timer')) ? "lg:-translate-x-full opacity-0 pointer-events-none" : "opacity-100"
        )}
      >
        {/* Logo Area */}
        <div className={cn(
          "h-16 flex items-center border-b border-base shrink-0 transition-all duration-300",
          isSidebarOpen ? "px-6 justify-between" : "px-0 justify-center"
        )}>
          {isSidebarOpen ? (
            <Link to="/" className="flex items-center">
              <Logo size="md" />
            </Link>
          ) : (
            <button
              className="hidden lg:flex p-2 rounded-lg text-text-muted hover:bg-elevated hover:text-primary transition-colors"
              onClick={() => { if (setIsSidebarOpen) setIsSidebarOpen(true); }}
            >
              <Menu className="w-6 h-6" />
            </button>
          )}

          <div className="flex items-center gap-1">
            {/* Close button for mobile inside sidebar */}
            <button
              className="lg:hidden p-1 rounded-lg text-text-muted hover:bg-elevated"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              <X className="w-5 h-5" />
            </button>
            {/* Collapse button for desktop inside sidebar */}
            {isSidebarOpen && (
              <button
                className="hidden lg:block p-1 rounded-lg text-text-muted hover:bg-elevated hover:text-primary transition-colors"
                onClick={() => { if (setIsSidebarOpen) setIsSidebarOpen(false); }}
              >
                <X className="w-5 h-5" />
              </button>
            )}
          </div>
        </div>

        {/* Navigation Links */}
        <div className={cn(
          "flex-1 overflow-y-auto py-6 flex flex-col gap-2 subject-select-scrollbar",
          isSidebarOpen ? "px-4" : "px-0 items-center"
        )}>
          {NAV_LINKS.map(renderNavLink)}
        </div>

        {/* Bottom Actions */}
        <div className={cn(
          "p-4 border-t border-base shrink-0 flex flex-col gap-4",
          isSidebarOpen ? "" : "items-center px-2"
        )}>
          <div className={cn("flex items-center", isSidebarOpen ? "justify-between px-2" : "justify-center")}>
            {isSidebarOpen && <span className="text-sm font-medium text-text-muted">Theme</span>}
            <ThemeToggle />
          </div>
          <UserButtons isCollapsed={!isSidebarOpen} />
        </div>
      </aside>
    </>
  );
}
