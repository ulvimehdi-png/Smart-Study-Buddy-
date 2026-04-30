import React from 'react';
import {
    Clock, TrendingUp, Flag, BarChart3, FileText, Layout,
    Users, Trophy, Upload, Zap, Brain, Calendar,
    Music, Download, Video, Target, Sparkles, ArrowRight,
    Star, Flame
} from 'lucide-react';
import { Badge, AnimatedBlobs, FeatureCard, GradientButton, Logo } from '../components/shared';

export default function Home() {
  const mustFeatures = [
    { icon: Clock, title: "Pomodoro Timer", description: "Stay focused with customizable study sessions", color: "bg-primary" },
    { icon: TrendingUp, title: "Progress Tracking", description: "Monitor your growth across all subjects", color: "bg-primary-dark" },
    { icon: Flag, title: "Priority Levels", description: "Organize tasks by importance and urgency", color: "bg-accent" },
    { icon: BarChart3, title: "Progress Dashboard", description: "Visualize your study statistics and achievements", color: "bg-accent-light" },
    { icon: FileText, title: "Notes & Documentation", description: "Write and organize your study notes", color: "bg-secondary" },
    { icon: Layout, title: "Simple Dashboard", description: "Clean interface for all your study needs", color: "bg-secondary-dark" },
  ];

  const shouldFeatures = [
    { icon: Users, title: "Study with Friends", description: "Share Pomodoro sessions with study partners" },
    { icon: Flame, title: "Track Streak", description: "Build consistent study habits" },
    { icon: Trophy, title: "Achievements", description: "Unlock rewards as you progress" },
    { icon: Upload, title: "Import Notes", description: "Upload from other apps and images" },
    { icon: Zap, title: "Competition", description: "Compete with friends on leaderboards" },
  ];

  const couldFeatures = [
    { icon: Brain, title: "AI Organization", description: "Smart assistance for study planning" },
    { icon: Target, title: "Smart Search", description: "Find anything in your notes instantly" },
    { icon: Music, title: "Lofi Music", description: "Integrated Spotify playlists" },
    { icon: Calendar, title: "Calendar Integration", description: "Sync with your schedule" },
    { icon: Download, title: "Import/Export", description: "Backup and restore your data" },
    { icon: Video, title: "Video Calls", description: "Study together with FaceTime" },
  ];

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-20 pb-20 px-4 min-h-dvh flex items-center">
        <AnimatedBlobs />
        <div className="max-w-7xl mx-auto">
          <div className="text-center">
            <Badge icon={Sparkles} className="mb-6">Your Personal Learning Companion</Badge>
            <h1 className="text-5xl md:text-7xl font-bold mb-6">
              <span className="text-gradient-primary">Study Smarter,</span><br />
              <span className="text-gradient-accent">Not Harder</span>
            </h1>
            <p className="text-xl text-text-secondary mb-12 max-w-3xl mx-auto">
              Transform your study routine with intelligent time management,
              progress tracking, and collaborative features designed for modern students.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
              <GradientButton to="/login" icon={ArrowRight} size="lg">Get Started Free</GradientButton>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 mt-20 max-w-4xl mx-auto">
              {[{ val: "10k+", label: "Active Students" }, { val: "1M+", label: "Study Sessions" }, { val: "95%", label: "Success Rate" }, { val: "24/7", label: "Available" }].map(s => (
                <div key={s.label} className="text-center">
                  <div className="text-4xl font-bold text-primary" style={{ textShadow: '0 0 30px rgba(255,159,10,0.3)' }}>{s.val}</div>
                  <div className="text-sm text-text-secondary mt-1">{s.label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Core Features */}
      <section id="features" className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <Badge icon={Star} className="mb-4">Core Features</Badge>
            <h2 className="text-4xl md:text-5xl font-bold mb-4">Everything You Need to <span className="text-gradient-accent">Excel</span></h2>
            <p className="text-lg text-text-secondary max-w-2xl mx-auto">Powerful tools designed to boost your productivity and academic performance</p>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {mustFeatures.map((f, i) => <FeatureCard key={i} icon={f.icon} title={f.title} description={f.description} color={f.color} />)}
          </div>
        </div>
      </section>

      {/* Advanced Features */}
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <Badge icon={Zap} className="mb-4">Advanced Features</Badge>
            <h2 className="text-4xl md:text-5xl font-bold mb-4">Take Your Studies to the <span className="text-gradient-secondary">Next Level</span></h2>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            {shouldFeatures.map((f, i) => {
              const Icon = f.icon;
              return (
                <div key={i} className="flex items-start space-x-4 p-6 glass-card rounded-xl hover:border-primary/20 hover:scale-[1.02] transition-all duration-300">
                  <div className="shrink-0 w-12 h-12 bg-gradient-primary rounded-xl flex items-center justify-center"><Icon className="w-6 h-6 text-white" /></div>
                  <div><h3 className="font-semibold mb-1">{f.title}</h3><p className="text-sm text-text-secondary">{f.description}</p></div>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* Future Features */}
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-8">
            <Badge icon={Sparkles} variant="accent" className="mb-4">Coming Soon</Badge>
            <h3 className="text-4xl md:text-5xl font-bold mb-8">Future Innovations</h3>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {couldFeatures.map((f, i) => {
              const Icon = f.icon;
              return (
                <div key={i} className="flex items-start space-x-4 p-6 glass-card rounded-xl opacity-70 hover:opacity-100 transition-all duration-300">
                  <div className="shrink-0 w-12 h-12 bg-white/[0.06] rounded-xl flex items-center justify-center"><Icon className="w-6 h-6 text-text-secondary" /></div>
                  <div><h3 className="font-semibold mb-1">{f.title}</h3><p className="text-sm text-text-secondary">{f.description}</p></div>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="relative px-4 overflow-hidden py-40 flex items-center">
        <AnimatedBlobs />
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-5xl md:text-7xl font-extrabold mb-8">Ready to Transform Your Study Habits?</h2>
          <p className="text-xl text-text-secondary max-w-2xl mx-auto mb-12">Join thousands of students achieving their academic goals with StudyBuddy</p>
          <GradientButton to="/login" variant="secondary" size="lg" icon={ArrowRight}>Start Your Journey Today</GradientButton>
        </div>
      </section>
    </div>
  );
}
