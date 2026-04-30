import React, { useState } from 'react';
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from '../lib/auth';
import { BookOpen, Brain, Sparkles } from 'lucide-react';
import { AnimatedBlobs, GradientButton, Logo } from '../components/shared';

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
    currentView === 'register' ? handleRegister() : handleLogin();
  };

  const handleRegister = async () => {
    try { await auth.register(username, password); setMessage('Registration successful! Please log in.'); setCurrentView('login'); }
    catch { setMessage('Error: Username might be taken.'); }
  };

  const handleLogin = async () => {
    try { await auth.login(username, password); setMessage(''); navigate(from, { replace: true }); }
    catch { setMessage('Invalid username or password.'); }
  };

  return (
    <div className="relative min-h-screen w-full flex items-center justify-center p-4 pt-20">
      <AnimatedBlobs />
      <div className="w-full max-w-md glass-card rounded-3xl p-8 page-enter" style={{ boxShadow: '0 0 60px rgba(255,159,10,0.05), 0 0 120px rgba(10,132,255,0.03)' }}>
        {/* Logo & Header */}
        <div className="text-center mb-8">
          <div className="flex justify-center mb-4"><Logo size="lg" showText={false} /></div>
          <h1 className="text-4xl font-bold text-gradient-primary mb-2">StudyBuddy</h1>
          <p className="text-text-secondary text-sm">Your personal learning companion</p>
        </div>

        {/* Feature Icons */}
        <div className="grid grid-cols-3 gap-3 mb-6">
          <div className="flex flex-col items-center p-3 rounded-xl bg-primary/[0.08] border border-primary/[0.12]">
            <BookOpen className="w-5 h-5 text-primary mb-1" /><span className="text-xs text-primary font-medium">Study</span>
          </div>
          <div className="flex flex-col items-center p-3 rounded-xl bg-secondary/[0.08] border border-secondary/[0.12]">
            <Brain className="w-5 h-5 text-secondary mb-1" /><span className="text-xs text-secondary font-medium">Focus</span>
          </div>
          <div className="flex flex-col items-center p-3 rounded-xl bg-accent/[0.08] border border-accent/[0.12]">
            <Sparkles className="w-5 h-5 text-accent mb-1" /><span className="text-xs text-accent font-medium">Excel</span>
          </div>
        </div>

        {/* Messages */}
        {(message || auth.error) && (
          <div className={`mb-4 p-3 rounded-xl text-sm ${
            message.includes('successful')
              ? 'bg-green-500/10 text-green-400 border border-green-500/20'
              : 'bg-red-500/10 text-red-400 border border-red-500/20'
          }`}>{message || auth.error}</div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="text-center mb-6">
            <h2 className="text-2xl font-bold">{currentView === 'register' ? 'Create Account' : 'Welcome Back'}</h2>
            <p className="text-sm text-text-secondary mt-1">{currentView === 'register' ? 'Join thousands of successful students' : 'Continue your learning journey'}</p>
          </div>
          <input type="text" placeholder="Username" value={username} onChange={e => setUsername(e.target.value)} required
            className="w-full glass-input px-4 py-3 rounded-xl text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
          <input type="password" placeholder="Password" value={password} onChange={e => setPassword(e.target.value)} required
            className="w-full glass-input px-4 py-3 rounded-xl text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
          <GradientButton type="submit" disabled={auth.loading}
            className="w-full rounded-xl hover:shadow-lg hover:shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
            {auth.loading ? (
              <span className="flex items-center justify-center">
                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>Please wait...
              </span>
            ) : currentView === 'register' ? 'Create Account' : 'Sign In'}
          </GradientButton>
          <div className="text-center mt-6">
            <button type="button" onClick={() => { setMessage(''); setCurrentView(currentView === 'register' ? 'login' : 'register'); }}
              className="text-text-secondary hover:text-primary cursor-pointer text-sm font-medium transition-all">
              {currentView === 'register' ? 'Already have an account? Sign in' : "Don't have an account? Sign up"}
            </button>
          </div>
        </form>
        <div className="mt-8 pt-6 border-t border-white/[0.06] text-center">
          <p className="text-xs text-text-muted">Empowering students to achieve their academic goals 🎓</p>
        </div>
      </div>
    </div>
  );
}