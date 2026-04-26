import React from 'react';

export default function PageBackdrop() {
    return (
        <div className="pointer-events-none absolute inset-0 overflow-hidden">
            <div className="absolute inset-x-0 top-0 h-40 bg-linear-to-b from-primary/5 via-bg-elevated/40 to-transparent" />
            <div className="absolute inset-x-0 top-0 border-b border-border-base/60" />
        </div>
    );
}
