import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { GraduationCap, Menu, X, User, LogOut } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { cn } from '@/lib/utils';
import { useTimer } from '../lib/useTimer';

export default function Navbar() {
  const { isPlaying } = useTimer();

  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const auth = useAuth();
  

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const handleLogout = () => {
    auth.logout();
    navigate('/');
  };

  const isActive = (path) => location.pathname === path;

  return (
    <nav
      className={cn('fixed top-0 left-0 right-0 z-50 transition-all duration-300', {
        'bg-white/95 backdrop-blur-lg shadow-lg': isScrolled,
        'bg-white/80 backdrop-blur-sm': !isScrolled,
        'opacity-0': isPlaying, // Hide navbar when timer is active
      })}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center space-x-2 group">
            <div className="w-10 h-10 bg-linear-to-br from-yellow-400 to-amber-500 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
              <GraduationCap className="w-6 h-6 text-white" />
            </div>
            <span className="text-xl font-bold bg-linear-to-r from-yellow-600 to-amber-600 bg-clip-text text-transparent">
              StudyBuddy
            </span>
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center space-x-8">
            <Link
              to="/"
              className={cn('text-sm font-medium transition-colors', 
                isActive('/') ? 'text-yellow-600' : 'text-gray-700 hover:text-yellow-600'
              )}
            >
              Home
            </Link>
            <a
              href="#features"
              className="text-sm font-medium text-gray-700 hover:text-yellow-600 transition-colors"
            >
              Features
            </a>
            {auth.user && (
              <Link
                to="/timer"
                className={cn('text-sm font-medium transition-colors',
                  isActive('/timer') ? 'text-yellow-600' :
                    'text-gray-700 hover:text-yellow-600',
                )}
              >
                Timer
              </Link>
            )}
          </div>

          {/* Desktop Auth Buttons */}
          <div className="hidden md:flex items-center space-x-4">
            {auth.user ? (
              <div className="flex items-center space-x-4">
                <div className="flex items-center space-x-2 px-3 py-2 bg-yellow-50 rounded-lg">
                  <User className="w-4 h-4 text-yellow-600" />
                  <span className="text-sm font-medium text-yellow-700">
                    {auth.user.username}
                  </span>
                </div>
                <button
                  onClick={handleLogout}
                  className="flex items-center space-x-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"
                >
                  <LogOut className="w-4 h-4" />
                  <span className="text-sm font-medium">Logout</span>
                </button>
              </div>
            ) : (
              <>
                <Link
                  to="/login"
                  className="text-sm font-medium text-gray-700 hover:text-yellow-600 transition-colors"
                >
                  Sign In
                </Link>
                <Link
                  to="/login"
                  className="px-4 py-2 bg-linear-to-r from-yellow-400 to-amber-500 text-white text-sm font-medium rounded-lg hover:shadow-lg hover:scale-105 transition-all"
                >
                  Get Started
                </Link>
              </>
            )}
          </div>

          {/* Mobile Menu Button */}
          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
          >
            {isMobileMenuOpen ? (
              <X className="w-6 h-6 text-gray-700" />
            ) : (
              <Menu className="w-6 h-6 text-gray-700" />
            )}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-white border-t border-gray-200 shadow-lg">
          <div className="px-4 py-4 space-y-3">
            <Link
              to="/"
              onClick={() => setIsMobileMenuOpen(false)}
              className={cn('block px-3 py-2 rounded-lg text-sm font-medium', {
                'bg-purple-50 text-purple-600': isActive('/'),
                'text-gray-700 hover:bg-gray-50': !isActive('/')
              })}
            >
              Home
            </Link>
            <a
              href="#features"
              onClick={() => setIsMobileMenuOpen(false)}
              className="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Features
            </a>
            {auth.user && (
              <Link
                to="/timer"
                onClick={() => setIsMobileMenuOpen(false)}
                className={`block px-3 py-2 rounded-lg text-sm font-medium ${
                  isActive('/timer')
                    ? 'bg-purple-50 text-purple-600'
                    : 'text-gray-700 hover:bg-gray-50'
                }`}
              >
                Timer
              </Link>
            )}
            <div className="pt-3 border-t border-gray-200">
              {auth.user ? (
                <>
                  <div className="flex items-center space-x-2 px-3 py-2 mb-2 bg-yellow-50 rounded-lg">
                    <User className="w-4 h-4 text-yellow-600" />
                    <span className="text-sm font-medium text-yellow-700">
                      {auth.user.username}
                    </span>
                  </div>
                  <button
                    onClick={() => {
                      handleLogout();
                      setIsMobileMenuOpen(false);
                    }}
                    className="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"
                  >
                    <LogOut className="w-4 h-4" />
                    <span className="text-sm font-medium">Logout</span>
                  </button>
                </>
              ) : (
                <>
                  <Link
                    to="/login"
                    onClick={() => setIsMobileMenuOpen(false)}
                    className="block w-full text-center px-4 py-2 mb-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
                  >
                    Sign In
                  </Link>
                  <Link
                    to="/login"
                    onClick={() => setIsMobileMenuOpen(false)}
                    className="block w-full text-center px-4 py-2 bg-linear-to-r from-yellow-400 to-amber-500 text-white text-sm font-medium rounded-lg hover:shadow-lg"
                  >
                    Get Started
                  </Link>
                </>
              )}
            </div>
          </div>
        </div>
      )}
    </nav>
  );
}
