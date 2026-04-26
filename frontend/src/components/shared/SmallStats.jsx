import React from "react";

export default function SmallStats({ icon, iconClass, label, value }) {
  return (
    <div className="flex items-start gap-3 rounded-2xl border border-border-base bg-bg-card p-4 shadow-sm">
      <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${iconClass}`}>
        {icon}
      </div>
      <div>
        <p className="text-sm font-medium text-text-secondary">{label}</p>
        <p className="text-2xl font-semibold text-center">{value}</p>
      </div>
    </div>
  );
}