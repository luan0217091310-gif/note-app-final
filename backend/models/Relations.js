const { DataTypes } = require('sequelize');
const sequelize = require('../config/database');

// Junction table: NoteLabel (many-to-many)
const NoteLabel = sequelize.define('NoteLabel', {
  noteId: { type: DataTypes.INTEGER, primaryKey: true },
  labelId: { type: DataTypes.INTEGER, primaryKey: true },
}, { timestamps: false, indexes: [] });

// NoteShare: ghi chú chia sẻ với người dùng khác
const NoteShare = sequelize.define('NoteShare', {
  id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  noteId: { type: DataTypes.INTEGER, allowNull: false },
  sharedWithUserId: { type: DataTypes.INTEGER, allowNull: false },
  sharedByUserId: { type: DataTypes.INTEGER, allowNull: false },
  role: { type: DataTypes.ENUM('read-only', 'edit'), defaultValue: 'read-only' },
});

module.exports = { NoteLabel, NoteShare };
