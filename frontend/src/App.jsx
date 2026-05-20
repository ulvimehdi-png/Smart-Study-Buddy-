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
import Calendar from './pages/Calendar';
import DeveloperDashboard from "./pages/Developer.jsx";

// import React, { lazy, Suspense } from "react";
// import { Routes, Route } from "react-router-dom";
//
// const Home = lazy(() => import("@/pages/Home"));
// const Login = lazy(() => import("@/pages/Login"));
// const Tasks = lazy(() => import("@/pages/Tasks"));
// const Notes = lazy(() => import("@/pages/Notes"));
// const Dashboard = lazy(() => import("@/pages/Dashboard"));
// const ProtectedRoute = lazy(() => import("./pages/ProtectedRoute"));
// const TimerPage = lazy(() => import("./pages/TimerPage"));
// const Layout = lazy(() => import("./components/Layout"));
// const Leaderboard = lazy(() => import("./pages/Leaderboard"));
// const UserPage = lazy(() => import("./pages/UserPage"));
// const Reports = lazy(() => import("./pages/Reports"));
// const NotFound = lazy(() => import("./pages/NotFound"));
// const Calendar = lazy(() => import("./pages/Calendar"));
// const DeveloperDashboard = lazy(() => import("./pages/Developer.jsx"));

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
                    <Route path="/calendar" element={<ProtectedRoute><Calendar /></ProtectedRoute>} />

                    <Route path="/developer" element={<ProtectedRoute><DeveloperDashboard /></ProtectedRoute>} />

                    <Route path="*" element={<NotFound />} />
                </Route>
            </Routes>
        </>
    );
}

export default App;
