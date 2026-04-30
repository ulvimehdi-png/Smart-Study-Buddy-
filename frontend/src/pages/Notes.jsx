import React, { useEffect, useState } from "react";
import { BookText, CalendarDays, NotebookPen, Tags, Trash2 } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";
import SmallStats from "../components/shared/SmallStats";
import SubjectBadge from "../components/shared/SubjectBadge";

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
  const [subject, setSubject] = useState("");

  const fetchNotes = () => {
    fetch("/public/api/notes", {
      credentials: "include",
    })
      .then((res) => res.json())
      .then((data) => setNotes(data))
      .catch((err) => console.error("Error fetching notes:", err));
  };

  useEffect(() => {
    fetchNotes();
  }, []);

  const handleDelete = (noteId) => {
    fetch(`/public/api/notes/${noteId}`, {
      method: "DELETE",
      credentials: "include",
    })
      .then((res) => res.json())
      .then(() => fetchNotes())
      .catch((err) => console.error("Error deleting note:", err));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title || !content) return;

    fetch("/public/api/notes", {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, content, subject }),
    })
      .then((res) => res.json())
      .then(() => {
        setTitle("");
        setContent("");
        setSubject("");
        fetchNotes();
      })
      .catch((err) => console.error("Error creating note:", err));
  };

  const uniqueSubjects = new Set(
    notes.map((note) => note.subject?.trim()).filter(Boolean),
  ).size;
  const latestNote = notes.reduce((latest, note) => {
    if (!note.createdAt) return latest;
    if (!latest) return note;
    return new Date(note.createdAt).getTime() >
      new Date(latest.createdAt).getTime()
      ? note
      : latest;
  }, null);

  const stats = [
    {
      label: "Saved Notes",
      value: notes.length,
      icon: <BookText className="h-5 w-5" />,
      iconClass: "bg-primary/10 text-primary",
    },
    {
      label: "Subjects",
      value: uniqueSubjects,
      icon: <Tags className="h-5 w-5" />,
      iconClass: "bg-accent-light/10 text-accent-light",
    },
    {
      label: "Latest Entry",
      value: latestNote ? formatCompactDate(latestNote.createdAt) : "--",
      icon: <CalendarDays className="h-5 w-5" />,
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
                <BookText className="h-5 w-5" />
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
                label={label}
                value={value}
                icon={icon}
                iconClass={iconClass}
              />
            ))}
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-[340px_minmax(0,1fr)]">
          <section className="lg:sticky lg:top-24 lg:self-start">
            <div className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm">
              <div className="flex items-start gap-4">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                  <NotebookPen className="h-5 w-5" />
                </div>
                <div>
                  <h2 className="text-lg font-semibold">New Note</h2>
                  <p className="mt-1 text-sm text-text-secondary">
                    Add a note without leaving the page.
                  </p>
                </div>
              </div>

              <form onSubmit={handleSubmit} className="mt-5 space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-medium text-text-secondary">
                    Subject
                  </label>
                  <input
                    type="text"
                    value={subject}
                    onChange={(e) => setSubject(e.target.value)}
                    className="w-full rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    placeholder="e.g. Math"
                  />
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-text-secondary">
                    Title *
                  </label>
                  <input
                    type="text"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    className="w-full rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    placeholder="What should you remember?"
                    required
                  />
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-text-secondary">
                    Content *
                  </label>
                  <textarea
                    value={content}
                    onChange={(e) => setContent(e.target.value)}
                    className="h-40 w-full resize-none rounded-xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    placeholder="Write the key ideas, definitions, or examples here..."
                    required
                  />
                </div>

                <button
                  type="submit"
                  className="w-full rounded-xl bg-primary px-4 py-3 font-medium text-white transition hover:bg-primary-dark"
                >
                  Save Note
                </button>
              </form>
            </div>
          </section>

          <section>
            {notes.length === 0 ? (
              <div className="rounded-3xl border border-dashed border-border-base bg-bg-card px-6 py-14 text-center shadow-sm">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                  <BookText className="h-6 w-6" />
                </div>
                <h2 className="mt-4 text-xl font-semibold">No notes yet</h2>
                <p className="mx-auto mt-2 max-w-md text-sm text-text-secondary">
                  Create a note from the panel on the left.
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

                      <button
                        onClick={() => handleDelete(note.noteId)}
                        className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm font-medium text-danger transition hover:bg-danger/15"
                      >
                        <Trash2 className="h-4 w-4" />
                        Delete
                      </button>
                    </div>

                    <div className="mt-4 flex items-center gap-2 text-sm text-text-muted">
                      <CalendarDays className="h-4 w-4" />
                      {formatDate(note.createdAt)}
                    </div>

                    <div className="mt-4 max-h-48 overflow-y-auto pr-1 text-sm leading-7 whitespace-pre-wrap text-text-secondary">
                      {note.content}
                    </div>
                  </article>
                ))}
              </div>
            )}
          </section>
        </div>
      </div>
    </div>
  );
}
