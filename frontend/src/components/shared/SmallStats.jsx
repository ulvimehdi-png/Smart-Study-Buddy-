import React, { useEffect, useState } from "react";

export default function SmallStats({ icon, iconClass, label, value }) {
  const [show, setShow] = useState(false);
  useEffect(() => { const t = setTimeout(() => setShow(true), 100); return () => clearTimeout(t); }, []);

  return (
    <div className="glass-card flex items-start gap-3 p-4 hover:scale-[1.02]"
         style={{ animation: show ? 'countUp 0.5s cubic-bezier(0.22,1,0.36,1)' : 'none' }}>
      <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${iconClass}`}>
        {icon}
      </div>
      <div>
        <p className="text-sm font-medium text-text-secondary">{label}</p>
        <p className="text-2xl font-bold text-primary" style={{ textShadow: '0 0 20px rgba(255,159,10,0.3)' }}>
          {value}
        </p>
      </div>
    </div>
  );
}