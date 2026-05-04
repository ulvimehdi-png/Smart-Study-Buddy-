import React, { useEffect, useState } from "react";
import {
  Award,
  Crown,
  Flame,
  Medal,
  Star,
  Trophy,
  TrendingUp,
  User2,
  Users,
} from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";
import SmallStats from "../components/shared/SmallStats";

const formatDate = (value) => {
  if (!value) return "recently";

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "recently";

  return new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(date);
};

const getInitial = (name) => name?.trim()?.charAt(0)?.toUpperCase() || "?";

const rankStyles = [
  {
    icon: Crown,
    cardClass: "border-amber-300/45 bg-amber-300/10 shadow-amber-300/10 dark:border-amber-400/35 dark:bg-amber-400/10",
    badgeClass: "bg-amber-400 text-amber-950 dark:bg-amber-300",
    nameClass: "text-amber-600 dark:text-amber-300",
    avatarClass: "border-amber-300/50 bg-amber-100 text-amber-600 dark:border-amber-400/40 dark:bg-amber-400/10 dark:text-amber-300",
  },
  {
    icon: Medal,
    cardClass: "border-slate-300/60 bg-slate-100/70 shadow-slate-300/10 dark:border-slate-400/35 dark:bg-slate-400/10",
    badgeClass: "bg-slate-200 text-slate-950 dark:bg-slate-300",
    nameClass: "text-slate-600 dark:text-slate-200",
    avatarClass: "border-slate-300 bg-slate-100 text-slate-600 dark:border-slate-300/30 dark:bg-slate-300/10 dark:text-slate-200",
  },
  {
    icon: Trophy,
    cardClass: "border-orange-400/40 bg-orange-400/10 shadow-orange-400/10 dark:border-orange-500/35 dark:bg-orange-500/10",
    badgeClass: "bg-orange-500 text-orange-950 dark:bg-orange-500",
    nameClass: "text-orange-700 dark:text-orange-300",
    avatarClass: "border-orange-300/60 bg-orange-100 text-orange-600 dark:border-orange-500/35 dark:bg-orange-500/10 dark:text-orange-300",
  },
];

function Avatar({ user, size = "md", className = "" }) {
  const sizeClasses = {
    sm: "h-10 w-10 text-sm",
    md: "h-12 w-12 text-base",
    lg: "h-16 w-16 text-xl",
  };

  if (user?.profileImagePath) {
    return (
      <img
        src={user.profileImagePath}
        alt={`${user.username}'s profile`}
        className={`${sizeClasses[size]} shrink-0 rounded-full border border-border-base object-cover shadow-sm ${className}`}
      />
    );
  }

  return (
    <div
      className={`${sizeClasses[size]} shrink-0 flex items-center justify-center rounded-full border font-semibold shadow-sm ${className}`}
    >
      {user?.username ? getInitial(user.username) : <User2 className="h-5 w-5" />}
    </div>
  );
}

function PodiumCard({ user, index }) {
  const style = rankStyles[index] ?? rankStyles[0];
  const Icon = style.icon;

  return (
    <article className={`rounded-3xl border p-5 shadow-sm ${style.cardClass}`}>
      <div className="flex items-start justify-between gap-4">
        <div
          className={`inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold ${style.badgeClass}`}
        >
          <Icon className="h-4 w-4" />
          #{index + 1}
        </div>
        <div className="rounded-full bg-bg-card px-3 py-1 text-sm font-semibold text-text-main">
          {user.points} pts
        </div>
      </div>

      <div className="mt-5 flex items-center gap-4">
        <Avatar user={user} size="lg" className={style.avatarClass} />
        <div className="min-w-0">
          <h3 className={`truncate text-lg font-semibold ${style.nameClass}`}>
            {user.username}
          </h3>
        </div>
      </div>
    </article>
  );
}

