import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Clock, TrendingUp, Flag, BarChart3, FileText, Layout, 
  Users, Award, Trophy, Upload, Zap, Brain, Calendar, 
  Music, Download, Video, Target, Sparkles, ArrowRight,
  CheckCircle2, Star, Flame
} from 'lucide-react';
import Navbar from '../components/Navbar';

export default function Home() {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    setIsVisible(true);
  }, []);

  const mustFeatures = [
    { icon: Clock, title: 'Pomodoro Timer', description: 'Stay focused with customizable study sessions', color: 'from-yellow-400 to-amber-500' },
    { icon: TrendingUp, title: 'Progress Tracking', description: 'Monitor your growth across all subjects', color: 'from-amber-400 to-orange-500' },
    { icon: Flag, title: 'Priority Levels', description: 'Organize tasks by importance and urgency', color: 'from-orange-400 to-red-500' },
    { icon: BarChart3, title: 'Progress Dashboard', description: 'Visualize your study statistics and achievements', color: 'from-yellow-300 to-yellow-500' },
    { icon: FileText, title: 'Notes & Documentation', description: 'Write and organize your study notes', color: 'from-amber-500 to-yellow-600' },
    { icon: Layout, title: 'Simple Dashboard', description: 'Clean interface for all your study needs', color: 'from-yellow-500 to-amber-600' },
  ];

  const shouldFeatures = [
    { icon: Users, title: 'Study with Friends', description: 'Share Pomodoro sessions with study partners' },
    { icon: Flame, title: 'Track Streak', description: 'Build consistent study habits' },
    { icon: Trophy, title: 'Achievements', description: 'Unlock rewards as you progress' },
    { icon: Upload, title: 'Import Notes', description: 'Upload from other apps and images' },
    { icon: Zap, title: 'Competition', description: 'Compete with friends on leaderboards' },
  ];

  const couldFeatures = [
    { icon: Brain, title: 'AI Organization', description: 'Smart assistance for study planning' },
    { icon: Target, title: 'Smart Search', description: 'Find anything in your notes instantly' },
    { icon: Music, title: 'Lofi Music', description: 'Integrated Spotify playlists' },
    { icon: Calendar, title: 'Calendar Integration', description: 'Sync with your schedule' },
    { icon: Download, title: 'Import/Export', description: 'Backup and restore your data' },
    { icon: Video, title: 'Video Calls', description: 'Study together with FaceTime' },
  ];

  return (
    <div className="min-h-screen">
      <Navbar />

      {/* Hero Section */}
      <section className="relative pt-24 pb-20 px-4 overflow-hidden">
        {/* Animated Background */}
        <div className="absolute inset-0 -z-10 bg-linear-to-br from-amber-50 via-yellow-50 to-orange-50">
          <div className="absolute top-20 left-10 w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
          <div className="absolute top-40 right-10 w-96 h-96 bg-amber-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
          <div className="absolute -bottom-8 left-1/3 w-96 h-96 bg-orange-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
        </div>

        <div className="max-w-7xl mx-auto">
          <div className={`text-center transition-all duration-1000 ${isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>
            <div className="inline-flex items-center px-4 py-2 bg-yellow-100 rounded-full mb-6">
              <Sparkles className="w-4 h-4 text-yellow-600 mr-2" />
              <span className="text-sm font-medium text-yellow-700">Your Personal Learning Companion</span>
            </div>
            
            <h1 className="text-5xl md:text-7xl font-bold mb-6">
              <span className="bg-linear-to-r from-yellow-600 via-amber-600 to-orange-600 bg-clip-text text-transparent">
                Study Smarter,
              </span>
              <br />
              <span className="text-gray-900">Not Harder</span>
            </h1>
            
            <p className="text-xl text-gray-600 mb-12 max-w-3xl mx-auto">
              Transform your study routine with intelligent time management, progress tracking, 
              and collaborative features designed for modern students.
            </p>

            <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
              <Link
                to="/login"
                className="group px-8 py-4 bg-linear-to-r from-yellow-400 via-amber-500 to-orange-500 text-white font-semibold rounded-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 flex items-center"
              >
                Get Started Free
                <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
              </Link>
              <a
                href="#features"
                className="px-8 py-4 bg-white border-2 border-yellow-300 text-gray-700 font-semibold rounded-xl hover:border-yellow-500 hover:text-yellow-600 transition-all duration-300"
              >
                Explore Features
              </a>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 mt-20 max-w-4xl mx-auto">
              <div className="text-center">
                <div className="text-4xl font-bold bg-linear-to-r from-yellow-600 to-amber-600 bg-clip-text text-transparent">10k+</div>
                <div className="text-sm text-gray-600 mt-1">Active Students</div>
              </div>
              <div className="text-center">
                <div className="text-4xl font-bold bg-linear-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">1M+</div>
                <div className="text-sm text-gray-600 mt-1">Study Sessions</div>
              </div>
              <div className="text-center">
                <div className="text-4xl font-bold bg-linear-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">95%</div>
                <div className="text-sm text-gray-600 mt-1">Success Rate</div>
              </div>
              <div className="text-center">
                <div className="text-4xl font-bold bg-linear-to-r from-yellow-500 to-amber-500 bg-clip-text text-transparent">24/7</div>
                <div className="text-sm text-gray-600 mt-1">Available</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Core Features Section */}
      <section id="features" className="py-20 px-4 bg-white">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <div className="inline-flex items-center px-4 py-2 bg-yellow-100 rounded-full mb-4">
              <Star className="w-4 h-4 text-yellow-600 mr-2" />
              <span className="text-sm font-medium text-yellow-700">Core Features</span>
            </div>
            <h2 className="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
              Everything You Need to <span className="bg-linear-to-r from-yellow-600 to-amber-600 bg-clip-text text-transparent">Excel</span>
            </h2>
            <p className="text-lg text-gray-600 max-w-2xl mx-auto">
              Powerful tools designed to boost your productivity and academic performance
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {mustFeatures.map((feature, index) => {
              const Icon = feature.icon;
              return (
                <div
                  key={index}
                  className="group relative bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100"
                >
                  <div className={`w-14 h-14 bg-linear-to-br ${feature.color} rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg`}>
                    <Icon className="w-7 h-7 text-white" />
                  </div>
                  <h3 className="text-xl font-bold text-gray-900 mb-2">{feature.title}</h3>
                  <p className="text-gray-600">{feature.description}</p>
                  <div className="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    <CheckCircle2 className="w-6 h-6 text-green-500" />
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* Advanced Features Section */}
      <section className="py-20 px-4 bg-linear-to-b from-amber-50 to-white">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <div className="inline-flex items-center px-4 py-2 bg-amber-100 rounded-full mb-4">
              <Zap className="w-4 h-4 text-amber-600 mr-2" />
              <span className="text-sm font-medium text-amber-700">Advanced Features</span>
            </div>
            <h2 className="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
              Take Your Studies to the <span className="bg-linear-to-r from-yellow-600 to-orange-600 bg-clip-text text-transparent">Next Level</span>
            </h2>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            {shouldFeatures.map((feature, index) => {
              const Icon = feature.icon;
              return (
                <div
                  key={index}
                  className="flex items-start space-x-4 p-6 bg-white rounded-xl border border-gray-200 hover:border-yellow-300 hover:shadow-lg transition-all"
                >
                  <div className="shrink-0 w-12 h-12 bg-linear-to-br from-yellow-400 to-amber-500 rounded-lg flex items-center justify-center">
                    <Icon className="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <h3 className="font-semibold text-gray-900 mb-1">{feature.title}</h3>
                    <p className="text-sm text-gray-600">{feature.description}</p>
                  </div>
                </div>
              );
            })}
          </div>

          {/* Future Features */}
          <div className="text-center mb-8">
            <div className="inline-flex items-center px-4 py-2 bg-orange-100 rounded-full mb-4">
              <Sparkles className="w-4 h-4 text-orange-600 mr-2" />
              <span className="text-sm font-medium text-orange-700">Coming Soon</span>
            </div>
            <h3 className="text-3xl font-bold text-gray-900 mb-8">Future Innovations</h3>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {couldFeatures.map((feature, index) => {
              const Icon = feature.icon;
              return (
                <div
                  key={index}
                  className="flex items-start space-x-4 p-6 bg-linear-to-br from-gray-50 to-white rounded-xl border border-gray-200"
                >
                  <div className="shrink-0 w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                    <Icon className="w-6 h-6 text-gray-600" />
                  </div>
                  <div>
                    <h3 className="font-semibold text-gray-900 mb-1">{feature.title}</h3>
                    <p className="text-sm text-gray-600">{feature.description}</p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 px-4 bg-linear-to-br from-yellow-400 via-amber-500 to-orange-500">
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">
            Ready to Transform Your Study Habits?
          </h2>
          <p className="text-xl text-yellow-50 mb-10">
            Join thousands of students achieving their academic goals with StudyBuddy
          </p>
          <Link
            to="/login"
            className="inline-flex items-center px-8 py-4 bg-white text-amber-600 font-semibold rounded-xl hover:shadow-2xl hover:scale-105 transition-all duration-300"
          >
            Start Your Journey Today
            <ArrowRight className="ml-2 w-5 h-5" />
          </Link>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-12 px-4 bg-gray-900 text-white">
        <div className="max-w-7xl mx-auto text-center">
          <div className="flex items-center justify-center space-x-2 mb-4">
            <div className="w-10 h-10 bg-linear-to-br from-yellow-400 to-amber-500 rounded-xl flex items-center justify-center">
              <svg className="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 14l9-5-9-5-9 5 9 5z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
              </svg>
            </div>
            <span className="text-2xl font-bold">StudyBuddy</span>
          </div>
          <p className="text-gray-400 mb-6">Empowering students to achieve their academic goals</p>
          <div className="text-sm text-gray-500">
            © 2026 StudyBuddy. Made with ❤️ for students worldwide.
          </div>
        </div>
      </footer>
    </div>
  );
}