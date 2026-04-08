const { DataTypes } = require('sequelize');
const sequelize = require('../config/database');
const bcrypt = require('bcryptjs');

const User = sequelize.define('User', {
  id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  email: { type: DataTypes.STRING, allowNull: false, unique: true },
  displayName: { type: DataTypes.STRING, allowNull: false },
  password: { type: DataTypes.STRING, allowNull: false },
  isActive: { type: DataTypes.BOOLEAN, defaultValue: false },
  activationToken: { type: DataTypes.STRING, allowNull: true },
  resetPasswordToken: { type: DataTypes.STRING, allowNull: true },
  resetPasswordExpires: { type: DataTypes.DATE, allowNull: true },
  resetOTP: { type: DataTypes.STRING(6), allowNull: true },
  resetOTPExpires: { type: DataTypes.DATE, allowNull: true },
  // Preferences stored flat for MySQL simplicity
  fontSize: { type: DataTypes.STRING, defaultValue: 'medium' },
  theme: { type: DataTypes.STRING, defaultValue: 'light' },
  defaultNoteColor: { type: DataTypes.STRING, defaultValue: '#ffffff' },
  avatar: { type: DataTypes.TEXT, defaultValue: '' },
}, {
  hooks: {
    beforeCreate: async (user) => {
      user.password = await bcrypt.hash(user.password, 12);
    },
    beforeUpdate: async (user) => {
      if (user.changed('password')) {
        user.password = await bcrypt.hash(user.password, 12);
      }
    }
  }
});

User.prototype.comparePassword = async function(candidatePassword) {
  return bcrypt.compare(candidatePassword, this.password);
};

module.exports = User;
