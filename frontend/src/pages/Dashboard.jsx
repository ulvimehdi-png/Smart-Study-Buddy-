import React, { useState, useEffect } from 'react';

export default function Dashboard() {
    const [data, setData] = useState(null);
    const userId = 1;

    useEffect(() => {
        fetch(`/api/dashboard?userId=${userId}`)
            .then(res => res.json())
            .then(d => setData(d))
            .catch(err => console.error("Error fetching dashboard:", err));
    }, []);

    if (!data) return <div className="p-8 text-center text-gray-500">Loading Dashboard...</div>;

    return (
        <div className="p-8 max-w-6xl mx-auto bg-gray-50 min-h-screen">
            <h1 className="text-3xl font-bold mb-6 text-gray-800">Dashboard</h1>

            {/* Top Stats Row */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div className="bg-white p-4 rounded-lg shadow text-center border border-gray-100">
                    <h3 className="text-gray-500 text-sm font-semibold">Total Notes</h3>
                    <p className="text-3xl font-bold text-blue-600 mt-2">{data.totalNotes}</p>
                </div>
                <div className="bg-white p-4 rounded-lg shadow text-center border border-gray-100">
                    <h3 className="text-gray-500 text-sm font-semibold">Total Hours</h3>
                    <p className="text-3xl font-bold text-blue-600 mt-2">{data.totalHours}</p>
                </div>
                <div className="bg-white p-4 rounded-lg shadow text-center border border-gray-100">
                    <h3 className="text-gray-500 text-sm font-semibold">Today's Hours</h3>
                    <p className="text-3xl font-bold text-blue-600 mt-2">{data.todayHours}</p>
                </div>
                <div className="bg-white p-4 rounded-lg shadow text-center border border-gray-100">
                    <h3 className="text-gray-500 text-sm font-semibold">Study Streak</h3>
                    <p className="text-3xl font-bold text-blue-600 mt-2">{data.studyStreak} <span className="text-lg">Days</span></p>
                </div>
            </div>

            <div className="grid md:grid-cols-2 gap-8">
                {/* Task Progress Section */}
                <div className="bg-white p-6 rounded-lg shadow border border-gray-100">
                    <h2 className="text-xl font-bold mb-4 text-gray-800">Task Progress</h2>
                    {data.progress && data.progress.length > 0 ? (
                        <div className="space-y-4">
                            {data.progress.map((prog, index) => (
                                <div key={index}>
                                    <div className="flex justify-between text-sm mb-1">
                                        <span className="font-semibold text-gray-700">{prog.subject}</span>
                                        <span className="text-gray-500">{prog.completedTasks} / {prog.totalTasks} Tasks ({prog.progressPercent}%)</span>
                                    </div>
                                    <div className="w-full bg-gray-200 rounded-full h-2.5">
                                        <div
                                            className="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                                            style={{ width: `${prog.progressPercent}%` }}
                                        ></div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-gray-500 italic">No tasks created yet.</p>
                    )}
                </div>

                {/* Recent Notes Section */}
                <div className="bg-white p-6 rounded-lg shadow border border-gray-100">
                    <h2 className="text-xl font-bold mb-4 text-gray-800">Recent Notes</h2>
                    {data.recentNotes && data.recentNotes.length > 0 ? (
                        <div className="space-y-4">
                            {data.recentNotes.map((note) => (
                                <div key={note.noteId} className="border-b border-gray-100 pb-3 last:border-0">
                                    <h4 className="font-semibold text-lg text-gray-800">{note.title}</h4>
                                    <p className="text-xs text-gray-500 mb-2">
                                        {note.subject || "No Subject"} • {new Date(note.createdAt).toLocaleDateString()}
                                    </p>
                                    <p className="text-sm text-gray-600 line-clamp-2">{note.content}</p>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-gray-500 italic">No notes created yet.</p>
                    )}
                </div>
            </div>
        </div>
    );
}