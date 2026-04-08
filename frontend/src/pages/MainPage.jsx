import React, { useState, useContext, useEffect, useCallback, useRef } from 'react';
import { AuthContext } from '../context/AuthContext';
import api from '../services/api';
import { db } from '../services/db';
import { socket } from '../services/socket';
import { motion, AnimatePresence } from 'framer-motion';
import { useNavigate } from 'react-router-dom';
import NoteCard from '../components/NoteCard';
import NoteEditor from '../components/NoteEditor';
import LabelManager from '../components/LabelManager';
import ShareModal from '../components/ShareModal';

const MainPage = () => {
  const { user, logout } = useContext(AuthContext);
  const navigate = useNavigate();
  const [notes, setNotes] = useState([]);
  const [sharedNotes, setSharedNotes] = useState([]);
  const [viewMode, setViewMode] = useState('grid');
  const [activeTab, setActiveTab] = useState('my'); // 'my' | 'shared'
  const [searchQuery, setSearchQuery] = useState('');
  const [debouncedQuery, setDebouncedQuery] = useState('');
  const [editingNote, setEditingNote] = useState(null);
  const [labels, setLabels] = useState([]);
  const [selectedLabel, setSelectedLabel] = useState(null);
  const [showLabelManager, setShowLabelManager] = useState(false);
  const [shareNote, setShareNote] = useState(null);
  const [loading, setLoading] = useState(true);
  const searchTimer = useRef(null);

  // Apply user preferences
  useEffect(() => {
    if (user?.preferences?.theme) document.documentElement.setAttribute('data-theme', user.preferences.theme);
    if (user?.preferences?.fontSize) document.documentElement.setAttribute('data-fontsize', user.preferences.fontSize);
  }, [user]);

  // Live search debounce 300ms
  useEffect(() => {
    if (searchTimer.current) clearTimeout(searchTimer.current);
    searchTimer.current = setTimeout(() => setDebouncedQuery(searchQuery), 300);
    return () => clearTimeout(searchTimer.current);
  }, [searchQuery]);

  useEffect(() => {
    socket.connect();
    fetchData();
    return () => socket.disconnect();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      if (navigator.onLine) {
        // Gọi từng API riêng để tránh crash toàn bộ nếu 1 cái lỗi
        const notesRes = await api.get('/notes').catch(() => ({ data: [] }));
        const labelsRes = await api.get('/notes/labels').catch(() => ({ data: [] }));
        const sharedRes = await api.get('/notes/shared-with-me').catch(() => ({ data: [] }));

        setNotes(notesRes.data);
        setLabels(labelsRes.data);
        setSharedNotes(sharedRes.data);

        // Cache offline
        if (notesRes.data.length > 0) {
          await db.notes.bulkPut(notesRes.data.map(n => ({ ...n, _localId: n.id })));
        }
      } else {
        const localNotes = await db.notes.toArray();
        setNotes(localNotes);
      }
    } catch (err) {
      console.error('fetchData error:', err);
      try {
        const localNotes = await db.notes.toArray();
        setNotes(localNotes);
      } catch {}
    }
    setLoading(false);
  };

  const filteredNotes = notes.filter(note => {
    const query = debouncedQuery.toLowerCase();
    const matchSearch = !query || note.title?.toLowerCase().includes(query) || note.content?.toLowerCase().includes(query);
    const matchLabel = !selectedLabel || note.labels?.some(l => l.id === selectedLabel);
    return matchSearch && matchLabel;
  });

  const handleNoteClick = (note) => {
    setEditingNote(note);
  };

  const handleNewNote = () => setEditingNote({});

  return (
    <div style={{ minHeight: '100vh', background: 'var(--bg)' }}>
      {/* Activation banner */}
      {user && !user.isActive && (
        <div className="activation-banner">
          ⚠️ Tài khoản chưa được kích hoạt. Kiểm tra email <strong>{user.email}</strong> để kích hoạt và sử dụng đầy đủ tính năng.
        </div>
      )}

      {/* Navbar */}
      <nav className="glass navbar">
        <div className="navbar-brand">📝 NoteApp</div>
        <div className="navbar-actions">
          <div className="search-bar">
            <span className="search-icon">🔍</span>
            <input type="text" placeholder="Tìm kiếm ghi chú..." value={searchQuery} onChange={e => setSearchQuery(e.target.value)} style={{ width: '220px', paddingLeft: '38px' }} />
          </div>
          <button className="btn btn-ghost btn-sm" onClick={() => setViewMode(v => v === 'grid' ? 'list' : 'grid')}>
            {viewMode === 'grid' ? '☰ Danh sách' : '⊞ Lưới'}
          </button>
          <button className="btn btn-ghost btn-sm" onClick={() => setShowLabelManager(true)}>🏷️ Nhãn</button>
          <button className="btn btn-sm" onClick={handleNewNote}>+ Ghi chú mới</button>
          <button className="btn btn-ghost btn-sm" onClick={() => navigate('/profile')}>
            {user?.avatar
              ? <img src={user.avatar} alt="" style={{ width: 24, height: 24, borderRadius: '50%', objectFit: 'cover' }} />
              : '👤'} {user?.displayName}
          </button>
        </div>
      </nav>

      <div style={{ padding: '0 24px 40px', maxWidth: '1400px', margin: '0 auto' }}>
        {/* Tabs */}
        <div style={{ display: 'flex', gap: '12px', marginBottom: '20px', borderBottom: '1px solid var(--border)', paddingBottom: '12px' }}>
          {[{ key: 'my', label: '📓 Ghi chú của tôi' }, { key: 'shared', label: '👥 Được chia sẻ với tôi' }].map(tab => (
            <button key={tab.key} onClick={() => setActiveTab(tab.key)}
              className={`btn btn-sm ${activeTab === tab.key ? '' : 'btn-ghost'}`}>
              {tab.label}
            </button>
          ))}
        </div>

        {/* Label Filter */}
        {activeTab === 'my' && labels.length > 0 && (
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', marginBottom: '20px', alignItems: 'center' }}>
            <span style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>Lọc:</span>
            <span className={`label-chip ${!selectedLabel ? 'active' : ''}`} onClick={() => setSelectedLabel(null)}>Tất cả</span>
            {labels.map(l => (
              <span key={l.id} className={`label-chip ${selectedLabel === l.id ? 'active' : ''}`} onClick={() => setSelectedLabel(l.id === selectedLabel ? null : l.id)}>
                🏷️ {l.name}
              </span>
            ))}
          </div>
        )}

        {/* Notes Grid/List */}
        {activeTab === 'my' && (
          loading ? (
            <div style={{ textAlign: 'center', padding: '60px' }}><div className="spinner" /></div>
          ) : filteredNotes.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '80px', opacity: 0.5 }}>
              <div style={{ fontSize: '4rem' }}>📭</div>
              <p style={{ marginTop: '16px', fontSize: '1.1rem' }}>
                {debouncedQuery ? 'Không tìm thấy ghi chú nào' : 'Chưa có ghi chú. Tạo ghi chú đầu tiên!'}
              </p>
              {!debouncedQuery && <button className="btn" onClick={handleNewNote} style={{ marginTop: '16px' }}>+ Tạo ngay</button>}
            </div>
          ) : (
            <motion.div layout className={viewMode === 'grid' ? 'notes-grid' : 'notes-list'}>
              <AnimatePresence>
                {filteredNotes.map(note => (
                  <NoteCard key={note.id} note={note} viewMode={viewMode}
                    onClick={() => handleNoteClick(note)}
                    onShare={() => setShareNote(note)}
                    onPin={async () => {
                      await api.put(`/notes/${note.id}`, { isPinned: !note.isPinned });
                      fetchData();
                    }} />
                ))}
              </AnimatePresence>
            </motion.div>
          )
        )}

        {/* Shared Notes Tab */}
        {activeTab === 'shared' && (
          <div>
            {sharedNotes.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '80px', opacity: 0.5 }}>
                <div style={{ fontSize: '4rem' }}>💌</div>
                <p style={{ marginTop: '16px' }}>Chưa có ghi chú nào được chia sẻ với bạn</p>
              </div>
            ) : (
              <div className={viewMode === 'grid' ? 'notes-grid' : 'notes-list'}>
                {sharedNotes.map(share => (
                  <div key={share.id} className="glass note-card" onClick={() => handleNoteClick({ ...share.Note, _shareRole: share.role, _sharedByUser: share.sharedByUser })}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                      <span className="badge badge-label">{share.role === 'edit' ? '✏️ Có thể sửa' : '👁️ Chỉ xem'}</span>
                      <small style={{ color: 'var(--text-muted)' }}>từ <strong>{share.sharedByUser?.displayName}</strong></small>
                    </div>
                    <div className="note-card-title">{share.Note?.title || 'Không có tiêu đề'}</div>
                    <div className="note-card-content">{share.Note?.isLocked ? '[Ghi chú bị khóa 🔒]' : share.Note?.content}</div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </div>

      {/* Modals */}
      {editingNote !== null && (
        <NoteEditor note={editingNote} labels={labels} onClose={() => { setEditingNote(null); fetchData(); }} />
      )}
      {showLabelManager && (
        <LabelManager labels={labels} onClose={() => { setShowLabelManager(false); fetchData(); }} />
      )}
      {shareNote && (
        <ShareModal note={shareNote} onClose={() => { setShareNote(null); fetchData(); }} />
      )}
    </div>
  );
};

export default MainPage;
