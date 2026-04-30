import React, { useEffect, useState, useRef, useCallback } from "react";
import { BookText, CalendarDays, NotebookPen, Tags, Trash2, Search, X, Upload, Download, ChevronDown, AlertCircle, Clock } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";
import SmallStats from "../components/shared/SmallStats";
import SubjectBadge from "../components/shared/SubjectBadge";
import { useToast } from "../lib/useToast";
import { parseCSV, mapNoteData } from "../lib/dataUtils";

const formatDate = (v) => new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(new Date(v));
const formatCompactDate = (v) => new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric" }).format(new Date(v));

function highlightText(text, query) {
  if (!query || !text) return text;
  const parts = text.split(new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi'));
  return parts.map((p, i) => p.toLowerCase() === query.toLowerCase() ? <mark key={i} className="bg-primary/30 text-primary rounded px-0.5">{p}</mark> : p);
}

export default function Notes() {
  const [notes, setNotes] = useState([]);
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [subject, setSubject] = useState("");
  const [noteDate, setNoteDate] = useState(new Date().toISOString().slice(0, 10));
  const [noteTime, setNoteTime] = useState(new Date().toTimeString().slice(0, 5));
  const [dateWarning, setDateWarning] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [showExportMenu, setShowExportMenu] = useState(false);
  const [importPreview, setImportPreview] = useState(null);
  const [importData, setImportData] = useState([]);
  const fileRef = useRef(null);
  const debounceRef = useRef(null);
  const toast = useToast();

  const fetchNotes = (query = "") => {
    const url = query ? `/api/notes/search?q=${encodeURIComponent(query)}` : "/api/notes";
    fetch(url, { credentials: "include" }).then(r => r.json()).then(d => setNotes(d)).catch(e => console.error(e));
  };
  useEffect(() => { fetchNotes(); }, []);

  const debouncedSearch = useCallback((q) => {
    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => fetchNotes(q), 300);
  }, []);

  const handleSearchChange = (e) => {
    setSearchQuery(e.target.value);
    debouncedSearch(e.target.value);
  };

  const handleDelete = (id) => fetch(`/api/notes/${id}`, { method: "DELETE", credentials: "include" }).then(r => r.json()).then(() => { fetchNotes(); toast.success("Note deleted"); });

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title || !content) return;
    if (!noteDate) { setDateWarning(true); return; }
    setDateWarning(false);

    const createdAt = noteDate + (noteTime ? `T${noteTime}:00` : `T${new Date().toTimeString().slice(0, 5)}:00`);
    fetch("/api/notes", {
      method: "POST", credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, content, subject, createdAt }),
    }).then(r => r.json()).then(() => {
      setTitle(""); setContent(""); setSubject("");
      setNoteDate(new Date().toISOString().slice(0, 10));
      setNoteTime(new Date().toTimeString().slice(0, 5));
      fetchNotes(); toast.success("Note saved!");
    });
  };

  // Export
  const doExport = (fmt) => {
    setShowExportMenu(false);
    const date = new Date().toISOString().slice(0, 10);
    if (fmt === "json") {
      const blob = new Blob([JSON.stringify(notes, null, 2)], { type: "application/json" });
      const url = URL.createObjectURL(blob); const a = document.createElement("a");
      a.href = url; a.download = `notes_export_${date}.json`; a.click(); URL.revokeObjectURL(url);
    } else {
      if (!notes.length) { toast.error("No notes to export"); return; }
      const headers = ["title", "content", "subject", "createdAt"];
      const csv = [headers.join(","), ...notes.map(r => headers.map(k => `"${String(r[k] ?? "").replace(/"/g, '""')}"`).join(","))].join("\n");
      const blob = new Blob([csv], { type: "text/csv" });
      const url = URL.createObjectURL(blob); const a = document.createElement("a");
      a.href = url; a.download = `notes_export_${date}.csv`; a.click(); URL.revokeObjectURL(url);
    }
    toast.success(`Notes exported as ${fmt.toUpperCase()}`);
  };

  // Import
  const handleImportFile = (e) => {
    const file = e.target.files?.[0]; if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      try {
        let data;
        if (file.name.endsWith(".json")) {
          data = JSON.parse(ev.target.result); if (!Array.isArray(data)) data = [data];
        } else if (file.name.endsWith(".csv")) {
          data = parseCSV(ev.target.result);
        } else { toast.error("Only .json and .csv files supported"); return; }
        if (!data.length) { toast.error("No records found"); return; }
        const mapped = data.map(mapNoteData);
        setImportData(mapped); setImportPreview(mapped.slice(0, 20));
      } catch { toast.error("❌ Invalid file format. Please use a StudyBuddy export file."); }
    };
    reader.readAsText(file); e.target.value = "";
  };

  const confirmImport = async () => {
    let ok = 0;
    for (const item of importData) {
      try {
        await fetch("/api/notes", {
          method: "POST", credentials: "include",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(item),
        }); ok++;
      } catch {}
    }
    toast.success(`✅ Imported ${ok} notes successfully`);
    setImportPreview(null); setImportData([]); fetchNotes();
  };

  const uniqueSubjects = new Set(notes.map(n => n.subject?.trim()).filter(Boolean)).size;
  const latestNote = notes.reduce((a, b) => {
    if (!b.createdAt) return a; if (!a) return b;
    return new Date(b.createdAt) > new Date(a.createdAt) ? b : a;
  }, null);

  const stats = [
    { label: "Saved Notes", value: notes.length, icon: <BookText className="h-5 w-5" />, iconClass: "bg-primary/10 text-primary" },
    { label: "Subjects", value: uniqueSubjects, icon: <Tags className="h-5 w-5" />, iconClass: "bg-secondary/10 text-secondary" },
    { label: "Latest Entry", value: latestNote ? formatCompactDate(latestNote.createdAt) : "--", icon: <CalendarDays className="h-5 w-5" />, iconClass: "bg-accent/10 text-accent" },
  ];

  return (
    <div className="relative min-h-screen overflow-hidden text-text-main page-enter" style={{ background: 'transparent' }}>
      <PageBackdrop />

      <div className="relative mx-auto max-w-7xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8 flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><BookText className="h-5 w-5" /></div>
            <div>
              <h1 className="text-2xl font-semibold sm:text-3xl">Notes</h1>
              <p className="mt-1 text-sm text-text-secondary">Save and organize study notes by subject.</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <button onClick={() => fileRef.current?.click()} className="inline-flex items-center gap-2 rounded-xl glass-card px-4 py-2.5 text-sm font-medium text-secondary hover:border-secondary/30 transition-all">
              <Download className="h-4 w-4" /> Import
            </button>
            <input ref={fileRef} type="file" accept=".json,.csv" onChange={handleImportFile} className="hidden" />
            <div className="relative">
              <button onClick={() => setShowExportMenu(!showExportMenu)} className="inline-flex items-center gap-2 rounded-xl glass-card px-4 py-2.5 text-sm font-medium text-primary hover:border-primary/30 transition-all">
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
          {stats.map(s => <SmallStats key={s.label} {...s} />)}
        </div>

        {/* Search */}
        <div className="mb-6">
          <div className="relative max-w-md">
            <Search className="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-text-muted" />
            <input type="text" value={searchQuery} onChange={handleSearchChange}
              className="w-full glass-input rounded-xl py-3 pl-12 pr-10 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 focus:shadow-[0_0_20px_rgba(255,159,10,0.15)]"
              placeholder="Search notes by title or content..." />
            {searchQuery && (
              <button onClick={() => { setSearchQuery(""); fetchNotes(""); }} className="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-text-muted hover:text-text-main"><X className="h-5 w-5" /></button>
            )}
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-[340px_minmax(0,1fr)]">
          {/* New Note Form */}
          <section className="lg:sticky lg:top-24 lg:self-start">
            <div className="glass-card rounded-3xl p-5">
              <div className="flex items-start gap-4">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><NotebookPen className="h-5 w-5" /></div>
                <div><h2 className="text-lg font-semibold">New Note</h2><p className="mt-1 text-sm text-text-secondary">Add a note without leaving the page.</p></div>
              </div>
              <form onSubmit={handleSubmit} className="mt-5 space-y-4">
                <div><label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Subject</label>
                  <input type="text" value={subject} onChange={e => setSubject(e.target.value)} className="w-full glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="e.g. Math" /></div>
                <div><label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Title *</label>
                  <input type="text" value={title} onChange={e => setTitle(e.target.value)} required className="w-full glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="What should you remember?" /></div>
                <div><label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Content *</label>
                  <textarea value={content} onChange={e => setContent(e.target.value)} required className="h-32 w-full resize-none glass-input rounded-xl px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Write key ideas, definitions, or examples..." /></div>

                <div className="grid grid-cols-2 gap-3">
                  <div><label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Note Date</label>
                    <input type="date" value={noteDate} onChange={e => { setNoteDate(e.target.value); setDateWarning(false); }} className="w-full glass-input rounded-xl px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" /></div>
                  <div><label className="mb-1.5 block text-xs font-medium text-text-secondary uppercase tracking-wide">Time</label>
                    <input type="time" value={noteTime} onChange={e => setNoteTime(e.target.value)} className="w-full glass-input rounded-xl px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" /></div>
                </div>
                {dateWarning && (
                  <div className="flex items-center gap-2 rounded-xl bg-primary/10 border border-primary/20 px-4 py-2 text-sm text-primary">
                    <AlertCircle className="h-4 w-4 shrink-0" /> 📅 Set a date to pin this note to your calendar.
                  </div>
                )}
                <button type="submit" className="w-full rounded-xl bg-gradient-primary-accent px-4 py-3 font-medium text-white transition-all hover:scale-[1.02] hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98]">Save Note</button>
              </form>
            </div>
          </section>

          {/* Notes List */}
          <section>
            {notes.length === 0 ? (
              <div className="empty-state">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary"><BookText className="h-6 w-6" /></div>
                <h2 className="mt-4 text-xl font-semibold">No notes yet</h2>
                <p className="mx-auto mt-2 max-w-md text-sm text-text-secondary">Create a note from the panel on the left.</p>
              </div>
            ) : (
              <div className="grid gap-4 xl:grid-cols-2">
                {notes.map(note => (
                  <article key={note.noteId} className="glass-card rounded-3xl p-5 hover:scale-[1.005]">
                    <div className="flex items-start justify-between gap-4">
                      <div className="min-w-0">
                        <SubjectBadge subject={note.subject} />
                        <h3 className="mt-4 text-xl font-semibold wrap-break-word">{searchQuery ? highlightText(note.title, searchQuery) : note.title}</h3>
                      </div>
                      <button onClick={() => handleDelete(note.noteId)} className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm font-medium text-red-400 transition hover:bg-red-500/20">
                        <Trash2 className="h-4 w-4" /> Delete
                      </button>
                    </div>
                    <div className="mt-3 flex items-center gap-3 text-sm text-text-muted">
                      <span className="inline-flex items-center gap-1.5"><CalendarDays className="h-3.5 w-3.5" /> {formatDate(note.createdAt)}</span>
                      {note.createdAt && note.createdAt.includes("T") && <span className="inline-flex items-center gap-1.5"><Clock className="h-3.5 w-3.5" /> {note.createdAt.split("T")[1]?.slice(0, 5)}</span>}
                    </div>
                    <div className="mt-3 max-h-36 overflow-y-auto pr-1 text-sm leading-7 whitespace-pre-wrap text-text-secondary">
                      {searchQuery ? highlightText(note.content, searchQuery) : note.content}
                    </div>
                  </article>
                ))}
              </div>
            )}
          </section>
        </div>
      </div>

      {/* Import Preview Modal */}
      {importPreview && (
        <div className="fixed inset-0 z-[500] flex items-center justify-center" style={{ background: 'rgba(10,10,15,0.8)', backdropFilter: 'blur(8px)', animation: 'fadeIn 0.2s ease' }}>
          <div className="glass-card rounded-3xl p-6 w-[90%] max-w-2xl max-h-[80vh] overflow-y-auto" style={{ animation: 'modalIn 0.35s cubic-bezier(0.22,1,0.36,1)' }}>
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold">Import Preview — {importData.length} notes</h3>
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
