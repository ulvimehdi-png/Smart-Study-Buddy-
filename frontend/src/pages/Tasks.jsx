import React, { useEffect, useState } from "react";
import { Check, Clock, Flag, Plus, Sparkles, Trash2 } from "lucide-react";
import { cn } from "@/lib/utils";
import PageBackdrop from "../components/shared/PageBackdrop";
import SmallStats from "../components/shared/SmallStats";
import SubjectBadge from "../components/shared/SubjectBadge";

export default function Tasks() {
  const [tasks, setTasks] = useState([]);
  const [title, setTitle] = useState("");
  const [subject, setSubject] = useState("");
  const [priority, setPriority] = useState("medium");

  const fetchTasks = () => {
    fetch("/api/tasks", {
      credentials: "include",
    })
      .then((res) => res.json())
      .then((data) => setTasks(data))
      .catch((err) => console.error("Error fetching tasks:", err));
  };

  useEffect(() => {
    fetchTasks();
  }, []);

  const handleToggle = (taskId) => {
    fetch(`/api/tasks/${taskId}/toggle`, {
      method: "PUT",
      credentials: "include",
    }).then(() => fetchTasks());
  };

  const handleDelete = (taskId) => {
    fetch(`/api/tasks/${taskId}`, {
      method: "DELETE",
      credentials: "include",
    }).then(() => fetchTasks());
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title) return;

    fetch("/api/tasks", {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, subject, priority }),
    }).then(() => {
      setTitle("");
      setSubject("");
      setPriority("medium");
      fetchTasks();
    });
  };

  const completedTasks = tasks.filter((task) => task.isCompleted).length;
  const pendingTasks = tasks.length - completedTasks;
  const activeSubjects = new Set(
    tasks.map((task) => task.subject?.trim()).filter(Boolean),
  ).size;

  const stats = [
    {
      label: "Pending",
      value: pendingTasks,
      icon: <Clock className="h-5 w-5" />,
      iconClass: "bg-primary/10 text-primary",
    },
    {
      label: "Completed",
      value: completedTasks,
      icon: <Check className="h-5 w-5" />,
      iconClass: "bg-accent-light/10 text-accent-light",
    },
    {
      label: "Subjects",
      value: activeSubjects,
      icon: <Sparkles className="h-5 w-5" />,
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
                <Flag className="h-5 w-5" />
              </div>
              <div>
                <h1 className="text-2xl font-semibold sm:text-3xl">Tasks</h1>
                <p className="mt-1 text-sm text-text-secondary">
                  Track what still needs to get done.
                </p>
              </div>
            </div>
          </div>

          <div className="grid gap-3 sm:grid-cols-3 lg:min-w-90">
            {stats.map(({ label, value, icon, iconClass }) => (
              <SmallStats label={label} value={value} icon={icon} iconClass={iconClass} />
            ))}
          </div>
        </div>

        <section className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm">
          <div className="flex items-start gap-4">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <Plus className="h-5 w-5" />
            </div>
            <div>
              <h2 className="text-lg font-semibold">Add a Task</h2>
              <p className="mt-1 text-sm text-text-secondary">
                Create a task and assign a subject if useful.
              </p>
            </div>
          </div>

          <form
            onSubmit={handleSubmit}
            className="mt-6 grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)_auto]"
          >
            <input
              type="text"
              placeholder="Subject"
              value={subject}
              onChange={(e) => setSubject(e.target.value)}
              className="rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
            />
            <input
              type="text"
              placeholder="What needs to be done?"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
              required
            />
            <select
              value={priority}
              onChange={(e) => setPriority(e.target.value)}
              className="rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
            >
              <option value="low">Low Priority</option>
              <option value="medium">Medium Priority</option>
              <option value="high">High Priority</option>
            </select>
            <button
              type="submit"
              className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 font-medium text-white transition hover:bg-primary-dark"
            >
              <Plus className="h-5 w-5" />
              Add Task
            </button>
          </form>
        </section>

        <section className="mt-6 rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div className="flex items-start gap-4">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                <Flag className="h-5 w-5" />
              </div>
              <div>
                <h2 className="text-lg font-semibold">Current Tasks</h2>
                <p className="mt-1 text-sm text-text-secondary">
                  Open and completed tasks.
                </p>
              </div>
            </div>

            <div className="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">
              {tasks.length} total
            </div>
          </div>

          {tasks.length === 0 ? (
            <div className="mt-6 rounded-2xl border border-dashed border-border-base bg-bg-base px-6 py-12 text-center">
              <Sparkles className="mx-auto h-8 w-8 text-accent" />
              <h3 className="mt-4 text-lg font-semibold">No tasks yet</h3>
              <p className="mt-2 text-text-secondary">
                Create your first task above and it will appear here!
              </p>
            </div>
          ) : (
            <div className="mt-6 space-y-4">
              {tasks.map((task) => (
                <article
                  key={task.taskId}
                  className={cn(
                    "rounded-2xl border p-5",
                    "md:flex md:items-center md:justify-between",
                    task.isCompleted
                      ? "border-primary/20 bg-primary/5"
                      : "border-border-base bg-bg-base",
                  )}
                >
                  <div className="flex items-start gap-4">
                    <input
                      type="checkbox"
                      checked={task.isCompleted}
                      onChange={() => handleToggle(task.taskId)}
                      className="mt-1 h-5 w-5 cursor-pointer rounded border-border-base bg-bg-elevated accent-primary"
                    />

                    <div className="flex flex-wrap items-center gap-2">
                      <p
                        className={cn(
                          "text-base font-semibold",
                          task.isCompleted
                            ? "text-text-muted line-through"
                            : "text-text-main",
                        )}
                      >
                        {task.title}
                      </p>
                      <SubjectBadge
                        subject={task.subject}
                        tone={task.isCompleted ? "primary" : "accent"}
                      />
                      <span className={cn(
                        "rounded-full px-2 py-0.5 text-xs font-semibold uppercase tracking-wider",
                        task.priority === "high" ? "bg-danger/20 text-danger" :
                        task.priority === "medium" ? "bg-accent/20 text-accent" :
                        "bg-primary/20 text-primary"
                      )}>
                        {task.priority}
                      </span>
                    </div>
                  </div>

                  <div className="mt-4 flex items-center justify-between gap-3 md:mt-0 md:justify-end">
                    <span
                      className={cn(
                        "inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium",
                        task.isCompleted
                          ? "bg-primary/10 text-primary"
                          : "bg-accent/10 text-accent",
                      )}
                    >
                      <span
                        className={cn("h-2.5 w-2.5 rounded-full",
                          task.isCompleted ? "bg-primary" : "bg-accent",
                        )}
                      />
                      {task.isCompleted ? "Done" : "In Progress"}
                    </span>

                    <button
                      onClick={() => handleDelete(task.taskId)}
                      className="inline-flex items-center gap-2 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm font-medium text-danger transition hover:bg-danger/15"
                    >
                      <Trash2 className="h-4 w-4" />
                      Delete
                    </button>
                  </div>
                </article>
              ))}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
