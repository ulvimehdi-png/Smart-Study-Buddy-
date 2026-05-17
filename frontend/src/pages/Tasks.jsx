import React, { useEffect, useState, useRef } from "react";
import { Check, Clock, Flag, Plus, Sparkles, Trash2, Upload, Download, X, ChevronDown, AlertCircle, Pencil } from "lucide-react";
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

/* TMP */
import { useToast } from "../lib/useToast.jsx";
import { parseCSV, mapTaskData } from "../lib/dataUtils";
/* TMP */

export default function Tasks() {
  const [tasks, setTasks] = useState([]);
  const [title, setTitle] = useState("");
  const [selectedSubject, setSelectedSubject] = useState("");
  const [customSubject, setCustomSubject] = useState("");
  const [subjectOptions, setSubjectOptions] = useState([]);

  /* TMP */
  const [priority, setPriority] = useState("medium");
  const [dueDate, setDueDate] = useState("");
  const [dueTime, setDueTime] = useState("");
  const [dateWarning, setDateWarning] = useState(false);
  const [showExportMenu, setShowExportMenu] = useState(false);
  const [importPreview, setImportPreview] = useState(null);
  const [importData, setImportData] = useState([]);
  const [editingTask, setEditingTask] = useState(null);
  const [editTitle, setEditTitle] = useState("");
  const [editSubject, setEditSubject] = useState("");
  const [editCustomSubject, setEditCustomSubject] = useState("");
  const [editPriority, setEditPriority] = useState("medium");
  const [editDueDate, setEditDueDate] = useState("");
  const [editDueTime, setEditDueTime] = useState("");
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

  /* TMP */

  const fetchTasks = () => {
    fetch("/api/tasks", {
      credentials: "include",
    })
      .then((res) => res.json())
      .then((data) => setTasks(data))
      .catch((err) => console.error("Error fetching tasks:", err));
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
    if (!title?.trim()) {
      toast.error("Title is required");
      return;
    }

    const subject =
      selectedSubject === NEW_SUBJECT_VALUE
        ? customSubject.trim()
        : selectedSubject;

    // if (!subject) { toast.error("Subject is required"); return; }
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
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, subject, priority, dueDate: dueDateStr }),
    }).then(() => {
      setTitle("");
      setSelectedSubject("");
      setCustomSubject("");
      setPriority("medium");
      setDueDate("");
      setDueTime("");
      refreshData();
      toast.success("Task created!");
    });
  };

  const handleRemoveSubject = async (option) => {
    if (String(option.id).startsWith("derived-")) {
      toast.error("This subject is assigned to a task. Remove it from the task first.");
      return;
    }
    await deleteSubjectOption(option.id);

    if (selectedSubject === option.name) {
      setSelectedSubject("");
    }

    refreshData();
  };

  const completedTasks = tasks.filter((task) => task.isCompleted).length;
  const pendingTasks = tasks.length - completedTasks;
  const activeSubjects = new Set(
    tasks.map((task) => task.subject?.trim()).filter(Boolean),
  ).size;

  const handleEditClick = (task) => {
    setEditingTask(task);
    setEditTitle(task.title || "");
    setEditSubject(task.subject?.name || task.subject || "");
    setEditCustomSubject("");
    setEditPriority(task.priority || "medium");

    if (task.dueDate) {
      const localStr = task.dueDate.replace(/(Z|[+-]\d{2}:\d{2})$/, '');
      const dateObj = new Date(localStr);
      setEditDueDate(dateObj.toISOString().slice(0, 10));
      setEditDueTime(dateObj.toTimeString().slice(0, 5));
    } else {
      setEditDueDate("");
      setEditDueTime("");
    }
  };

  const handleEditSubmit = async (e) => {
    e.preventDefault();
    if (!editTitle?.trim()) { toast.error("Title is required"); return; }
    const subject = editSubject === NEW_SUBJECT_VALUE ? editCustomSubject.trim() : editSubject;
    // if (!subject) { toast.error("Subject is required"); return; }
    if (!editDueDate || !editDueTime) { toast.error("Due Date and Time are required"); return; }

    const dueDateStr = editDueDate + `T${editDueTime}:00`;
    const nowLocalString = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);

    if (dueDateStr.slice(0, 16) < nowLocalString) {
      toast.error("Due date and time cannot be in the past!");
      return;
    }

    try {
      const res = await fetch(`/api/tasks/${editingTask.id}`, {
        method: "PUT", credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ title: editTitle, subject: subject, priority: editPriority, dueDate: dueDateStr }),
      });
      if (!res.ok) throw new Error("Failed");
      setEditingTask(null);
      refreshData();
      toast.success("Task updated!");
    } catch (e) {
      toast.error("Failed to update task");
    }
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
      } catch { }
    }
    toast.success(`✅ Imported ${ok} tasks successfully`);
    setImportPreview(null); setImportData([]);
    fetchTasks();
  };

  const priorityGlow = { high: "border-l-4 border-l-red-500/60", medium: "border-l-4 border-l-primary/60", low: "border-l-4 border-l-secondary/60" };


  const stats = [
    {
      label: "Pending",
      value: pendingTasks,
      icon: <Clock className="h-4 w-4 sm:h-5 sm:w-5" />,
      iconClass: "bg-primary/10 text-primary",
    },
    {
      label: "Completed",
      value: completedTasks,
      icon: <Check className="h-4 w-4 sm:h-5 sm:w-5" />,
      iconClass: "bg-accent-light/10 text-accent-light",
    },
    {
      label: "Subjects",
      value: activeSubjects,
      icon: <Sparkles className="h-4 w-4 sm:h-5 sm:w-5" />,
      iconClass: "bg-accent/10 text-accent",
    },
  ];

  const nowLocalString = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
  const minDateStr = nowLocalString.split("T")[0];
  const minTimeStr = nowLocalString.split("T")[1];

  const canImport = importData.length > 0 && importData.every(task => {
    if (!task.title?.trim() || !task.subject?.trim() || !task.priority || !task.dueDate) return false;
    return task.dueDate.slice(0, 16) >= nowLocalString;
  });

  const combinedSubjectOptions = React.useMemo(() => {
    // Collect all subject names currently used in any task
    const activeNames = new Set(tasks.map(t => t.subject?.trim()).filter(Boolean));

    return Array.from(activeNames).map((name, i) => {
      // Find matching subject from API, if any (case-insensitive)
      const existing = subjectOptions.find(s => s.name.toLowerCase() === name.toLowerCase());
      return existing ? existing : { id: `derived-${i}`, name };
    }).sort((a, b) => a.name.localeCompare(b.name));
  }, [tasks, subjectOptions]);


  return (
    <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
      <PageBackdrop />

      <div className="relative mx-auto max-w-6xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
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

          <div className="flex flex-col items-end gap-4">
            {/* Import / Export buttons */}
            <div className="flex items-center gap-3">
              <button onClick={() => fileRef.current?.click()}
                className="inline-flex items-center gap-2 rounded-xl border border-border-base bg-bg-card px-4 py-2.5 text-sm font-medium text-primary-dark hover:border-secondary/30 transition-all">
                <Download className="h-4 w-4" /> Import
              </button>
              <input ref={fileRef} type="file" accept=".json,.csv" onChange={handleImportFile} className="hidden" />

              <div className="relative">
                <button onClick={() => setShowExportMenu(!showExportMenu)}
                  className="inline-flex items-center gap-2 rounded-xl border border-border-base bg-bg-card px-4 py-2.5 text-sm font-medium text-primary-dark hover:border-primary/30 transition-all">
                  <Upload className="h-4 w-4" /> Export <ChevronDown className="h-3 w-3" />
                </button>
                {showExportMenu && (
                  <div className="absolute right-0 top-full mt-2 border border-border-base bg-bg-card rounded-xl p-2 min-w-[160px] z-50" style={{ animation: 'modalIn 0.2s ease' }}>
                    <button onClick={() => doExport("json")} className="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-primary/10 transition">JSON format</button>
                    <button onClick={() => doExport("csv")} className="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-primary/10 transition">CSV format</button>
                  </div>
                )}
              </div>
            </div>

            <div className="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:min-w-90 w-full">
              {stats.map(({ label, value, icon, iconClass }, index) => (
                <SmallStats key={index} label={label} value={value} icon={icon} iconClass={iconClass} />
              ))}
            </div>
          </div>
        </div>

        {/* Add Task Form */}
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
            <SubjectSelect
              options={subjectOptions}
              value={selectedSubject}
              onChange={setSelectedSubject}
              customSubject={customSubject}
              onCustomSubjectChange={setCustomSubject}
              onRemoveOption={handleRemoveSubject}
            />
            <input
              type="text"
              placeholder="What needs to be done?"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary"
              required
            />
            <select value={priority} onChange={e => setPriority(e.target.value)}
              className="border bg-bg-elevated rounded-xl px-4 py-3 text-text-main outline-none transition focus:border-primary">
              <option value="low">Low Priority</option>
              <option value="medium">Medium Priority</option>
              <option value="high">High Priority</option>
            </select>

            <div className="grid gap-4 sm:grid-cols-[1fr_1fr_auto]">
              <div>
                <label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Due Date</label>
                <input type="date" value={dueDate} onChange={e => { setDueDate(e.target.value); setDateWarning(false); }}
                  min={minDateStr}
                  className="border bg-bg-elevated rounded-xl px-4 py-3 text-text-main w-full outline-none transition focus:border-primary" />
              </div>
              <div>
                <label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Time</label>
                <input type="time" value={dueTime} onChange={e => setDueTime(e.target.value)}
                  min={dueDate === minDateStr ? minTimeStr : undefined}
                  className="border bg-bg-elevated rounded-xl px-4 py-3 text-text-main w-full outline-none transition focus:border-primary" />
              </div>
              <div className="flex items-end">
                <button
                  type="submit"
                  className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-primary px-6 py-3 font-medium text-white transition hover:bg-primary-dark"
                >
                  <Plus className="h-5 w-5" />
                  Add Task
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
                    priorityGlow[task.priority] || priorityGlow.medium,
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
                    {!task.isCompleted && (
                      <button onClick={() => handleEditClick(task)}
                        className="inline-flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/10 px-3 py-2 text-sm font-medium text-primary transition hover:bg-primary/20">
                        <Pencil className="h-4 w-4" /> Edit
                      </button>
                    )}

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
