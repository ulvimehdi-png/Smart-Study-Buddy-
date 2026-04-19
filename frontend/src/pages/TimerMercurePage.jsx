import React, {useState} from 'react';
import {createLiveTimerSession, deleteLiveTimerSession, getLiveTimerSession,} from '@/lib/liveTimerSessionApi';

export default function TimerMercurePage() {
    const [sessionId, setSessionId] = useState('');
    const [data, setData] = useState(null);
    const [error, setError] = useState('');

    async function handleCreate() {
        setError('');

        try {
            const created = await createLiveTimerSession({
                work: {minutes: 25, seconds: 0},
                break: {minutes: 5, seconds: 0},
                subjectId: null,
                taskId: null,
            });

            setSessionId(created.id);
            setData(created);
        } catch (err) {
            setError(String(err.message || err));
        }
    }

    async function handleLoad() {
        setError('');

        try {
            const loaded = await getLiveTimerSession(sessionId);
            setData(loaded);
        } catch (err) {
            setError(String(err.message || err));
        }
    }

    async function handleDelete() {
        setError('');

        try {
            await deleteLiveTimerSession(sessionId);
            setData(null);
        } catch (err) {
            setError(String(err.message || err));
        }
    }

    return (<div className="min-h-screen bg-gray-50 flex items-start justify-center p-8">
        <div className="w-full max-w-2xl bg-white shadow rounded-lg p-6 space-y-6">
            <div><h1 className="text-2xl font-semibold">Timer Mercure Test</h1> <p
                className="text-sm text-gray-500"> Simple test UI for live timer session API </p></div>
            {/* Controls */}
            <div className="space-y-3">
                <div className="flex flex-wrap gap-2">
                    <button onClick={handleCreate}
                            className="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700"> Create
                    </button>
                    <button onClick={handleLoad}
                            className="px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800"
                            disabled={!sessionId}> Load
                    </button>
                    <button onClick={handleDelete}
                            className="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700"
                            disabled={!sessionId}> Delete
                    </button>
                </div>
                <input value={sessionId} onChange={(e) => setSessionId(e.target.value)} placeholder="Session ID"
                       className="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
            </div>
            {/* Error */} {error && (<div
            className="bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded"> {error} </div>)} {/* Data */}
            <div><h2 className="text-sm font-medium text-gray-600 mb-2"> Response </h2>
                <pre
                    className="bg-gray-900 text-green-200 text-xs p-4 rounded overflow-auto"> {JSON.stringify(data, null, 2)} </pre>
            </div>
        </div>
    </div>);
}