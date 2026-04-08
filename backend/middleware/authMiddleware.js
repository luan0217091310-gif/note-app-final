const jwt = require('jsonwebtoken');
const { User } = require('../models/index');

const protect = async (req, res, next) => {
  let token;
  if (req.headers.authorization && req.headers.authorization.startsWith('Bearer')) {
    try {
      token = req.headers.authorization.split(' ')[1];
      const decoded = jwt.verify(token, process.env.JWT_SECRET || 'super_secret_jwt_key_123');
      req.user = await User.findByPk(decoded.id, { attributes: { exclude: ['password'] } });
      if (!req.user) return res.status(401).json({ message: 'Người dùng không tồn tại' });
      return next();
    } catch (error) {
      return res.status(401).json({ message: 'Token không hợp lệ' });
    }
  }
  if (!token) return res.status(401).json({ message: 'Chưa đăng nhập' });
};

module.exports = { protect };
