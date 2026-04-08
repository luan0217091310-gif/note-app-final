const bcrypt = require('bcryptjs');
const { Op } = require('sequelize');
const { Note, Label, NoteLabel, NoteShare, User } = require('../models/index');

const formatNote = (note) => ({
  ...note.toJSON(),
  labels: note.labels || [],
  shares: note.shares || [],
});

// GET /api/notes
const getNotes = async (req, res) => {
  try {
    const myNotes = await Note.findAll({
      where: { userId: req.user.id },
      include: [{ model: Label, as: 'labels', through: { attributes: [] } },
                { model: NoteShare, as: 'shares', include: [
                  { model: User, as: 'sharedWithUser', attributes: ['id', 'email', 'displayName'] }
                ]}],
      order: [['isPinned', 'DESC'], ['pinnedAt', 'DESC'], ['updatedAt', 'DESC']]
    });
    return res.json(myNotes.map(formatNote));
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// GET /api/notes/shared-with-me  
const getSharedNotes = async (req, res) => {
  try {
    const shares = await NoteShare.findAll({
      where: { sharedWithUserId: req.user.id },
      include: [{
        model: Note,
        include: [{ model: Label, as: 'labels', through: { attributes: [] } }]
      }, { model: User, as: 'sharedByUser', attributes: ['id', 'email', 'displayName', 'avatar'] }]
    });
    return res.json(shares);
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// POST /api/notes
const createNote = async (req, res) => {
  try {
    const { title, content, labelIds, images, color, isLocked, lockPassword } = req.body;
    const noteData = { title, content, userId: req.user.id, images, color };
    if (isLocked && lockPassword) {
      noteData.isLocked = true;
      noteData.lockPassword = await bcrypt.hash(lockPassword, 10);
    }
    const note = await Note.create(noteData);
    if (labelIds && labelIds.length > 0) {
      await note.setLabels(labelIds);
    }
    const freshNote = await Note.findByPk(note.id, {
      include: [{ model: Label, as: 'labels', through: { attributes: [] } },
                { model: NoteShare, as: 'shares' }]
    });
    return res.status(201).json(formatNote(freshNote));
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// PUT /api/notes/:id
const updateNote = async (req, res) => {
  try {
    const note = await Note.findByPk(req.params.id, {
      include: [{ model: NoteShare, as: 'shares' }]
    });
    if (!note) return res.status(404).json({ message: 'Không tìm thấy ghi chú' });

    const isOwner = note.userId === req.user.id;
    const sharedPerm = note.shares?.find(s => s.sharedWithUserId === req.user.id);
    if (!isOwner && (!sharedPerm || sharedPerm.role !== 'edit'))
      return res.status(403).json({ message: 'Không có quyền chỉnh sửa' });

    // Bật/tắt/đổi mật khẩu ghi chú
    if (req.body.isLocked !== undefined && isOwner) {
      if (req.body.isLocked === false && req.body.oldLockPassword && note.isLocked) {
        const match = await bcrypt.compare(req.body.oldLockPassword, note.lockPassword);
        if (!match) return res.status(401).json({ message: 'Mật khẩu cũ không đúng' });
        note.isLocked = false;
        note.lockPassword = null;
      } else if (req.body.isLocked === true && req.body.lockPassword) {
        note.isLocked = true;
        note.lockPassword = await bcrypt.hash(req.body.lockPassword, 10);
      }
    }

    if (req.body.title !== undefined) note.title = req.body.title;
    if (req.body.content !== undefined) note.content = req.body.content;
    if (req.body.images !== undefined) note.images = req.body.images;
    if (req.body.color !== undefined) note.color = req.body.color;

    if (req.body.isPinned !== undefined && isOwner) {
      if (req.body.isPinned && !note.isPinned) note.pinnedAt = new Date();
      else if (!req.body.isPinned) { note.pinnedAt = null; }
      note.isPinned = req.body.isPinned;
    }

    await note.save();

    if (req.body.labelIds !== undefined) {
      await note.setLabels(req.body.labelIds);
    }

    const freshNote = await Note.findByPk(note.id, {
      include: [{ model: Label, as: 'labels', through: { attributes: [] } },
                { model: NoteShare, as: 'shares', include: [
                  { model: User, as: 'sharedWithUser', attributes: ['id', 'email', 'displayName'] }
                ]}]
    });
    return res.json(formatNote(freshNote));
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// DELETE /api/notes/:id
const deleteNote = async (req, res) => {
  try {
    const note = await Note.findByPk(req.params.id);
    if (!note) return res.status(404).json({ message: 'Không tìm thấy ghi chú' });
    if (note.userId !== req.user.id) return res.status(403).json({ message: 'Không có quyền xóa' });

    if (note.isLocked) {
      if (!req.body.lockPassword) return res.status(401).json({ message: 'Vui lòng nhập mật khẩu để xóa ghi chú đã khóa' });
      const match = await bcrypt.compare(req.body.lockPassword, note.lockPassword);
      if (!match) return res.status(401).json({ message: 'Mật khẩu không đúng' });
    }
    await note.destroy();
    return res.json({ message: 'Đã xóa ghi chú' });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// POST /api/notes/:id/verify-lock
const verifyLock = async (req, res) => {
  try {
    const note = await Note.findByPk(req.params.id);
    if (!note) return res.status(404).json({ message: 'Không tìm thấy ghi chú' });
    if (!note.isLocked) return res.json({ success: true });
    if (!req.body.lockPassword) return res.status(400).json({ success: false, message: 'Vui lòng nhập mật khẩu' });
    const match = await bcrypt.compare(req.body.lockPassword, note.lockPassword);
    return match ? res.json({ success: true }) : res.status(401).json({ success: false, message: 'Mật khẩu không đúng' });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// POST /api/notes/:id/share
const shareNote = async (req, res) => {
  try {
    const note = await Note.findByPk(req.params.id);
    if (!note || note.userId !== req.user.id) return res.status(403).json({ message: 'Không có quyền chia sẻ' });

    const { email, role } = req.body;
    const targetUser = await User.findOne({ where: { email } });
    if (!targetUser) return res.status(404).json({ message: 'Email người dùng không tồn tại trong hệ thống' });
    if (targetUser.id === req.user.id) return res.status(400).json({ message: 'Không thể chia sẻ với chính mình' });

    const [share, created] = await NoteShare.findOrCreate({
      where: { noteId: note.id, sharedWithUserId: targetUser.id },
      defaults: { sharedByUserId: req.user.id, role: role || 'read-only' }
    });
    if (!created) { share.role = role || share.role; await share.save(); }

    return res.json({ message: 'Đã chia sẻ ghi chú', share });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// DELETE /api/notes/:id/share/:shareId
const revokeShare = async (req, res) => {
  try {
    const share = await NoteShare.findByPk(req.params.shareId);
    if (!share) return res.status(404).json({ message: 'Không tìm thấy quyền chia sẻ' });
    await share.destroy();
    return res.json({ message: 'Đã thu hồi quyền chia sẻ' });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// ===== LABELS =====
const getLabels = async (req, res) => {
  const labels = await Label.findAll({ where: { userId: req.user.id } });
  return res.json(labels);
};

const createLabel = async (req, res) => {
  const label = await Label.create({ name: req.body.name, userId: req.user.id });
  return res.status(201).json(label);
};

const updateLabel = async (req, res) => {
  const label = await Label.findByPk(req.params.id);
  if (!label || label.userId !== req.user.id) return res.status(403).json({ message: 'Không có quyền' });
  label.name = req.body.name || label.name;
  await label.save();
  return res.json(label);
};

const deleteLabel = async (req, res) => {
  const label = await Label.findByPk(req.params.id);
  if (!label || label.userId !== req.user.id) return res.status(403).json({ message: 'Không có quyền' });
  await NoteLabel.destroy({ where: { labelId: label.id } });
  await label.destroy();
  return res.json({ message: 'Đã xóa nhãn' });
};

module.exports = {
  getNotes, getSharedNotes, createNote, updateNote, deleteNote,
  verifyLock, shareNote, revokeShare,
  getLabels, createLabel, updateLabel, deleteLabel
};
