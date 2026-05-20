import React, {useEffect, useState} from "react";
import {
    Activity,
    AlertTriangle,
    BarChart3,
    Clock,
    Database,
    FileCode2,
    FileText,
    RefreshCw,
    Server,
    ShieldCheck,
    Users,
} from "lucide-react";
import PageBackdrop from "@/components/shared/PageBackdrop";

function formatValue(value) {
    if (value === true) return "OK";
    if (value === false) return "DOWN";
    if (value === null || value === undefined) return "—";
    return String(value);
}

export default function DeveloperPage() {
    const [status, setStatus] = useState(null);
    const [logs, setLogs] = useState([]);
    const [statusMessage, setStatusMessage] = useState("");
    const [logsMessage, setLogsMessage] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const [errorsOnly, setErrorsOnly] = useState(false);

    const loadDevData = async () => {
        setIsLoading(true);
        setStatusMessage("");
        setLogsMessage("");

        try {
            const statusResponse = await fetch("/api/dev/status", {
                credentials: "include",
            });

            const statusData = await statusResponse.json();

            if (!statusResponse.ok) {
                throw new Error(statusData?.error || "Failed to load developer status.");
            }

            setStatus(statusData);
        } catch (error) {
            setStatusMessage(String(error?.message || error));
        }

        try {
            const logsResponse = await fetch(`/api/dev/logs?errorsOnly=${errorsOnly ? "1" : "0"}`, {
                credentials: "include",
            });

            const logsData = await logsResponse.json();

            if (!logsResponse.ok) {
                throw new Error(logsData?.error || "Failed to load logs.");
            }

            setLogs(logsData?.lines || []);
        } catch (error) {
            setLogsMessage(String(error?.message || error));
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        loadDevData();
    }, []);

    const stats = status?.stats || {};

    return (
        <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
            <PageBackdrop/>

            <div className="relative mx-auto max-w-6xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-semibold">Developer Dashboard</h1>
                        <p className="mt-2 text-sm text-text-secondary">
                            Operational status, application metrics, recent logs, and test coverage.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={loadDevData}
                        disabled={isLoading}
                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-5 py-3 font-medium text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <RefreshCw className={`h-4 w-4 ${isLoading ? "animate-spin" : ""}`}/>
                        {isLoading ? "Refreshing..." : "Refresh"}
                    </button>
                </div>

                {statusMessage ? (
                    <div className="mb-6 rounded-3xl border border-danger/20 bg-danger/10 p-4 text-sm text-danger">
                        {statusMessage}
                    </div>
                ) : null}

                <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <div className="space-y-6">
                        <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
                            <div className="mb-6 flex items-start gap-4">
                                <div
                                    className="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                    <Activity className="h-5 w-5"/>
                                </div>
                                <div>
                                    <h2 className="text-lg font-semibold">System Status</h2>
                                    <p className="mt-1 text-sm text-text-secondary">
                                        Basic runtime and infrastructure checks.
                                    </p>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <MetricCard
                                    icon={Server}
                                    label="Environment"
                                    value={status?.environment}
                                />
                                <MetricCard
                                    icon={Database}
                                    label="Database"
                                    value={formatValue(status?.database?.reachable)}
                                    danger={status?.database?.reachable === false}
                                />
                                <MetricCard
                                    icon={ShieldCheck}
                                    label="Debug"
                                    value={status?.debug ? "Enabled" : "Disabled"}
                                    danger={status?.debug === true && status?.environment === "prod"}
                                />
                                <MetricCard
                                    icon={Clock}
                                    label="Server Time"
                                    value={status?.serverTime}
                                    small
                                />
                                <MetricCard
                                    icon={Activity}
                                    label="Mercure"
                                    value={status?.mercure?.reachable ? "OK" : "DOWN"}
                                    danger={status?.mercure?.reachable === false}
                                />

                                <MetricCard
                                    icon={FileText}
                                    label="Uploads"
                                    value={status?.storage?.uploads?.writable ? "Writable" : "Blocked"}
                                    danger={status?.storage?.uploads?.writable === false}
                                />

                                <MetricCard
                                    icon={Users}
                                    label="Admin"
                                    value={status?.currentUser?.username}
                                />
                            </div>
                        </section>

                        <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
                            <div className="mb-6 flex items-start gap-4">
                                <div
                                    className="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent/10 text-accent">
                                    <BarChart3 className="h-5 w-5"/>
                                </div>
                                <div>
                                    <h2 className="text-lg font-semibold">Application Stats</h2>
                                    <p className="mt-1 text-sm text-text-secondary">
                                        Entity counts from the active database.
                                    </p>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                                <MetricCard icon={Users} label="Users" value={stats.users}/>
                                <MetricCard icon={FileText} label="Subjects" value={stats.subjects}/>
                                <MetricCard icon={FileText} label="Tasks" value={stats.tasks}/>
                                <MetricCard icon={FileText} label="Notes" value={stats.notes}/>
                                <MetricCard icon={FileText} label="Sessions" value={stats.studySessions}/>
                            </div>
                        </section>

                        <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
                            <div className="mb-6 flex items-start justify-between gap-4">
                                <div className="flex items-start gap-4">
                                    <div
                                        className="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                        <FileText className="h-5 w-5"/>
                                    </div>
                                    <div>
                                        <h2 className="text-lg font-semibold">Recent Logs</h2>
                                        <p className="mt-1 text-sm text-text-secondary">
                                            Last server log lines returned by the backend.
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => setErrorsOnly((current) => !current)}
                                    className="rounded-2xl border border-border-base bg-bg-elevated px-4 py-2 text-sm text-text-secondary"
                                >
                                    {errorsOnly ? "Show All Logs" : "Show Errors Only"}
                                </button>

                                <span
                                    className="rounded-2xl border border-border-base bg-bg-elevated px-3 py-1 text-xs text-text-secondary">
                  {logs.length} lines
                </span>
                            </div>

                            {logsMessage ? (
                                <div
                                    className="rounded-2xl border border-danger/20 bg-danger/10 p-4 text-sm text-danger">
                                    {logsMessage}
                                </div>
                            ) : (
                                <pre
                                    className="max-h-[520px] overflow-auto rounded-2xl border border-border-base bg-bg-elevated p-4 text-xs leading-6 text-text-secondary">
                  {logs.length ? logs.join("\n") : "No logs loaded."}
                </pre>
                            )}
                        </section>
                    </div>

                    <aside className="space-y-6">
                        <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
                            <div className="flex items-start gap-4">
                                <div
                                    className="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent/10 text-accent">
                                    <FileCode2 className="h-5 w-5"/>
                                </div>
                                <div>
                                    <h2 className="text-lg font-semibold">Coverage</h2>
                                    <p className="mt-1 text-sm text-text-secondary">
                                        Static report generated by CI/CD.
                                    </p>
                                </div>
                            </div>

                            <div className="mt-6 space-y-3">
                                <StatusRow
                                    label="Roles"
                                    value={(status?.currentUser?.roles || []).join(", ") || "—"}
                                />
                                <StatusRow
                                    label="Available"
                                    value={formatValue(status?.coverage?.available)}
                                    danger={status?.coverage?.available === false}
                                />

                                <a
                                    href={status?.coverage?.url || "/upload/coverage/html/index.html"}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex w-full items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 font-medium text-white transition hover:bg-primary-dark"
                                >
                                    <FileCode2 className="h-4 w-4"/>
                                    Open Coverage Report
                                </a>
                            </div>
                        </section>

                        <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
                            <div className="flex items-start gap-4">
                                <div
                                    className="flex h-11 w-11 items-center justify-center rounded-2xl bg-danger/10 text-danger">
                                    <AlertTriangle className="h-5 w-5"/>
                                </div>
                                <div>
                                    <h2 className="text-lg font-semibold">Defence Notes</h2>
                                    <p className="mt-1 text-sm text-text-secondary">
                                        Keep this page read-only and admin-only.
                                    </p>
                                </div>
                            </div>

                            <div className="mt-6 space-y-3 text-sm text-text-secondary">
                                <p>Recommended additions:</p>
                                <ul className="list-inside list-disc space-y-1">
                                    <li>Mercure health</li>
                                    <li>Writable upload/avatar directory check</li>
                                    <li>Latest errors only</li>
                                    <li>Current admin username and roles</li>
                                    <li>Latest registered users</li>
                                </ul>
                            </div>
                        </section>
                        <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
                            <h2 className="text-lg font-semibold">Latest Users</h2>

                            <div className="mt-4 space-y-3">
                                {(status?.latestUsers || []).map((user) => (
                                    <div
                                        key={user.id}
                                        className="rounded-2xl border border-border-base bg-bg-elevated px-4 py-3"
                                    >
                                        <div className="font-medium">{user.username}</div>
                                        <div className="mt-1 text-xs text-text-secondary">
                                            {(user.roles || []).join(", ") || "ROLE_USER"}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    );
}

function MetricCard({icon: Icon, label, value, danger = false, small = false}) {
    return (
        <div className="rounded-3xl border border-border-base bg-bg-elevated p-4">
            <div className="mb-4 flex items-center justify-between gap-3">
                <span className="text-sm font-medium text-text-secondary">{label}</span>
                <Icon className={`h-4 w-4 ${danger ? "text-danger" : "text-primary"}`}/>
            </div>

            <div
                className={`font-semibold ${danger ? "text-danger" : "text-text-main"} ${
                    small ? "break-all text-sm" : "text-2xl"
                }`}
            >
                {formatValue(value)}
            </div>
        </div>
    );
}

function StatusRow({label, value, danger = false}) {
    return (
        <div
            className="flex items-center justify-between rounded-2xl border border-border-base bg-bg-elevated px-4 py-3">
            <span className="text-sm text-text-secondary">{label}</span>
            <span className={`text-sm font-semibold ${danger ? "text-danger" : "text-text-main"}`}>
        {value}
      </span>
        </div>
    );
}