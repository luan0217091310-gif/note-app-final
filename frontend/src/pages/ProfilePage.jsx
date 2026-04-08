import React, { useContext, useState } from 'react';
import { AuthContext } from '../context/AuthContext';
import api from '../services/api';
import { motion } from 'framer-motion';
import { useNavigate } from 'react-router-dom';

const ProfilePage = () => {
  const { user, setUser, logout } = useContext(AuthContext);
  const [displayName, setDisplayName] = useState(user?.displayName || '');
  const [theme, setTheme] = useState(user?.preferences?.theme || 'light');
  const [fontSize, setFontSize] = useState(user?.preferences?.fontSize || 'medium');
  const [defaultNoteColor, setDefaultNoteColor] = useState(user?.preferences?.defaultNoteColor || '#ffffff');
  const [avatar, setAvatar] = useState(user?.avatar || '');
  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState('');
  const navigate = useNavigate();

  const handleAvatarUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => setAvatar(ev.target.result);
    reader.readAsDataURL(file);
  };

  const handleUpdate = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      const res = await api.put('/auth/profile', {
        displayName,
        avatar,
        preferences: { theme, fontSize, defaultNoteColor }
      });
      setUser(res.data);
      document.documentElement.setAttribute('data-theme', theme);
      document.documentElement.setAttribute('data-fontsize', fontSize);
      setMsg('Đã lưu thay đổi!');
      setTimeout(() => setMsg(''), 3000);
    } catch (err) {
      setMsg('Lỗi khi lưu.');
    }
    setSaving(false);
  };

  return (
    <div className="auth-bg" style={{ alignItems: 'flex-start', paddingTop: '40px' }}>
      <motion.div initial={{ y: 20, opacity: 0 }} animate={{ y: 0, opacity: 1 }} className="glass" style={{ padding: '36px', width: '100%', maxWidth: '540px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '28px' }}>
          <h2 className="auth-title" style={{ margin: 0 }}>👤 Hồ sơ cá nhân</h2>
          <button className="btn btn-ghost btn-sm" onClick={() => navigate('/')}>← Quay lại</button>
        </div>

        {msg && <div className="alert-success">{msg}</div>}

        {/* Avatar */}
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', marginBottom: '28px', gap: '12px' }}>
          <img src={avatar || `https://ui-avatars.com/api/?name=${displayName}&background=4361ee&color=fff&size=120`}
            alt="Avatar"
            style={{ width: 100, height: 100, borderRadius: '50%', objectFit: 'cover', border: '3px solid var(--primary)' }} />
          <label className="btn btn-outline btn-sm" style={{ cursor: 'pointer' }}>
            📷 Đổi ảnh đại diện
            <input type="file" accept="image/*" onChange={handleAvatarUpload} style={{ display: 'none' }} />
          </label>
        </div>

        <form onSubmit={handleUpdate} style={{ display: 'flex', flexDirection: 'column', gap: '18px' }}>
          <div className="form-group">
            <label>Tên hiển thị</label>
            <input value={displayName} onChange={e => setDisplayName(e.target.value)} />
          </div>
          <div className="form-group">
            <label>Email</label>
            <input value={user?.email} disabled style={{ opacity: 0.6 }} />
          </div>
          <div className="form-group">
            <label>Giao diện (Theme)</label>
            <select value={theme} onChange={e => setTheme(e.target.value)}>
              <option value="light">☀️ Sáng (Light)</option>
              <option value="dark">🌙 Tối (Dark)</option>
            </select>
          </div>
          <div className="form-group">
            <label>Cỡ chữ</label>
            <select value={fontSize} onChange={e => setFontSize(e.target.value)}>
              <option value="small">Nhỏ</option>
              <option value="medium">Vừa (Mặc định)</option>
              <option value="large">Lớn</option>
            </select>
          </div>
          <div className="form-group">
            <label>Màu mặc định ghi chú</label>
            <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
              <input type="color" value={defaultNoteColor} onChange={e => setDefaultNoteColor(e.target.value)} style={{ width: '50px', height: '38px', padding: '2px', cursor: 'pointer' }} />
              <span style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>{defaultNoteColor}</span>
            </div>
          </div>
          <button type="submit" className="btn btn-full" disabled={saving}>
            {saving ? 'Đang lưu...' : '💾 Lưu thay đổi'}
          </button>
        </form>

        <hr style={{ margin: '28px 0', opacity: 0.2 }} />
        <div style={{ display: 'flex', justify: 'center' }}>
          <button className="btn btn-danger btn-full" onClick={() => { logout(); navigate('/login'); }}>
            🚪 Đăng xuất
          </button>
        </div>
      </motion.div>
    </div>
  );
};

export default ProfilePage;
