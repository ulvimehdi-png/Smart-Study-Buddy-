import React, { useState } from 'react';
import reactLogo from './assets/react.svg';
import viteLogo from './assets/vite.svg';
import heroImg from './assets/hero.png';
import './App.css';
import Timer from './components/Timer';

function App() {
  // Your Login States
  const [currentView, setCurrentView] = useState('login'); // 'login', 'register', or 'dashboard'
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');
  
  // Colleague's Dashboard State
  const [count, setCount] = useState(0);

  // Your Fetch Options
  const fetchOptions = {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'omit'
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
      credentials: 'include',
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
      credentials: 'include'
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
    <>
      {/* Global Message Display */}
      {message && <p className="message" style={{ textAlign: 'center', marginTop: '20px' }}>{message}</p>}

      {/* --- YOUR LOGIN VIEW --- */}
      {currentView === 'login' && (
        <div className="container" style={{ margin: '0 auto', marginTop: '50px' }}>
          <div className="card">
            <h1>StudyBuddy</h1>
            <form onSubmit={handleLogin}>
              <h2>Login</h2>
              <input placeholder="Username" value={username} onChange={e => setUsername(e.target.value)} required />
              <input type="password" placeholder="Password" value={password} onChange={e => setPassword(e.target.value)} required />
              <button type="submit">Log In</button>
              <p onClick={() => setCurrentView('register')} className="link">Need an account? Register</p>
            </form>
          </div>
        </div>
      )}

      {/* --- YOUR REGISTER VIEW --- */}
      {currentView === 'register' && (
        <div className="container" style={{ margin: '0 auto', marginTop: '50px' }}>
          <div className="card">
            <h1>StudyBuddy</h1>
            <form onSubmit={handleRegister}>
              <h2>Register</h2>
              <input placeholder="Username" value={username} onChange={e => setUsername(e.target.value)} required />
              <input type="password" placeholder="Password" value={password} onChange={e => setPassword(e.target.value)} required />
              <button type="submit">Sign Up</button>
              <p onClick={() => setCurrentView('login')} className="link">Already have an account? Log in</p>
            </form>
          </div>
        </div>
      )}

      {/* --- YOUR COLLEAGUE'S DASHBOARD VIEW --- */}
      {currentView === 'dashboard' && (
        <>
          <section id="center">
            <div>
               <Timer />
            </div>
            
            {/* We added your backend testing buttons here */}
            <div style={{ margin: '20px 0' }}>
              <h2>Welcome to the Dashboard</h2>
              <button onClick={handleLogout} className="logout-btn">Log Out</button>
            </div>
          </section>
        </>
      )}
    </>
  );
}

export default App;