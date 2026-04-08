const express = require('express');
const multer = require('multer');
const path = require('path');
const {
  getNotes, getSharedNotes, createNote, updateNote, deleteNote,
  verifyLock, shareNote, revokeShare,
  getLabels, createLabel, updateLabel, deleteLabel
} = require('../controllers/noteController');
const { protect } = require('../middleware/authMiddleware');

const router = express.Router();

// Multer setup cho upload ảnh
const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, 'uploads/'),
  filename: (req, file, cb) => cb(null, `${Date.now()}-${file.originalname}`)
});
const upload = multer({ storage, limits: { fileSize: 5 * 1024 * 1024 } });

// ⚠️ QUAN TRỌNG: Đặt route cụ thể TRƯỚC route có params (/:id)
// Nếu không, Express sẽ match /labels vào /:id trước

// Labels routes (phải đặt TRƯỚC /:id)
router.route('/labels').get(protect, getLabels).post(protect, createLabel);
router.route('/labels/:id').put(protect, updateLabel).delete(protect, deleteLabel);

// Shared notes (phải đặt TRƯỚC /:id)
router.get('/shared-with-me', protect, getSharedNotes);

// Upload ảnh (phải đặt TRƯỚC /:id)
router.post('/upload', protect, upload.array('images', 10), (req, res) => {
  const urls = req.files.map(f => `/uploads/${f.filename}`);
  res.json({ urls });
});

// Root route
router.route('/').get(protect, getNotes).post(protect, createNote);

// Parameterized routes (đặt CUỐI CÙNG)
router.route('/:id').put(protect, updateNote).delete(protect, deleteNote);
router.post('/:id/verify-lock', protect, verifyLock);
router.post('/:id/share', protect, shareNote);
router.delete('/:id/share/:shareId', protect, revokeShare);

module.exports = router;
