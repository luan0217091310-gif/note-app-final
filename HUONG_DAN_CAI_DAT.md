# 📘 HƯỚNG DẪN CÀI ĐẶT & TRIỂN KHAI
## Note Management Application — Laravel 12 + Bootstrap 5

---

## 📋 MỤC LỤC

1. [Yêu cầu hệ thống](#1-yêu-cầu-hệ-thống)
2. [Cài đặt Local (XAMPP)](#2-cài-đặt-local-xampp)
3. [Cấu hình Database (MySQL)](#3-cấu-hình-database-mysql)
4. [Cấu hình Email gửi OTP (Gmail)](#4-cấu-hình-email-gửi-otp-gmail)
5. [Cấu hình file .env đầy đủ](#5-cấu-hình-file-env-đầy-đủ)
6. [Chạy WebSocket (Real-time)](#6-chạy-websocket-real-time)
7. [Deploy lên Online (InfinityFree / Railway)](#7-deploy-lên-online)
8. [Tài khoản demo](#8-tài-khoản-demo)
9. [Xử lý lỗi thường gặp](#9-xử-lý-lỗi-thường-gặp)

---

## 1. YÊU CẦU HỆ THỐNG

| Thành phần | Phiên bản tối thiểu | Link tải |
|---|---|---|
| PHP | >= 8.2 | Có sẵn trong XAMPP |
| MySQL | >= 5.7 | Có sẵn trong XAMPP |
| Apache | >= 2.4 | Có sẵn trong XAMPP |
| XAMPP | >= 8.2 | https://www.apachefriends.org/ |
| Composer | Bất kỳ | https://getcomposer.org/ |
| Trình duyệt | Chrome / Firefox / Edge | — |

---

## 2. CÀI ĐẶT LOCAL (XAMPP)

### Bước 1 — Khởi động XAMPP
1. Mở **XAMPP Control Panel**
2. Nhấn **Start** cho **Apache** và **MySQL**
3. Đảm bảo cả 2 đều hiển thị màu **xanh lá**

### Bước 2 — Sao chép dự án
```
Sao chép thư mục  laravel-app  vào:
C:\xampp\htdocs\note-laravel\
```

> ✅ Kết quả: `C:\xampp\htdocs\note-laravel\artisan` phải tồn tại

### Bước 3 — Cài thư viện PHP (Composer)
Mở **Command Prompt** (không cần Admin), chạy:

```bash
cd C:\xampp\htdocs\note-laravel

C:\xampp\php\php.exe composer.phar install
```

> ⏳ Quá trình này mất 2–5 phút tùy tốc độ mạng.

### Bước 4 — Tạo file .env
```bash
copy .env.example .env
C:\xampp\php\php.exe artisan key:generate
```

### Bước 5 — Chạy Migration + Seed dữ liệu demo
```bash
C:\xampp\php\php.exe artisan migrate --seed
```

### Bước 6 — Tạo symbolic link cho ảnh upload
```bash
C:\xampp\php\php.exe artisan storage:link
```

### Bước 7 — Truy cập ứng dụng
Mở trình duyệt:
```
http://localhost/note-laravel/public/
```

---

## 3. CẤU HÌNH DATABASE (MySQL)

### Tạo Database trong phpMyAdmin

1. Mở trình duyệt → `http://localhost/phpmyadmin`
2. Click **"New"** ở sidebar trái
3. Nhập tên database: `note_laravel_db`
4. Chọn collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

### Cấu hình trong file `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=note_laravel_db
DB_USERNAME=root
DB_PASSWORD=
```

> 📌 XAMPP mặc định: username = `root`, password = **để trống**

### Sau khi cấu hình, chạy lại:
```bash
C:\xampp\php\php.exe artisan migrate:fresh --seed
```

**`migrate:fresh`** sẽ xóa toàn bộ bảng và tạo lại từ đầu (dùng khi muốn reset sạch).

---

## 4. CẤU HÌNH EMAIL GỬI OTP (GMAIL)

> Ứng dụng dùng email để:
> - Gửi **link kích hoạt tài khoản** sau đăng ký
> - Gửi **mã OTP** để đặt lại mật khẩu (có hiệu lực 15 phút)

### Bước A — Bật xác minh 2 bước Google

1. Truy cập: https://myaccount.google.com/security
2. Tìm mục **"Xác minh 2 bước"** → Nhấn **Bật**
3. Làm theo hướng dẫn để hoàn tất

### Bước B — Tạo App Password (Mật khẩu ứng dụng)

1. Truy cập: https://myaccount.google.com/apppasswords
2. Chọn **"Tên ứng dụng"** → nhập bất kỳ (ví dụ: `NoteApp`)
3. Nhấn **"Tạo"**
4. Google sẽ cấp mật khẩu **16 ký tự** (dạng: `xxxx xxxx xxxx xxxx`)
5. **Copy mật khẩu này** (chỉ hiển thị 1 lần!)

### Bước C — Cấu hình trong `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Note App"
```

> ⚠️ **Thay** `your-email@gmail.com` bằng email thật của bạn  
> ⚠️ **Thay** `xxxx xxxx xxxx xxxx` bằng App Password vừa tạo  
> ⚠️ Mật khẩu App Password có thể **có khoảng trắng hoặc không** đều được

### Kiểm tra email hoạt động

Sau khi cấu hình, đăng ký tài khoản mới trong ứng dụng. Nếu nhận được email → **thành công**!

> 💡 **Nếu không cấu hình email**: Ứng dụng vẫn chạy bình thường, chỉ hiện banner nhắc kích hoạt nhưng tài khoản vẫn dùng được nếu `is_activated = 1` trong database.

---

## 5. CẤU HÌNH FILE .ENV ĐẦY ĐỦ

Đây là file `.env` hoàn chỉnh sau khi cấu hình xong:

```env
APP_NAME="Note App"
APP_ENV=local
APP_KEY=base64:...    # Tự động tạo bởi artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost/note-laravel/public

# ── DATABASE ──────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=note_laravel_db
DB_USERNAME=root
DB_PASSWORD=

# ── SESSION & CACHE ───────────────────────
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

# ── EMAIL (GMAIL SMTP) ────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Note App"
```

---

## 6. CHẠY WEBSOCKET (REAL-TIME COLLABORATION)

WebSocket cho phép nhiều người cùng chỉnh sửa một ghi chú đồng thời.

### Khởi động WebSocket Server

Mở **cửa sổ Command Prompt MỚI** (giữ cửa sổ này mở):
```bash
cd C:\xampp\htdocs\note-laravel
C:\xampp\php\php.exe server\websocket.php
```

> Kết quả hiển thị: `WebSocket Server running on port 8081`

### Lưu ý
- **KHÔNG đóng** cửa sổ CMD này khi đang dùng tính năng cộng tác
- WebSocket chạy trên cổng **8081**
- Khi deploy online, cần server hỗ trợ chạy process liên tục (VPS/Railway)

---

## 7. DEPLOY LÊN ONLINE

Có 2 cách deploy: **Railway** (dễ, miễn phí) và **VPS/cPanel** (chuyên nghiệp).

---

### CÁCH 1: Deploy lên Railway (Khuyến nghị — Miễn phí)

Railway là nền tảng cloud dễ dùng nhất cho Laravel.

#### Bước 1 — Tạo tài khoản Railway
1. Truy cập: https://railway.app
2. **"Login with GitHub"** → Đăng nhập bằng GitHub

#### Bước 2 — Đẩy code lên GitHub
```bash
# Trong thư mục dự án
git init
git add .
git commit -m "Initial commit - Note App Laravel"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/note-app.git
git push -u origin main
```

#### Bước 3 — Tạo project trên Railway
1. Nhấn **"New Project"**
2. Chọn **"Deploy from GitHub repo"**
3. Chọn repo vừa push
4. Railway tự detect Laravel và build

#### Bước 4 — Thêm MySQL Database
1. Trong project Railway → Nhấn **"+ New"**
2. Chọn **"Database"** → **"MySQL"**
3. Railway tự tạo database và cấp thông tin kết nối

#### Bước 5 — Cấu hình Environment Variables
Vào **"Variables"** trong Railway, thêm:

```
APP_NAME=Note App
APP_ENV=production
APP_KEY=base64:...           # Copy từ file .env local của bạn
APP_DEBUG=false
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql
DB_HOST=                     # Copy từ Railway MySQL Variables
DB_PORT=                     # Copy từ Railway MySQL Variables
DB_DATABASE=                 # Copy từ Railway MySQL Variables
DB_USERNAME=                 # Copy từ Railway MySQL Variables
DB_PASSWORD=                 # Copy từ Railway MySQL Variables

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Note App"
```

#### Bước 6 — Chạy Migration trên Railway
Vào **"Settings"** → **"Deploy"** → thêm **Start Command**:
```bash
php artisan migrate --seed --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=$PORT
```

> ✅ Railway sẽ tự động chạy khi deploy

---

### CÁCH 2: Deploy lên cPanel / InfinityFree (Hosting thông thường)

#### Bước 1 — Chuẩn bị file để upload
```bash
# Chạy trên máy local để tối ưu
C:\xampp\php\php.exe artisan config:cache
C:\xampp\php\php.exe artisan route:cache
```

**Xóa trước khi nén ZIP** (để file nhỏ hơn):
```
vendor/          ← Xóa (giảng viên chạy composer install)
node_modules/    ← Xóa nếu có
storage/logs/    ← Xóa
.git/            ← Xóa
```

#### Bước 2 — Upload lên Hosting
1. Đăng nhập **cPanel** của hosting
2. Vào **File Manager** → `public_html`
3. Upload toàn bộ thư mục dự án (trừ `public/`)
4. Upload riêng **nội dung** thư mục `public/` vào `public_html/`

#### Bước 3 — Sửa `public/index.php`
```php
// Sửa đường dẫn trỏ đúng về thư mục dự án
require __DIR__.'/../note-laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../note-laravel/bootstrap/app.php';
```

#### Bước 4 — Tạo Database trên cPanel
1. Vào **MySQL Databases** trong cPanel
2. Tạo database mới (ví dụ: `user_notedb`)
3. Tạo user MySQL và gán quyền **ALL PRIVILEGES**
4. Ghi lại: host, database, username, password

#### Bước 5 — Cập nhật `.env` trên hosting
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=localhost
DB_DATABASE=user_notedb
DB_USERNAME=user_dbuser
DB_PASSWORD=your-db-password
```

#### Bước 6 — Chạy Migration qua SSH hoặc phpMyAdmin
**Nếu có SSH:**
```bash
php artisan migrate --seed --force
php artisan storage:link
```

**Nếu không có SSH** → Import file SQL vào phpMyAdmin:
1. Chạy trên máy local: `C:\xampp\php\php.exe artisan schema:dump`
2. Upload file `database/schema.sql` lên phpMyAdmin

---

### CÁCH 3: Docker Compose (Cho giảng viên chấm điểm)

Tạo file `docker-compose.yml` trong thư mục dự án:

```yaml
version: '3.8'
services:
  app:
    image: php:8.2-apache
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html/note-app
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_DATABASE=note_laravel_db
      - DB_USERNAME=root
      - DB_PASSWORD=secret

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: note_laravel_db
    ports:
      - "3307:3306"
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

**Chạy Docker:**
```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

**Truy cập:** `http://localhost:8080/note-app/public/`

---

## 8. TÀI KHOẢN DEMO

| Email | Mật khẩu | Ghi chú |
|---|---|---|
| `demo@example.com` | `123456` | Tài khoản 1 |
| `demo2@example.com` | `123456` | Tài khoản 2 |

> 💡 Dùng 2 tài khoản để test tính năng **Chia sẻ** và **Cộng tác thời gian thực**

---

## 9. XỬ LÝ LỖI THƯỜNG GẶP

### ❌ Lỗi: "No application encryption key has been specified"
```bash
C:\xampp\php\php.exe artisan key:generate
```

### ❌ Lỗi: "SQLSTATE[HY000] [2002] No connection"
- Kiểm tra MySQL đã **Start** trong XAMPP chưa
- Kiểm tra `DB_HOST`, `DB_PORT` trong `.env`
- Đảm bảo database `note_laravel_db` đã được tạo

### ❌ Lỗi: "Class not found" hoặc "Target class does not exist"
```bash
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
C:\xampp\php\php.exe composer.phar dump-autoload
```

### ❌ Lỗi: Ảnh upload không hiển thị
```bash
C:\xampp\php\php.exe artisan storage:link
```

### ❌ Lỗi: Email không gửi được
1. Kiểm tra đã **bật xác minh 2 bước** trong Google chưa
2. Kiểm tra **App Password** có đúng không (16 ký tự)
3. Kiểm tra `MAIL_PORT=587` và `MAIL_ENCRYPTION=tls`
4. Thử đổi port: `MAIL_PORT=465` và `MAIL_ENCRYPTION=ssl`

### ❌ Lỗi: "Permission denied" trên Linux/hosting
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### ❌ Lỗi: 404 trên Apache (routing không hoạt động)
Kiểm tra file `.htaccess` trong thư mục `public/` phải tồn tại:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```
Đồng thời đảm bảo `mod_rewrite` đã được bật trong XAMPP.

---

## 🔗 TÀI LIỆU THAM KHẢO

- Laravel Docs: https://laravel.com/docs
- Bootstrap 5: https://getbootstrap.com/docs/5.3/
- Railway Deploy: https://docs.railway.app
- XAMPP: https://www.apachefriends.org/faq_windows.html

---

*Tài liệu tạo ngày: 2026-05-08 | Note Management App v1.0*
