import React from 'react';
import { motion } from 'framer-motion';

const NoteCard = ({ note, viewMode, onClick, onShare, onPin }) => {
  const handleAction = (e, fn) => { e.stopPropagation(); fn(); };

  return (
    <motion.div
      layout
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.9 }}
      whileHover={{ y: -4 }}
      onClick={onClick}
      className={`note-card ${note.isPinned ? 'pinned' : ''}`}
      style={{ backgroundColor: note.color && note.color !== '#ffffff' ? note.color : 'var(--surface)', cursor: 'pointer' }}
    >
      {/* Status icons */}
      <div className="note-card-icons">
        {note.isPinned && <span title="Đã ghim" style={{ fontSize: '0.9rem' }}>📌</span>}
        {note.isLocked && <span title="Đã khóa" style={{ fontSize: '0.9rem' }}>🔒</span>}
        {note.shares?.length > 0 && <span title="Đã chia sẻ" style={{ fontSize: '0.9rem' }}>👥</span>}
      </div>

      {/* Labels */}
      {note.labels?.length > 0 && (
        <div className="note-card-badges" style={{ flexWrap: 'wrap' }}>
          {note.labels.map(l => (
            <span key={l.id} className="badge badge-label">🏷️ {l.name}</span>
          ))}
        </div>
      )}

      <div className="note-card-title">{note.title || 'Không có tiêu đề'}</div>

      <div className="note-card-content">
        {note.isLocked ? '🔒 Nội dung bị khóa' : (note.content || 'Chưa có nội dung')}
      </div>

      {/* Images preview */}
      {!note.isLocked && note.images?.length > 0 && (
        <div style={{ display: 'flex', gap: '4px', marginTop: '10px', flexWrap: 'wrap' }}>
          {note.images.slice(0, 3).map((img, i) => (
            <img key={i} src={img} alt="" style={{ width: 60, height: 50, objectFit: 'cover', borderRadius: '6px' }} />
          ))}
          {note.images.length > 3 && <div style={{ width: 60, height: 50, background: 'var(--bg-secondary)', borderRadius: '6px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '0.8rem', color: 'var(--text-muted)' }}>+{note.images.length - 3}</div>}
        </div>
      )}

      {/* Actions */}
      <div style={{ display: 'flex', gap: '6px', marginTop: '12px', justifyContent: 'flex-end' }} onClick={e => e.stopPropagation()}>
        <button className="btn btn-ghost btn-sm" onClick={e => handleAction(e, onPin)} title={note.isPinned ? 'Bỏ ghim' : 'Ghim lên đầu'}>
          {note.isPinned ? '📌 Bỏ ghim' : '📌 Ghim'}
        </button>
        <button className="btn btn-ghost btn-sm" onClick={e => handleAction(e, onShare)} title="Chia sẻ">
          📤 Chia sẻ
        </button>
      </div>

      <div style={{ marginTop: '8px', fontSize: '0.72rem', color: 'var(--text-muted)' }}>
        {new Date(note.updatedAt).toLocaleString('vi-VN')}
      </div>
    </motion.div>
  );
};

export default NoteCard;
