import React, { createContext, useContext, useCallback, useState } from 'react';
import { AnimatePresence, motion } from 'motion/react';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-react';

const ToastContext = createContext(null);

export function ToastProvider({ children }) {
    const [toasts, setToasts] = useState([]);

    const removeToast = useCallback((id) => {
        setToasts(prev => prev.filter(t => t.id !== id));
    }, []);

    const addToast = useCallback((message, type = 'info') => {
        const id = Date.now() + Math.random();
        setToasts(prev => [...prev, { id, message, type }]);
        setTimeout(() => removeToast(id), 3500);
    }, [removeToast]);

    const toast = {
        success: (msg) => addToast(msg, 'success'),
        error: (msg) => addToast(msg, 'error'),
        info: (msg) => addToast(msg, 'info'),
    };

    return (
        <ToastContext.Provider value={toast}>
            {children}
            <div className="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none">
                <AnimatePresence>
                    {toasts.map(t => {
                        const Icon = t.type === 'success' ? CheckCircle2 : t.type === 'error' ? AlertCircle : Info;
                        
                        return (
                            <motion.div
                                layout
                                key={t.id}
                                initial={{ opacity: 0, y: 50, scale: 0.9, filter: "blur(10px)" }}
                                animate={{ opacity: 1, y: 0, scale: 1, filter: "blur(0px)" }}
                                exit={{ opacity: 0, scale: 0.9, filter: "blur(10px)", transition: { duration: 0.2 } }}
                                transition={{ type: "spring", stiffness: 300, damping: 25 }}
                                className={`pointer-events-auto relative overflow-hidden rounded-2xl border border-border-base bg-bg-card/80 px-4 py-3.5 shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex items-center gap-3 min-w-[300px] max-w-[420px]
                                  ${t.type === 'success' ? 'border-green-500/30 text-green-400' : ''}
                                  ${t.type === 'error' ? 'border-red-500/30 text-red-400' : ''}
                                  ${t.type === 'info' ? 'border-primary/30 text-primary' : ''}
                                `}
                                style={{
                                    backdropFilter: 'blur(24px) saturate(180%)',
                                }}
                            >
                                {/* Background ambient glow */}
                                <div className={`absolute inset-0 opacity-[0.08] pointer-events-none
                                    ${t.type === 'success' ? 'bg-green-500' : ''}
                                    ${t.type === 'error' ? 'bg-red-500' : ''}
                                    ${t.type === 'info' ? 'bg-primary' : ''}
                                `} />

                                <Icon className="w-5 h-5 shrink-0" />
                                
                                <span className="text-sm font-medium leading-snug flex-1">
                                    {t.message}
                                </span>

                                <button 
                                    onClick={() => removeToast(t.id)}
                                    className="p-1.5 -mr-1.5 rounded-lg opacity-40 hover:opacity-100 hover:bg-white/10 transition-all shrink-0"
                                >
                                    <X className="w-4 h-4" />
                                </button>
                            </motion.div>
                        );
                    })}
                </AnimatePresence>
            </div>
        </ToastContext.Provider>
    );
}

export function useToast() {
    const ctx = useContext(ToastContext);
    if (!ctx) throw new Error('useToast must be used within ToastProvider');
    return ctx;
}
