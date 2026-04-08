const { DataTypes } = require('sequelize');
const sequelize = require('../config/database');

const Note = sequelize.define('Note', {
  id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  title: { type: DataTypes.STRING, defaultValue: '' },
  content: { type: DataTypes.TEXT, defaultValue: '' },
  userId: { type: DataTypes.INTEGER, allowNull: false },
  isPinned: { type: DataTypes.BOOLEAN, defaultValue: false },
  pinnedAt: { type: DataTypes.DATE, allowNull: true },
  isLocked: { type: DataTypes.BOOLEAN, defaultValue: false },
  lockPassword: { type: DataTypes.STRING, allowNull: true },
  color: { type: DataTypes.STRING, defaultValue: '#ffffff' },
  images: { type: DataTypes.TEXT, defaultValue: '[]', get() { 
    const val = this.getDataValue('images'); 
    return val ? JSON.parse(val) : []; 
  }, set(val) { 
    this.setDataValue('images', JSON.stringify(val || [])); 
  }},
});

module.exports = Note;
