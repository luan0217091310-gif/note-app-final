const nodemailer = require('nodemailer');

const transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASS,
  }
});

const sendMail = async (to, subject, text, html) => {
  try {
    const info = await transporter.sendMail({
      from: `"Note App" <${process.env.EMAIL_USER}>`,
      to,
      subject,
      text,
      html
    });
    console.log(`✅ Email sent to ${to}: ${info.messageId}`);
    return info;
  } catch (error) {
    console.error('❌ Error sending email:', error.message);
    // Không throw để không block đăng ký dù email thất bại
  }
};

module.exports = { sendMail };
