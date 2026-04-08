const crypto = require('crypto');
const jwt = require('jsonwebtoken');
const { User } = require('../models/index');
const { sendMail } = require('../services/emailService');

const generateToken = (id) =>
  jwt.sign({ id }, process.env.JWT_SECRET || 'super_secret_jwt_key_123', { expiresIn: '30d' });

const formatUser = (user) => ({
  id: user.id,
  displayName: user.displayName,
  email: user.email,
  isActive: user.isActive,
  preferences: { fontSize: user.fontSize, theme: user.theme, defaultNoteColor: user.defaultNoteColor },
  avatar: user.avatar,
});

// POST /api/auth/register
const registerUser = async (req, res) => {
  const { email, displayName, password } = req.body;
  try {
    const existing = await User.findOne({ where: { email } });
    if (existing) return res.status(400).json({ message: 'Email đã được sử dụng' });

    const activationToken = crypto.randomBytes(20).toString('hex');
    const user = await User.create({ email, displayName, password, activationToken });

    const activationUrl = `${process.env.FRONTEND_URL}/activate/${activationToken}`;
    await sendMail(
      email,
      '✅ Kích hoạt tài khoản Note App',
      `Chào ${displayName},\nNhấp vào link để kích hoạt: ${activationUrl}`,
      `<div style="font-family:sans-serif;max-width:400px;margin:0 auto;padding:30px;border-radius:12px;background:#f9f9f9">
        <h2 style="color:#4361ee">Chào mừng đến với Note App!</h2>
        <p>Xin chào <strong>${displayName}</strong>,</p>
        <p>Nhấn nút bên dưới để kích hoạt tài khoản của bạn:</p>
        <a href="${activationUrl}" style="display:inline-block;padding:12px 24px;background:#4361ee;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold">Kích hoạt tài khoản</a>
        <p style="color:#888;font-size:12px;margin-top:20px">Link hết hạn sau 24 giờ.</p>
      </div>`
    );

    return res.status(201).json({ ...formatUser(user), token: generateToken(user.id) });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// POST /api/auth/login
const loginUser = async (req, res) => {
  const { email, password } = req.body;
  try {
    const user = await User.findOne({ where: { email } });
    if (!user || !(await user.comparePassword(password)))
      return res.status(401).json({ message: 'Email hoặc mật khẩu không đúng' });
    return res.json({ ...formatUser(user), token: generateToken(user.id) });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// GET /api/auth/activate/:token
const activateAccount = async (req, res) => {
  try {
    const user = await User.findOne({ where: { activationToken: req.params.token } });
    if (!user) return res.status(400).json({ message: 'Token không hợp lệ' });
    user.isActive = true;
    user.activationToken = null;
    await user.save();
    return res.json({ message: 'Kích hoạt tài khoản thành công!' });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// GET /api/auth/profile
const getProfile = async (req, res) => {
  const user = await User.findByPk(req.user.id);
  if (!user) return res.status(404).json({ message: 'Không tìm thấy người dùng' });
  return res.json(formatUser(user));
};

// PUT /api/auth/profile
const updateProfile = async (req, res) => {
  const user = await User.findByPk(req.user.id);
  if (!user) return res.status(404).json({ message: 'Không tìm thấy người dùng' });

  if (req.body.displayName) user.displayName = req.body.displayName;
  if (req.body.avatar !== undefined) user.avatar = req.body.avatar;
  if (req.body.preferences) {
    if (req.body.preferences.fontSize) user.fontSize = req.body.preferences.fontSize;
    if (req.body.preferences.theme) user.theme = req.body.preferences.theme;
    if (req.body.preferences.defaultNoteColor) user.defaultNoteColor = req.body.preferences.defaultNoteColor;
  }
  await user.save();
  return res.json({ ...formatUser(user), token: generateToken(user.id) });
};

// POST /api/auth/forgot-password
const forgotPassword = async (req, res) => {
  try {
    const user = await User.findOne({ where: { email: req.body.email } });
    if (!user) return res.status(404).json({ message: 'Email không tồn tại trong hệ thống' });

    const resetToken = crypto.randomBytes(20).toString('hex');
    const otp = Math.floor(100000 + Math.random() * 900000).toString(); // 6-digit OTP

    user.resetPasswordToken = resetToken;
    user.resetPasswordExpires = new Date(Date.now() + 3600000); // 1 giờ
    user.resetOTP = otp;
    user.resetOTPExpires = new Date(Date.now() + 600000); // 10 phút
    await user.save();

    const resetUrl = `${process.env.FRONTEND_URL}/reset-password/${resetToken}`;
    await sendMail(
      user.email,
      '🔑 Đặt lại mật khẩu Note App',
      `Link đặt lại mật khẩu: ${resetUrl}\nMã OTP: ${otp} (hết hạn sau 10 phút)`,
      `<div style="font-family:sans-serif;max-width:400px;margin:0 auto;padding:30px;border-radius:12px;background:#f9f9f9">
        <h2 style="color:#4361ee">Đặt lại mật khẩu</h2>
        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản <strong>${user.email}</strong>.</p>
        <h3>Cách 1: Nhấn link</h3>
        <a href="${resetUrl}" style="display:inline-block;padding:12px 24px;background:#4361ee;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold">Đặt lại mật khẩu</a>
        <h3>Cách 2: Nhập mã OTP</h3>
        <div style="font-size:2rem;font-weight:bold;letter-spacing:8px;color:#4361ee;text-align:center;padding:10px;background:#eef0ff;border-radius:8px">${otp}</div>
        <p style="color:#888;font-size:12px;margin-top:20px">Link hết hạn sau 1 giờ. Mã OTP hết hạn sau 10 phút.</p>
      </div>`
    );
    return res.json({ message: 'Email đã được gửi' });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// POST /api/auth/reset-password/:token
const resetPasswordByLink = async (req, res) => {
  try {
    const user = await User.findOne({
      where: { resetPasswordToken: req.params.token }
    });
    if (!user || new Date(user.resetPasswordExpires) < new Date())
      return res.status(400).json({ message: 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn' });

    user.password = req.body.password;
    user.resetPasswordToken = null;
    user.resetPasswordExpires = null;
    user.resetOTP = null;
    user.resetOTPExpires = null;
    await user.save();
    return res.json({ message: 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.' });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

// POST /api/auth/reset-password-otp
const resetPasswordByOTP = async (req, res) => {
  try {
    const { email, otp, newPassword } = req.body;
    const user = await User.findOne({ where: { email } });
    if (!user || user.resetOTP !== otp || new Date(user.resetOTPExpires) < new Date())
      return res.status(400).json({ message: 'Mã OTP không hợp lệ hoặc đã hết hạn' });

    user.password = newPassword;
    user.resetPasswordToken = null;
    user.resetPasswordExpires = null;
    user.resetOTP = null;
    user.resetOTPExpires = null;
    await user.save();
    return res.json({ message: 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.' });
  } catch (error) {
    return res.status(500).json({ message: error.message });
  }
};

module.exports = {
  registerUser, loginUser, activateAccount,
  getProfile, updateProfile,
  forgotPassword, resetPasswordByLink, resetPasswordByOTP
};
