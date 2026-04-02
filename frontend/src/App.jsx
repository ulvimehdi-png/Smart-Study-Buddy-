import React from 'react';
import { Routes, Route } from "react-router-dom";
import './App.css'
import Home from '@/pages/Home'
import Login from '@/pages/Login';
import Timer from '@/components/Timer'
import ProtectedRoute from './pages/ProtectedRoute';


function App() {

  return (
    <>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/login" element={<Login />} />
        <Route
          path="/timer"
          element={
            <ProtectedRoute>
              <Timer />
            </ProtectedRoute>
          }
        />
      </Routes>
    </>
  );
}

export default App;