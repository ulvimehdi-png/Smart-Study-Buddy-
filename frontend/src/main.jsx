import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from "react-router-dom";
import './index.css'
import App from './App.jsx'
import { AuthProvider } from './lib/auth'
import { TimerProvider } from './lib/useTimer.js';
import { ThemeProvider } from './lib/useTheme.js';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <AuthProvider>
      <TimerProvider>
        <ThemeProvider>
          <BrowserRouter basename={process.env.BASE_URL}>
            <App />
          </BrowserRouter>
        </ThemeProvider>
      </TimerProvider>
    </AuthProvider>
  </StrictMode>,
)
