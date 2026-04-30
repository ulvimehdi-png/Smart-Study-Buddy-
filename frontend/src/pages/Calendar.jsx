import React, { useEffect, useState } from "react";
import { ChevronLeft, ChevronRight, Clock, BookText, CheckCircle, Circle, Plus, X, Flag, NotebookPen } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";
import { useToast } from "../lib/useToast";

const DAYS = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const MONTHS = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

const formatDate = (d) => new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(new Date(d));

export default function Calendar() {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [calendarData, setCalendarData] = useState({ sessions: [], tasks: [], notes: [] });
  const [selectedDate, setSelectedDate] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [addType, setAddType] = useState("task");
  const [addTitle, setAddTitle] = useState("");
  const [addSubject, setAddSubject] = useState("");
  const [addTime, setAddTime] = useState("");
  const [addPriority, setAddPriority] = useState("medium");
  const [addContent, setAddContent] = useState("");
  const [addMinutes, setAddMinutes] = useState(25);
  const toast = useToast();

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  const fetchCalendarData = () => {
    setLoading(true);
    const toLocalDateStr = (d) => {
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    const startDate = new Date(year, month, 1);
    const endDate = new Date(year, month + 1, 0);
    const startStr = toLocalDateStr(startDate);
    const endStr = toLocalDateStr(endDate);

    Promise.all([
      fetch(`/api/calendar/sessions?start=${startStr}&end=${endStr}`, { credentials: "include" }).then(r => r.json()).catch(() => []),
      fetch(`/api/calendar/tasks?start=${startStr}&end=${endStr}`, { credentials: "include" }).then(r => r.json()).catch(() => []),
      fetch(`/api/notes`, { credentials: "include" }).then(r => r.json()).catch(() => []),
    ]).then(([sessions, tasks, notes]) => {
      setCalendarData({ sessions, tasks, notes });
    }).finally(() => setLoading(false));
  };

  useEffect(() => { fetchCalendarData(); }, [year, month]);

  const getDaysInMonth = () => {
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    const days = [];
    for (let i = firstDay - 1; i >= 0; i--) days.push({ day: daysInPrevMonth - i, isCurrentMonth: false, date: new Date(year, month - 1, daysInPrevMonth - i) });
    for (let i = 1; i <= daysInMonth; i++) days.push({ day: i, isCurrentMonth: true, date: new Date(year, month, i) });
    const remaining = 42 - days.length;
    for (let i = 1; i <= remaining; i++) days.push({ day: i, isCurrentMonth: false, date: new Date(year, month + 1, i) });
    return days;
  };

  const getEventsForDate = (date) => {
    // Use local date string YYYY-MM-DD to avoid timezone shifts
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const dateStr = `${y}-${m}-${d}`;

    const sessions = calendarData.sessions.filter(s => s.date?.split("T")[0] === dateStr);
    const tasks = calendarData.tasks.filter(t => t.dueDate?.split("T")[0] === dateStr);
    const notes = calendarData.notes.filter(n => n.createdAt?.split("T")[0] === dateStr);
    return { sessions, tasks, notes };
  };

  const navigateMonth = (d) => { setCurrentDate(new Date(year, month + d, 1)); setSelectedDate(null); };
  const goToToday = () => { setCurrentDate(new Date()); setSelectedDate(new Date()); };

  const openAddModal = (date) => {
    setSelectedDate(date);
    setShowAddModal(true);
    setAddType("task"); setAddTitle(""); setAddSubject(""); setAddTime(""); setAddPriority("medium"); setAddContent(""); setAddMinutes(25);
  };

  const handleAddEntry = async (e) => {
    e.preventDefault();
    if (!addTitle) return;
    
    const y = selectedDate.getFullYear();
    const m = String(selectedDate.getMonth() + 1).padStart(2, '0');
    const d = String(selectedDate.getDate()).padStart(2, '0');
    const dateStr = `${y}-${m}-${d}`;
    try {
      if (addType === "task") {
        await fetch("/api/tasks", { method: "POST", credentials: "include", headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ title: addTitle, subject: addSubject, priority: addPriority, dueDate: dateStr + (addTime ? `T${addTime}:00` : "T23:59:00") }) });
        toast.success("Task added to calendar!");
      } else if (addType === "note") {
        await fetch("/api/notes", { method: "POST", credentials: "include", headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ title: addTitle, content: addContent || addTitle, subject: addSubject, createdAt: dateStr + (addTime ? `T${addTime}:00` : `T${new Date().toTimeString().slice(0, 5)}:00`) }) });
        toast.success("Note added to calendar!");
      } else {
        await fetch("/api/sessions", { method: "POST", credentials: "include", headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ subject: addSubject || addTitle, minutes: addMinutes, date: dateStr }) });
        toast.success("Study session logged!");
      }
      setShowAddModal(false); fetchCalendarData();
    } catch { toast.error("Failed to add entry"); }
  };

  const days = getDaysInMonth();
  const today = new Date();
  const todayStr = (() => {
    const y = today.getFullYear();
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const d = String(today.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  })();

  return (
    <div className="relative min-h-screen overflow-hidden text-text-main page-enter" style={{ background: 'transparent' }}>
      <PageBackdrop />

      <div className="relative mx-auto max-w-7xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        <div className="mb-8 flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><Clock className="h-5 w-5" /></div>
          <div>
            <h1 className="text-2xl font-semibold sm:text-3xl">Calendar</h1>
            <p className="mt-1 text-sm text-text-secondary">View your study sessions, tasks, and notes.</p>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-[1fr_340px]">
          {/* Calendar Grid */}
          <div className="glass-card rounded-3xl p-6">
            <div className="mb-6 flex items-center justify-between">
              <h2 className="text-xl font-semibold">{MONTHS[month]} {year}</h2>
              <div className="flex items-center gap-2">
                <button onClick={goToToday} className="rounded-xl px-3 py-1.5 text-sm font-medium text-primary hover:bg-primary/10 transition">Today</button>
                <button onClick={() => navigateMonth(-1)} className="rounded-xl p-2 hover:bg-primary/10 transition"><ChevronLeft className="h-5 w-5" /></button>
                <button onClick={() => navigateMonth(1)} className="rounded-xl p-2 hover:bg-primary/10 transition"><ChevronRight className="h-5 w-5" /></button>
              </div>
            </div>

            <div className="mb-2 grid grid-cols-7 gap-1">
              {DAYS.map(d => <div key={d} className="py-2 text-center text-xs font-medium text-text-muted uppercase tracking-wider">{d}</div>)}
            </div>

            <div className="grid grid-cols-7 gap-1">
              {days.map((dayInfo, index) => {
                const y = dayInfo.date.getFullYear();
                const m = String(dayInfo.date.getMonth() + 1).padStart(2, '0');
                const d = String(dayInfo.date.getDate()).padStart(2, '0');
                const dateStr = `${y}-${m}-${d}`;
                
                const isToday = dateStr === todayStr;
                const isSelected = selectedDate && (() => {
                  const sy = selectedDate.getFullYear();
                  const sm = String(selectedDate.getMonth() + 1).padStart(2, '0');
                  const sd = String(selectedDate.getDate()).padStart(2, '0');
                  return `${sy}-${sm}-${sd}` === dateStr;
                })();
                const { sessions, tasks, notes } = getEventsForDate(dayInfo.date);
                const hasEvents = sessions.length > 0 || tasks.length > 0 || notes.length > 0;

                return (
                  <button key={index} onClick={() => setSelectedDate(dayInfo.date)} onDoubleClick={() => openAddModal(dayInfo.date)}
                    className={`relative min-h-[80px] rounded-xl p-2 text-left transition-all duration-200
                      ${!dayInfo.isCurrentMonth ? "text-text-muted/40" : "text-text-main"}
                      ${isToday ? "ring-1 ring-primary/60 bg-primary/[0.06]" : ""}
                      ${isSelected ? "ring-2 ring-primary bg-primary/10" : "hover:bg-primary/5"}
                    `}
                    style={isToday ? { boxShadow: '0 0 15px rgba(255,159,10,0.15)' } : {}}>
                    <span className={`text-sm font-medium ${isToday ? "text-primary" : ""}`}>{dayInfo.day}</span>
                    {hasEvents && dayInfo.isCurrentMonth && (
                      <div className="mt-1.5 flex gap-1">
                        {tasks.length > 0 && <span className="h-1.5 w-1.5 rounded-full bg-primary" />}
                        {notes.length > 0 && <span className="h-1.5 w-1.5 rounded-full bg-secondary" />}
                        {sessions.length > 0 && <span className="h-1.5 w-1.5 rounded-full bg-green-400" />}
                      </div>
                    )}
                  </button>
                );
              })}
            </div>

            {/* Legend */}
            <div className="mt-4 flex items-center gap-6 text-xs text-text-muted">
              <span className="flex items-center gap-1.5"><span className="h-2 w-2 rounded-full bg-primary" /> Tasks</span>
              <span className="flex items-center gap-1.5"><span className="h-2 w-2 rounded-full bg-secondary" /> Notes</span>
              <span className="flex items-center gap-1.5"><span className="h-2 w-2 rounded-full bg-green-400" /> Sessions</span>
              <span className="ml-auto text-text-muted/60">Double-click to add entry</span>
            </div>
          </div>

          {/* Selected Date Details */}
          <div className="glass-card rounded-3xl p-5 lg:sticky lg:top-24 lg:self-start">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold">{selectedDate ? formatDate(selectedDate) : "Select a date"}</h3>
              {selectedDate && (
                <button onClick={() => openAddModal(selectedDate)}
                  className="rounded-xl bg-primary/10 text-primary px-3 py-1.5 text-sm font-medium hover:bg-primary/20 transition">
                  <Plus className="h-4 w-4 inline mr-1" />Add
                </button>
              )}
            </div>

            {selectedDate ? (() => {
              const { sessions, tasks, notes } = getEventsForDate(selectedDate);
              return (
                <div className="space-y-5">
                  {/* Sessions */}
                  <div>
                    <h4 className="mb-2 flex items-center gap-2 text-sm font-medium text-text-secondary">
                      <Clock className="h-4 w-4 text-green-400" /> Study Sessions
                    </h4>
                    {sessions.length === 0 ? <p className="text-sm italic text-text-muted">No sessions logged</p> : (
                      <ul className="space-y-2">{sessions.map(s => (
                        <li key={s.id} className="surface-subtle p-3">
                          <p className="text-sm font-medium">{s.subject}</p>
                          <p className="text-xs text-text-muted">{s.minutes} minutes</p>
                        </li>
                      ))}</ul>
                    )}
                  </div>

                  {/* Tasks */}
                  <div>
                    <h4 className="mb-2 flex items-center gap-2 text-sm font-medium text-text-secondary">
                      <Flag className="h-4 w-4 text-primary" /> Tasks
                    </h4>
                    {tasks.length === 0 ? <p className="text-sm italic text-text-muted">No tasks due</p> : (
                      <ul className="space-y-2">{tasks.map(t => (
                        <li key={t.id} className="flex items-center gap-2 surface-subtle p-3">
                          {t.status === "done" ? <CheckCircle className="h-4 w-4 text-green-400" /> : <Circle className="h-4 w-4 text-primary" />}
                          <span className={`text-sm ${t.status === "done" ? "line-through text-text-muted" : ""}`}>{t.title}</span>
                          {t.priority && <span className={`ml-auto text-xs rounded-full px-2 py-0.5 font-medium
                            ${t.priority === 'high' ? 'bg-red-500/15 text-red-400' : t.priority === 'medium' ? 'bg-primary/15 text-primary' : 'bg-secondary/15 text-secondary'}`}>
                            {t.priority}
                          </span>}
                        </li>
                      ))}</ul>
                    )}
                  </div>

                  {/* Notes */}
                  <div>
                    <h4 className="mb-2 flex items-center gap-2 text-sm font-medium text-text-secondary">
                      <BookText className="h-4 w-4 text-secondary" /> Notes
                    </h4>
                    {notes.length === 0 ? <p className="text-sm italic text-text-muted">No notes</p> : (
                      <ul className="space-y-2">{notes.map(n => (
                        <li key={n.noteId} className="surface-subtle p-3">
                          <p className="text-sm font-medium">📝 {n.title}</p>
                          {n.subject && <p className="text-xs text-text-muted mt-1">{n.subject}</p>}
                        </li>
                      ))}</ul>
                    )}
                  </div>
                </div>
              );
            })() : (
              <p className="text-sm text-text-muted">Click on a day to see details.</p>
            )}
          </div>
        </div>
      </div>

      {/* Add Entry Modal */}
      {showAddModal && selectedDate && (
        <div className="fixed inset-0 z-[500] flex items-center justify-center" style={{ background: 'rgba(10,10,15,0.4)', backdropFilter: 'blur(8px)', animation: 'fadeIn 0.2s ease' }}
          onClick={(e) => { if (e.target === e.currentTarget) setShowAddModal(false); }}
          onKeyDown={(e) => { if (e.key === 'Escape') setShowAddModal(false); }}>
          <div className="glass-card rounded-3xl p-6 w-[90%] max-w-lg" style={{ animation: 'modalIn 0.35s cubic-bezier(0.22,1,0.36,1)' }}>
            <div className="flex items-center justify-between mb-5">
              <h3 className="text-lg font-semibold">Add Entry — {formatDate(selectedDate)}</h3>
              <button onClick={() => setShowAddModal(false)} className="p-1 rounded-lg hover:bg-primary/10 transition"><X className="h-5 w-5" /></button>
            </div>

            {/* Type selector */}
            <div className="flex gap-2 mb-5">
              {[{ val: "task", icon: <Flag className="h-4 w-4" />, label: "Task" },
                { val: "note", icon: <NotebookPen className="h-4 w-4" />, label: "Note" },
                { val: "session", icon: <Clock className="h-4 w-4" />, label: "Session" }
              ].map(t => (
                <button key={t.val} onClick={() => setAddType(t.val)}
                  className={`flex-1 flex items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-medium transition-all
                    ${addType === t.val ? 'bg-primary/15 text-primary border border-primary/30' : 'surface-subtle text-text-secondary hover:bg-primary/5'}`}>
                  {t.icon} {t.label}
                </button>
              ))}
            </div>

            <form onSubmit={handleAddEntry} className="space-y-4">
              <input type="text" value={addTitle} onChange={e => setAddTitle(e.target.value)} required
                placeholder={addType === "session" ? "Subject" : "Title"} className="w-full glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />

              {addType !== "session" && (
                <input type="text" value={addSubject} onChange={e => setAddSubject(e.target.value)} placeholder="Subject (optional)"
                  className="w-full glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              )}

              {addType === "note" && (
                <textarea value={addContent} onChange={e => setAddContent(e.target.value)} placeholder="Content"
                  className="w-full h-24 resize-none glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              )}

              {addType === "task" && (
                <select value={addPriority} onChange={e => setAddPriority(e.target.value)}
                  className="w-full glass-input rounded-xl px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                  <option value="low">Low Priority</option><option value="medium">Medium Priority</option><option value="high">High Priority</option>
                </select>
              )}

              {addType === "session" && (
                <input type="number" value={addMinutes} onChange={e => setAddMinutes(parseInt(e.target.value) || 0)} min="1"
                  placeholder="Minutes studied" className="w-full glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              )}

              {addType !== "session" && (
                <input type="time" value={addTime} onChange={e => setAddTime(e.target.value)}
                  className="w-full glass-input rounded-xl px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
              )}

              <button type="submit"
                className="w-full rounded-xl bg-gradient-primary-accent px-4 py-3 font-medium text-white transition-all hover:scale-[1.02] hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98]">
                Add {addType === "task" ? "Task" : addType === "note" ? "Note" : "Session"}
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}