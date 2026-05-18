import React from "react";

export default function SmallStats({ icon: Icon, iconClass, label, value }) {
  return (
    <div className="flex items-start gap-3 rounded-2xl border border-border-base bg-bg-card p-2 sm:p-4 shadow-sm">
      <div className={`flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-xl ${iconClass}`}>
        <Icon className="w-4 h-4 sm:w-5 sm:h-5" />
      </div>
      <div>
        <p className="text-sm font-medium text-text-secondary">{label}</p>
        <p className="text-2xl font-semibold text-center">{value}</p>
      </div>
    </div>
  );
}