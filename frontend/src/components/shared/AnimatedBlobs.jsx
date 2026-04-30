import React from 'react';

export default function AnimatedBlobs() {
    return (
        <div className="absolute inset-0 overflow-hidden pointer-events-none">
            <div className="absolute -top-40 -right-40 w-96 h-96 rounded-full animate-blob"
                 style={{ background: 'radial-gradient(circle, rgba(255,159,10,0.12) 0%, transparent 70%)' }} />
            <div className="absolute -bottom-40 -left-40 w-96 h-96 rounded-full animate-blob"
                 style={{ animationDelay: '2s', background: 'radial-gradient(circle, rgba(10,132,255,0.08) 0%, transparent 70%)' }} />
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full animate-blob"
                 style={{ animationDelay: '4s', background: 'radial-gradient(circle, rgba(255,107,44,0.06) 0%, transparent 70%)' }} />
        </div>
    );
}
