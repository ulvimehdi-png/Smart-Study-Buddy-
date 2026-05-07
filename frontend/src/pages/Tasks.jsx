import React, { useEffect, useState, useRef } from "react";
import { Check, Clock, Flag, Plus, Sparkles, Trash2, Upload, Download, X, ChevronDown, AlertCircle } from "lucide-react";
import { cn } from "@/lib/utils";
import {
  deleteSubjectOption,
  fetchSubjectOptions,
  NEW_SUBJECT_VALUE,
} from "@/lib/subjectOptions";
import PageBackdrop from "../components/shared/PageBackdrop";
import SmallStats from "../components/shared/SmallStats";
import SubjectBadge from "../components/shared/SubjectBadge";
import SubjectSelect from "../components/shared/SubjectSelect";
import { useToast } from "../lib/useToast";
import { parseCSV, mapTaskData } from "../lib/dataUtils";

export default function Tasks() {
  const [tasks, setTasks] = useState([]);
  const [title, setTitle] = useState("");
  const [selectedSubject, setSelectedSubject] = useState("");
  const [customSubject, setCustomSubject] = useState("");
  const [subjectOptions, setSubjectOptions] = useState([]);
  const [priority, setPriority] = useState("medium");
  const [dueDate, setDueDate] = useState("");
  const [dueTime, setDueTime] = useState("");
  const [dateWarning, setDateWarning] = useState(false);
  const [showExportMenu, setShowExportMenu] = useState(false);
  const [importPreview, setImportPreview] = useState(null);
  const [importData, setImportData] = useState([]);
  const fileRef = useRef(null);
  const toast = useToast();

  const formatTaskDate = (dateStr) => {
    if (!dateStr) return "";
    const localStr = dateStr.replace(/(Z|[+-]\d{2}:\d{2})$/, '');
    return new Date(localStr).toLocaleDateString();
  };

  const formatTaskTime = (dateStr) => {
    if (!dateStr) return "";
    const localStr = dateStr.replace(/(Z|[+-]\d{2}:\d{2})$/, '');
    return new Date(localStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
  };

  const fetchTasks = () => {
    fetch("/api/tasks", { credentials: "include" })
      .then(r => r.json()).then(d => setTasks(d))
      .catch(e => console.error("Error fetching tasks:", e));
  };
  const fetchSubjects = () => {
    fetchSubjectOptions()
      .then((data) => setSubjectOptions(data))
      .catch((err) => console.error("Error fetching subjects:", err));
  };

  useEffect(() => {
    fetchTasks();
    fetchSubjects();
  }, []);

  const refreshData = () => {
    fetchTasks();
    fetchSubjects();
  };

  const handleRemoveSubject = async (option) => {
    if (String(option.id).startsWith("derived-")) {
      toast.error("This subject is assigned to a task. Remove it from the task first.");
      return;
    }
    await deleteSubjectOption(option.id);
    if (selectedSubject === option.name) setSelectedSubject("");
    refreshData();
  };

  const handleToggle = (id) => fetch(`/api/tasks/${id}/toggle`, { method: "PUT", credentials: "include" }).then(() => fetchTasks());
  const handleDelete = (id) => fetch(`/api/tasks/${id}`, { method: "DELETE", credentials: "include" }).then(() => { fetchTasks(); toast.success("Task deleted"); });

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title?.trim()) { toast.error("Title is required"); return; }
    const finalSubject = selectedSubject === NEW_SUBJECT_VALUE ? customSubject.trim() : selectedSubject;
    if (!finalSubject) { toast.error("Subject is required"); return; }
    if (!dueDate) { setDateWarning(true); toast.error("Due Date is required"); return; }
    if (!dueTime) { toast.error("Due Time is required"); return; }
    
    setDateWarning(false);

    const dueDateStr = dueDate + `T${dueTime}:00`;
    const nowLocalString = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    
    if (dueDateStr.slice(0, 16) < nowLocalString) {
      toast.error("Due date and time cannot be in the past!");
      return;
    }

    fetch("/api/tasks", {
      method: "POST", credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, subject: finalSubject, priority, dueDate: dueDateStr }),
    }).then(() => {
      setTitle(""); setSelectedSubject(""); setCustomSubject(""); setPriority("medium"); setDueDate(""); setDueTime("");
      refreshData(); toast.success("Task created!");
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
        if (!ev.target.result.trim()) {
          toast.error("File is empty");
          return;
        }
        let data;
        const fileName = file.name.toLowerCase();
        if (fileName.endsWith(".json")) {
          data = JSON.parse(ev.target.result);
          if (Object.keys(data).length === 0 && data.constructor === Object) {
            toast.error("File contains no tasks");
            return;
          }
          if (!Array.isArray(data)) {
            if (Array.isArray(data.tasks)) {
              data = data.tasks;
            } else {
              data = [data];
            }
          }
        } else if (fileName.endsWith(".csv")) {
          data = parseCSV(ev.target.result);
        } else { toast.error("Only .json and .csv files supported"); return; }
        if (!data || !data.length) { toast.error("No records found"); return; }
        const mapped = data.map(mapTaskData);
        setImportData(mapped); setImportPreview(mapped.slice(0, 20));
      } catch (err) { toast.error("❌ Invalid file format or empty content."); }
    };
    reader.readAsText(file);
    e.target.value = "";
  };

  const handleImportEdit = (index, field, value) => {
    const newData = [...importData];
    newData[index] = { ...newData[index], [field]: value };
    setImportData(newData);
    setImportPreview(newData.slice(0, 20)); // update preview
  };

  const confirmImport = async () => {
    let ok = 0;
    for (const item of importData) {
      if (!item.title) continue; // skip if no title
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

  const nowLocalString = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
  const minDateStr = nowLocalString.split("T")[0];
  const minTimeStr = nowLocalString.split("T")[1];

  const canImport = importData.length > 0 && importData.every(task => {
    if (!task.title?.trim() || !task.subject?.trim() || !task.priority || !task.dueDate) return false;
    return task.dueDate.slice(0, 16) >= nowLocalString;
  });

  const combinedSubjectOptions = React.useMemo(() => {
    const combined = [...subjectOptions];
    const taskSubjects = tasks.map(t => t.subject).filter(Boolean);
    
    taskSubjects.forEach(name => {
      // Case-insensitive check to see if it's already in the API subjects
      if (!combined.some(s => s.name.toLowerCase() === name.toLowerCase())) {
        combined.push({ id: `derived-${name}`, name });
      }
    });
    
    return combined.sort((a, b) => a.name.localeCompare(b.name));
  }, [tasks, subjectOptions]);

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
              <SubjectSelect
                options={combinedSubjectOptions}
                value={selectedSubject}
                onChange={setSelectedSubject}
                customSubject={customSubject}
                onCustomSubjectChange={setCustomSubject}
                onRemoveOption={handleRemoveSubject}
              />
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
                <label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Due Date</label>
                <input type="date" value={dueDate} onChange={e => { setDueDate(e.target.value); setDateWarning(false); }}
                  min={minDateStr}
                  className="glass-input rounded-xl px-4 py-3 text-text-main w-full outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              </div>
              <div>
                <label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Time</label>
                <input type="time" value={dueTime} onChange={e => setDueTime(e.target.value)}
                  min={dueDate === minDateStr ? minTimeStr : undefined}
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
                      {task.dueDate && (
                        <span className="text-xs text-text-muted flex items-center gap-2">
                          <span className="flex items-center gap-1">📅 {formatTaskDate(task.dueDate)}</span>
                          <span className="flex items-center gap-1">⏰ {formatTaskTime(task.dueDate)}</span>
                        </span>
                      )}
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
            <div className="overflow-x-auto rounded-xl border border-border-base mb-4 max-h-[50vh] custom-scrollbar">
              <table className="w-full text-sm">
                <thead className="sticky top-0 bg-[#0a0a0f] z-10 shadow-sm"><tr className="bg-primary/10">
                  <th className="px-4 py-3 text-left text-text-secondary font-semibold">Task Title</th>
                  <th className="px-4 py-3 text-left text-text-secondary font-semibold">Subject</th>
                  <th className="px-4 py-3 text-left text-text-secondary font-semibold">Priority</th>
                  <th className="px-4 py-3 text-left text-text-secondary font-semibold min-w-[200px]">Due Date & Time</th>
                </tr></thead>
                <tbody>{importPreview.map((row, i) => (
                  <tr key={i} className="border-t border-border-base hover:bg-primary/5 transition">
                    <td className="px-2 py-3">
                      <input type="text" placeholder="Task title..." value={row.title || ""} onChange={e => handleImportEdit(i, 'title', e.target.value)} className={cn("glass-input rounded-lg px-3 py-2 w-full bg-transparent transition outline-none focus:ring-2 focus:ring-primary/30", !row.title?.trim() ? "border border-red-500/50 bg-red-500/5 placeholder:text-red-400/50" : "border border-transparent")} />
                    </td>
                    <td className="px-2 py-3">
                      <input type="text" placeholder="Subject..." value={row.subject || ""} onChange={e => handleImportEdit(i, 'subject', e.target.value)} className={cn("glass-input rounded-lg px-3 py-2 w-full bg-transparent transition outline-none focus:ring-2 focus:ring-primary/30", !row.subject?.trim() ? "border border-red-500/50 bg-red-500/5 placeholder:text-red-400/50" : "border border-transparent")} />
                    </td>
                    <td className="px-2 py-3">
                      <select value={row.priority || "medium"} onChange={e => handleImportEdit(i, 'priority', e.target.value)} className={cn("glass-input rounded-lg px-3 py-2 w-full bg-transparent outline-none transition focus:ring-2 focus:ring-primary/30", !row.priority ? "border border-red-500/50 bg-red-500/5" : "border border-transparent")}>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                      </select>
                    </td>
                    <td className="px-2 py-3">
                      <input type="datetime-local" 
                        min={nowLocalString}
                        value={row.dueDate ? row.dueDate.slice(0, 16) : ""} 
                        onChange={e => handleImportEdit(i, 'dueDate', e.target.value ? e.target.value + ":00" : null)} 
                        className={cn("glass-input rounded-lg px-3 py-2 w-full bg-transparent outline-none transition focus:ring-2 focus:ring-primary/30 [color-scheme:dark]", 
                          (!row.dueDate || row.dueDate.slice(0, 16) < nowLocalString) ? "border border-red-500/50 bg-red-500/5" : "border border-transparent")} 
                      />
                    </td>
                  </tr>
                ))}</tbody>
              </table>
            </div>
            {!canImport && (
              <div className="mb-4 flex items-center gap-2 rounded-xl bg-red-500/10 border border-red-500/20 px-4 py-2.5 text-sm text-red-400" style={{ animation: 'fadeIn 0.2s ease' }}>
                <AlertCircle className="h-4 w-4 shrink-0" />
                Please ensure all fields are filled and due dates are set to the future.
              </div>
            )}
            <div className="flex gap-3">
              <button onClick={confirmImport} disabled={!canImport} className={cn("rounded-xl px-6 py-2.5 text-sm font-medium transition flex items-center justify-center min-w-[140px]", canImport ? "bg-green-500/20 text-green-400 border border-green-500/30 hover:bg-green-500/30" : "bg-gray-500/10 text-gray-500 border border-gray-500/20 cursor-not-allowed opacity-60")}>
                {canImport ? "✓ Confirm Import" : "Incomplete..."}
              </button>
              <button onClick={() => { setImportPreview(null); setImportData([]); }} className="rounded-xl glass-card px-5 py-2.5 text-sm font-medium hover:bg-primary/10 transition">Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
