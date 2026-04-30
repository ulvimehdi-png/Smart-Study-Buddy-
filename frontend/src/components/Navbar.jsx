import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Menu, X } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { cn } from '@/lib/utils';
import { useTimer } from '../lib/useTimer';
import { Logo, ThemeToggle } from './shared';
import UserButtons from './UserButtons';

const NAV_LINKS = [
  { name: 'Home', path: '/', needsAuth: false, isRoute: true },
  { name: 'Dashboard', path: '/dashboard', needsAuth: true, isRoute: true },
  { name: 'Tasks', path: '/tasks', needsAuth: true, isRoute: true },
  { name: 'Notes', path: '/notes', needsAuth: true, isRoute: true },
  { name: 'Calendar', path: '/calendar', needsAuth: true, isRoute: true },
  { name: 'Timer', path: '/timer', needsAuth: true, isRoute: true },
];

export default function Navbar() {
  const { isPlaying } = useTimer();
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const location = useLocation();
  const auth = useAuth();

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const isActive = (path) => location.pathname === path;

  const getLinkClassName = (path, isMobile = false) => cn(
    'text-sm font-medium transition-all duration-300',
    isMobile ? 'block px-4 py-2 rounded-xl' : 'px-3 py-1.5 rounded-xl',
    isActive(path)
      ? 'text-primary bg-primary/10'
      : 'text-text-secondary hover:text-text-main hover:bg-white/[0.04]'
  );

  const renderNavLink = ({ name, path, needsAuth, isRoute }, isMobile = false) => {
    if (needsAuth && !auth.user) return null;
    const className = getLinkClassName(path, isMobile);
    const onClick = isMobile ? () => setIsMobileMenuOpen(false) : undefined;
    return isRoute ? (
      <Link key={name} to={path} onClick={onClick} className={className}>{name}</Link>
    ) : (
      <a key={name} href={path} onClick={onClick} className={className}>{name}</a>
    );
  };

  return (
    <nav className={cn(
      'fixed top-0 left-0 right-0 z-50 transition-all duration-500',
      isPlaying ? 'opacity-0 pointer-events-none' : 'opacity-100',
      isScrolled || isMobileMenuOpen
        ? 'border-b border-border-base'
        : 'bg-transparent'
    )} style={{
      background: isScrolled || isMobileMenuOpen ? 'var(--bg-navbar)' : 'transparent',
      backdropFilter: isScrolled || isMobileMenuOpen ? 'blur(24px) saturate(180%)' : 'none',
      WebkitBackdropFilter: isScrolled || isMobileMenuOpen ? 'blur(24px) saturate(180%)' : 'none',
    }}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
        <Link to="/" className="flex items-center transition-transform hover:scale-105">
          <Logo size="md" />
        </Link>

        {/* Desktop Nav — Centered */}
        <div className="hidden md:flex items-center gap-1 rounded-2xl bg-white/[0.03] border border-white/[0.06] px-2 py-1">
          {NAV_LINKS.map(link => renderNavLink(link))}
        </div>

        {/* Desktop Actions */}
        <div className="hidden md:flex items-center gap-4">
          <UserButtons />
          <div className="h-6 w-px bg-white/[0.08] mx-1" />
          <ThemeToggle />
        </div>

        {/* Mobile Toggle */}
        <div className="md:hidden flex items-center gap-2">
          <ThemeToggle />
          <button onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="p-2 rounded-xl text-text-secondary hover:bg-white/[0.04] transition" aria-label="Toggle Menu">
            {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMobileMenuOpen && (
        <div className="md:hidden border-t border-border-base"
          style={{ background: 'var(--bg-navbar-mobile)', backdropFilter: 'blur(24px)', animation: 'modalIn 0.3s ease' }}>
          <div className="px-4 py-6 flex flex-col gap-2">
            {NAV_LINKS.map(link => renderNavLink(link, true))}
            <div className="h-px bg-white/[0.06] my-3" />
            <div className="flex justify-center"><UserButtons /></div>
          </div>
        </div>
      )}
    </nav>
  );
}