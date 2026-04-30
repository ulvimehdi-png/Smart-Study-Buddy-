import React from "react";
import { cn } from "@/lib/utils";

export default function FeatureCard({ icon: Icon, title, description, color }) {
  return (
    <div className="glass-card rounded-2xl p-6 transition-all duration-300 hover:border-primary/20 hover:scale-[1.03] hover:shadow-lg hover:shadow-primary/10 group">
      <div className={cn("w-14 h-14 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300", color || "bg-primary/15")}>
        <Icon className="w-7 h-7 text-white" />
      </div>
      <h3 className="text-lg font-semibold mb-2 group-hover:text-primary transition-colors">{title}</h3>
      <p className="text-text-secondary text-sm leading-relaxed">{description}</p>
    </div>
  );
}
