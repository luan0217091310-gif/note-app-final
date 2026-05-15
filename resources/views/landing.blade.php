<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteApp - Đơn giản và Hiệu quả</title>
    {{-- Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --bg-color: #ffffff;
            --text-color: #1f2937;
            --secondary-text: #6b7280;
            --card-bg: #f9fafb;
            --border-color: #e5e7eb;
            --success-color: #10b981;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .navbar {
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hero-section {
            padding: 100px 0 60px;
            text-align: center;
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.03em;
            margin-bottom: 24px;
        }

        .hero-description {
            font-size: 1.2rem;
            color: var(--secondary-text);
            max-width: 650px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            color: white;
            padding: 14px 36px;
            font-weight: 600;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            display: inline-block;
        }

        .btn-primary-custom:hover {
            background-color: #4338ca;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.15);
        }

        .btn-outline-custom {
            color: var(--text-color);
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid var(--border-color);
            display: inline-block;
        }

        .btn-outline-custom:hover {
            background-color: #f3f4f6;
            color: var(--text-color);
        }

        /* Features Intro Section */
        .features-intro {
            padding: 80px 0;
            background-color: #fff;
        }

        .section-tag {
            text-transform: uppercase;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.1em;
            margin-bottom: 15px;
            display: block;
            text-align: center;
        }

        .section-title {
            text-align: center;
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 50px;
            color: #111827;
        }

        .features-list-container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
        }

        .feature-check-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }

        .feature-check-item:last-child {
            border-bottom: none;
        }

        .check-icon {
            color: var(--success-color);
            font-weight: 900;
            font-size: 1.2rem;
        }

        .feature-label {
            font-weight: 500;
            color: #374151;
            font-size: 1.05rem;
        }

        footer {
            padding: 60px 0;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--secondary-text);
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .features-list-container { padding: 25px; }
            .hero-section { padding: 60px 20px 40px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="/" class="navbar-brand">
                <i class="bi bi-journal-text text-primary"></i>
                NoteApp
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('auth.login') }}" class="btn-outline-custom">Đăng nhập</a>
                <a href="{{ route('auth.register') }}" class="btn-primary-custom">Tham gia ngay</a>
            </div>
        </div>
    </nav>

    <main>
        {{-- Hero --}}
        <section class="hero-section container">
            <h1 class="hero-title">Đơn giản hóa việc ghi chú</h1>
            <p class="hero-description">
                NoteApp được thiết kế để mang lại trải nghiệm ghi chú mượt mà nhất. 
                Giúp bạn quản lý ý tưởng, công việc và cuộc sống một cách hiệu quả.
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('auth.register') }}" class="btn-primary-custom">Bắt đầu miễn phí</a>
            </div>
        </section>

        {{-- Features Introduction --}}
        <section class="features-intro">
            <div class="container">
                <span class="section-tag">Khám phá</span>
                <h2 class="section-title">Giới thiệu hệ thống</h2>
                
                <div class="features-list-container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Đăng ký / Đăng nhập / Đăng xuất (Laravel Auth)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Kích hoạt tài khoản qua email</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Quên mật khẩu (OTP qua email)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">CRUD ghi chú với Auto-save (không nút Save)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Hiển thị Grid / List view</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Đính kèm nhiều hình ảnh</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Ghim (Pin) ghi chú</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Tìm kiếm Live Search (delay 300ms)</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Quản lý nhãn (Label CRUD + Lọc)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Khóa ghi chú (confirm + verify)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Chia sẻ ghi chú (Read-only / Edit)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Cộng tác thời gian thực (WebSocket)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Tùy chỉnh: Font, Màu sắc, Theme</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">Responsive Design (Bootstrap 5)</span>
                            </div>
                            <div class="feature-check-item">
                                <span class="check-icon">✓</span>
                                <span class="feature-label">PWA - Offline Capabilities</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>© 2026 NoteApp. Đơn giản - Hiện đại - Hiệu quả.</p>
        </div>
    </footer>

</body>
</html>
