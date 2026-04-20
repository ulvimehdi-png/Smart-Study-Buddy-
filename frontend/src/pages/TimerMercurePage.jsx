import React, { useMemo, useState } from 'react';
import { formatMs } from '@/features/liveTimerSession/formatMs';
import { useSession } from '@/features/liveTimerSession/useSession';

function Section({ title, subtitle, children }) {
    return (
        <section className="rounded-lg border border-gray-200 bg-white p-4 space-y-4">
            <div>
                <h2 className="text-base font-semibold text-gray-900">{title}</h2>
                {subtitle ? <p className="text-sm text-gray-500">{subtitle}</p> : null}
            </div>
            {children}
        </section>
    );
}

function Label({ children }) {
    return <label className="block text-sm font-medium text-gray-700">{children}</label>;
}

function ConnectionBadge({ status }) {
    const colorClass =
        status === 'open'
            ? 'bg-green-100 text-green-800 border-green-200'
            : status === 'connecting'
                ? 'bg-yellow-100 text-yellow-800 border-yellow-200'
                : status === 'error'
                    ? 'bg-red-100 text-red-800 border-red-200'
                    : 'bg-gray-100 text-gray-700 border-gray-200';

    return (
        <span className={`inline-flex rounded-full border px-2 py-1 text-xs font-medium ${colorClass}`}>
            mercure: {status}
        </span>
    );
}

function DebugBlock({ title, value }) {
    return (
        <div className="space-y-2">
            <div className="text-sm font-medium text-gray-700">{title}</div>
            <pre className="rounded-md bg-gray-950 p-4 text-xs text-green-200 overflow-auto">
                {JSON.stringify(value, null, 2)}
            </pre>
        </div>
    );
}

