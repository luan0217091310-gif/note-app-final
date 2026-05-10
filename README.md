# Note Management Application - Laravel
> Môn: 503073 - Web Programming & Applications

## Công nghệ sử dụng
- **Backend**: Laravel 12 (PHP Framework)
- **Frontend**: Bootstrap 5.3 + Bootstrap Icons
- **Database**: MySQL (XAMPP)
- **Email**: SMTP Gmail (Laravel Mail)
- **Real-time**: WebSocket (Ratchet)
- **PWA**: Service Worker + IndexedDB

---

## Cài đặt LOCAL (XAMPP)

### Bước 1: Chuẩn bị
- Cài XAMPP: https://www.apachefriends.org/
- Khởi động **Apache** và **MySQL** trong XAMPP Control Panel

### Bước 2: Sao chép dự án
```
Sao chép thư mục laravel-app vào: C:\xampp\htdocs\note-laravel\
```

### Bước 3: Tạo Database
- Vào **phpMyAdmin**: http://localhost/phpmyadmin
- Tạo database mới: `note_laravel_db`

### Bước 4: Cấu hình .env
```env
DB_DATABASE=note_laravel_db
DB_USERNAME=root
DB_PASSWORD=          # XAMPP mặc định để trống
```

### Bước 5: Cài đặt
Mở **Command Prompt**, chạy:
```bash
cd C:\xampp\htdocs\note-laravel
C:\xampp\php\php.exe composer.phar install
C:\xampp\php\php.exe artisan migrate --seed
C:\xampp\php\php.exe artisan storage:link
```

### Bước 6: Truy cập
```
http://localhost/note-laravel/public/
```

---

## Tài khoản demo
| Email | Mật khẩu |
|---|---|
| demo@example.com | 123456 |
| demo2@example.com | 123456 |

*(Dùng 2 tài khoản để test tính năng Chia sẻ & Cộng tác)*

---

## Chạy WebSocket (Real-time Collaboration)
```bash
C:\xampp\php\php.exe server\websocket.php
```
WebSocket chạy trên cổng **8081**. Giữ cửa sổ này mở khi dùng tính năng cộng tác.

---

## Cấu hình Email (tùy chọn)
Trong file `.env`:
```env
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password    # Google App Password 16 ký tự
```
> Nếu không cấu hình email, tài khoản vẫn sử dụng được (chỉ hiện banner nhắc kích hoạt).

---

## Tính năng chính
✓ Đăng ký / Đăng nhập / Đăng xuất (Laravel Auth)  
✓ Kích hoạt tài khoản qua email  
✓ Quên mật khẩu (OTP qua email)  
✓ CRUD ghi chú với Auto-save (không nút Save)  
✓ Hiển thị Grid / List view  
✓ Đính kèm nhiều hình ảnh  
✓ Ghim (Pin) ghi chú  
✓ Tìm kiếm Live Search (delay 300ms)  
✓ Quản lý nhãn (Label CRUD + Lọc)  
✓ Khóa ghi chú (Better Approach: confirm + verify)  
✓ Chia sẻ ghi chú (Read-only / Edit)  
✓ Cộng tác thời gian thực (WebSocket)  
✓ Tùy chỉnh: Font, Màu sắc, Dark/Light theme  
✓ Responsive Design (Bootstrap 5)  
✓ PWA - Offline Capabilities (Service Worker + IndexedDB)  
