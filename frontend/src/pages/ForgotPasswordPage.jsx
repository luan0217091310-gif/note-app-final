import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import api from '../services/api';

const ForgotPasswordPage = () => {
  const [step, setStep] = useState(1); // 1: nhập email, 2: chọn phương thức, 3: đặt mật khẩu mới
  const [email, setEmail] = useState('');
  const [method, setMethod] = useState('link'); // 'link' | 'otp'
  const [otp, setOtp] = useState('');
  const [resetToken, setResetToken] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const sendEmail = async (e) => {
    e.preventDefault();
    setLoading(true); setError('');
    try {
      await api.post('/auth/forgot-password', { email });
      setStep(2);
      setMessage('Email đã được gửi! Kiểm tra hộp thư của bạn.');
    } catch (err) {
      setError(err.response?.data?.message || 'Có lỗi xảy ra');
    }
    setLoading(false);
  };

  const verifyOTP = async (e) => {
    e.preventDefault();
    if (newPassword !== confirmPassword) { setError('Mật khẩu xác nhận không khớp'); return; }
    setLoading(true); setError('');
    try {
      await api.post('/auth/reset-password-otp', { email, otp, newPassword });
      setMessage('Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại.');
      setTimeout(() => navigate('/login'), 2000);
    } catch (err) {
      setError(err.response?.data?.message || 'Mã OTP không đúng hoặc đã hết hạn');
    }
    setLoading(false);
  };

  return (
    <div className="auth-bg">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="glass auth-card">
        <h2 className="auth-title">🔑 Quên mật khẩu</h2>

        {message && <div className="alert-success">{message}</div>}
        {error && <div className="alert-error">{error}</div>}

        {step === 1 && (
          <form onSubmit={sendEmail} className="auth-form">
            <div className="form-group">
              <label>Email tài khoản</label>
              <input type="email" placeholder="your@email.com" value={email} onChange={e => setEmail(e.target.value)} required />
            </div>
            <button type="submit" className="btn btn-full" disabled={loading}>
              {loading ? 'Đang gửi...' : 'Gửi email khôi phục'}
            </button>
            <p className="auth-link"><Link to="/login">← Quay lại đăng nhập</Link></p>
          </form>
        )}

        {step === 2 && (
          <div>
            <p style={{ marginBottom: '20px', opacity: 0.8 }}>Chọn phương thức đặt lại mật khẩu:</p>
            <div style={{ display: 'flex', gap: '10px', marginBottom: '20px' }}>
              <button className={`btn ${method === 'link' ? '' : 'btn-outline'}`} onClick={() => setMethod('link')} style={{ flex: 1 }}>📧 Qua Link</button>
              <button className={`btn ${method === 'otp' ? '' : 'btn-outline'}`} onClick={() => setMethod('otp')} style={{ flex: 1 }}>🔢 Qua OTP</button>
            </div>
            {method === 'link' && (
              <div className="alert-info">
                <p>✅ Đã gửi link đặt lại mật khẩu đến email của bạn.</p>
                <p>Nhấn vào link trong email để đặt mật khẩu mới.</p>
              </div>
            )}
            {method === 'otp' && (
              <form onSubmit={verifyOTP} className="auth-form">
                <div className="form-group">
                  <label>Mã OTP (6 chữ số)</label>
                  <input type="text" placeholder="123456" value={otp} onChange={e => setOtp(e.target.value)} maxLength={6} required />
                </div>
                <div className="form-group">
                  <label>Mật khẩu mới</label>
                  <input type="password" placeholder="••••••••" value={newPassword} onChange={e => setNewPassword(e.target.value)} required />
                </div>
                <div className="form-group">
                  <label>Xác nhận mật khẩu</label>
                  <input type="password" placeholder="••••••••" value={confirmPassword} onChange={e => setConfirmPassword(e.target.value)} required />
                </div>
                <button type="submit" className="btn btn-full" disabled={loading}>
                  {loading ? 'Đang xử lý...' : 'Xác nhận OTP & Đổi mật khẩu'}
                </button>
              </form>
            )}
          </div>
        )}
      </motion.div>
    </div>
  );
};

export default ForgotPasswordPage;
