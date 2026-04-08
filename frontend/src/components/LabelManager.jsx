import React, { useState } from 'react';
import { motion } from 'framer-motion';
import api from '../services/api';

const LabelManager = ({ labels, onClose }) => {
  const [newName, setNewName] = useState('');
  const [editingId, setEditingId] = useState(null);
  const [editName, setEditName] = useState('');
  const [error, setError] = useState('');

  const handleCreate = async (e) => {
    e.preventDefault();
    if (!newName.trim()) return;
    try {
      await api.post('/notes/labels', { name: newName.trim() });
      setNewName('');
      onClose(); // Refresh and close
    } catch { setError('Không thể tạo nhãn'); }
  };

  const handleUpdate = async (id) => {
    if (!editName.trim()) return;
    try {
      await api.put(`/notes/labels/${id}`, { name: editName.trim() });
      setEditingId(null);
      onClose();
    } catch { setError('Không thể cập nhật nhãn'); }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Xóa nhãn này? Ghi chú liên quan sẽ không bị xóa.')) return;
    try {
      await api.delete(`/notes/labels/${id}`);
      onClose();
    } catch { setError('Không thể xóa nhãn'); }
  };

  return (
    <div className="overlay">
      <motion.div initial={{ scale: 0.9 }} animate={{ scale: 1 }} className="modal modal-sm">
        <div className="modal-header">
          <span className="modal-title">🏷️ Quản lý nhãn</span>
          <button className="btn-close" onClick={onClose}>×</button>
        </div>

        {error && <div className="alert-error">{error}</div>}

        {/* Tạo nhãn mới */}
        <form onSubmit={handleCreate} style={{ display: 'flex', gap: '8px', marginBottom: '20px' }}>
          <input value={newName} onChange={e => setNewName(e.target.value)} placeholder="Tên nhãn mới..." style={{ flex: 1 }} />
          <button type="submit" className="btn btn-sm">+ Thêm</button>
        </form>

        {/* Danh sách nhãn */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', maxHeight: '320px', overflowY: 'auto' }}>
          {labels.length === 0 && <p style={{ color: 'var(--text-muted)', textAlign: 'center', padding: '20px' }}>Chưa có nhãn nào</p>}
          {labels.map(label => (
            <div key={label.id} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '8px', borderRadius: '8px', background: 'var(--bg-secondary)' }}>
              <span>🏷️</span>
              {editingId === label.id ? (
                <>
                  <input value={editName} onChange={e => setEditName(e.target.value)} style={{ flex: 1 }} autoFocus />
                  <button className="btn btn-sm" onClick={() => handleUpdate(label.id)}>✓</button>
                  <button className="btn btn-ghost btn-sm" onClick={() => setEditingId(null)}>✕</button>
                </>
              ) : (
                <>
                  <span style={{ flex: 1 }}>{label.name}</span>
                  <button className="btn btn-ghost btn-sm" onClick={() => { setEditingId(label.id); setEditName(label.name); }}>✏️</button>
                  <button className="btn btn-danger btn-sm" onClick={() => handleDelete(label.id)}>🗑️</button>
                </>
              )}
            </div>
          ))}
        </div>
      </motion.div>
    </div>
  );
};

export default LabelManager;
