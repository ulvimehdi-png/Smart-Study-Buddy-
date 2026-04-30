import React from 'react';

export default function PageBackdrop() {
    return (
        <div className="pointer-events-none absolute inset-0 overflow-hidden">
            {/* Ambient gradient glow */}
            <div className="absolute inset-x-0 top-0 h-[500px]" style={{
                background: 'radial-gradient(ellipse 80% 50% at 50% -20%, rgba(255,159,10,0.08) 0%, transparent 70%)'
            }} />
            {/* Secondary blue glow */}
            <div className="absolute right-0 top-1/4 h-[400px] w-[400px]" style={{
                background: 'radial-gradient(circle, rgba(10,132,255,0.05) 0%, transparent 70%)'
            }} />
            {/* Top border line */}
            <div className="absolute inset-x-0 top-0 border-b border-border-base opacity-50" />
        </div>
    );
}
