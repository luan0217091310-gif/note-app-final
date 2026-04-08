import React, { useState, useContext } from 'react';
import { AuthContext } from '../context/AuthContext';
import { useNavigate, Link } from 'react-router-dom';
import { motion } from 'framer-motion';

const LoginPage = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useContext(AuthContext);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setError('');
    try {
      await login(email, password);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || 'Email hoặc mật khẩu không đúng');
    }
    setLoading(false);
  };

  return (
    <div className="auth-bg">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="glass auth-card">
        <div style={{ textAlign: 'center', marginBottom: '28px' }}>
          <div style={{ fontSize: '3rem', marginBottom: '8px' }}>📝</div>
          <h2 className="auth-title" style={{ display: 'inline-block' }}>Đăng nhập</h2>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>Chào mừng bạn trở lại!</p>
        </div>

        {error && <div className="alert-error">{error}</div>}

        <form onSubmit={handleSubmit} className="auth-form">
          <div className="form-group">
            <label>📧 Email</label>
            <input type="email" placeholder="your@email.com" value={email} onChange={e => setEmail(e.target.value)} required autoFocus />
          </div>
          <div className="form-group">
            <label>🔑 Mật khẩu</label>
            <input type="password" placeholder="••••••••" value={password} onChange={e => setPassword(e.target.value)} required />
          </div>
          <div style={{ textAlign: 'right' }}>
            <Link to="/forgot-password" style={{ color: 'var(--primary)', fontSize: '0.85rem', textDecoration: 'none' }}>Quên mật khẩu?</Link>
          </div>
          <button type="submit" className="btn btn-full" disabled={loading}>
            {loading ? 'Đang đăng nhập...' : '🚀 Đăng nhập'}
          </button>
        </form>

        <p className="auth-link">Chưa có tài khoản? <Link to="/register">Đăng ký ngay</Link></p>
      </motion.div>
    </div>
  );
};

export default LoginPage;
