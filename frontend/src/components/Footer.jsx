import React from 'react';
import { Link } from 'react-router-dom';
import { GradientButton, Logo } from './shared';

const LINKS = [
  { name: 'Home', path: '/' },
  { name: 'Get Started', path: '/login' },
  { name: 'Dashboard', path: '/dashboard' },
  { name: 'Tasks', path: '/tasks' },
  { name: 'Notes', path: '/notes' },
  { name: 'Timer', path: '/timer' },
];

export default function Footer() {
  
  return (
    <footer className="bg-bg-base text-text-main">
      <div className="max-w-7xl mx-auto px-4 py-14">
        <div className="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
          <div className="lg:col-span-2">
            <Logo size="lg" showIcon={false} className="mb-4" textClassName='text-text-main'/>
            <p className="text-text-secondary max-w-md leading-relaxed">
              Built for students who want clear goals, focused sessions, and measurable progress without friction.
            </p>
          </div>

          <div>
            <h4 className="text-sm font-semibold uppercase tracking-widest text-text-secondary mb-4">Product</h4>
            <ul className="space-y-3 text-text-secondary columns-3">
              { LINKS.map(link => (
                <li key={link.name}>
                  <Link to={link.path} className="hover:text-primary transition-colors">{link.name}</Link>
                </li>
              )) }
            </ul>
          </div>
        </div>

        <div className="mt-12 pt-6 border-t border-border-base text-center text-sm text-text-muted">
          <p>© 2026 StudyBuddy. Made with ❤️ for students worldwide.</p>
        </div>
      </div>
    </footer>
  );
}