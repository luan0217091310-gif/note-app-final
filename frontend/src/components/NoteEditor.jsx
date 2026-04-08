import React, { useState, useEffect, useCallback, useRef } from 'react';
import { motion } from 'framer-motion';
import api from '../services/api';
import { socket } from '../services/socket';

const NoteEditor = ({ note, labels, onClose }) => {
  const [title, setTitle] = useState(note.title || '');
  const [content, setContent] = useState(note.content || '');
  const [selectedLabels, setSelectedLabels] = useState(note.labels?.map(l => l.id) || []);
  const [color, setColor] = useState(note.color || '#ffffff');
  const [images, setImages] = useState(note.images || []);
  const [unlocked, setUnlocked] = useState(!note.isLocked);
  const [lockInput, setLockInput] = useState('');
  const [showLockSettings, setShowLockSettings] = useState(false);
  const [newLockPwd, setNewLockPwd] = useState('');
  const [confirmLockPwd, setConfirmLockPwd] = useState('');
  const [oldLockPwd, setOldLockPwd] = useState('');
  const [uploadingImg, setUploadingImg] = useState(false);
  const [saving, setSaving] = useState(false);
  const saveTimer = useRef(null);
  const noteIdRef = useRef(note.id);

  useEffect(() => {
    noteIdRef.current = note.id;
    if (note.id && unlocked) {
      socket.emit('join_note', note.id);
      socket.on('receive_edit', (data) => {
        if (data.title !== undefined) setTitle(data.title);
        if (data.content !== undefined) setContent(data.content);
      });
      return () => {
        socket.emit('leave_note', note.id);
        socket.off('receive_edit');
      };
    }
  }, [note.id, unlocked]);

  // Auto-save debounce
  const autoSave = useCallback(async (newTitle, newContent, newColor, newLabels, newImages) => {
    if (saveTimer.current) clearTimeout(saveTimer.current);
    saveTimer.current = setTimeout(async () => {
      setSaving(true);
      try {
        if (noteIdRef.current) {
          const res = await api.put(`/notes/${noteIdRef.current}`, { title: newTitle, content: newContent, color: newColor, labelIds: newLabels, images: newImages });
          socket.emit('edit_note', { noteId: noteIdRef.current, title: newTitle, content: newContent });
        }
      } catch (err) { console.error('Auto-save error:', err); }
      setSaving(false);
    }, 800);
  }, []);

  const handleTitleChange = (e) => { setTitle(e.target.value); autoSave(e.target.value, content, color, selectedLabels, images); };
  const handleContentChange = (e) => { setContent(e.target.value); autoSave(title, e.target.value, color, selectedLabels, images); };
  const handleColorChange = (e) => { setColor(e.target.value); autoSave(title, content, e.target.value, selectedLabels, images); };

  const handleClose = async () => {
    if (saveTimer.current) clearTimeout(saveTimer.current);
    if (!noteIdRef.current && (title || content)) {
      await api.post('/notes', { title, content, color, labelIds: selectedLabels, images });
    } else if (noteIdRef.current && unlocked) {
      await api.put(`/notes/${noteIdRef.current}`, { title, content, color, labelIds: selectedLabels, images });
    }
    onClose();
  };

  const handleDelete = async () => {
    if (!window.confirm('Bạn có chắc muốn xóa ghi chú này?')) return;
    try {
      let body = {};
      if (note.isLocked && unlocked) {
        const pwd = window.prompt('Nhập mật khẩu ghi chú để xác nhận xóa:');
        if (!pwd) return;
        body.lockPassword = pwd;
      }
      await api.delete(`/notes/${note.id}`, { data: body });
      onClose();
    } catch (err) {
      alert(err.response?.data?.message || 'Xóa thất bại');
    }
  };

  const handleImageUpload = async (e) => {
    const files = Array.from(e.target.files);
    setUploadingImg(true);
    for (const file of files) {
      const reader = new FileReader();
      reader.onload = (ev) => {
        setImages(prev => {
          const newImgs = [...prev, ev.target.result];
          autoSave(title, content, color, selectedLabels, newImgs);
          return newImgs;
        });
      };
      reader.readAsDataURL(file);
    }
    setUploadingImg(false);
  };

  const handleUnlock = async (e) => {
    e.preventDefault();
    try {
      await api.post(`/notes/${note.id}/verify-lock`, { lockPassword: lockInput });
      setUnlocked(true);
    } catch { alert('Mật khẩu không đúng'); }
  };

  const handleSetLock = async (e) => {
    e.preventDefault();
    if (newLockPwd !== confirmLockPwd) { alert('Mật khẩu xác nhận không khớp'); return; }
    try {
      await api.put(`/notes/${note.id}`, {
        isLocked: true, lockPassword: newLockPwd,
        ...(note.isLocked ? { oldLockPassword: oldLockPwd } : {})
      });
      alert('Đã đặt mật khẩu cho ghi chú');
      setShowLockSettings(false);
    } catch (err) { alert(err.response?.data?.message || 'Lỗi'); }
  };

  const handleRemoveLock = async () => {
    const pwd = window.prompt('Nhập mật khẩu hiện tại để tắt khóa:');
    if (!pwd) return;
    try {
      await api.put(`/notes/${note.id}`, { isLocked: false, oldLockPassword: pwd });
      alert('Đã tắt khóa ghi chú');
    } catch (err) { alert(err.response?.data?.message || 'Mật khẩu không đúng'); }
  };

  const toggleLabel = (labelId) => {
    setSelectedLabels(prev => {
      const next = prev.includes(labelId) ? prev.filter(id => id !== labelId) : [...prev, labelId];
      autoSave(title, content, color, next, images);
      return next;
    });
  };

  // Show lock prompt
  if (!unlocked) return (
    <div className="overlay">
      <motion.div initial={{ scale: 0.9 }} animate={{ scale: 1 }} className="glass modal modal-sm">
        <div className="modal-header">
          <span className="modal-title">🔒 Ghi chú bị khóa</span>
          <button className="btn-close" onClick={onClose}>×</button>
        </div>
        <form onSubmit={handleUnlock} style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
          <input type="password" value={lockInput} onChange={e => setLockInput(e.target.value)} placeholder="Nhập mật khẩu ghi chú" autoFocus />
          <div style={{ display: 'flex', gap: '8px' }}>
            <button type="submit" className="btn" style={{ flex: 1 }}>Mở khóa</button>
            <button type="button" className="btn btn-ghost" onClick={onClose}>Hủy</button>
          </div>
        </form>
      </motion.div>
    </div>
  );

  return (
    <div className="overlay">
      <motion.div initial={{ scale: 0.96, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} className="modal"
        style={{ display: 'flex', flexDirection: 'column', height: '85vh', maxWidth: '800px', width: '100%', background: color !== '#ffffff' ? color : 'var(--surface)' }}>

        {/* Header */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px', gap: '8px', flexWrap: 'wrap' }}>
          <input value={title} onChange={handleTitleChange} placeholder="Tiêu đề ghi chú..." style={{ fontSize: '1.3rem', fontWeight: '700', border: 'none', background: 'transparent', flex: 1, minWidth: '150px' }} />
          <div style={{ display: 'flex', gap: '6px', alignItems: 'center', flexWrap: 'wrap' }}>
            {saving && <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>💾 Đang lưu...</span>}
            <input type="color" value={color} onChange={handleColorChange} style={{ width: '32px', height: '32px', border: 'none', background: 'none', cursor: 'pointer', padding: 0 }} title="Màu ghi chú" />
            <button className="btn btn-ghost btn-sm" onClick={() => setShowLockSettings(!showLockSettings)}>🔒</button>
            {note.id && <button className="btn btn-danger btn-sm" onClick={handleDelete}>🗑️ Xóa</button>}
            <button className="btn btn-sm" onClick={handleClose}>✓ Lưu & Đóng</button>
          </div>
        </div>

        {/* Label bar */}
        {labels?.length > 0 && (
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '6px', marginBottom: '12px' }}>
            {labels.map(l => (
              <span key={l.id} onClick={() => toggleLabel(l.id)}
                className={`label-chip ${selectedLabels.includes(l.id) ? 'active' : ''}`}>
                🏷️ {l.name}
              </span>
            ))}
          </div>
        )}

        {/* Lock settings */}
        {showLockSettings && (
          <div className="glass" style={{ padding: '16px', marginBottom: '12px' }}>
            <h4 style={{ marginBottom: '12px' }}>🔒 Cài đặt khóa ghi chú</h4>
            <form onSubmit={handleSetLock} style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
              {note.isLocked && <input type="password" placeholder="Mật khẩu hiện tại" value={oldLockPwd} onChange={e => setOldLockPwd(e.target.value)} required />}
              <input type="password" placeholder="Mật khẩu mới" value={newLockPwd} onChange={e => setNewLockPwd(e.target.value)} required />
              <input type="password" placeholder="Xác nhận mật khẩu mới" value={confirmLockPwd} onChange={e => setConfirmLockPwd(e.target.value)} required />
              <div style={{ display: 'flex', gap: '8px' }}>
                <button type="submit" className="btn btn-sm">Đặt mật khẩu</button>
                {note.isLocked && <button type="button" className="btn btn-danger btn-sm" onClick={handleRemoveLock}>Tắt khóa</button>}
              </div>
            </form>
          </div>
        )}

        {/* Content */}
        <textarea value={content} onChange={handleContentChange} placeholder="Bắt đầu viết ghi chú..." style={{ flex: 1, resize: 'none', border: 'none', background: 'transparent', fontSize: '1rem', lineHeight: 1.8 }} />

        {/* Image upload */}
        <div style={{ borderTop: '1px solid var(--border)', paddingTop: '12px', marginTop: '12px' }}>
          {images.length > 0 && (
            <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap', marginBottom: '10px' }}>
              {images.map((img, i) => (
                <div key={i} style={{ position: 'relative' }}>
                  <img src={img} alt="" style={{ width: 80, height: 70, objectFit: 'cover', borderRadius: '8px' }} />
                  <button onClick={() => { const newImgs = images.filter((_, j) => j !== i); setImages(newImgs); autoSave(title, content, color, selectedLabels, newImgs); }}
                    style={{ position: 'absolute', top: -6, right: -6, background: '#ef4444', color: 'white', border: 'none', borderRadius: '50%', width: 20, height: 20, cursor: 'pointer', fontSize: '0.7rem', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>×</button>
                </div>
              ))}
            </div>
          )}
          <label className="btn btn-ghost btn-sm" style={{ cursor: 'pointer' }}>
            📎 {uploadingImg ? 'Đang tải...' : 'Đính kèm ảnh'}
            <input type="file" accept="image/*" multiple onChange={handleImageUpload} style={{ display: 'none' }} disabled={uploadingImg} />
          </label>
        </div>
      </motion.div>
    </div>
  );
};

export default NoteEditor;
