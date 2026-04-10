import React, { useEffect, useState } from 'react';
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from '../lib/auth';
import { BookOpen, Brain, Sparkles } from 'lucide-react';
import { AnimatedBlobs, Logo } from '../components/shared';

export default function Login() {
  const navigate = useNavigate();
  const location = useLocation();
  const [currentView, setCurrentView] = useState('login');
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  const auth = useAuth();
  const from = location.state?.from?.pathname || "/timer";
  
  const handleSubmit = (e) => {
    e.preventDefault();
    if (currentView === 'register') {
      handleRegister();
    } else {
      handleLogin();
    }
  }

  const handleRegister = async () => {
    try {
      await auth.register(username, password);
      setMessage('Registration successful! Please log in.');
      setCurrentView('login');
    } catch {
      setMessage('Error: Username might be taken.');
    }
  };
  
  const handleLogin = async () => {
    try {
      await auth.login(username, password);
      setMessage('');
      navigate(from, { replace: true });
    } catch {
      setMessage('Invalid username or password.');
    }
  };

  return (
    <div className="relative min-h-screen w-full flex items-center justify-center bg-gradient-hero dark:bg-gradient-to-br dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 p-4 pt-20">
      {/* Floating Background Elements */}
      <AnimatedBlobs />

      {/* Main Card */}
      <div className="relative w-full max-w-md">
        <div className="absolute inset-0 bg-gradient-primary rounded-3xl blur-2xl opacity-20"></div>
        
        <div className="relative bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20 dark:border-gray-700/50">
          {/* Logo & Header */}
          <div className="text-center mb-8">
            <div className="flex justify-center mb-4">
              <Logo size="lg" showText={false} />
            </div>
            <h1 className="text-4xl font-bold text-gradient-primary mb-2">
              StudyBuddy
            </h1>
            <p className="text-gray-600 dark:text-gray-400 text-sm">Your personal learning companion</p>
          </div>

          {/* Features Icons */}
          <div className="grid grid-cols-3 gap-3 mb-6">
            <div className="flex flex-col items-center p-3 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-xl">
              <BookOpen className="w-5 h-5 text-yellow-600 dark:text-yellow-400 mb-1" />
              <span className="text-xs text-yellow-700 dark:text-yellow-300 font-medium">Study</span>
            </div>
            <div className="flex flex-col items-center p-3 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 rounded-xl">
              <Brain className="w-5 h-5 text-amber-600 dark:text-amber-400 mb-1" />
              <span className="text-xs text-amber-700 dark:text-amber-300 font-medium">Focus</span>
            </div>
            <div className="flex flex-col items-center p-3 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl">
              <Sparkles className="w-5 h-5 text-orange-600 dark:text-orange-400 mb-1" />
              <span className="text-xs text-orange-700 dark:text-orange-300 font-medium">Excel</span>
            </div>
          </div>

          {/* Message Display */}
          {(message || auth.error) && (
            <div className={`mb-4 p-3 rounded-xl text-sm ${
              message.includes('successful') 
                ? 'bg-green-50 text-green-700 border border-green-200' 
                : 'bg-red-50 text-red-700 border border-red-200'
            }`}>
              {message || auth.error}
            </div>
          )}

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="text-center mb-6">
              <h2 className="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {currentView === 'register' ? 'Create Account' : 'Welcome Back'}
              </h2>
              <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {currentView === 'register' 
                  ? 'Join thousands of successful students' 
                  : 'Continue your learning journey'}
              </p>
            </div>

            <div className="space-y-4">
              <div className="relative">
                <input 
                  type="text"
                  placeholder="Username" 
                  value={username} 
                  onChange={e => setUsername(e.target.value)} 
                  required
                  className="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500 text-gray-900 dark:text-gray-100"
                />
              </div>

              <div className="relative">
                <input 
                  type="password"
                  placeholder="Password" 
                  value={password} 
                  onChange={e => setPassword(e.target.value)} 
                  required
                  className="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 dark:placeholder-gray-500 text-gray-900 dark:text-gray-100"
                />
              </div>
            </div>

            <button 
              type="submit" 
              disabled={auth.loading}
              className="w-full py-3 bg-gradient-primary-extended text-white font-semibold rounded-xl hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
            >
              {auth.loading ? (
                <span className="flex items-center justify-center">
                  <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Please wait...
                </span>
              ) : (
                currentView === 'register' ? 'Create Account' : 'Sign In'
              )}
            </button>

            <div className="text-center mt-6">
              <button
                type="button"
                onClick={() => {
                  setMessage('');
                  setCurrentView(currentView === 'register' ? 'login' : 'register');
                }}
                className="amber-link text-sm font-medium transition-colors"
              >
                {currentView === 'register' 
                  ? 'Already have an account? Sign in' 
                  : "Don't have an account? Sign up"}
              </button>
            </div>
          </form>

          {/* Footer */}
          <div className="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
            <p className="text-xs text-gray-500 dark:text-gray-400">
              Empowering students to achieve their academic goals 🎓
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}