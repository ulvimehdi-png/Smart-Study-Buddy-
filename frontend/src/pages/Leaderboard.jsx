import React, { useEffect, useState } from "react";
import { Trophy, Medal, Star, Flame, Award } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";

const formatDate = (value) =>
  new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(
    new Date(value),
  );

export default function Leaderboard() {
  const [leaderboard, setLeaderboard] = useState([]);
  const [myPoints, setMyPoints] = useState(0);
  const [myBadges, setMyBadges] = useState([]);

  useEffect(() => {
    fetch("/api/achievements/leaderboard", { credentials: "include" })
      .then((res) => res.json())
      .then((data) => setLeaderboard(data))
      .catch((err) => console.error(err));

    fetch("/api/achievements/mine", { credentials: "include" })
      .then((res) => res.json())
      .then((data) => {
        setMyPoints(data.points);
        setMyBadges(data.badges);
      })
      .catch((err) => console.error(err));
  }, []);

  return (
    <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
      <PageBackdrop />

      <div className="relative mx-auto max-w-7xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
              <Trophy className="h-5 w-5" />
            </div>
            <div>
              <h1 className="text-2xl font-semibold sm:text-3xl">Achievements</h1>
              <p className="mt-1 text-sm text-text-secondary">
                Track your progress and see how you rank.
              </p>
            </div>
          </div>
          
          <div className="rounded-2xl border border-border-base bg-bg-card p-4 shadow-sm min-w-[200px]">
            <p className="text-sm font-medium text-text-secondary">My Total Points</p>
            <div className="mt-2 flex items-end gap-3">
              <span className="text-3xl font-semibold text-accent">{myPoints}</span>
              <span className="mb-1 text-sm text-text-secondary">pts</span>
            </div>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-[1fr_350px]">
          <section className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm">
            <div className="flex items-center gap-3 mb-6">
              <Award className="h-6 w-6 text-primary" />
              <h2 className="text-xl font-semibold">My Badges</h2>
            </div>

            {myBadges.length === 0 ? (
              <div className="rounded-2xl border border-dashed border-border-base bg-bg-base px-6 py-12 text-center">
                <Star className="mx-auto h-8 w-8 text-text-muted" />
                <h3 className="mt-4 text-lg font-semibold">No badges yet</h3>
                <p className="mt-2 text-text-secondary">
                  Complete tasks and study sessions to earn points and unlock badges.
                </p>
              </div>
            ) : (
              <div className="grid gap-4 sm:grid-cols-2">
                {myBadges.map((badge, index) => (
                  <div key={index} className="flex items-start gap-4 rounded-2xl border border-border-base bg-bg-base p-4 transition hover:border-primary/30">
                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                      <Flame className="h-6 w-6" />
                    </div>
                    <div>
                      <h3 className="font-semibold text-text-main">{badge.name}</h3>
                      <p className="mt-1 text-sm text-text-secondary">{badge.description}</p>
                      <p className="mt-2 text-xs font-medium text-primary">
                        Unlocked on {formatDate(badge.earnedAt)}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </section>

          <section className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm self-start">
            <div className="flex items-center gap-3 mb-6">
              <Medal className="h-6 w-6 text-accent" />
              <h2 className="text-xl font-semibold">Top 10 Leaderboard</h2>
            </div>

            <div className="space-y-3">
              {leaderboard.map((user, index) => (
                <div 
                  key={index} 
                  className={`flex items-center justify-between rounded-xl p-3 ${index === 0 ? 'bg-accent/10 border border-accent/20' : 'bg-bg-base border border-border-base'}`}
                >
                  <div className="flex items-center gap-3">
                    <span className={`flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold ${index === 0 ? 'bg-accent text-white' : index < 3 ? 'bg-text-secondary/20 text-text-main' : 'text-text-muted'}`}>
                      {index + 1}
                    </span>
                    <span className={`font-medium ${index === 0 ? 'text-accent' : 'text-text-main'}`}>
                      {user.username}
                    </span>
                  </div>
                  <span className="font-semibold text-text-secondary">
                    {user.points} pts
                  </span>
                </div>
              ))}
              
              {leaderboard.length === 0 && (
                <p className="text-center text-sm text-text-secondary py-4">
                  No one is on the leaderboard yet!
                </p>
              )}
            </div>
          </section>
        </div>
      </div>
    </div>
  );
}