import React from 'react';
import { Outlet } from 'react-router-dom';
import Sidebar from './Sidebar';
import Footer from './Footer';
import { useTimer } from '../lib/useTimer';
import { cn } from '@/lib/utils';

export default function Layout() {
  const { isPlaying } = useTimer();

  return (
    <div className="flex min-h-screen">
      <Sidebar />
      <div className={cn(
        "flex-1 flex flex-col min-w-0 transition-all duration-300",
        !isPlaying ? "md:ml-64" : "ml-0",
        "pt-16 md:pt-0"
      )}>
        <main className="flex-1 w-full flex flex-col relative">
          <Outlet />
        </main>
        <Footer />
      </div>
    </div>
  );
}