function LeaderboardRow({ user, index }) {
  const topThree = index < 3;
  const style = rankStyles[index];
  console.log(index, user);
  
  return (
    <article
      className={`rounded-2xl border p-4 transition hover:-translate-y-1 hover:shadow-sm ${
        topThree ? style.cardClass : "border-border-base bg-bg-base"
      }`}
    >
      <div className="flex items-center justify-between gap-4">
        <div className="flex min-w-0 items-center gap-3">
          <div
            className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold ${
              topThree
                ? style.badgeClass
                : "border border-border-base bg-bg-elevated text-text-secondary"
            }`}
          >
            {index + 1}
          </div>

          <Avatar
            user={user}
            size="sm"
            className={
              topThree
                ? style.avatarClass
                : "border-border-base bg-bg-elevated text-text-secondary"
            }
          />

          <div className="min-w-0">
            <p className={`truncate font-semibold ${topThree ? style.nameClass : "text-text-main"}`}>
              {user.username}
            </p>
          </div>
        </div>

        <div className="rounded-full bg-bg-card px-3 py-1 text-sm font-semibold text-text-main">
          {user.points} pts
        </div>
      </div>
    </article>
  );
}

export default function Leaderboard() {
  const [leaderboard, setLeaderboard] = useState([]);
  const [myPoints, setMyPoints] = useState(0);
  const [myBadges, setMyBadges] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let mounted = true;

    Promise.all([
      fetch("/api/achievements/leaderboard", { credentials: "include" }).then(
        (res) => {
          if (!res.ok) throw new Error("Could not load leaderboard.");
          return res.json();
        },
      ),
      fetch("/api/achievements/mine", { credentials: "include" }).then((res) => {
        if (!res.ok) throw new Error("Could not load your achievements.");
        return res.json();
      }),
    ])
      .then(([leaderboardData, mineData]) => {
        if (!mounted) return;

        setLeaderboard(Array.isArray(leaderboardData) ? leaderboardData : []);
        setMyPoints(mineData?.points ?? 0);
        setMyBadges(Array.isArray(mineData?.badges) ? mineData.badges : []);
      })
      .catch((err) => {
        console.error(err);
        if (mounted) setError(err.message || "Something went wrong.");
      })
      .finally(() => {
        if (mounted) setLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, []);

  const topThree = leaderboard.slice(0, 3);
  const topScore = leaderboard[0]?.points ?? 0;

  const stats = [
    {
      label: "My Points",
      value: myPoints,
      icon: <TrendingUp className="h-5 w-5" />,
      iconClass: "bg-primary/10 text-primary",
    },
    {
      label: "Badges",
      value: myBadges.length,
      icon: <Award className="h-5 w-5" />,
      iconClass: "bg-accent-light/10 text-accent-light",
    },
    {
      label: "Top Score",
      value: topScore,
      icon: <Users className="h-5 w-5" />,
      iconClass: "bg-accent/10 text-accent",
    },
  ];

  return (
    <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
      <PageBackdrop />

      <div className="relative mx-auto max-w-6xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <Trophy className="h-5 w-5" />
              </div>
              <div>
                <h1 className="text-2xl font-semibold sm:text-3xl">Leaderboard</h1>
                <p className="mt-1 text-sm text-text-secondary">
                  See how your points and achievements compare.
                </p>
              </div>
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3 lg:min-w-90">
            {stats.map(({ label, value, icon, iconClass }) => (
              <SmallStats
                key={label}
                label={label}
                value={value}
                icon={icon}
                iconClass={iconClass}
              />
            ))}
          </div>
        </div>

        {error && (
          <div className="mb-6 rounded-2xl border border-danger/20 bg-danger/10 px-4 py-3 text-sm text-danger">
            {error}
          </div>
        )}

        {loading ? (
          <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div className="space-y-6">
              <div className="h-64 animate-pulse rounded-3xl border border-border-base bg-bg-card" />
              <div className="h-80 animate-pulse rounded-3xl border border-border-base bg-bg-card" />
            </div>
            <div className="h-96 animate-pulse rounded-3xl border border-border-base bg-bg-card" />
          </div>
        ) : (
          <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <main className="space-y-6">
              <section className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm">
                <div className="flex items-start gap-4">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <Crown className="h-5 w-5" />
                  </div>
                  <div>
                    <h2 className="text-lg font-semibold">Top Students</h2>
                    <p className="mt-1 text-sm text-text-secondary">
                      The highest point earners right now.
                    </p>
                  </div>
                </div>

                {topThree.length === 0 ? (
                  <div className="mt-6 rounded-2xl border border-dashed border-border-base bg-bg-base px-6 py-12 text-center">
                    <Trophy className="mx-auto h-8 w-8 text-text-muted" />
                    <h3 className="mt-4 text-lg font-semibold">No rankings yet</h3>
                    <p className="mt-2 text-text-secondary">
                      Complete a task to become the first person on the board.
                    </p>
                  </div>
                ) : (
                  <div className="mt-6 grid gap-4 md:grid-cols-3">
                    {topThree.map((user, index) => (
                      <PodiumCard
                        key={user.id ?? user.username ?? index}
                        user={user}
                        index={index}
                      />
                    ))}
                  </div>
                )}
              </section>

              <section className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div className="flex items-start gap-4">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                      <Medal className="h-5 w-5" />
                    </div>
                    <div>
                      <h2 className="text-lg font-semibold">Full Leaderboard</h2>
                      <p className="mt-1 text-sm text-text-secondary">
                        Top 10 StudyBuddy users by points.
                      </p>
                    </div>
                  </div>

                  <div className="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">
                    {leaderboard.length} ranked
                  </div>
                </div>

                {leaderboard.length === 0 ? (
                  <div className="mt-6 rounded-2xl border border-dashed border-border-base bg-bg-base px-6 py-12 text-center">
                    <Users className="mx-auto h-8 w-8 text-text-muted" />
                    <h3 className="mt-4 text-lg font-semibold">No players yet</h3>
                    <p className="mt-2 text-text-secondary">
                      Once points are earned, the full ranking will appear here.
                    </p>
                  </div>
                ) : (
                  <div className="mt-6 space-y-4">
                    {leaderboard.map((user, index) => (
                      <LeaderboardRow
                        key={user.id ?? user.username ?? index}
                        user={user}
                        index={index}
                      />
                    ))}
                  </div>
                )}
              </section>
            </main>

            <aside>
              <section className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm">
                <div className="flex items-start gap-4">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <Star className="h-5 w-5" />
                  </div>
                  <div>
                    <h2 className="text-lg font-semibold">My Badges</h2>
                    <p className="mt-1 text-sm text-text-secondary">
                      Your unlocked achievements.
                    </p>
                  </div>
                </div>

                {myBadges.length === 0 ? (
                  <div className="mt-6 rounded-2xl border border-dashed border-border-base bg-bg-base px-6 py-12 text-center">
                    <Star className="mx-auto h-8 w-8 text-text-muted" />
                    <h3 className="mt-4 text-lg font-semibold">No badges yet</h3>
                    <p className="mt-2 text-text-secondary">
                      Complete tasks and study sessions to unlock achievements.
                    </p>
                  </div>
                ) : (
                  <div className="mt-6 space-y-4">
                    {myBadges.map((badge, index) => (
                      <article
                        key={badge.id ?? badge.name ?? index}
                        className="rounded-2xl border border-border-base bg-bg-base p-4"
                      >
                        <div className="flex items-start gap-4">
                          <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <Flame className="h-6 w-6" />
                          </div>

                          <div className="min-w-0">
                            <h3 className="font-semibold text-text-main">{badge.name}</h3>
                            <p className="mt-1 text-sm leading-6 text-text-secondary">
                              {badge.description}
                            </p>
                            <p className="mt-3 text-xs font-semibold text-primary">
                              Unlocked {formatDate(badge.earnedAt)}
                            </p>
                          </div>
                        </div>
                      </article>
                    ))}
                  </div>
                )}
              </section>
            </aside>
          </div>
        )}
      </div>
    </div>
  );
}
