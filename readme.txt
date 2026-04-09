===========================================================
 NOTE MANAGEMENT APPLICATION - HƯỚNG DẪN CÀI ĐẶT
 Môn: 503073 - Web Programming & Applications
===========================================================

1. YÊU CẦU HỆ THỐNG
-----------------------------------------------------------
- XAMPP (PHP >= 7.4 + MySQL/MariaDB + Apache)
  Download: https://www.apachefriends.org/
- Composer (PHP Package Manager)
  Download: https://getcomposer.org/
- Trình duyệt web hiện đại (Chrome/Firefox/Edge)

2. CÀI ĐẶT
-----------------------------------------------------------

Bước 1: Chuẩn bị XAMPP
  - Mở XAMPP Control Panel
  - Khởi động Apache và MySQL

Bước 2: Sao chép dự án
  - Sao chép toàn bộ thư mục "note_app" vào C:\xampp\htdocs\
  - Đảm bảo cấu trúc: C:\xampp\htdocs\note_app\

Bước 3: Tạo Database
  - Mở phpMyAdmin: http://localhost/phpmyadmin
  - Import file: note_app/database/schema.sql
  - Database sẽ được tạo với tên: note_app_db

Bước 4: Cấu hình Database (nếu cần)
  - Mở file: config/database.php
  - Sửa thông số kết nối:
    $host = 'localhost';
    $username = 'root';
    $password = '';      (mặc định XAMPP để trống)
    $database = 'note_app_db';

Bước 5: Cài thư viện PHP (PHPMailer + Ratchet)
  - Mở Command Prompt/Terminal
  - cd C:\xampp\htdocs\note_app
  - Chạy lệnh:
    composer require phpmailer/phpmailer
    composer require cboden/ratchet

Bước 6: Cấu hình Email (Gửi mail kích hoạt/reset)
  - Mở file: config/mail.php
  - Thay đổi:
    MAIL_USERNAME = 'your-email@gmail.com'
    MAIL_PASSWORD = 'your-app-password'
  - Để tạo App Password cho Gmail:
    1. Đăng nhập Google Account
    2. Bảo mật > Xác minh 2 bước (bật lên)
    3. Tìm "App passwords" > Tạo mật khẩu ứng dụng
    4. Copy mật khẩu 16 ký tự vào MAIL_PASSWORD

Bước 7: Truy cập ứng dụng
  - Mở trình duyệt: http://localhost/note_app/

3. CHẠY WEBSOCKET SERVER (cho tính năng cộng tác)
-----------------------------------------------------------
  - Mở Command Prompt/Terminal mới
  - cd C:\xampp\htdocs\note_app
  - Chạy: php server/websocket.php
  - Server sẽ chạy trên cổng 8081
  - KHÔNG đóng cửa sổ này khi sử dụng tính năng cộng tác

4. TÀI KHOẢN DEMO
-----------------------------------------------------------
  Tài khoản 1:
    Email:    demo@example.com
    Mật khẩu: 123456

  Tài khoản 2:
    Email:    demo2@example.com
    Mật khẩu: 123456

  (Hai tài khoản dùng để test tính năng chia sẻ & cộng tác)

5. CÁC TÍNH NĂNG CHÍNH
-----------------------------------------------------------
  ✓ Đăng ký / Đăng nhập / Đăng xuất
  ✓ Kích hoạt tài khoản qua email
  ✓ Quên mật khẩu (OTP qua email)
  ✓ CRUD ghi chú (Thêm/Sửa/Xóa) với Auto-save
  ✓ Hiển thị Grid/List view
  ✓ Đính kèm nhiều hình ảnh
  ✓ Ghim (Pin) ghi chú
  ✓ Tìm kiếm Live Search (delay 300ms)
  ✓ Quản lý nhãn (Label) - CRUD + Lọc
  ✓ Khóa ghi chú bằng mật khẩu riêng
  ✓ Chia sẻ ghi chú (Read-only / Edit)
  ✓ Cộng tác thời gian thực (WebSocket)
  ✓ Tùy chỉnh: Font size, Màu sắc, Dark/Light theme
  ✓ Responsive Design (Mobile/Tablet/Desktop)
  ✓ PWA - Offline Capabilities

6. CÔNG NGHỆ SỬ DỤNG
-----------------------------------------------------------
  - HTML5, CSS3 (Responsive)
  - JavaScript thuần (DOM, Fetch API, Service Worker)
  - PHP OOP (Session, Cookie)
  - MySQL (MySQLi + Prepared Statements)
  - bcrypt (mã hóa mật khẩu)
  - PHPMailer (gửi email)
  - Ratchet WebSocket (cộng tác thời gian thực)
  - PWA (Service Worker + IndexedDB)

7. LƯU Ý
-----------------------------------------------------------
  - Sử dụng đường dẫn tương đối (relative URL)
  - Mô hình MVC (Model-View-Controller)
  - Prepared Statements chống SQL Injection
  - bcrypt hash cho mật khẩu
  - Nếu email không gửi được, tài khoản vẫn dùng bình thường
    (chỉ hiện banner nhắc kích hoạt)
