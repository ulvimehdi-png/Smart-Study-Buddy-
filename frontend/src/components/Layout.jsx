import React, { useState } from 'react';
import { Outlet } from 'react-router-dom';
import Sidebar from './Sidebar';
import Footer from './Footer';
import { useTimer } from '../lib/useTimer';
import { cn } from '@/lib/utils';
import { motion as Motion } from 'motion/react';

export default function Layout() {
  const { isPlaying } = useTimer();
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  return (
    <div className="flex min-h-screen">
      <Sidebar isSidebarOpen={isSidebarOpen} setIsSidebarOpen={setIsSidebarOpen} />
      <Motion.div
        layout
        transition={{ type: "spring", stiffness: 300, damping: 30 }}
        className={cn(
          "flex-1 flex flex-col min-w-0",
          !isPlaying ? (isSidebarOpen ? "lg:ml-64" : "lg:ml-20") : "ml-0"
        )}
      >
        <main className="flex-1 w-full flex flex-col relative">
          <Outlet />
        </main>
        <Footer />
      </Motion.div>
    </div>
  );
}