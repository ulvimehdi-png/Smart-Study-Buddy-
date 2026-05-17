import React, { createContext, useContext, useCallback, useState } from 'react';

const ToastContext = createContext(null);

export function ToastProvider({ children }) {
    const [toasts, setToasts] = useState([]);

    const addToast = useCallback((message, type = 'info') => {
        const id = Date.now() + Math.random();
        setToasts(prev => [...prev, { id, message, type }]);
        setTimeout(() => setToasts(prev => prev.filter(t => t.id !== id)), 3500);
    }, []);

    const toast = {
        success: (msg) => addToast(msg, 'success'),
        error: (msg) => addToast(msg, 'error'),
        info: (msg) => addToast(msg, 'info'),
    };

    return (
        <>
            {React.createElement(ToastContext.Provider, { value: toast }, children)}
            <div className="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none">
                {toasts.map(t => (
                    <div
                        key={t.id}
                        className={`pointer-events-auto border border-border-base bg-bg-card px-5 py-3.5 text-sm font-medium flex items-center gap-3 min-w-[300px] max-w-[420px]
              ${t.type === 'success' ? 'border-green-500/30 text-green-400' : ''}
              ${t.type === 'error' ? 'border-red-500/30 text-red-400' : ''}
              ${t.type === 'info' ? 'border-primary/30 text-primary' : ''}
            `}
                        style={{
                            animation: 'toastSlideIn 0.4s cubic-bezier(0.22,1,0.36,1), toastSlideOut 0.3s ease 3s forwards',
                            backdropFilter: 'blur(24px) saturate(180%)',
                        }}
                    >
                        <span>{t.type === 'success' ? '✅' : t.type === 'error' ? '❌' : 'ℹ️'}</span>
                        <span>{t.message}</span>
                    </div>
                ))}
            </div>
        </>
    );
}

export function useToast() {
    const ctx = useContext(ToastContext);
    if (!ctx) throw new Error('useToast must be used within ToastProvider');
    return ctx;
}
