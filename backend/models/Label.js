const { DataTypes } = require('sequelize');
const sequelize = require('../config/database');

const Label = sequelize.define('Label', {
  id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  name: { type: DataTypes.STRING, allowNull: false },
  userId: { type: DataTypes.INTEGER, allowNull: false },
});

module.exports = Label;
