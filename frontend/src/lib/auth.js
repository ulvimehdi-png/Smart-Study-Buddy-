import { useState, useCallback, createContext, useContext, createElement, useEffect } from 'react';

const AuthContext = createContext(null);
const DEBUG = false;

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [authChecked, setAuthChecked] = useState(false);

    const getUser = useCallback(async () => {
        if (DEBUG) {
            const mockUser = { username: 'testuser' };
            setUser(mockUser);
            setAuthChecked(true);
            return mockUser;
        }
        setLoading(true);
        try {
            const response = await fetch('/public/api/user', { credentials: 'include' });
            if (!response.ok) {
                setUser(null);
                return null;
            }
            const data = await response.json();
            setUser(data.user);
            return data.user;
        } catch {
            setUser(null);
            return null;
        } finally {
            setLoading(false);
            setAuthChecked(true);
        }
    }, []);

    useEffect(() => {
        getUser();
    }, [getUser]);

    const login = useCallback(async (email, password) => {
        if (DEBUG) {
            const mockUser = { username: 'testuser' };
            setUser(mockUser);
            return mockUser;
        }
        setLoading(true);
        setError(null);
        try {
            const response = await fetch('/public/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ username: email, password }),
            });
            if (!response.ok) throw new Error('Login failed');
            const data = await response.json();
            setUser(email);
            return data;
        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    const register = useCallback(async (username, password) => {
        if (DEBUG) {
            const mockUser = { username: 'testuser' };
            setUser(mockUser);
            return mockUser;
        }
        setLoading(true);
        setError(null);
        try {
            const response = await fetch('/public/api/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ username, password }),
            });
            if (!response.ok) throw new Error('Registration failed');
            const data = await response.json();
            return data;
        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    const logout = useCallback(async () => {
        if (DEBUG) {
            setUser(null);
            return;
        }
        setLoading(true);
        setError(null);
        try {
            const response = await fetch('/public/api/logout', {
                method: 'POST',
                credentials: 'include',
            });
            if (!response.ok) throw new Error('Logout failed');
        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setUser(null);
            setLoading(false);
        }
    }, []);

    return createElement(
        AuthContext.Provider,
        { value: { user, isAuthenticated: !!user, loading, error, authChecked, login, register, logout, getUser } },
        children
    );
}

export function useAuth() {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }

    return context;
}