import React, { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { CircularProgressbar, buildStyles } from 'react-circular-progressbar';
import 'react-circular-progressbar/dist/styles.css';

// 1. Stateless UI - receives raw numbers for max fluidity
const SharedTimer = ({ percentage, displayTime }) => (
    <div style={{ width: 250, height: 250 }}>
        <CircularProgressbar
            value={percentage}
            text={displayTime}
            strokeWidth={6}
            styles={buildStyles({
                pathColor: '#00d1b2',
                textColor: '#333',
                trailColor: '#f5f5f5',
                // Critical: Disable internal library transitions for manual RAF control
                pathTransition: 'none',
            })}
        />
    </div>
);

export default function App() {
    const DURATION_MS = 1 * 1000; // 60 seconds in ms
    const [pipWindow, setPipWindow] = useState(null);
    const [isPlaying, setIsPlaying] = useState(false);

    // High-precision tracking
    const [msLeft, setMsLeft] = useState(DURATION_MS);
    const requestRef = useRef();
    const previousTimeRef = useRef();

    // 2. The Animation Loop (60fps)
    const animate = (time) => {
        if (previousTimeRef.current !== undefined && isPlaying) {
            const deltaTime = time - previousTimeRef.current;
            setMsLeft((prev) => Math.max(0, prev - deltaTime));
        }
        previousTimeRef.current = time;
        requestRef.current = requestAnimationFrame(animate);
    };

    useEffect(() => {
        requestRef.current = requestAnimationFrame(animate);
        return () => cancelAnimationFrame(requestRef.current);
    }, [isPlaying]);

    // Calculations for UI
    const percentage = (msLeft / DURATION_MS) * 100;
    const secondsLeft = Math.ceil(msLeft / 1000);
    const displayTime = `${Math.floor(secondsLeft / 60)}:${String(secondsLeft % 60).padStart(2, '0')}`;

    const toggleTimer = () => {
        previousTimeRef.current = undefined; // Reset delta on toggle
        setIsPlaying(!isPlaying);
    };

    const resetTimer = () => {
        setIsPlaying(false);
        setMsLeft(DURATION_MS);
    };

    const openPiP = async () => {
        const pw = await window.documentPictureInPicture.requestWindow({ width: 320, height: 350 });

        // Copy Styles (Standard Loop)
        [...document.styleSheets].forEach((ss) => {
            try {
                const style = pw.document.createElement('style');
                style.textContent = [...ss.cssRules].map(r => r.cssText).join('');
                pw.document.head.appendChild(style);
            } catch (e) {
                const link = pw.document.createElement('link');
                link.rel = 'stylesheet'; link.href = ss.href;
                pw.document.head.appendChild(link);
            }
        });

        pw.addEventListener("pagehide", () => setPipWindow(null));
        setPipWindow(pw);
    };

    return (
        <div style={{ textAlign: 'center', fontFamily: 'sans-serif' }}>
            <h1>Fluid Synced Timer</h1>

            <div style={{ display: 'flex', gap: '10px', justifyContent: 'center', marginBottom: '20px' }}>
                <button onClick={toggleTimer}>{isPlaying ? 'Pause' : 'Start'}</button>
                <button onClick={resetTimer}>Reset</button>
                <button onClick={openPiP} disabled={!!pipWindow}>Pop Out</button>
            </div>

            {/* Main Window Render */}
            <div style={{ display: 'inline-block' }}>
                <SharedTimer percentage={percentage} displayTime={displayTime} />
            </div>

            {/* PiP Window Render */}
            {pipWindow && createPortal(
                <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
                    <SharedTimer percentage={percentage} displayTime={displayTime} />
                </div>,
                pipWindow.document.body
            )}
        </div>
    );
}
