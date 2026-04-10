import React from 'react';
import { Routes, Route } from "react-router-dom";
import './App.css';
import Home from '@/pages/Home';
import Login from '@/pages/Login';
import Timer from '@/components/Timer';
import Tasks from '@/pages/Tasks';
import Notes from '@/pages/Notes'; 
import Dashboard from '@/pages/Dashboard'; // Make sure this points to your new dashboard file
import ProtectedRoute from './pages/ProtectedRoute';

function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/login" element={<Login />} />
      
      <Route path="/tasks" element={<ProtectedRoute><Tasks /></ProtectedRoute>} />
      <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
      <Route path="/timer" element={<ProtectedRoute><Timer /></ProtectedRoute>} />
      <Route path="/notes" element={<ProtectedRoute><Notes /></ProtectedRoute>} /> 
    </Routes>
  );
}

export default App;