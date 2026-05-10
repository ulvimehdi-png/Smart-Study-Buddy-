import React from "react";
import { Routes, Route } from "react-router-dom";
import Home from "@/pages/Home";
import Login from "@/pages/Login";
import Tasks from "@/pages/Tasks";
import Notes from "@/pages/Notes";
import Dashboard from "@/pages/Dashboard";
import ProtectedRoute from "./pages/ProtectedRoute";
import TimerPage from "./pages/TimerPage";
import Layout from "./components/Layout";
import Leaderboard from './pages/Leaderboard';
import UserPage from "./pages/UserPage";
import Reports from './pages/Reports';
import NotFound from "./pages/NotFound";

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
                    <Route path="/user" element={<ProtectedRoute><UserPage /></ProtectedRoute>} />
                    <Route path="/reports" element={<ProtectedRoute><Reports /></ProtectedRoute>} />

                    <Route path="*" element={<NotFound />} />
                </Route>
            </Routes>
        </>
    );
}

export default App;
