Note Application - MERN Stack với MySQL
========================================

## Yêu cầu tiên quyết

1. Node.js >= 18.x
2. MySQL >= 8.0 (xem hướng dẫn cài bên dưới)
3. Docker & Docker Compose (nếu muốn chạy toàn bộ qua docker)

## Hướng dẫn cài MySQL (Nếu chưa có)
-----------------------------------------
Tải MySQL Installer tại: https://dev.mysql.com/downloads/installer/
Chọn "mysql-installer-community" → Cài đặt "MySQL Server" + "MySQL Workbench"
Trong quá trình cài đặt, đặt Root Password (nhớ lại để điền vào .env)

## Cấu hình .env (backend)
-----------------------------------------
Chỉnh file backend/.env:
  MYSQL_HOST=127.0.0.1
  MYSQL_PORT=3306
  MYSQL_USER=root
  MYSQL_PASSWORD=<mật_khẩu_root_mysql>
  MYSQL_DATABASE=noteapp
  PORT=5000
  JWT_SECRET=super_secret_jwt_key_123_noteapp_2025
  FRONTEND_URL=http://localhost:5173
  EMAIL_USER=<gmail_của_bạn>@gmail.com
  EMAIL_PASS=<gmail_app_password>   ← Xem hướng dẫn bên dưới

## Cách lấy Gmail App Password
-----------------------------------------
1. Đăng nhập Gmail → Vào myaccount.google.com/security
2. Bật "Xác minh 2 bước" nếu chưa bật
3. Tìm "Mật khẩu ứng dụng" (App Passwords)
4. Tạo mới: Tên ứng dụng = "Note App" → Sao chép mật khẩu 16 ký tự
5. Dán vào EMAIL_PASS trong backend/.env

## Tạo Database MySQL
-----------------------------------------
Chạy lệnh sau trong MySQL Workbench hoặc terminal:
  CREATE DATABASE noteapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
(Sequelize sẽ tự động tạo bảng khi server khởi động lần đầu)

## Chạy Local
-----------------------------------------
Terminal 1 - Backend:
  cd backend
  npm install
  npm start
  → Server chạy tại http://localhost:5000

Terminal 2 - Frontend:
  cd frontend
  npm install
  npm run dev
  → App chạy tại http://localhost:5173

## Chạy qua Docker Compose
-----------------------------------------
Từ thư mục gốc:
  docker-compose up -d --build
Truy cập: http://localhost:5173

## Tài khoản Demo
-----------------------------------------
Đăng ký tài khoản mới tại /register
Tài khoản demo sẵn có:
  Email: demo@noteapp.com
  Password: Demo@12345
  (Tạo thủ công sau khi chạy app)

## Cấu trúc thư mục
-----------------------------------------
backend/
  config/       ← Cấu hình database Sequelize
  models/       ← User, Note, Label, Relations (MVC - Models)
  controllers/  ← authController, noteController (MVC - Controllers)
  routes/       ← authRoutes, noteRoutes (MVC - Routes)
  middleware/   ← authMiddleware (JWT validation)
  services/     ← emailService (Gmail SMTP)
  server.js     ← Entry point

frontend/
  src/
    context/    ← AuthContext (global state)
    pages/      ← Login, Register, Main, Profile, Activate, ForgotPassword, ResetPassword
    components/ ← NoteCard, NoteEditor, LabelManager, ShareModal
    services/   ← api.js, db.js (IndexedDB), socket.js
    styles/     ← global.css

## Ghi chú kỹ thuật
-----------------------------------------
- Mật khẩu được mã hóa bcrypt (không lưu plain text)
- JWT token hết hạn sau 30 ngày
- Auto-save ghi chú sau 800ms (debounce)
- Live Search debounce 300ms
- Offline: Ghi chú được cache trong IndexedDB (Dexie.js)
- Real-time: WebSocket (Socket.io) cho cộng tác nhiều người
- Responsive: hỗ trợ Mobile / Tablet / Desktop
