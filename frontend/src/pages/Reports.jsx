import React, { useEffect, useState } from "react";
import { BarChart3, Star, CheckCircle } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";

export default function Reports() {
  const [data, setData] = useState(null);
  const [period, setPeriod] = useState("daily");

  useEffect(() => {
    fetch(`/api/reports?period=${period}`, { credentials: "include" })
      .then((res) => res.json())
      .then((json) => setData(json))
      .catch((err) => console.error(err));
  }, [period]);

  if (!data) return <div className="p-10 text-center" aria-live="polite">Loading reports...</div>;

  return (
    <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
      <PageBackdrop />

      <div className="relative mx-auto max-w-4xl px-4 pb-12 pt-22 sm:px-6">
        
        <div className="mb-8 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <BarChart3 className="h-5 w-5" aria-hidden="true" />
            </div>
            <h1 className="text-2xl font-semibold sm:text-3xl">My Reports</h1>
          </div>
          
          <div className="flex rounded-lg bg-bg-card p-1 shadow-sm border border-border-base" role="group" aria-label="Select report period">
            {["daily", "weekly", "monthly"].map((p) => (
              <button
                key={p}
                onClick={() => setPeriod(p)}
                aria-pressed={period === p}
                className={`capitalize cursor-pointer px-4 py-2 text-sm font-medium rounded-md transition-all ${
                  period === p ? "bg-primary text-white shadow" : "text-text-secondary hover:text-text-main"
                }`}
              >
                {p}
              </button>
            ))}
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 mb-8">
          <div className="rounded-2xl border border-border-base bg-bg-card p-6 shadow-sm flex items-center gap-4">
            <CheckCircle className="h-10 w-10 text-green-500" aria-hidden="true" />
            <div>
              <h2 className="text-sm font-medium text-text-secondary capitalize">Tasks Finished {period}</h2>
              <p className="text-3xl font-bold">{data.totalTasks}</p>
            </div>
          </div>
          <div className="rounded-2xl border border-border-base bg-bg-card p-6 shadow-sm flex items-center gap-4">
            <Star className="h-10 w-10 text-accent" aria-hidden="true" />
            <div>
              <h2 className="text-sm font-medium text-text-secondary capitalize">Points Earned {period}</h2>
              <p className="text-3xl font-bold">{data.pointsEarned}</p>
            </div>
          </div>
        </div>

        <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
          <h3 className="text-xl font-semibold mb-6">Subject Breakdown</h3>
          
          {data.subjects.length === 0 ? (
            <p className="text-center text-text-secondary py-8">No tasks finished in this timeframe yet.</p>
          ) : (
            <ul className="space-y-4">
              {data.subjects.map((item, index) => (
                <li key={index} className="flex items-center justify-between border-b border-border-base pb-3 last:border-0 last:pb-0">
                  <span className="font-medium text-text-main">{item.subject}</span>
                  <span className="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">
                    {item.count} task{item.count !== 1 && 's'}
                  </span>
                </li>
              ))}
            </ul>
          )}
        </section>

      </div>
    </div>
  );
}