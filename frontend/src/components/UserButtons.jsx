import React from 'react';
import { Link } from 'react-router-dom';
import { User, LogOut } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { GradientButton } from './shared';
import { cn } from '@/lib/utils';
import { motion, AnimatePresence } from 'motion/react';

const MotionLink = motion.create(Link);

export default function UserButtons({ isCollapsed }) {
  const auth = useAuth();

  const handleLogout = () => {
    auth.logout();
  };

  return (
    <motion.div layout className={cn(isCollapsed ? "flex flex-col gap-3 items-center w-full" : "grid grid-cols-2 gap-3")}>
      {auth.user ? (
        <>
          {/* User Badge */}
          <MotionLink
            layout
            to="/user"
            className={cn(
              "flex items-center justify-center rounded-lg transition-colors overflow-hidden",
              isCollapsed ? "p-3 w-full" : "space-x-2 px-4 py-2",
              "bg-primary/10 hover:bg-primary/20",
              "text-primary border border-primary/20 hover:border-primary/30",
              "cursor-pointer"
            )}
          >
            {auth.user.profileImageUrl ? (
              <motion.img
                layout
                src={auth.user.profileImageUrl}
                alt="Profile"
                className="h-5 w-5 rounded-full object-cover shrink-0"
              />
            ) : (
              <motion.div layout className="shrink-0 flex items-center justify-center"><User className="w-4 h-4 text-primary" /></motion.div>
            )}
            <AnimatePresence>
              {!isCollapsed && (
                <motion.span 
                  layout
                  initial={{ opacity: 0, width: 0, x: -10 }}
                  animate={{ opacity: 1, width: "auto", x: 0 }}
                  exit={{ opacity: 0, width: 0, x: -10 }}
                  className="text-sm font-medium text-primary truncate origin-left ml-1"
                >
                  {auth.user.username}
                </motion.span>
              )}
            </AnimatePresence>
          </MotionLink>

          {/* Logout Button - Using Semantic Red (or custom vars) */}
          <motion.button
            layout
            onClick={handleLogout}
            className={cn(
              "flex items-center justify-center rounded-lg transition-colors overflow-hidden",
              isCollapsed ? "p-3 w-full" : "space-x-2 px-4 py-2",
              "bg-danger/10 hover:bg-danger/20",
              "text-danger border border-danger/20 hover:border-danger/30",
              "cursor-pointer"
            )}
          >
            <motion.div layout className="shrink-0 flex items-center justify-center"><LogOut className="w-4 h-4" /></motion.div>
            <AnimatePresence>
              {!isCollapsed && (
                <motion.span 
                  layout
                  initial={{ opacity: 0, width: 0, x: -10 }}
                  animate={{ opacity: 1, width: "auto", x: 0 }}
                  exit={{ opacity: 0, width: 0, x: -10 }}
                  className="text-sm font-medium origin-left whitespace-nowrap ml-1"
                >
                  Logout
                </motion.span>
              )}
            </AnimatePresence>
          </motion.button>
        </>
      ) : (
        <div className="w-full text-center">
          {/* Sign In Link */}
          {isCollapsed ? (
            <GradientButton to="/login" className="w-full flex justify-center p-2">
              <User className="w-4 h-4" />
            </GradientButton>
          ) : (
            <GradientButton to="/login" size="sm" className="w-full">
              Sign In
            </GradientButton>
          )}
        </div>
      )}
    </motion.div>
  );
}
