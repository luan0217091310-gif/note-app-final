import React, { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import api from '../services/api';

const ResetPasswordPage = () => {
  const { token } = useParams();
  const navigate = useNavigate();
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (password !== confirmPassword) { setError('Mật khẩu xác nhận không khớp'); return; }
    if (password.length < 6) { setError('Mật khẩu phải có ít nhất 6 ký tự'); return; }
    setLoading(true); setError('');
    try {
      await api.post(`/auth/reset-password/${token}`, { password });
      setSuccess(true);
      setTimeout(() => navigate('/login'), 3000);
    } catch (err) {
      setError(err.response?.data?.message || 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn');
    }
    setLoading(false);
  };

  if (success) return (
    <div className="auth-bg">
      <motion.div initial={{ scale: 0.9 }} animate={{ scale: 1 }} className="glass auth-card" style={{ textAlign: 'center' }}>
        <div style={{ fontSize: '4rem' }}>✅</div>
        <h2 style={{ color: '#22c55e' }}>Đặt lại mật khẩu thành công!</h2>
        <p>Đang chuyển về trang đăng nhập...</p>
      </motion.div>
    </div>
  );

  return (
    <div className="auth-bg">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="glass auth-card">
        <h2 className="auth-title">🔒 Đặt lại mật khẩu</h2>
        {error && <div className="alert-error">{error}</div>}
        <form onSubmit={handleSubmit} className="auth-form">
          <div className="form-group">
            <label>Mật khẩu mới</label>
            <input type="password" placeholder="Tối thiểu 6 ký tự" value={password} onChange={e => setPassword(e.target.value)} required />
          </div>
          <div className="form-group">
            <label>Xác nhận mật khẩu mới</label>
            <input type="password" placeholder="Nhập lại mật khẩu" value={confirmPassword} onChange={e => setConfirmPassword(e.target.value)} required />
          </div>
          <button type="submit" className="btn btn-full" disabled={loading}>
            {loading ? 'Đang xử lý...' : 'Xác nhận đổi mật khẩu'}
          </button>
        </form>
      </motion.div>
    </div>
  );
};

export default ResetPasswordPage;