export default function TimerMercurePage() {
    const {
        sessionId,
        setSessionId,
        projection,
        display,
        error,
        setError,
        isPending,
        connectionStatus,
        joinUrl,
        createSession,
        loadSession,
        deleteSession,
        start,
        pause,
        resume,
        skipPhase,
        resetPhase,
        changeConfig,
        stopAndSave,
    } = useSession();

    const [workMinutes, setWorkMinutes] = useState(25);
    const [breakMinutes, setBreakMinutes] = useState(5);

    const actionDisabled = !sessionId || isPending;

    const progressPercent = useMemo(() => {
        return display ? Math.round(display.progress * 100) : 0;
    }, [display]);

    return (
        <div className="min-h-screen bg-gray-50 p-8">
            <div className="mx-auto w-full max-w-5xl space-y-6">
                <header className="space-y-2">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-950">Live Timer Session</h1>
                            <p className="text-sm text-gray-600">
                                Reference Mercure test page. Built for visibility, debugging, and manual testing.
                            </p>
                        </div>

                        <ConnectionBadge status={connectionStatus} />
                    </div>
                </header>

                {error ? (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 space-y-2">
                        <div className="font-medium">Error</div>
                        <div>{error}</div>
                        <button
                            type="button"
                            onClick={() => setError('')}
                            className="rounded border border-red-300 px-3 py-1 text-xs font-medium"
                        >
                            Clear
                        </button>
                    </div>
                ) : null}

                <div className="grid gap-6 lg:grid-cols-2">
                    <Section
                        title="Session"
                        subtitle="Create, load, or delete a live timer session."
                    >
                        <div className="space-y-2">
                            <Label>Session ID</Label>
                            <input
                                value={sessionId}
                                onChange={(event) => setSessionId(event.target.value)}
                                placeholder="Live timer session ID"
                                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-2">
                                <Label>Work minutes</Label>
                                <input
                                    type="number"
                                    min="0"
                                    value={workMinutes}
                                    onChange={(event) => setWorkMinutes(Number(event.target.value))}
                                    className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label>Break minutes</Label>
                                <input
                                    type="number"
                                    min="0"
                                    value={breakMinutes}
                                    onChange={(event) => setBreakMinutes(Number(event.target.value))}
                                    className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={() => createSession({ workMinutes, breakMinutes })}
                                disabled={isPending}
                                className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Create
                            </button>

                            <button
                                type="button"
                                onClick={() => loadSession()}
                                disabled={!sessionId || isPending}
                                className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Load
                            </button>

                            <button
                                type="button"
                                onClick={deleteSession}
                                disabled={!sessionId || isPending}
                                className="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Delete
                            </button>
                        </div>

                        <div className="space-y-2">
                            <Label>Join URL</Label>
                            <input
                                readOnly
                                value={joinUrl}
                                placeholder="No session loaded"
                                className="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-600"
                            />
                        </div>
                    </Section>

                    <Section
                        title="Actions"
                        subtitle="Manual action surface against the current session."
                    >
                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={start}
                                disabled={actionDisabled}
                                className="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Start
                            </button>

                            <button
                                type="button"
                                onClick={pause}
                                disabled={actionDisabled}
                                className="rounded-md bg-yellow-500 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Pause
                            </button>

                            <button
                                type="button"
                                onClick={resume}
                                disabled={actionDisabled}
                                className="rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Resume
                            </button>

                            <button
                                type="button"
                                onClick={skipPhase}
                                disabled={actionDisabled}
                                className="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Skip phase
                            </button>

                            <button
                                type="button"
                                onClick={resetPhase}
                                disabled={actionDisabled}
                                className="rounded-md bg-orange-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Reset phase
                            </button>
                        </div>

                        <div className="flex flex-wrap items-end gap-3">
                            <div className="space-y-2">
                                <Label>Work minutes</Label>
                                <input
                                    type="number"
                                    min="0"
                                    value={workMinutes}
                                    onChange={(event) => setWorkMinutes(Number(event.target.value))}
                                    className="w-28 rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label>Break minutes</Label>
                                <input
                                    type="number"
                                    min="0"
                                    value={breakMinutes}
                                    onChange={(event) => setBreakMinutes(Number(event.target.value))}
                                    className="w-28 rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <button
                                type="button"
                                onClick={() => changeConfig({ workMinutes, breakMinutes })}
                                disabled={actionDisabled}
                                className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Apply config
                            </button>

                            <button
                                type="button"
                                onClick={() => stopAndSave()}
                                disabled={actionDisabled}
                                className="rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                Stop &amp; Save
                            </button>
                        </div>
                    </Section>
                </div>

                <Section
                    title="Derived live view"
                    subtitle="This is what the page is currently rendering from the backend projection."
                >
                    {display ? (
                        <div className="space-y-4">
                            <div className="rounded-lg bg-gray-950 p-6 text-center text-green-200">
                                <div className="text-5xl font-mono tracking-wider">
                                    {formatMs(display.remainingMs)}
                                </div>

                                <div className="mt-3 text-sm text-green-100">
                                    {display.status} • {display.phase}
                                </div>

                                <div className="mt-4 h-2 overflow-hidden rounded-full bg-gray-800">
                                    <div
                                        className="h-full bg-green-400"
                                        style={{ width: `${progressPercent}%` }}
                                    />
                                </div>

                                <div className="mt-2 text-xs text-green-100">
                                    {progressPercent}% of current phase elapsed
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 text-sm">
                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Session ID</div>
                                    <div className="font-mono break-all text-gray-900">{display.id}</div>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Work duration</div>
                                    <div className="text-gray-900">{formatMs(display.workMs)}</div>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Break duration</div>
                                    <div className="text-gray-900">{formatMs(display.breakMs)}</div>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Current phase duration</div>
                                    <div className="text-gray-900">{formatMs(display.phaseDurationMs)}</div>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Accumulated focus</div>
                                    <div className="text-gray-900">{formatMs(display.accumulatedFocusMs)}</div>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Accumulated break</div>
                                    <div className="text-gray-900">{formatMs(display.accumulatedBreakMs)}</div>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Subject ID</div>
                                    <div className="text-gray-900">{String(display.subjectId ?? 'null')}</div>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-3">
                                    <div className="text-gray-500">Task ID</div>
                                    <div className="text-gray-900">{String(display.taskId ?? 'null')}</div>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">
                            No active projection loaded.
                        </div>
                    )}
                </Section>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Section
                        title="Raw backend projection"
                        subtitle="Direct response/mercure payload."
                    >
                        <DebugBlock title="projection" value={projection} />
                    </Section>

                    <Section
                        title="Derived frontend view"
                        subtitle="Pure computed state used by the page."
                    >
                        <DebugBlock title="display" value={display} />
                    </Section>
                </div>
            </div>
        </div>
    );
}