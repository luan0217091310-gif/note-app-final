require('dotenv').config();
const express = require('express');
const http = require('http');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const { Server } = require('socket.io');

const sequelize = require('./config/database');
require('./models/index'); // Load associations

const authRoutes = require('./routes/authRoutes');
const noteRoutes = require('./routes/noteRoutes');

const app = express();
app.use(cors());
app.use(express.json({ limit: '10mb' }));

// Tạo thư mục uploads nếu chưa có
const uploadsDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir);
app.use('/uploads', express.static(uploadsDir));

const server = http.createServer(app);
const io = new Server(server, {
  cors: { origin: process.env.FRONTEND_URL || '*', methods: ['GET', 'POST', 'PUT', 'DELETE'] }
});

// Đồng bộ database - tạo từng bảng, bỏ qua NoteLabel nếu lỗi
const syncDatabase = async () => {
  try {
    // Sync từng model NGOẠI TRỪ NoteLabel (tự tạo bằng raw SQL)
    const { User, Note, Label, NoteShare } = require('./models/index');
    await User.sync({ force: false });
    await Note.sync({ force: false });
    await Label.sync({ force: false });
    await NoteShare.sync({ force: false });

    
    // Tạo bảng NoteLabels bằng raw SQL tương thích SQL Server
    await sequelize.query(`
      IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='NoteLabels' AND xtype='U')
      CREATE TABLE NoteLabels (
        noteId INT NOT NULL,
        labelId INT NOT NULL,
        CONSTRAINT PK_NoteLabels PRIMARY KEY (noteId, labelId),
        CONSTRAINT FK_NoteLabels_Note FOREIGN KEY (noteId) REFERENCES Notes(id),
        CONSTRAINT FK_NoteLabels_Label FOREIGN KEY (labelId) REFERENCES Labels(id)
      )
    `);

    
    console.log('✅ Database synced successfully');
  } catch (err) {
    console.error('❌ DB sync error:', err.message);
  }
};

syncDatabase();

app.use('/api/auth', authRoutes);
app.use('/api/notes', noteRoutes);

// WebSocket Real-time Collaboration
io.on('connection', (socket) => {
  console.log('🔌 User connected:', socket.id);

  socket.on('join_note', (noteId) => {
    socket.join(`note_${noteId}`);
    socket.to(`note_${noteId}`).emit('user_joined', { socketId: socket.id });
  });

  socket.on('leave_note', (noteId) => {
    socket.leave(`note_${noteId}`);
  });

  socket.on('edit_note', (data) => {
    socket.to(`note_${data.noteId}`).emit('receive_edit', data);
  });

  socket.on('disconnect', () => {
    console.log('🔌 User disconnected:', socket.id);
  });
});

const PORT = process.env.PORT || 5000;
server.listen(PORT, () => console.log(`🚀 Server running on port ${PORT}`));
