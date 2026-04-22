import React from 'react';

export default function Home() {
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex flex-col items-center justify-center">
            <header className="flex flex-col items-center justify-center text-center px-4">
                <h1 className="text-5xl font-bold text-gray-900 mb-4">Welcome to Our Site</h1>
                <p className="text-xl text-gray-600 mb-8">Your journey starts here</p>
            </header>
            
            <main className="flex items-center justify-center pb-12">
                <a href="/timer" className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-8 rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                    Start Timer
                </a>
            </main>
        </div>
    );
}