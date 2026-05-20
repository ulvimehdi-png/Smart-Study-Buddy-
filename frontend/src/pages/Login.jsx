import React, { useState } from 'react';
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from '../lib/auth';
import { BookOpen, Brain, Sparkles } from 'lucide-react';
import { AnimatedBlobs, GradientButton, Logo } from '../components/shared';

export default function Login() {
  const navigate = useNavigate();
  const location = useLocation();
  const auth = useAuth();
  
  const [isRegistering, setIsRegistering] = useState(false);
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  const from = location.state?.from?.pathname || "/timer";

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (isRegistering) {
      try {
        await auth.register(username, password);
        setMessage('Registration successful! Please log in.');
        setIsRegistering(false);
      } catch {
        setMessage('Error: Username might be taken.');
      }
      return;
    }

    try {
      await auth.login(username, password);
      setMessage('');
      navigate(from, { replace: true });
    } catch {
      setMessage('Invalid username or password.');
    }
  };

  const toggleView = () => {
    setMessage('');
    setIsRegistering(!isRegistering);
  };

  return (
    <div className="relative flex min-h-screen w-full items-center justify-center p-4 pt-20">
      <AnimatedBlobs />

      <div className="w-md rounded-3xl border bg-bg-card p-8 shadow-2xl">
        <div className="mb-8 text-center">
          <div className="mb-4 flex justify-center">
            <Logo size="lg" showText={false} />
          </div>
          <h1 className="text-gradient-primary mb-2 text-4xl font-bold">
            StudyBuddy
          </h1>
          <p className="text-sm text-gray-600 dark:text-gray-400">Your personal learning companion</p>
        </div>

        <div className="mb-6 grid grid-cols-3 gap-3">
          <div className="flex flex-col items-center rounded-xl bg-secondary/15 p-3">
            <BookOpen className="mb-1 h-5 w-5 text-secondary-dark dark:text-secondary-light" />
            <span className="text-xs font-medium text-secondary-dark dark:text-secondary-light">Study</span>
          </div>
          <div className="flex flex-col items-center rounded-xl bg-primary/15 p-3">
            <Brain className="mb-1 h-5 w-5 text-primary-dark dark:text-primary-light" />
            <span className="text-xs font-medium text-primary-dark dark:text-primary-light">Focus</span>
          </div>
          <div className="flex flex-col items-center rounded-xl bg-accent/15 p-3">
            <Sparkles className="mb-1 h-5 w-5 text-accent-dark dark:text-accent-light" />
            <span className="text-xs font-medium text-accent-dark dark:text-accent-light">Excel</span>
          </div>
        </div>

        <div aria-live="polite">
          {(message || auth.error) && (
            <div className={`mb-4 rounded-xl border p-3 text-sm ${
              message.includes('successful') 
                ? 'border-green-200 bg-green-50 text-green-700' 
                : 'border-red-200 bg-red-50 text-red-700'
            }`}>
              {message || auth.error}
            </div>
          )}
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="mb-6 text-center">
            <h2 className="text-2xl font-bold">
              {isRegistering ? 'Create Account' : 'Welcome Back'}
            </h2>
            <p className="mt-1 text-sm text-text-secondary">
              {isRegistering ? 'Join thousands of successful students' : 'Continue your learning journey'}
            </p>
          </div>

          <div className="space-y-4">
            <div className="relative">
              <label htmlFor="usernameInput" className="sr-only">Username</label>
              <input 
                id="usernameInput"
                type="text"
                placeholder="Username" 
                value={username} 
                onChange={e => setUsername(e.target.value)} 
                required
                className="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 transition-all duration-200 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500"
              />
            </div>

            <div className="relative">
              <label htmlFor="passwordInput" className="sr-only">Password</label>
              <input 
                id="passwordInput"
                type="password"
                placeholder="Password" 
                value={password} 
                onChange={e => setPassword(e.target.value)} 
                required
                className="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 transition-all duration-200 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-500"
              />
            </div>
          </div>

          <GradientButton 
            type="submit" 
            disabled={auth.loading}
            className="w-full rounded-xl transition-all duration-200 hover:scale-[1.02] hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100"
          >
            {auth.loading ? (
              <span className="flex items-center justify-center">
                <svg className="-ml-1 mr-3 h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Please wait...
              </span>
            ) : (
              isRegistering ? 'Create Account' : 'Sign In'
            )}
          </GradientButton>

          <div className="mt-6 text-center">
            <button
              type="button"
              onClick={toggleView}
              className="cursor-pointer text-sm font-medium text-text-secondary transition-all hover:text-accent"
            >
              {isRegistering ? 'Already have an account? Sign in' : "Don't have an account? Sign up"}
            </button>
          </div>
        </form>

        <div className="mt-8 border-t border-gray-200 pt-6 text-center dark:border-gray-700">
          <p className="text-xs text-gray-500 dark:text-gray-400">
            Empowering students to achieve their academic goals 🎓
          </p>
        </div>
      </div>
    </div>
  );
}