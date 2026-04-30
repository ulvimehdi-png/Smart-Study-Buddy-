import React from "react";
import { cn } from "@/lib/utils";

export default function SubjectBadge({
  subject,
  tone = "primary",
  className,
}) {
  const label = subject?.trim() || "No Subject";

  return (
    <span
      className={cn(
        "inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide",
        tone === "accent"
          ? "border-accent/20 bg-accent/10 text-accent"
          : "border-primary/20 bg-primary/10 text-primary",
        className,
      )}
    >
      {label}
    </span>
  );
}
