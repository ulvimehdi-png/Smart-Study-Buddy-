import React, { useEffect, useState } from "react";
import { BookText, CalendarDays, NotebookPen, Tags, Trash2 } from "lucide-react";
import {
  deleteSubjectOption,
  fetchSubjectOptions,
  NEW_SUBJECT_VALUE,
} from "@/lib/subjectOptions";
import PageBackdrop from "../components/shared/PageBackdrop";
import SmallStats from "../components/shared/SmallStats";
import SubjectBadge from "../components/shared/SubjectBadge";
import SubjectSelect from "../components/shared/SubjectSelect";
import TiptapEditor, { TiptapViewer } from "../components/TiptapEditor";

const formatDate = (value) =>
  new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(
    new Date(value),
  );

const formatCompactDate = (value) =>
  new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric" }).format(
    new Date(value),
  );

export default function Notes() {
  const [notes, setNotes] = useState([]);
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [editingNoteId, setEditingNoteId] = useState(null);
  const [selectedSubject, setSelectedSubject] = useState("");
  const [customSubject, setCustomSubject] = useState("");
  const [subjectOptions, setSubjectOptions] = useState([]);
  const [isSaving, setIsSaving] = useState(false);

  const fetchNotes = () => {
    fetch("/api/notes", { credentials: "include" })
      .then((res) => res.json())
      .then((data) => setNotes(data))
      .catch((err) => console.error("Error fetching notes:", err));
  };

  const fetchSubjects = () => {
    fetchSubjectOptions()
      .then((data) => setSubjectOptions(data))
      .catch((err) => console.error("Error fetching subjects:", err));
  };

  useEffect(() => {
    fetchNotes();
    fetchSubjects();
  }, []);

  useEffect(() => {
    if (!editingNoteId) return;

    const strippedContent = content.replace(/<[^>]*>/g, "").trim();
    if (!title || !strippedContent) return;

    const timeoutId = setTimeout(() => {
      setIsSaving(true);
      const subject =
        selectedSubject === NEW_SUBJECT_VALUE
          ? customSubject.trim()
          : selectedSubject;

      fetch(`/api/notes/${editingNoteId}`, {
        method: "PUT",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ title, content, subject }),
      })
        .then(() => {
          setIsSaving(false);
          refreshData();
        })
        .catch((err) => {
          console.error("Error auto-saving note:", err);
          setIsSaving(false);
        });
    }, 1000);

    return () => clearTimeout(timeoutId);
  }, [title, content, selectedSubject, customSubject, editingNoteId]);

  const refreshData = () => {
    fetchNotes();
    fetchSubjects();
  };

  const handleDelete = (noteId) => {
    fetch(`/api/notes/${noteId}`, {
      method: "DELETE",
      credentials: "include",
    })
      .then(() => fetchNotes())
      .catch((err) => console.error("Error deleting note:", err));
  };

  const handleEdit = (note) => {
    setEditingNoteId(note.noteId);
    setTitle(note.title);
    setContent(note.content);

    const subjectExists = subjectOptions.some((opt) => opt.name === note.subject);
    if (note.subject && !subjectExists) {
      setSelectedSubject(NEW_SUBJECT_VALUE);
      setCustomSubject(note.subject);
    } else {
      setSelectedSubject(note.subject || "");
      setCustomSubject("");
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const cancelEdit = () => {
    setEditingNoteId(null);
    setTitle("");
    setContent("");
    setSelectedSubject("");
    setCustomSubject("");
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const strippedContent = content.replace(/<[^>]*>/g, "").trim();
    if (!title || !strippedContent) return;

    const subject =
      selectedSubject === NEW_SUBJECT_VALUE
        ? customSubject.trim()
        : selectedSubject;

    const url = editingNoteId ? `/api/notes/${editingNoteId}` : "/api/notes";
    const method = editingNoteId ? "PUT" : "POST";

    fetch(url, {
      method,
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, content, subject }),
    })
      .then(() => {
        setIsSaving(false);
        cancelEdit();
        refreshData();
      })
      .catch((err) => {
        console.error("Error saving note:", err);
        setIsSaving(false);
      });
  };

  const handleRemoveSubject = async (option) => {
    await deleteSubjectOption(option.id);

    if (selectedSubject === option.name) {
      setSelectedSubject("");
    }

    refreshData();
  };

  const uniqueSubjects = new Set(
    notes.map((note) => note.subject?.trim()).filter(Boolean),
  ).size;

  const latestNote = notes.reduce((latest, note) => {
    if (!note.createdAt) return latest;
    if (!latest) return note;
    return new Date(note.createdAt).getTime() > new Date(latest.createdAt).getTime()
      ? note
      : latest;
  }, null);

  const stats = [
    {
      label: "Saved Notes",
      value: notes.length,
      icon: <BookText className="h-5 w-5" aria-hidden="true" />,
      iconClass: "bg-primary/10 text-primary",
    },
    {
      label: "Subjects",
      value: uniqueSubjects,
      icon: <Tags className="h-5 w-5" aria-hidden="true" />,
      iconClass: "bg-accent-light/10 text-accent-light",
    },
    {
      label: "Latest Entry",
      value: latestNote ? formatCompactDate(latestNote.createdAt) : "--",
      icon: <CalendarDays className="h-5 w-5" aria-hidden="true" />,
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
                <BookText className="h-5 w-5" aria-hidden="true" />
              </div>
              <div>
                <h1 className="text-2xl font-semibold sm:text-3xl">Notes</h1>
                <p className="mt-1 text-sm text-text-secondary">
                  Save and organize study notes by subject.
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

        <section className="mb-8">
          <div className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm sm:p-6">
            <div className="flex items-start gap-4 mb-5">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <NotebookPen className="h-5 w-5" aria-hidden="true" />
              </div>
              <div>
                <h2 className="text-lg font-semibold">
                  {editingNoteId ? "Edit Note" : "New Note"}
                </h2>
                <p className="mt-1 text-sm text-text-secondary">
                  {editingNoteId
                    ? "Update your saved note."
                    : "Write rich notes with formatting, lists, links, and more."}
                </p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <div>
                  <label htmlFor="noteTitleInput" className="mb-2 block text-sm font-medium text-text-secondary">
                    Title *
                  </label>
                  <input
                    id="noteTitleInput"
                    type="text"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    className="w-full rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    placeholder="What should you remember?"
                    required
                  />
                </div>

                <div>
                  <label htmlFor="noteSubjectSelect" className="mb-2 block text-sm font-medium text-text-secondary">
                    Subject
                  </label>
                  <div id="noteSubjectSelect">
                    <SubjectSelect
                      options={subjectOptions}
                      value={selectedSubject}
                      onChange={setSelectedSubject}
                      customSubject={customSubject}
                      onCustomSubjectChange={setCustomSubject}
                      onRemoveOption={handleRemoveSubject}
                    />
                  </div>
                </div>
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-text-secondary">
                  Content *
                </label>
                <TiptapEditor
                  key={`editor-${editingNoteId || "new"}-${notes.length}`}
                  content={content}
                  onUpdate={setContent}
                  placeholder="Start writing your notes here..."
                  minHeight="280px"
                />
              </div>

              <div className="flex justify-end gap-3">
                {editingNoteId && (
                  <button
                    type="button"
                    onClick={cancelEdit}
                    className="rounded-xl border border-border-base bg-bg-elevated px-6 py-3 font-medium text-text-main transition hover:bg-bg-hover cursor-pointer"
                  >
                    Cancel
                  </button>
                )}
                <button
                  type="submit"
                  disabled={isSaving}
                  className="rounded-xl bg-primary px-8 py-3 font-medium text-white transition hover:bg-primary-dark cursor-pointer disabled:opacity-70 disabled:cursor-wait"
                >
                  {isSaving
                    ? "Saving..."
                    : editingNoteId
                    ? "Update Note"
                    : "Save Note"}
                </button>
              </div>
            </form>
          </div>
        </section>

        <section>
          {notes.length === 0 ? (
            <div className="rounded-3xl border border-dashed border-border-base bg-bg-card px-6 py-14 text-center shadow-sm">
              <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <BookText className="h-6 w-6" aria-hidden="true" />
              </div>
              <h2 className="mt-4 text-xl font-semibold">No notes yet</h2>
              <p className="mx-auto mt-2 max-w-md text-sm text-text-secondary">
                Create a note using the editor above.
              </p>
            </div>
          ) : (
            <div className="grid gap-4 xl:grid-cols-2">
              {notes.map((note) => (
                <article
                  key={note.noteId}
                  className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm"
                >
                  <div className="flex items-start justify-between gap-4">
                    <div className="min-w-0">
                      <SubjectBadge subject={note.subject} />
                      <h3 className="mt-4 text-xl font-semibold wrap-break-word">
                        {note.title}
                      </h3>
                    </div>

                    <div className="flex gap-2">
                      <button
                        onClick={() => handleEdit(note)}
                        className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-border-base bg-bg-elevated px-3 py-2 text-sm font-medium text-text-main transition hover:bg-bg-hover cursor-pointer"
                      >
                        <NotebookPen className="h-4 w-4" aria-hidden="true" />
                        Edit
                      </button>
                      <button
                        onClick={() => handleDelete(note.noteId)}
                        className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm font-medium text-danger transition hover:bg-danger/15 cursor-pointer"
                      >
                        <Trash2 className="h-4 w-4" aria-hidden="true" />
                        Delete
                      </button>
                    </div>
                  </div>
                  <div className="mt-4 flex items-center gap-2 text-sm text-text-muted">
                    <CalendarDays className="h-4 w-4" aria-hidden="true" />
                    {formatDate(note.createdAt)}
                  </div>

                  <div className="mt-4 max-h-64 overflow-y-auto pr-1">
                    <TiptapViewer content={note.content} />
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