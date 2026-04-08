import React, { createContext, useState, useEffect, useContext } from 'react';
import api from '../services/api';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const checkUser = async () => {
      const token = localStorage.getItem('token');
      if (token) {
        try {
          const res = await api.get('/auth/profile');
          const userData = res.data;
          setUser(userData);
          // Apply preferences
          if (userData.preferences?.theme) document.documentElement.setAttribute('data-theme', userData.preferences.theme);
          if (userData.preferences?.fontSize) document.documentElement.setAttribute('data-fontsize', userData.preferences.fontSize);
        } catch {
          localStorage.removeItem('token');
        }
      }
      setLoading(false);
    };
    checkUser();
  }, []);

  const login = async (email, password) => {
    const res = await api.post('/auth/login', { email, password });
    localStorage.setItem('token', res.data.token);
    setUser(res.data);
    if (res.data.preferences?.theme) document.documentElement.setAttribute('data-theme', res.data.preferences.theme);
    if (res.data.preferences?.fontSize) document.documentElement.setAttribute('data-fontsize', res.data.preferences.fontSize);
  };

  const register = async (email, displayName, password) => {
    const res = await api.post('/auth/register', { email, displayName, password });
    localStorage.setItem('token', res.data.token);
    setUser(res.data);
  };

  const logout = () => {
    localStorage.removeItem('token');
    setUser(null);
    document.documentElement.setAttribute('data-theme', 'light');
  };

  return (
    <AuthContext.Provider value={{ user, setUser, login, register, logout, loading }}>
      {children}
    </AuthContext.Provider>
  );
};
