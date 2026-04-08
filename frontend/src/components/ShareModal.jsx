import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import api from '../services/api';

const ShareModal = ({ note, onClose }) => {
  const [email, setEmail] = useState('');
  const [role, setRole] = useState('read-only');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [shares, setShares] = useState(note.shares || []);
  const [success, setSuccess] = useState('');

  const handleShare = async (e) => {
    e.preventDefault();
    setLoading(true); setError(''); setSuccess('');
    try {
      await api.post(`/notes/${note.id}/share`, { email, role });
      setSuccess(`Đã chia sẻ với ${email}`);
      setEmail('');
      // Refresh shares
      const res = await api.get('/notes');
      const updated = res.data.find(n => n.id === note.id);
      if (updated) setShares(updated.shares || []);
    } catch (err) {
      setError(err.response?.data?.message || 'Không thể chia sẻ');
    }
    setLoading(false);
  };

  const handleRevoke = async (shareId) => {
    if (!window.confirm('Thu hồi quyền chia sẻ này?')) return;
    try {
      await api.delete(`/notes/${note.id}/share/${shareId}`);
      setShares(prev => prev.filter(s => s.id !== shareId));
    } catch (err) {
      setError('Không thể thu hồi quyền');
    }
  };

  const handleChangeRole = async (shareId, newRole) => {
    try {
      // Re-share with new role
      const share = shares.find(s => s.id === shareId);
      await api.post(`/notes/${note.id}/share`, { email: share.sharedWithUser?.email, role: newRole });
      setShares(prev => prev.map(s => s.id === shareId ? { ...s, role: newRole } : s));
    } catch (err) { setError('Không thể thay đổi quyền'); }
  };

  return (
    <div className="overlay">
      <motion.div initial={{ scale: 0.9 }} animate={{ scale: 1 }} className="modal modal-sm">
        <div className="modal-header">
          <span className="modal-title">📤 Chia sẻ ghi chú</span>
          <button className="btn-close" onClick={onClose}>×</button>
        </div>

        <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '16px' }}>
          Chia sẻ "<strong>{note.title || 'Không tiêu đề'}</strong>" với người dùng khác
        </p>

        {error && <div className="alert-error">{error}</div>}
        {success && <div className="alert-success">{success}</div>}

        <form onSubmit={handleShare} style={{ display: 'flex', flexDirection: 'column', gap: '10px', marginBottom: '20px' }}>
          <input type="email" value={email} onChange={e => setEmail(e.target.value)} placeholder="Email người dùng..." required />
          <select value={role} onChange={e => setRole(e.target.value)}>
            <option value="read-only">👁️ Chỉ xem (Read-only)</option>
            <option value="edit">✏️ Có thể chỉnh sửa (Edit)</option>
          </select>
          <button type="submit" className="btn" disabled={loading}>
            {loading ? 'Đang chia sẻ...' : '📤 Chia sẻ'}
          </button>
        </form>

        {/* Active shares */}
        {shares.length > 0 && (
          <div>
            <h4 style={{ marginBottom: '10px', fontSize: '0.9rem', color: 'var(--text-muted)' }}>Đang chia sẻ với:</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
              {shares.map(share => (
                <div key={share.id} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '10px', borderRadius: '8px', background: 'var(--bg-secondary)' }}>
                  <div style={{ flex: 1 }}>
                    <div style={{ fontWeight: 600, fontSize: '0.9rem' }}>{share.sharedWithUser?.displayName || share.sharedWithUser?.email}</div>
                    <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{share.sharedWithUser?.email}</div>
                  </div>
                  <select value={share.role} onChange={e => handleChangeRole(share.id, e.target.value)}
                    style={{ width: 'auto', padding: '4px 8px', fontSize: '0.8rem' }}>
                    <option value="read-only">👁️ Chỉ xem</option>
                    <option value="edit">✏️ Chỉnh sửa</option>
                  </select>
                  <button className="btn btn-danger btn-sm" onClick={() => handleRevoke(share.id)}>Thu hồi</button>
                </div>
              ))}
            </div>
          </div>
        )}
      </motion.div>
    </div>
  );
};

export default ShareModal;
