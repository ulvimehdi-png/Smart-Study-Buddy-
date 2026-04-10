import React from 'react';
import { Routes, Route } from "react-router-dom";
import './App.css'
import Home from '@/pages/Home'
import Login from '@/pages/Login';
import ProtectedRoute from './pages/ProtectedRoute';
import TimerPage from './pages/TimerPage';
import Test from './pages/Test';
import Layout from './components/Layout';


function App() {

  return (
    <>
      <Routes>
        <Route element={<Layout />}>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route
            path="/timer"
            element={
              <ProtectedRoute>
                <TimerPage />
              </ProtectedRoute>
            }
          />
          <Route path="/test" element={<Test />} />
        </Route>
      </Routes>
    </>
  );
}

export default App;