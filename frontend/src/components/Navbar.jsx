import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { Menu, X } from 'lucide-react'; // Removed unused User, LogOut
import { useAuth } from '../lib/auth';
import { cn } from '@/lib/utils';
import { useTimer } from '../lib/useTimer';
import { Logo, ThemeToggle } from './shared'; // Removed unused GradientButton
import UserButtons from './UserButtons';

const NAV_LINKS = [
  { name: 'Home', path: '/', needsAuth: false, isRoute: true },
  { name: 'Features', path: '/#features', needsAuth: false, isRoute: true },
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

  const getLinkClassName = (path, isMobile = true) => {
    return cn(
      'text-sm font-medium transition-colors duration-200',
      isMobile ? 'block px-3 py-2 rounded-lg' : '',
      isActive(path) 
        ? 'text-primary' // Use our brand primary color
        : 'text-text-secondary hover:text-accent dark:hover:text-accent-light'
    );
  };

  const renderNavLink = ({ name, path, needsAuth, isRoute }, isMobile = false) => {
    if (needsAuth && !auth.user) return null;
    
    const className = getLinkClassName(path, isMobile);
    const onClick = isMobile ? () => setIsMobileMenuOpen(false) : undefined;

    return isRoute ? (
      <Link key={name} to={path} onClick={onClick} className={className}>
        {name}
      </Link>
    ) : (
      <a key={name} href={path} onClick={onClick} className={className}>
        {name}
      </a>
    );
  };

  return (
    <nav
      className={cn(
        'fixed top-0 left-0 right-0 z-50 transition-all duration-300 backdrop-blur-lg',
        isPlaying ? 'opacity-0 pointer-events-none' : 'opacity-100',
        isScrolled || isMobileMenuOpen
          ? 'bg-bg-base/80 shadow-lg border-b border-base'
          : 'bg-transparent'
      )}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
        {/* Logo */}
        <Link to="/" className="flex items-center">
          <Logo size="md" className="transition-transform hover:scale-105" />
        </Link>

        {/* Desktop Navigation */}
        <div className="hidden md:flex gap-8">
          {NAV_LINKS.map((link) => renderNavLink(link))}
        </div>

        {/* Desktop Actions */}
        <div className="hidden md:flex items-center gap-4">
          <UserButtons />
          <div className="h-6 w-[1px] bg-border-base mx-2" /> {/* Divider */}
          <ThemeToggle />
        </div>

        {/* Mobile Toggle */}
        <div className="md:hidden flex items-center gap-2">
          <ThemeToggle />
          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="p-2 rounded-lg text-secondary hover:bg-elevated transition-colors"
            aria-label="Toggle Menu"
          >
            {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-base border-t border-base shadow-xl animate-in fade-in slide-in-from-top-4 duration-200">
          <div className="px-4 py-6 flex flex-col gap-4 text-center">
            {NAV_LINKS.map((link) => renderNavLink(link, true))}
            <div className="h-[1px] bg-border-base my-2" />
            <div className="flex justify-center">
              <UserButtons />
            </div>
          </div>
        </div>
      )}
    </nav>
  );
}