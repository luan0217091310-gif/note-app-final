import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import api from '../services/api';

const ActivatePage = () => {
  const { token } = useParams();
  const navigate = useNavigate();
  const [status, setStatus] = useState('loading'); // loading | success | error
  const [message, setMessage] = useState('');

  useEffect(() => {
    const activate = async () => {
      try {
        const res = await api.get(`/auth/activate/${token}`);
        setMessage(res.data.message);
        setStatus('success');
        // Cập nhật lại user trong localStorage nếu đang đăng nhập
        const savedUser = localStorage.getItem('user');
        if (savedUser) {
          const u = JSON.parse(savedUser);
          u.isActive = true;
          localStorage.setItem('user', JSON.stringify(u));
        }
        setTimeout(() => navigate('/'), 3000);
      } catch (err) {
        setMessage(err.response?.data?.message || 'Link kích hoạt không hợp lệ hoặc đã hết hạn.');
        setStatus('error');
      }
    };
    activate();
  }, [token]);

  return (
    <div className="auth-bg">
      <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} className="glass auth-card" style={{ textAlign: 'center' }}>
        {status === 'loading' && <>
          <div className="spinner" />
          <p>Đang kích hoạt tài khoản...</p>
        </>}
        {status === 'success' && <>
          <div style={{ fontSize: '4rem' }}>✅</div>
          <h2 style={{ color: '#22c55e' }}>Kích hoạt thành công!</h2>
          <p>{message}</p>
          <p style={{ opacity: 0.6 }}>Đang chuyển về trang chính...</p>
        </>}
        {status === 'error' && <>
          <div style={{ fontSize: '4rem' }}>❌</div>
          <h2 style={{ color: '#ef4444' }}>Kích hoạt thất bại</h2>
          <p>{message}</p>
          <button className="btn" onClick={() => navigate('/login')} style={{ marginTop: '20px' }}>Về trang đăng nhập</button>
        </>}
      </motion.div>
    </div>
  );
};

export default ActivatePage;
