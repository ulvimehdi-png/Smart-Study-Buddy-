import React, { useState } from 'react';
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from '../lib/auth';


export default function Login() {

  const navigate = useNavigate();
  const location = useLocation();
  const [currentView, setCurrentView] = useState('login'); // 'login', 'register', or 'dashboard'
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  const auth = useAuth();

  const from = location.state?.from?.pathname || "/timer";
  
    const handleSubmit = (e) => {
      e.preventDefault();
      if (currentView === 'register') {
        handleRegister();
      } else {
        handleLogin();
      }
    }


    const handleRegister = async () => {
      try {
        await auth.register(username, password);
        setMessage('Registration successful! Please log in.');
        setCurrentView('login');
      } catch {
        setMessage('Error: Username might be taken.');
      }
    };
  
    const handleLogin = async () => {
      try {
        await auth.login(username, password);
        setMessage('');
        navigate(from, { replace: true });
      } catch {
        setMessage('Invalid username or password.');
      }
    };

  
  return (
    <div className="container" style={{ margin: '0 auto', marginTop: '50px' }}>
      <div className="card">
        <h1>StudyBuddy</h1>
        {(message || auth.error) && <p className="message">{message || auth.error}</p>}
        <form onSubmit={handleSubmit}>
          <h2>{currentView === 'register' ? 'Register' : 'Login'}</h2>
          <input placeholder="Username" value={username} onChange={e => setUsername(e.target.value)} required />
          <input type="password" placeholder="Password" value={password} onChange={e => setPassword(e.target.value)} required />
          <button type="submit" disabled={auth.loading}>
            {auth.loading ? 'Please wait...' : currentView === 'register' ? 'Sign Up' : 'Log In'}
          </button>
          {currentView === 'register' ? (
            <p onClick={() => setCurrentView('login')} className="link">Already have an account? Log in</p>
          ) : (
            <p onClick={() => setCurrentView('register')} className="link">Need an account? Register</p>
          )}
        </form>
      </div>
    </div>
  );
}