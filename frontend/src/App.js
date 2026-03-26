import React, { useState } from 'react';
import './App.css';

function App() {
  const [currentView, setCurrentView] = useState('login'); // 'login', 'register', or 'dashboard'
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  // Important: This tells fetch to send our cookies to the backend
  const fetchOptions = {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'omit' // Default setup. We change to 'include' in the functions below
  };

  const handleRegister = async (e) => {
    e.preventDefault();
    const res = await fetch('http://localhost:8000/register', {
      ...fetchOptions,
      body: JSON.stringify({ username, password })
    });
    if (res.ok) {
      setMessage('Registration successful! Please log in.');
      setCurrentView('login');
    } else {
      setMessage('Error: Username might be taken.');
    }
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    const res = await fetch('http://localhost:8000/login', {
      ...fetchOptions,
      credentials: 'include', // Send cookies!
      body: JSON.stringify({ username, password })
    });
    if (res.ok) {
      setMessage('');
      setCurrentView('dashboard');
    } else {
      setMessage('Invalid username or password.');
    }
  };

  const fetchDashboard = async () => {
    const res = await fetch('http://localhost:8000/dashboard', {
      method: 'GET',
      credentials: 'include' // Send cookies to prove we are logged in!
    });
    if (res.ok) {
      const data = await res.json();
      setMessage(data.message);
    } else {
      setMessage('You are not authorized to view this page.');
      setCurrentView('login');
    }
  };

  const handleLogout = async () => {
    await fetch('http://localhost:8000/logout', {
      ...fetchOptions,
      credentials: 'include'
    });
    setCurrentView('login');
    setMessage('Logged out successfully.');
    setUsername('');
    setPassword('');
  };

  return (
    <div className="container">
      <div className="card">
        <h1>My App</h1>
        {message && <p className="message">{message}</p>}

        {currentView === 'login' && (
          <form onSubmit={handleLogin}>
            <h2>Login</h2>
            <input placeholder="Username" value={username} onChange={e => setUsername(e.target.value)} required />
            <input type="password" placeholder="Password" value={password} onChange={e => setPassword(e.target.value)} required />
            <button type="submit">Log In</button>
            <p onClick={() => setCurrentView('register')} className="link">Need an account? Register</p>
          </form>
        )}

        {currentView === 'register' && (
          <form onSubmit={handleRegister}>
            <h2>Register</h2>
            <input placeholder="Username" value={username} onChange={e => setUsername(e.target.value)} required />
            <input type="password" placeholder="Password" value={password} onChange={e => setPassword(e.target.value)} required />
            <button type="submit">Sign Up</button>
            <p onClick={() => setCurrentView('login')} className="link">Already have an account? Log in</p>
          </form>
        )}

        {currentView === 'dashboard' && (
          <div>
            <h2>Dashboard</h2>
            <button onClick={fetchDashboard} className="action-btn">Fetch Secret Data</button>
            <button onClick={handleLogout} className="logout-btn">Log Out</button>
          </div>
        )}
      </div>
    </div>
  );
}

export default App;