import React, { useState } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import Sidebar from './Sidebar';
import Footer from './Footer';
import { useTimer } from '../lib/useTimer';
import { useAuth } from '@/lib/auth';

import { cn } from '@/lib/utils';
import { motion as Motion } from 'motion/react';
import TimerPip from './TimerPip';

export default function Layout() {
  const { isPlaying } = useTimer();
  const { user } = useAuth();
  const location = useLocation();
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  return (
    <div className="flex min-h-screen">
      <Sidebar isSidebarOpen={isSidebarOpen} setIsSidebarOpen={setIsSidebarOpen} />
      <Motion.div
        layout
        transition={{ type: "spring", stiffness: 300, damping: 30 }}
        className={cn(
          "flex-1 flex flex-col min-w-0",
          !(isPlaying && location.pathname.startsWith('/timer')) ? (isSidebarOpen ? "lg:ml-64" : "lg:ml-20") : "ml-0"
        )}
      >
        <main className="flex-1 w-full flex flex-col relative">
          <Outlet />
        </main>
        <Footer />
      </Motion.div>
      {user && (
        <div className="fixed bottom-6 right-6 z-40">
          <TimerPip className="shadow-lg" />
        </div>
      )}
    </div>
  );
}
