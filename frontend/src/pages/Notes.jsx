import React, { useState, useEffect } from 'react';

export default function Notes() {
    const [notes, setNotes] = useState([]);
    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [subject, setSubject] = useState('');
    const userId = 1; // Keeping it hardcoded to 1 for now, just like the dashboard

    // 1. Grab notes from the backend
    const fetchNotes = () => {
        fetch(`http://127.0.0.1:8000/api/notes?userId=${userId}`)
            .then(res => res.json())
            .then(data => setNotes(data))
            .catch(err => console.error("Error fetching notes:", err));
    };

    useEffect(() => {
        fetchNotes();
    }, []);

    // 2. Delete a note
    const handleDelete = (noteId) => {
        fetch(`http://127.0.0.1:8000/api/notes/${noteId}?userId=${userId}`, {
            method: 'DELETE',
        })
            .then(res => res.json())
            .then(() => fetchNotes()) // Refresh the list after deleting
            .catch(err => console.error("Error deleting note:", err));
    };

    // 3. Create a new note
    const handleSubmit = (e) => {
        e.preventDefault();
        if (!title || !content) return;

        fetch('http://127.0.0.1:8000/api/notes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userId, title, content, subject })
        })
            .then(res => res.json())
            .then(() => {
                // Clear the form and refresh the notes list
                setTitle('');
                setContent('');
                setSubject('');
                fetchNotes();
            })
            .catch(err => console.error("Error creating note:", err));
    };

    return (
        <div className="p-8 max-w-6xl mx-auto bg-gray-50 min-h-screen">
            <h1 className="text-3xl font-bold mb-6 text-gray-800">My Study Notes</h1>

            <div className="grid md:grid-cols-3 gap-8">

                {/* Left Column: Create Note Form */}
                <div className="md:col-span-1">
                    <div className="bg-white p-6 rounded-lg shadow border border-gray-100">
                        <h2 className="text-xl font-bold mb-4 text-gray-800">New Note</h2>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input
                                    type="text"
                                    value={subject}
                                    onChange={(e) => setSubject(e.target.value)}
                                    className="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g. Math"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                <input
                                    type="text"
                                    value={title}
                                    onChange={(e) => setTitle(e.target.value)}
                                    className="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                                <textarea
                                    value={content}
                                    onChange={(e) => setContent(e.target.value)}
                                    className="w-full p-2 border border-gray-300 rounded h-32 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                />
                            </div>
                            <button
                                type="submit"
                                className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200"
                            >
                                Save Note
                            </button>
                        </form>
                    </div>
                </div>

                {/* Right Column: List of Notes */}
                <div className="md:col-span-2 space-y-4">
                    {notes.length === 0 ? (
                        <p className="text-gray-500 italic">No notes found. Create one!</p>
                    ) : (
                        notes.map(note => (
                            <div key={note.noteId} className="bg-white p-6 rounded-lg shadow border border-gray-100 relative group">
                                <div className="flex justify-between items-start mb-2">
                                    <div>
                    <span className="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mb-2 font-semibold">
                      {note.subject || 'General'}
                    </span>
                                        <h3 className="text-xl font-bold text-gray-800">{note.title}</h3>
                                    </div>

                                    {/* Delete Button */}
                                    <button
                                        onClick={() => handleDelete(note.noteId)}
                                        className="text-red-500 hover:text-red-700 font-bold px-2 py-1 rounded border border-red-200 hover:bg-red-50 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>

                                <p className="text-sm text-gray-500 mb-4">{new Date(note.createdAt).toLocaleDateString()}</p>

                                {/* The 'max-h-40 overflow-y-auto' classes prevent giant notes from taking up the whole screen */}
                                <div className="text-gray-700 whitespace-pre-wrap max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                                    {note.content}
                                </div>
                            </div>
                        ))
                    )}
                </div>

            </div>
        </div>
    );
}