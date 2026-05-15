# 📝 Note Management Application — Laravel 12

Dự án cuối khóa môn **Web Programming & Applications (503073)**. Ứng dụng quản lý ghi chú hiện đại với khả năng cộng tác thời gian thực, chế độ ngoại tuyến (PWA) và bảo mật cao.

---

## 🌟 Tính năng chính (28 Tiêu chí chấm điểm)

Ứng dụng đáp ứng đầy đủ các yêu cầu từ đề bài:

### 1. Quản lý tài khoản (Account Management)

- [X] **Đăng ký/Đăng nhập**: Hệ thống xác thực Laravel chuẩn, mật khẩu mã hóa bcrypt.
- [X] **Kích hoạt tài khoản**: Gửi link xác nhận qua email (SMTP). Hiển thị banner nhắc nhở nếu chưa kích hoạt.
- [X] **Quên mật khẩu**: Khôi phục qua mã OTP gửi về email.
- [X] **Trang cá nhân & Avatar**: Xem và chỉnh sửa thông tin, đổi ảnh đại diện.
- [X] **Tùy chỉnh (Preferences)**: Thay đổi kích thước font, màu sắc ghi chú, giao diện Sáng/Tối (Dark/Light Mode).

### 2. Quản lý ghi chú (Simple Note Management)

- [X] **Giao diện linh hoạt**: Chuyển đổi qua lại giữa Grid view (mặc định) và List view.
- [X] **CRUD Ghi chú**: Thêm, sửa, xóa ghi chú (có xác nhận xóa).
- [X] **Auto-save**: Tự động lưu nội dung khi đang soạn thảo, không cần nút Save.
- [X] **Đính kèm hình ảnh**: Hỗ trợ tải lên nhiều ảnh cho một ghi chú.
- [X] **Ghim ghi chú (Pin)**: Giữ các ghi chú quan trọng ở đầu danh sách.
- [X] **Tìm kiếm Live Search**: Tìm kiếm theo tiêu đề/nội dung với độ trễ 300ms tối ưu hiệu năng.
- [X] **Quản lý Nhãn (Labels)**: Thêm, sửa, xóa nhãn; gắn nhãn cho ghi chú và lọc ghi chú theo nhãn.

### 3. Tính năng nâng cao (Advanced Features)

- [X] **Khóa ghi chú**: Đặt mật khẩu riêng cho từng ghi chú. Yêu cầu mật khẩu khi xem/sửa/xóa.
- [X] **Chia sẻ ghi chú**: Chia sẻ qua email với quyền Chỉ xem (Read-only) hoặc Có thể chỉnh sửa (Edit).
- [X] **Cộng tác thời gian thực**: Sử dụng **WebSocket (Laravel Reverb)** để nhiều người cùng chỉnh sửa một ghi chú đồng thời.
- [X] **Biểu tượng trạng thái**: Hiển thị icon nhận diện cho ghi chú đã Pin, Khóa, hoặc được Chia sẻ.

### 4. Công nghệ & UX/UI

- [X] **Responsive Design**: Tương thích hoàn hảo trên Mobile, Tablet và Desktop (Bootstrap 5).
- [X] **PWA (Offline Capabilities)**: Sử dụng Service Worker và IndexedDB để truy cập và xem ghi chú ngay cả khi không có mạng.
- [X] **UI/UX Premium**: Giao diện hiện đại, hiệu ứng mượt mà, Dark mode chuyên nghiệp.

---

## 🛠 Công nghệ sử dụng

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Bootstrap 5.3, Bootstrap Icons
- **Real-time**: Laravel Reverb (WebSocket)
- **Database**: MySQL
- **Email**: SMTP Gmail
- **Offline**: Service Worker + IndexedDB

---

## 🚀 Hướng dẫn cài đặt LOCAL (XAMPP)

### 1. Chuẩn bị

- Cài đặt **XAMPP** (PHP 8.2+) và **Composer**.
- Khởi động **Apache** và **MySQL** từ XAMPP Control Panel.

### 2. Cài đặt dự án

Mở terminal (CMD/PowerShell) tại thư mục dự án:

```bash
# 1. Cài đặt các thư viện PHP
composer install

# 2. Tạo file cấu hình và Key
copy .env.example .env
php artisan key:generate

# 3. Tạo Database
# Vào http://localhost/phpmyadmin tạo database tên: note_app_db
# Sau đó cập nhật thông tin DB_DATABASE trong file .env

# 4. Chạy Migration và Seed dữ liệu mẫu
php artisan migrate --seed

# 5. Tạo link lưu trữ ảnh
php artisan storage:link
```

### 3. Chạy WebSocket (Bắt buộc cho tính năng Cộng tác)

Mở một cửa sổ terminal **mới** và chạy:

```bash
php artisan reverb:start
```

*Giữ cửa sổ này hoạt động trong suốt quá trình sử dụng.*

### 4. Truy cập

Mở trình duyệt: `http://localhost/note-app-final/public/`

---

## 🔑 Tài khoản Demo

| Email                 | Mật khẩu | Vai trò        |
| --------------------- | ---------- | --------------- |
| `demo@example.com`  | `123456` | Người dùng 1 |
| `demo2@example.com` | `123456` | Người dùng 2 |

*Sử dụng 2 trình duyệt khác nhau để test tính năng chia sẻ và cộng tác thời gian thực.*

---
