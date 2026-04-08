import React, { useState, useContext } from 'react';
import { AuthContext } from '../context/AuthContext';
import { useNavigate, Link } from 'react-router-dom';
import { motion } from 'framer-motion';

const RegisterPage = () => {
  const [displayName, setDisplayName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { register } = useContext(AuthContext);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (password !== confirmPassword) { setError('Mật khẩu xác nhận không khớp'); return; }
    if (password.length < 6) { setError('Mật khẩu phải có ít nhất 6 ký tự'); return; }
    setLoading(true); setError('');
    try {
      await register(email, displayName, password);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || 'Đăng ký thất bại');
    }
    setLoading(false);
  };

  return (
    <div className="auth-bg">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="glass auth-card">
        <div style={{ textAlign: 'center', marginBottom: '28px' }}>
          <div style={{ fontSize: '3rem', marginBottom: '8px' }}>📝</div>
          <h2 className="auth-title" style={{ display: 'inline-block' }}>Tạo tài khoản</h2>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>Miễn phí, mãi mãi!</p>
        </div>

        {error && <div className="alert-error">{error}</div>}

        <form onSubmit={handleSubmit} className="auth-form">
          <div className="form-group">
            <label>👤 Tên hiển thị</label>
            <input type="text" placeholder="Nguyễn Văn A" value={displayName} onChange={e => setDisplayName(e.target.value)} required />
          </div>
          <div className="form-group">
            <label>📧 Email</label>
            <input type="email" placeholder="your@email.com" value={email} onChange={e => setEmail(e.target.value)} required />
          </div>
          <div className="form-group">
            <label>🔑 Mật khẩu</label>
            <input type="password" placeholder="Tối thiểu 6 ký tự" value={password} onChange={e => setPassword(e.target.value)} required />
          </div>
          <div className="form-group">
            <label>🔑 Xác nhận mật khẩu</label>
            <input type="password" placeholder="Nhập lại mật khẩu" value={confirmPassword} onChange={e => setConfirmPassword(e.target.value)} required />
          </div>
          <button type="submit" className="btn btn-full" disabled={loading}>
            {loading ? 'Đang tạo tài khoản...' : '✨ Đăng ký'}
          </button>
        </form>

        <p className="auth-link">Đã có tài khoản? <Link to="/login">Đăng nhập</Link></p>
      </motion.div>
    </div>
  );
};

export default RegisterPage;
