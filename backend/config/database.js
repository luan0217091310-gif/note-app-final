const { Sequelize } = require('sequelize');

// Hỗ trợ cả SQL Server Express (named instance) và SQL Server thường
const dialectOptions = {
  options: {
    encrypt: false,
    trustServerCertificate: true,
    enableArithAbort: true,
  }
};

// Nếu có DB_INSTANCE (ví dụ: SQLEXPRESS), dùng instanceName thay vì port cứng
if (process.env.DB_INSTANCE) {
  dialectOptions.options.instanceName = process.env.DB_INSTANCE;
}

const sequelize = new Sequelize(
  process.env.DB_NAME || 'noteapp',
  process.env.DB_USER || 'sa',
  process.env.DB_PASSWORD || '',
  {
    host: process.env.DB_HOST || 'localhost',
    // Không set port cứng khi dùng named instance (SQL Server Express dùng dynamic port)
    ...(process.env.DB_INSTANCE ? {} : { port: parseInt(process.env.DB_PORT || '1433') }),
    dialect: 'mssql',
    logging: false,
    dialectOptions,
    pool: { max: 5, min: 0, acquire: 30000, idle: 10000 }
  }
);

module.exports = sequelize;
