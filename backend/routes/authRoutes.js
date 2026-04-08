const express = require('express');
const { 
  registerUser, loginUser, activateAccount, 
  getProfile, updateProfile, 
  forgotPassword, resetPasswordByLink, resetPasswordByOTP 
} = require('../controllers/authController');
const { protect } = require('../middleware/authMiddleware');

const router = express.Router();

router.post('/register', registerUser);
router.post('/login', loginUser);
router.get('/activate/:token', activateAccount);
router.post('/forgot-password', forgotPassword);
router.post('/reset-password/:token', resetPasswordByLink);
router.post('/reset-password-otp', resetPasswordByOTP);
router.route('/profile').get(protect, getProfile).put(protect, updateProfile);

module.exports = router;
