import React, { useEffect, useState } from "react";
import { BarChart3, Clock, FileText, Flame, Sparkles, Target, Download, Upload, X } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";
import SubjectBadge from "../components/shared/SubjectBadge";
import { useToast } from "../lib/useToast";
import { mapTaskData, mapNoteData } from "../lib/dataUtils";

const formatDate = (v) => new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(new Date(v));

export default function Dashboard() {
  const [data, setData] = useState(null);
  const [showBackupModal, setShowBackupModal] = useState(false);
  const [importSummary, setImportSummary] = useState(null);
  const [importPayload, setImportPayload] = useState(null);
  const toast = useToast();

  useEffect(() => {
    fetch("/api/dashboard", { credentials: "include" })
      .then(r => r.json()).then(d => setData(d))
      .catch(e => console.error("Error fetching dashboard:", e));
  }, []);

  // Global Export
  const handleGlobalExport = async () => {
    try {
      const [tasks, notes] = await Promise.all([
        fetch("/api/tasks", { credentials: "include" }).then(r => r.json()),
        fetch("/api/notes", { credentials: "include" }).then(r => r.json()),
      ]);
      const backup = { version: "1.0", exportedAt: new Date().toISOString(), tasks, notes };
      const blob = new Blob([JSON.stringify(backup, null, 2)], { type: "application/json" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url; a.download = `studybuddy_backup_${new Date().toISOString().slice(0, 10)}.json`; a.click();
      URL.revokeObjectURL(url);
      toast.success(`Backup exported: ${tasks.length} tasks, ${notes.length} notes`);
    } catch { toast.error("Export failed"); }
  };

  // Global Import
  const handleGlobalImport = (e) => {
    const file = e.target.files?.[0]; if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      try {
        const data = JSON.parse(ev.target.result);
        const tasks = Array.isArray(data.tasks) ? data.tasks : (data.tasks ? [data.tasks] : []);
        const notes = Array.isArray(data.notes) ? data.notes : (data.notes ? [data.notes] : []);
        if (!tasks.length && !notes.length) { toast.error("❌ Invalid file format. Please use a StudyBuddy export file."); return; }
        const summary = { tasks: tasks.length, notes: notes.length };
        setImportSummary(summary); setImportPayload({ tasks, notes });
      } catch {
        toast.error("❌ Invalid file format. Please use a StudyBuddy export file.");
      }
    };
    reader.readAsText(file); e.target.value = "";
  };

  const confirmGlobalImport = async () => {
    let taskOk = 0, noteOk = 0;

    try {
      if (importPayload.tasks?.length) {
        const existingTasks = await fetch("/api/tasks", { credentials: "include" }).then(r => r.json());
        await Promise.all(existingTasks.map(task => fetch(`/api/tasks/${task.taskId}`, { method: "DELETE", credentials: "include" })));
      }
      if (importPayload.notes?.length) {
        const existingNotes = await fetch("/api/notes", { credentials: "include" }).then(r => r.json());
        await Promise.all(existingNotes.map(note => fetch(`/api/notes/${note.noteId}`, { method: "DELETE", credentials: "include" })));
      }
    } catch (err) {
      console.error("Error clearing existing backup data:", err);
    }

    if (importPayload.tasks?.length) {
      for (const t of importPayload.tasks) {
        try {
          await fetch("/api/tasks", {
            method: "POST", credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(mapTaskData(t)),
          });
          taskOk++;
        } catch (err) {
          console.error("Task import failed:", err);
        }
      }
    }

    if (importPayload.notes?.length) {
      for (const n of importPayload.notes) {
        try {
          await fetch("/api/notes", {
            method: "POST", credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(mapNoteData(n)),
          });
          noteOk++;
        } catch (err) {
          console.error("Note import failed:", err);
        }
      }
    }

    toast.success(`✅ Restored ${taskOk} tasks, ${noteOk} notes successfully`);
    setImportSummary(null); setImportPayload(null);
    fetch("/api/dashboard", { credentials: "include" }).then(r => r.json()).then(d => setData(d));
  };

  if (!data) {
    return (
      <div className="relative min-h-screen overflow-hidden text-text-main" style={{ background: 'transparent' }}>
        <PageBackdrop />
        <div className="relative mx-auto flex min-h-screen max-w-4xl items-center justify-center px-4 pt-20 sm:px-6">
          <div className="glass-card w-full rounded-3xl p-10 text-center">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary"><BarChart3 className="h-7 w-7" /></div>
            <h1 className="mt-5 text-2xl font-semibold">Loading Dashboard</h1>
            <p className="mt-3 text-text-secondary">Pulling together notes, study time, and task progress.</p>
          </div>
        </div>
      </div>
    );
  }

  const progressItems = data.progress ?? [];
  const recentNotes = data.recentNotes ?? [];
  const totalTrackedTasks = progressItems.reduce((s, i) => s + i.totalTasks, 0);
  const completedTrackedTasks = progressItems.reduce((s, i) => s + i.completedTasks, 0);
  const overallProgress = totalTrackedTasks > 0 ? Math.round((completedTrackedTasks / totalTrackedTasks) * 100) : 0;
  const leadingSubject = progressItems.reduce((b, c) => (!b || c.progressPercent > b.progressPercent) ? c : b, null);

  const stats = [
    { label: "Total Notes", value: data.totalNotes, detail: "Saved across your workspace", icon: <FileText className="h-5 w-5" />, iconClass: "bg-primary/10 text-primary" },
    { label: "Total Hours", value: data.totalHours, detail: "Focus sessions logged", icon: <Clock className="h-5 w-5" />, iconClass: "bg-secondary/10 text-secondary" },
    { label: "Today's Hours", value: data.todayHours, detail: "Time invested today", icon: <Target className="h-5 w-5" />, iconClass: "bg-accent/10 text-accent" },
    { label: "Study Streak", value: `${data.studyStreak} Days`, detail: "Consistency is building", icon: <Flame className="h-5 w-5" />, iconClass: "bg-red-500/10 text-red-400" },
  ];

  return (
    <div className="relative min-h-screen overflow-hidden text-text-main page-enter" style={{ background: 'transparent' }}>
      <PageBackdrop />

      <div className="relative mx-auto max-w-7xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><BarChart3 className="h-5 w-5" /></div>
            <div>
              <h1 className="text-2xl font-semibold sm:text-3xl">Dashboard</h1>
              <p className="mt-1 text-sm text-text-secondary">Overview of notes, study time, and task progress.</p>
            </div>
          </div>

          {/* Global Import/Export */}
          <div className="flex items-center gap-3">
            <label className="inline-flex items-center gap-2 rounded-xl glass-card px-4 py-2.5 text-sm font-medium text-secondary hover:border-secondary/30 transition-all cursor-pointer">
              <Download className="h-4 w-4" /> Restore Backup
              <input type="file" accept=".json" onChange={handleGlobalImport} className="hidden" />
            </label>
            <button onClick={handleGlobalExport}
              className="inline-flex items-center gap-2 rounded-xl glass-card px-4 py-2.5 text-sm font-medium text-primary hover:border-primary/30 transition-all">
              <Upload className="h-4 w-4" /> Full Backup
            </button>
          </div>
        </div>

        {/* Summary Cards */}
        <div className="grid gap-3 sm:grid-cols-2 lg:min-w-[320px] mb-6">
          <div className="glass-card rounded-2xl p-4">
            <p className="text-sm font-medium text-text-secondary">Overall task completion</p>
            <div className="mt-3 flex items-end justify-between gap-4">
              <span className="text-3xl font-bold text-primary" style={{ textShadow: '0 0 20px rgba(255,159,10,0.3)' }}>{overallProgress}%</span>
              <span className="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">{completedTrackedTasks}/{totalTrackedTasks || 0} done</span>
            </div>
          </div>
          <div className="glass-card rounded-2xl p-4">
            <p className="text-sm font-medium text-text-secondary">Leading subject</p>
            <div className="mt-3 text-lg font-semibold">{leadingSubject?.subject || "No subjects yet"}</div>
            <p className="mt-1 text-sm text-text-secondary">{leadingSubject ? `${leadingSubject.progressPercent}% completion` : "Create tasks to see momentum by subject."}</p>
          </div>
        </div>

        {/* Stats */}
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
          {stats.map(({ label, value, detail, icon, iconClass }) => (
            <div key={label} className="glass-card rounded-2xl p-4 hover:scale-[1.02]">
              <div className="flex flex-wrap items-center gap-2">
                <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${iconClass}`}>{icon}</div>
                <div><p className="font-medium">{label}</p><p className="text-sm text-text-secondary">{detail}</p></div>
              </div>
              <div className="mt-3 text-3xl font-bold text-primary" style={{ textShadow: '0 0 20px rgba(255,159,10,0.3)' }}>{value}</div>
            </div>
          ))}
        </div>

        <div className="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
          {/* Task Progress */}
          <section className="glass-card rounded-3xl p-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div className="flex items-start gap-4">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><BarChart3 className="h-5 w-5" /></div>
                <div><h2 className="text-lg font-semibold">Task Progress</h2><p className="mt-1 text-sm text-text-secondary">Progress by subject.</p></div>
              </div>
              {totalTrackedTasks > 0 && <div className="rounded-full bg-accent/10 px-3 py-1 text-sm font-semibold text-accent">{progressItems.length} subject{progressItems.length === 1 ? "" : "s"} tracked</div>}
            </div>
            {progressItems.length > 0 ? (
              <div className="mt-6 space-y-4">
                {progressItems.map((prog, i) => (
                  <div key={`${prog.subject}-${i}`} className="surface-subtle p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div><p className="font-semibold">{prog.subject || "General"}</p><p className="text-sm text-text-secondary">{prog.completedTasks} of {prog.totalTasks} tasks complete</p></div>
                      <span className="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">{prog.progressPercent}%</span>
                    </div>
                    <div className="mt-4 h-2 overflow-hidden rounded-full bg-border-light">
                      <div className="h-full rounded-full transition-all duration-700" style={{ width: `${prog.progressPercent}%`, background: 'linear-gradient(90deg, #FF9F0A, #FF6B2C)' }} />
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="mt-6 empty-state">
                <Sparkles className="mx-auto h-8 w-8 text-primary" /><h3 className="mt-4 text-lg font-semibold">No tasks yet</h3>
                <p className="mt-2 text-text-secondary">Add tasks to unlock subject-by-subject progress tracking here.</p>
              </div>
            )}
          </section>

          {/* Recent Notes */}
          <section className="glass-card rounded-3xl p-5">
            <div className="flex items-start gap-4">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary/10 text-secondary"><FileText className="h-5 w-5" /></div>
              <div><h2 className="text-lg font-semibold">Recent Notes</h2><p className="mt-1 text-sm text-text-secondary">Latest saved notes.</p></div>
            </div>
            {recentNotes.length > 0 ? (
              <div className="mt-6 space-y-4">
                {recentNotes.map(note => (
                  <article key={note.noteId} className="surface-subtle p-4 transition hover:border-primary/30">
                    <div className="flex flex-wrap items-center gap-2">
                      <SubjectBadge subject={note.subject} />
                      <span className="text-xs text-text-muted">{formatDate(note.createdAt)}</span>
                    </div>
                    <h3 className="mt-3 text-lg font-semibold">{note.title}</h3>
                    <p className="mt-2 text-sm leading-6 text-text-secondary line-clamp-3">{note.content}</p>
                  </article>
                ))}
              </div>
            ) : (
              <div className="mt-6 empty-state">
                <FileText className="mx-auto h-8 w-8 text-secondary" />
                <h3 className="mt-4 text-lg font-semibold">No notes created yet</h3>
                <p className="mt-2 text-text-secondary">As soon as you save notes, the latest ones will show up here.</p>
              </div>
            )}
          </section>
        </div>
      </div>

      {/* Global Import Confirm Modal */}
      {importSummary && (
        <div className="fixed inset-0 z-[500] flex items-center justify-center" style={{ background: 'rgba(10,10,15,0.8)', backdropFilter: 'blur(8px)', animation: 'fadeIn 0.2s ease' }}>
          <div className="glass-card rounded-3xl p-6 w-[90%] max-w-md" style={{ animation: 'modalIn 0.35s cubic-bezier(0.22,1,0.36,1)' }}>
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold">Restore Backup</h3>
              <button onClick={() => { setImportSummary(null); setImportPayload(null); }} className="p-1 rounded-lg hover:bg-white/5"><X className="h-5 w-5" /></button>
            </div>
            <p className="text-text-secondary mb-4">This backup contains:</p>
            <div className="space-y-2 mb-6">
              <div className="flex justify-between glass-card rounded-xl p-3"><span>Tasks</span><span className="text-primary font-semibold">{importSummary.tasks}</span></div>
              <div className="flex justify-between glass-card rounded-xl p-3"><span>Notes</span><span className="text-secondary font-semibold">{importSummary.notes}</span></div>
            </div>
            <div className="flex gap-3">
              <button onClick={confirmGlobalImport} className="flex-1 rounded-xl bg-green-500/20 text-green-400 border border-green-500/30 px-5 py-2.5 text-sm font-medium hover:bg-green-500/30 transition">✓ Confirm Import</button>
              <button onClick={() => { setImportSummary(null); setImportPayload(null); }} className="flex-1 rounded-xl glass-card px-5 py-2.5 text-sm font-medium hover:bg-white/5 transition">Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
