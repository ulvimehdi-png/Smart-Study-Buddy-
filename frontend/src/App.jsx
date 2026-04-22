import React from "react";
import { Routes, Route } from "react-router-dom";
import Home from "@/pages/Home";
import Login from "@/pages/Login";
import Timer from "@/components/Timer";
import Tasks from "@/pages/Tasks";
import Notes from "@/pages/Notes";
import Dashboard from "@/pages/Dashboard"; // Make sure this points to your new dashboard file
import ProtectedRoute from "./pages/ProtectedRoute";
import TimerPage from "./pages/TimerPage";
import Test from "./pages/Test";
import Layout from "./components/Layout";
import TimerMercurePage from "./pages/TimerMercurePage.jsx";
import Leaderboard from './pages/Leaderboard';

function App() {
    return (
        <>
            <Routes>
                <Route element={<Layout />}>
                    <Route path="/" element={<Home />} />
                    <Route path="/login" element={<Login />} />

                    <Route path="/timer" element={<ProtectedRoute><TimerPage /></ProtectedRoute>} />
                    <Route path="/tasks" element={<ProtectedRoute><Tasks /></ProtectedRoute>} />
                    <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
                    <Route path="/notes" element={<ProtectedRoute><Notes /></ProtectedRoute>} />
                    <Route path="/leaderboard" element={<ProtectedRoute><Leaderboard /></ProtectedRoute>} />

                    <Route path="/test" element={<Test />} />
                    <Route path="/timerMercurePage" element={<ProtectedRoute><TimerMercurePage /></ProtectedRoute>} />
                </Route>
            </Routes>
        </>
    );
}

export default App;