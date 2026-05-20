import React, { useEffect, useState, useMemo } from "react";
import { BarChart3, Clock, FileText, Flame, Sparkles, Target, CheckSquare } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";
import SubjectBadge from "../components/shared/SubjectBadge";
import { TiptapViewer } from "../components/TiptapEditor";

const formatDate = (value) =>
  new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(
    new Date(value),
  );

export default function Dashboard() {
  const [data, setData] = useState(null);

  useEffect(() => {
    fetch("/api/dashboard", {
      credentials: "include",
    })
      .then((res) => res.json())
      .then(setData)
      .catch((err) => console.error("Error fetching dashboard:", err));
  }, []);

  const calculations = useMemo(() => {
    if (!data) return null;

    const progressItems = data.progress ?? [];
    const recentNotes = data.recentNotes ?? [];
    const pendingTasks = data.pendingTasks ?? [];

    const totalTrackedTasks = progressItems.reduce((sum, item) => sum + item.totalTasks, 0);
    const completedTrackedTasks = progressItems.reduce((sum, item) => sum + item.completedTasks, 0);
    
    const overallProgress = totalTrackedTasks > 0
      ? Math.round((completedTrackedTasks / totalTrackedTasks) * 100)
      : 0;

    const leadingSubject = progressItems.reduce((best, current) => {
      if (!best || current.progressPercent > best.progressPercent) {
        return current;
      }
      return best;
    }, null);

    return {
      progressItems,
      recentNotes,
      pendingTasks,
      totalTrackedTasks,
      completedTrackedTasks,
      overallProgress,
      leadingSubject
    };
  }, [data]);

  if (!data || !calculations) {
    return (
      <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main" aria-busy="true">
        <PageBackdrop />
        <div className="relative mx-auto flex min-h-screen max-w-4xl items-center justify-center px-4 pt-20 sm:px-6">
          <div className="w-full rounded-4xl border border-border-base bg-bg-card p-10 text-center shadow-xl shadow-black/5">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
              <BarChart3 className="h-7 w-7" aria-hidden="true" />
            </div>
            <h1 className="mt-5 text-2xl font-semibold">Loading Dashboard</h1>
            <p className="mt-3 text-text-secondary">
              Pulling together notes, study time, and task progress.
            </p>
          </div>
        </div>
      </div>
    );
  }

  const stats = [
    {
      label: "Total Notes",
      value: data.totalNotes,
      detail: "Saved across your workspace",
      icon: <FileText className="h-5 w-5" aria-hidden="true" />,
      iconClass: "bg-primary-light/10 text-primary-light",
    },
    {
      label: "Total Hours",
      value: data.totalHours,
      detail: "Focus sessions logged",
      icon: <Clock className="h-5 w-5" aria-hidden="true" />,
      iconClass: "bg-primary/10 text-primary",
    },
    {
      label: "Today's Hours",
      value: data.todayHours,
      detail: "Time invested today",
      icon: <Target className="h-5 w-5" aria-hidden="true" />,
      iconClass: "bg-accent-light/10 text-accent-light",
    },
    {
      label: "Study Streak",
      value: `${data.studyStreak} Days`,
      detail: "Consistency is building",
      icon: <Flame className="h-5 w-5" aria-hidden="true" />,
      iconClass: "bg-accent/10 text-accent",
    },
    {
      label: "My Points",
      value: data.points || 0,
      detail: "Academic rewards",
      icon: <Sparkles className="h-5 w-5" aria-hidden="true" />,
      iconClass: "bg-accent/10 text-accent",
    },
  ];

  return (
    <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
      <PageBackdrop />

      <div className="relative mx-auto max-w-7xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <BarChart3 className="h-5 w-5" aria-hidden="true" />
              </div>
              <div>
                <h1 className="text-2xl font-semibold sm:text-3xl">Dashboard</h1>
                <p className="mt-1 text-sm text-text-secondary">
                  Overview of notes, study time, and task progress.
                </p>
              </div>
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-2 lg:min-w-[320px]">
            <div className="rounded-2xl border border-border-base bg-bg-card p-4 shadow-sm">
              <p className="text-sm font-medium text-text-secondary">Overall task completion</p>
              <div className="mt-3 flex items-end justify-between gap-4">
                <span className="text-3xl font-semibold text-text-main">
                  {calculations.overallProgress}%
                </span>
                <span className="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">
                  {calculations.completedTrackedTasks}/{calculations.totalTrackedTasks || 0} done
                </span>
              </div>
            </div>

            <div className="rounded-2xl border border-border-base bg-bg-card p-4 shadow-sm">
              <p className="text-sm font-medium text-text-secondary">Leading subject</p>
              <div className="mt-3 text-lg font-semibold">
                {calculations.leadingSubject?.subject || "No subjects yet"}
              </div>
              <p className="mt-1 text-sm text-text-secondary">
                {calculations.leadingSubject
                  ? `${calculations.leadingSubject.progressPercent}% completion`
                  : "Create tasks to see momentum by subject."}
              </p>
            </div>
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          {stats.map(({ label, value, detail, icon, iconClass }) => (
            <div key={label} className="rounded-2xl border border-border-base bg-bg-card p-4 shadow-sm">
              <div className="flex flex-wrap items-center gap-2">
                <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${iconClass}`}>
                  {icon}
                </div>
                <div>
                  <p className="font-medium">{label}</p>
                  <p className="text-sm text-text-secondary">{detail}</p>
                </div>
              </div>
              <div className="mt-3 text-3xl font-semibold">{value}</div>
            </div>
          ))}
        </div>

        <div className="mt-6 grid gap-6 xl:grid-cols-3">
          <section className="flex flex-col rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm xl:col-span-1">
              <div className="mb-6 flex items-start gap-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-500/10 text-green-500">
                  <CheckSquare className="h-5 w-5" aria-hidden="true" />
                </div>
                <div>
                  <h2 className="text-lg font-semibold">To-Do List</h2>
                  <p className="mt-1 text-sm text-text-secondary">Tasks needing your attention.</p>
                </div>
              </div>

              {calculations.pendingTasks.length > 0 ? (
                <ul className="flex-1 space-y-3">
                  {calculations.pendingTasks.map((task) => (
                    <li key={task.id} className="flex items-start gap-3 rounded-xl border border-border-base bg-bg-base p-3 transition-colors hover:border-primary/30">
                      <div className="mt-1 h-4 w-4 shrink-0 rounded border border-text-muted" />
                      <div className="min-w-0 flex-1">
                         <p className="truncate text-sm font-medium">{task.title}</p>
                         <div className="mt-1 flex flex-wrap items-center gap-2">
                            {task.subject && <SubjectBadge subject={task.subject} />}
                            {task.dueDate && (
                               <span className="text-xs text-text-muted">
                                   Due: {formatDate(task.dueDate)}
                               </span>
                            )}
                         </div>
                      </div>
                    </li>
                  ))}
                </ul>
              ) : (
                <div className="flex flex-1 items-center justify-center rounded-xl border border-dashed border-border-base bg-bg-base p-6 text-center">
                  <div>
                      <CheckSquare className="mx-auto h-8 w-8 text-green-500/50" aria-hidden="true" />
                      <p className="mt-2 text-sm text-text-secondary">All caught up! No pending tasks.</p>
                  </div>
                </div>
              )}
          </section>

          <section className="flex flex-col rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm xl:col-span-1">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div className="flex items-start gap-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                  <BarChart3 className="h-5 w-5" aria-hidden="true" />
                </div>
                <div>
                  <h2 className="text-lg font-semibold">Task Progress</h2>
                  <p className="mt-1 text-sm text-text-secondary">Progress by subject.</p>
                </div>
              </div>

              {calculations.totalTrackedTasks > 0 && (
                <div className="whitespace-nowrap rounded-full bg-accent/10 px-3 py-1 text-sm font-semibold text-accent">
                  {calculations.progressItems.length} track{calculations.progressItems.length === 1 ? "" : "s"}
                </div>
              )}
            </div>

            {calculations.progressItems.length > 0 ? (
              <div className="flex-1 space-y-4">
                {calculations.progressItems.map((prog, index) => (
                  <div key={`${prog.subject}-${index}`} className="rounded-2xl border border-border-base bg-bg-base p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div>
                        <p className="font-semibold">{prog.subject || "No Subject"}</p>
                        <p className="text-xs text-text-secondary">
                          {prog.completedTasks} of {prog.totalTasks} done
                        </p>
                      </div>
                      <span className="rounded-full bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">
                        {prog.progressPercent}%
                      </span>
                    </div>

                    <div className="mt-3 h-2 overflow-hidden rounded-full bg-border-base">
                      <div
                        className="h-full rounded-full transition-all duration-500"
                        style={{ width: `${prog.progressPercent}%`, background: "var(--color-primary)" }}
                        role="progressbar"
                        aria-valuenow={prog.progressPercent}
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label={`Progress for ${prog.subject || "No Subject"}`}
                      />
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-border-base bg-bg-base px-6 py-12 text-center">
                <div>
                    <Sparkles className="mx-auto h-8 w-8 text-accent/50" aria-hidden="true" />
                    <p className="mt-2 text-sm text-text-secondary">Add tasks to see progress here.</p>
                </div>
              </div>
            )}
          </section>

          <section className="flex flex-col rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm xl:col-span-1">
            <div className="mb-6 flex items-start gap-4">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-accent">
                <FileText className="h-5 w-5" aria-hidden="true" />
              </div>
              <div>
                <h2 className="text-lg font-semibold">Recent Notes</h2>
                <p className="mt-1 text-sm text-text-secondary">Latest saved notes.</p>
              </div>
            </div>

            {calculations.recentNotes.length > 0 ? (
              <div className="flex-1 space-y-4">
                {calculations.recentNotes.map((note) => (
                  <article key={note.noteId} className="rounded-2xl border border-border-base bg-bg-base p-4">
                    <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                      <SubjectBadge subject={note.subject} />
                      <span className="text-[10px] text-text-muted">
                        {formatDate(note.createdAt)}
                      </span>
                    </div>
                    <h3 className="mt-3 text-lg font-semibold">{note.title}</h3>
                    <div className="line-clamp-3 mt-2 text-sm leading-6 text-text-secondary">
                      <TiptapViewer content={note.content} />
                    </div>
                  </article>
                ))}
              </div>
            ) : (
              <div className="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-border-base bg-bg-base px-6 py-12 text-center">
                 <div>
                    <FileText className="mx-auto h-8 w-8 text-primary/50" aria-hidden="true" />
                    <p className="mt-2 text-sm text-text-secondary">No notes created yet.</p>
                 </div>
              </div>
            )}
          </section>
        </div>
      </div>
    </div>
  );
}