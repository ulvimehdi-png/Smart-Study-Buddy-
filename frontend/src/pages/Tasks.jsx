import React, { useEffect, useState, useRef } from "react";
import { Check, Clock, Flag, Plus, Sparkles, Trash2, Upload, Download, X, ChevronDown, AlertCircle } from "lucide-react";
import { cn } from "@/lib/utils";
import PageBackdrop from "../components/shared/PageBackdrop";
import SmallStats from "../components/shared/SmallStats";
import SubjectBadge from "../components/shared/SubjectBadge";
import { useToast } from "../lib/useToast";
import { parseCSV, mapTaskData } from "../lib/dataUtils";

export default function Tasks() {
  const [tasks, setTasks] = useState([]);
  const [title, setTitle] = useState("");
  const [subject, setSubject] = useState("");
  const [priority, setPriority] = useState("medium");
  const [dueDate, setDueDate] = useState("");
  const [dueTime, setDueTime] = useState("");
  const [dateWarning, setDateWarning] = useState(false);
  const [showExportMenu, setShowExportMenu] = useState(false);
  const [importPreview, setImportPreview] = useState(null);
  const [importData, setImportData] = useState([]);
  const fileRef = useRef(null);
  const toast = useToast();

  const fetchTasks = () => {
    fetch("/api/tasks", { credentials: "include" })
      .then(r => r.json()).then(d => setTasks(d))
      .catch(e => console.error("Error fetching tasks:", e));
  };
  useEffect(() => { fetchTasks(); }, []);

  const handleToggle = (id) => fetch(`/api/tasks/${id}/toggle`, { method: "PUT", credentials: "include" }).then(() => fetchTasks());
  const handleDelete = (id) => fetch(`/api/tasks/${id}`, { method: "DELETE", credentials: "include" }).then(() => { fetchTasks(); toast.success("Task deleted"); });

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title) return;
    if (!dueDate) { setDateWarning(true); return; }
    setDateWarning(false);

    const dueDateStr = dueDate + (dueTime ? `T${dueTime}:00` : "T23:59:00");
    fetch("/api/tasks", {
      method: "POST", credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, subject, priority, dueDate: dueDateStr }),
    }).then(() => {
      setTitle(""); setSubject(""); setPriority("medium"); setDueDate(""); setDueTime("");
      fetchTasks(); toast.success("Task created!");
    });
  };

  // Export
  const doExport = (fmt) => {
    setShowExportMenu(false);
    const date = new Date().toISOString().slice(0, 10);
    if (fmt === "json") {
      const blob = new Blob([JSON.stringify(tasks, null, 2)], { type: "application/json" });
      const url = URL.createObjectURL(blob); const a = document.createElement("a");
      a.href = url; a.download = `tasks_export_${date}.json`; a.click(); URL.revokeObjectURL(url);
    } else {
      if (!tasks.length) { toast.error("No tasks to export"); return; }
      const headers = ["title", "subject", "priority", "dueDate", "description", "status"];
      const csv = [headers.join(","), ...tasks.map(r => headers.map(k => {
        const val = k === 'subject' ? (r[k]?.name || r[k] || "") : (r[k] ?? "");
        return `"${String(val).replace(/"/g, '""')}"`;
      }).join(","))].join("\n");
      const blob = new Blob([csv], { type: "text/csv" });
      const url = URL.createObjectURL(blob); const a = document.createElement("a");
      a.href = url; a.download = `tasks_export_${date}.csv`; a.click(); URL.revokeObjectURL(url);
    }
    toast.success(`Tasks exported as ${fmt.toUpperCase()}`);
  };

  // Import
  const handleImportFile = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      try {
        let data;
        if (file.name.endsWith(".json")) {
          data = JSON.parse(ev.target.result);
          if (!Array.isArray(data)) data = [data];
        } else if (file.name.endsWith(".csv")) {
          data = parseCSV(ev.target.result);
        } else { toast.error("Only .json and .csv files supported"); return; }
        if (!data.length) { toast.error("No records found"); return; }
        const mapped = data.map(mapTaskData);
        setImportData(mapped); setImportPreview(mapped.slice(0, 20));
      } catch (err) { toast.error("❌ Invalid file format. Please use a StudyBuddy export file."); }
    };
    reader.readAsText(file);
    e.target.value = "";
  };

  const confirmImport = async () => {
    let ok = 0;
    for (const item of importData) {
      try {
        await fetch("/api/tasks", {
          method: "POST", credentials: "include",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(item),
        });
        ok++;
      } catch {}
    }
    toast.success(`✅ Imported ${ok} tasks successfully`);
    setImportPreview(null); setImportData([]);
    fetchTasks();
  };

  const completedTasks = tasks.filter(t => t.isCompleted).length;
  const pendingTasks = tasks.length - completedTasks;
  const activeSubjects = new Set(tasks.map(t => t.subject?.trim()).filter(Boolean)).size;

  const priorityGlow = { high: "border-l-4 border-l-red-500/60", medium: "border-l-4 border-l-primary/60", low: "border-l-4 border-l-secondary/60" };

  const stats = [
    { label: "Pending", value: pendingTasks, icon: <Clock className="h-5 w-5" />, iconClass: "bg-primary/10 text-primary" },
    { label: "Completed", value: completedTasks, icon: <Check className="h-5 w-5" />, iconClass: "bg-green-500/10 text-green-400" },
    { label: "Subjects", value: activeSubjects, icon: <Sparkles className="h-5 w-5" />, iconClass: "bg-secondary/10 text-secondary" },
  ];

  return (
    <div className="relative min-h-screen overflow-hidden text-text-main page-enter" style={{ background: 'transparent' }}>
      <PageBackdrop />

      <div className="relative mx-auto max-w-6xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <Flag className="h-5 w-5" />
            </div>
            <div>
              <h1 className="text-2xl font-semibold sm:text-3xl">Tasks</h1>
              <p className="mt-1 text-sm text-text-secondary">Track what still needs to get done.</p>
            </div>
          </div>

          {/* Import / Export buttons */}
          <div className="flex items-center gap-3">
            <button onClick={() => fileRef.current?.click()}
              className="inline-flex items-center gap-2 rounded-xl glass-card px-4 py-2.5 text-sm font-medium text-secondary hover:border-secondary/30 transition-all">
              <Download className="h-4 w-4" /> Import
            </button>
            <input ref={fileRef} type="file" accept=".json,.csv" onChange={handleImportFile} className="hidden" />

            <div className="relative">
              <button onClick={() => setShowExportMenu(!showExportMenu)}
                className="inline-flex items-center gap-2 rounded-xl glass-card px-4 py-2.5 text-sm font-medium text-primary hover:border-primary/30 transition-all">
                <Upload className="h-4 w-4" /> Export <ChevronDown className="h-3 w-3" />
              </button>
              {showExportMenu && (
                <div className="absolute right-0 top-full mt-2 glass-card rounded-xl p-2 min-w-[160px] z-50" style={{ animation: 'modalIn 0.2s ease' }}>
                  <button onClick={() => doExport("json")} className="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-primary/10 transition">JSON format</button>
                  <button onClick={() => doExport("csv")} className="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-primary/10 transition">CSV format</button>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Stats */}
        <div className="grid gap-3 sm:grid-cols-3 mb-6">
          {stats.map(({ label, value, icon, iconClass }) => (
            <SmallStats key={label} label={label} value={value} icon={icon} iconClass={iconClass} />
          ))}
        </div>

        {/* Add Task Form */}
        <section className="glass-card rounded-3xl p-5 mb-6">
          <div className="flex items-start gap-4">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <Plus className="h-5 w-5" />
            </div>
            <div>
              <h2 className="text-lg font-semibold">Add a Task</h2>
              <p className="mt-1 text-sm text-text-secondary">Create a task and assign a subject if useful.</p>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div className="grid gap-4 lg:grid-cols-[200px_1fr_160px]">
              <input type="text" placeholder="Subject" value={subject} onChange={e => setSubject(e.target.value)}
                className="glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              <input type="text" placeholder="What needs to be done?" value={title} onChange={e => setTitle(e.target.value)} required
                className="glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              <select value={priority} onChange={e => setPriority(e.target.value)}
                className="glass-input rounded-xl px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                <option value="low">Low Priority</option>
                <option value="medium">Medium Priority</option>
                <option value="high">High Priority</option>
              </select>
            </div>

            <div className="grid gap-4 sm:grid-cols-[1fr_1fr_auto]">
              <div>
                <label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Due Date & Time</label>
                <input type="date" value={dueDate} onChange={e => { setDueDate(e.target.value); setDateWarning(false); }}
                  className="glass-input rounded-xl px-4 py-3 text-text-main w-full outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              </div>
              <div>
                <label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Time</label>
                <input type="time" value={dueTime} onChange={e => setDueTime(e.target.value)}
                  className="glass-input rounded-xl px-4 py-3 text-text-main w-full outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              </div>
              <div className="flex items-end">
                <button type="submit"
                  className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-primary-accent px-6 py-3 font-medium text-white transition-all hover:scale-[1.03] hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98]">
                  <Plus className="h-5 w-5" /> Add Task
                </button>
              </div>
            </div>

            {dateWarning && (
              <div className="flex items-center gap-2 rounded-xl bg-primary/10 border border-primary/20 px-4 py-2.5 text-sm text-primary">
                <AlertCircle className="h-4 w-4 shrink-0" />
                📅 When is this due? Adding a date puts it on your calendar.
              </div>
            )}
          </form>
        </section>

        {/* Task List */}
        <section className="glass-card rounded-3xl p-5">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div className="flex items-start gap-4">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent"><Flag className="h-5 w-5" /></div>
              <div><h2 className="text-lg font-semibold">Current Tasks</h2><p className="mt-1 text-sm text-text-secondary">Open and completed tasks.</p></div>
            </div>
            <div className="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">{tasks.length} total</div>
          </div>

          {tasks.length === 0 ? (
            <div className="mt-6 empty-state">
              <Sparkles className="mx-auto h-8 w-8 text-primary" />
              <h3 className="mt-4 text-lg font-semibold">No tasks yet</h3>
              <p className="mt-2 text-text-secondary">Create your first task above and it will appear here!</p>
            </div>
          ) : (
            <div className="mt-6 space-y-3">
              {tasks.map(task => (
                <article key={task.taskId}
                  className={cn("rounded-2xl p-5 glass-card hover:scale-[1.005]",
                    "md:flex md:items-center md:justify-between",
                    priorityGlow[task.priority] || priorityGlow.medium,
                    task.isCompleted ? "opacity-60" : ""
                  )}>
                  <div className="flex items-start gap-4">
                    <input type="checkbox" checked={task.isCompleted} onChange={() => handleToggle(task.taskId)}
                      className="mt-1 h-5 w-5 cursor-pointer rounded accent-primary" />
                    <div className="flex flex-wrap items-center gap-2">
                      <p className={cn("text-base font-semibold", task.isCompleted ? "text-text-muted line-through" : "text-text-main")}>{task.title}</p>
                      <SubjectBadge subject={task.subject} tone={task.isCompleted ? "primary" : "accent"} />
                      <span className={cn("rounded-full px-2 py-0.5 text-xs font-semibold uppercase tracking-wider",
                        task.priority === "high" ? "bg-red-500/15 text-red-400" :
                        task.priority === "medium" ? "bg-primary/15 text-primary" :
                        "bg-secondary/15 text-secondary")}>{task.priority}</span>
                      {task.dueDate && <span className="text-xs text-text-muted">📅 {new Date(task.dueDate).toLocaleDateString()}</span>}
                    </div>
                  </div>
                  <div className="mt-4 flex items-center justify-between gap-3 md:mt-0 md:justify-end">
                    <span className={cn("inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium",
                      task.isCompleted ? "bg-green-500/10 text-green-400" : "bg-primary/10 text-primary")}>
                      <span className={cn("h-2 w-2 rounded-full", task.isCompleted ? "bg-green-400" : "bg-primary")} />
                      {task.isCompleted ? "Done" : "In Progress"}
                    </span>
                    <button onClick={() => handleDelete(task.taskId)}
                      className="inline-flex items-center gap-2 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm font-medium text-red-400 transition hover:bg-red-500/20">
                      <Trash2 className="h-4 w-4" /> Delete
                    </button>
                  </div>
                </article>
              ))}
            </div>
          )}
        </section>
      </div>

      {/* Import Preview Modal */}
      {importPreview && (
        <div className="fixed inset-0 z-[500] flex items-center justify-center" style={{ background: 'rgba(10,10,15,0.8)', backdropFilter: 'blur(8px)', animation: 'fadeIn 0.2s ease' }}>
          <div className="glass-card rounded-3xl p-6 w-[90%] max-w-2xl max-h-[80vh] overflow-y-auto" style={{ animation: 'modalIn 0.35s cubic-bezier(0.22,1,0.36,1)' }}>
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold">Import Preview — {importData.length} tasks</h3>
              <button onClick={() => { setImportPreview(null); setImportData([]); }} className="p-1 rounded-lg hover:bg-primary/10 transition"><X className="h-5 w-5" /></button>
            </div>
            <div className="overflow-x-auto rounded-xl border border-border-base mb-4">
              <table className="w-full text-sm">
                <thead><tr className="bg-primary/5">
                  {Object.keys(importPreview[0] || {}).slice(0, 5).map(k => <th key={k} className="px-3 py-2 text-left text-text-secondary font-medium">{k}</th>)}
                </tr></thead>
                <tbody>{importPreview.map((row, i) => (
                  <tr key={i} className="border-t border-border-base hover:bg-primary/5 transition">
                    {Object.values(row).slice(0, 5).map((v, j) => <td key={j} className="px-3 py-2 text-text-secondary truncate max-w-[200px]">{String(v ?? "")}</td>)}
                  </tr>
                ))}</tbody>
              </table>
            </div>
            <div className="flex gap-3">
              <button onClick={confirmImport} className="rounded-xl bg-green-500/20 text-green-400 border border-green-500/30 px-5 py-2.5 text-sm font-medium hover:bg-green-500/30 transition">✓ Confirm Import</button>
              <button onClick={() => { setImportPreview(null); setImportData([]); }} className="rounded-xl glass-card px-5 py-2.5 text-sm font-medium hover:bg-primary/10 transition">Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
