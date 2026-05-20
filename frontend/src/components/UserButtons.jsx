import React from 'react';
import { Link } from 'react-router-dom';
import { User, LogOut } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { GradientButton } from './shared';
import { cn } from '@/lib/utils';

export default function UserButtons() {
  const auth = useAuth();

  const handleLogout = () => {
    auth.logout();
  };

  return (
    <div className="flex items-center gap-3">
      {auth.user ? (
        <>
          <Link
            to="/user"
            className={cn(
              "flex items-center space-x-2 px-4 py-2 rounded-lg transition-all",
              "bg-primary/10 hover:bg-primary/20",
              "text-primary border border-primary/20 hover:border-primary/30",
              "cursor-pointer"
            )}
          >
            {auth.user.profileImageUrl ? (
              <img
                src={auth.user.profileImageUrl}
                alt={`${auth.user.username} profile picture`}
                className="h-5 w-5 rounded-full object-cover"
              />
            ) : (
              <User className="w-4 h-4 text-primary" aria-hidden="true" />
            )}
            <span className="text-sm font-medium text-primary">
              {auth.user.username}
            </span>
          </Link>

          <button
            onClick={handleLogout}
            className={cn(
              "flex items-center space-x-2 px-4 py-2 rounded-lg transition-all",
              "bg-danger/10 hover:bg-danger/20",
              "text-danger border border-danger/20 hover:border-danger/30",
              "cursor-pointer"
            )}
          >
            <LogOut className="w-4 h-4" aria-hidden="true" />
            <span className="text-sm font-medium">Logout</span>
          </button>
        </>
      ) : (
        <>
          <GradientButton to="/login" size="sm">
            Sign In
          </GradientButton>
        </>
      )}
    </div>
  );
}