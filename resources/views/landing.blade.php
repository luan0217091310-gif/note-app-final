<!DOCTYPE html>
<html lang="vi" id="html-root">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NoteApp — Ghi Chú Thông Minh</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --c1:#000000;--c2:#333333;--c3:#555555;--c4:#777777;
  --bg:#ffffff;--bg2:#f9f9f9;--bg3:#f2f2f2;
  --txt:#111111;--txt2:#555555;--txt3:#999999;
  --card:#ffffff;--border:rgba(0,0,0,.10);
  --shadow:0 4px 24px rgba(0,0,0,.06);
  --shadow-lg:0 16px 48px rgba(0,0,0,.10);
  --nav-bg:rgba(255,255,255,.88);
  --grid-line:rgba(0,0,0,.04);
}
[data-theme=dark]{
  --bg:#0a0a0a;--bg2:#111111;--bg3:#1a1a1a;
  --txt:#f0f0f0;--txt2:#aaaaaa;--txt3:#666666;
  --card:#151515;--border:rgba(255,255,255,.10);
  --shadow:0 4px 24px rgba(0,0,0,.5);
  --shadow-lg:0 16px 48px rgba(0,0,0,.7);
  --nav-bg:rgba(10,10,10,.88);
  --grid-line:rgba(255,255,255,.04);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--txt);transition:background .3s,color .3s;min-height:100vh;overflow-x:hidden}

/* Grid background */
.grid-bg{
  position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:linear-gradient(var(--grid-line) 1px,transparent 1px),linear-gradient(90deg,var(--grid-line) 1px,transparent 1px);
  background-size:48px 48px;
}

/* Navbar */
.lp-nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  background:var(--nav-bg);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  padding:0 32px;height:64px;
  display:flex;align-items:center;justify-content:space-between;
  transition:background .3s;
}
.nav-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;color:var(--txt);text-decoration:none}
.brand-dot{width:32px;height:32px;background:#111;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px}
[data-theme=dark] .brand-dot{background:#fff;color:#111}
.nav-actions{display:flex;align-items:center;gap:12px}
.btn-theme{background:none;border:1px solid var(--border);color:var(--txt2);width:40px;height:40px;border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;transition:all .2s}
.btn-theme:hover{background:var(--bg3);color:var(--txt)}
.btn-login{padding:8px 20px;border-radius:12px;border:1px solid var(--border);background:var(--card);color:var(--txt);font-weight:500;font-size:14px;text-decoration:none;transition:all .2s}
.btn-login:hover{border-color:#111;color:#111}
[data-theme=dark] .btn-login:hover{border-color:#fff;color:#fff}
.btn-start{padding:8px 20px;border-radius:12px;background:#111;color:#fff;font-weight:600;font-size:14px;text-decoration:none;transition:all .2s;border:none;box-shadow:0 4px 12px rgba(0,0,0,.15)}
[data-theme=dark] .btn-start{background:#fff;color:#111}
.btn-start:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,0,0,.25);color:#fff}
[data-theme=dark] .btn-start:hover{color:#111}

/* Hero */
.hero{
  position:relative;z-index:1;
  padding:160px 32px 100px;
  text-align:center;
  min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 16px;border-radius:100px;
  background:var(--bg3);border:1px solid var(--border);
  font-size:13px;font-weight:500;color:var(--txt);margin-bottom:28px;
}
.hero-badge span{width:8px;height:8px;border-radius:50%;background:#111;animation:pulse 2s infinite}
[data-theme=dark] .hero-badge span{background:#fff}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(.85)}}

.hero h1{
  font-size:clamp(40px,7vw,80px);font-weight:900;line-height:1.1;
  color:var(--txt);
  margin-bottom:24px;
}
.hero p{font-size:clamp(16px,2.5vw,20px);color:var(--txt2);max-width:560px;line-height:1.7;margin-bottom:44px}

.hero-actions{display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-bottom:64px}
.btn-hero-primary{
  padding:14px 32px;border-radius:16px;font-size:16px;font-weight:700;text-decoration:none;
  background:#111;color:#fff;
  box-shadow:0 8px 24px rgba(0,0,0,.18);transition:all .25s;border:none;
}
[data-theme=dark] .btn-hero-primary{background:#fff;color:#111}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(0,0,0,.28);color:#fff}
[data-theme=dark] .btn-hero-primary:hover{color:#111}
.btn-hero-secondary{
  padding:14px 32px;border-radius:16px;font-size:16px;font-weight:600;text-decoration:none;
  background:var(--card);color:var(--txt);border:1px solid var(--border);transition:all .25s;
}
.btn-hero-secondary:hover{border-color:#111;color:#111;transform:translateY(-2px)}
[data-theme=dark] .btn-hero-secondary:hover{border-color:#fff;color:#fff}

/* Stats */
.stats{display:flex;gap:48px;justify-content:center;flex-wrap:wrap}
.stat-item{text-align:center}
.stat-num{font-size:28px;font-weight:800;color:var(--txt)}
.stat-lbl{font-size:13px;color:var(--txt3);margin-top:2px}

/* Preview card floating */
.hero-preview{
  margin-top:72px;position:relative;z-index:1;width:100%;max-width:900px;
}
.preview-card{
  background:var(--card);border:1px solid var(--border);border-radius:20px;
  box-shadow:var(--shadow-lg);overflow:hidden;
  animation:float 6s ease-in-out infinite;
}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.preview-topbar{background:var(--bg2);border-bottom:1px solid var(--border);padding:12px 20px;display:flex;align-items:center;gap:8px}
.dot{width:12px;height:12px;border-radius:50%}
.dot-r{background:#999}.dot-y{background:#bbb}.dot-g{background:#ddd}
[data-theme=dark] .dot-r{background:#555}[data-theme=dark] .dot-y{background:#444}[data-theme=dark] .dot-g{background:#333}
.preview-body{padding:20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px}
.mini-note{
  background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:14px;
  transition:all .2s;cursor:pointer;
}
.mini-note:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.mini-note-title{font-weight:600;font-size:13px;margin-bottom:6px;color:var(--txt)}
.mini-note-body{font-size:11px;color:var(--txt3);line-height:1.6}
.mini-note-tag{display:inline-block;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;margin-top:8px}
.tag-purple{background:rgba(0,0,0,.08);color:#333}
.tag-pink{background:rgba(0,0,0,.05);color:#555}
.tag-cyan{background:rgba(0,0,0,.06);color:#444}
[data-theme=dark] .tag-purple{background:rgba(255,255,255,.1);color:#ccc}
[data-theme=dark] .tag-pink{background:rgba(255,255,255,.07);color:#aaa}
[data-theme=dark] .tag-cyan{background:rgba(255,255,255,.08);color:#bbb}

/* Features */
.section{position:relative;z-index:1;padding:80px 32px}
.section-label{font-size:13px;font-weight:600;color:var(--txt3);letter-spacing:2px;text-transform:uppercase;margin-bottom:12px}
.section-title{font-size:clamp(28px,4vw,44px);font-weight:800;color:var(--txt);margin-bottom:16px;line-height:1.2}
.section-desc{font-size:16px;color:var(--txt2);max-width:500px;line-height:1.7}

.features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;margin-top:52px}
.feature-card{
  background:var(--card);border:1px solid var(--border);border-radius:20px;padding:28px;
  transition:all .25s;position:relative;overflow:hidden;
}
.feature-card::before{
  content:'';position:absolute;inset:0;border-radius:20px;
  background:rgba(0,0,0,.02);opacity:0;transition:.25s;
}
[data-theme=dark] .feature-card::before{background:rgba(255,255,255,.03)}
.feature-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
.feature-card:hover::before{opacity:1}
.feature-icon{
  width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;
  font-size:22px;margin-bottom:18px;
}
.fi-purple,.fi-pink,.fi-cyan,.fi-orange,.fi-green,.fi-blue{background:rgba(0,0,0,.06);color:#333}
[data-theme=dark] .fi-purple,[data-theme=dark] .fi-pink,[data-theme=dark] .fi-cyan,[data-theme=dark] .fi-orange,[data-theme=dark] .fi-green,[data-theme=dark] .fi-blue{background:rgba(255,255,255,.08);color:#ddd}
.feature-card h3{font-size:17px;font-weight:700;color:var(--txt);margin-bottom:8px}
.feature-card p{font-size:14px;color:var(--txt2);line-height:1.6}

/* CTA */
.cta-section{
  position:relative;z-index:1;margin:0 32px 80px;border-radius:28px;
  background:#111;
  padding:72px 40px;text-align:center;overflow:hidden;
}
[data-theme=dark] .cta-section{background:#fff}
.cta-section::before{
  content:'';position:absolute;inset:-50%;
  background:radial-gradient(circle at 30% 50%,rgba(255,255,255,.03) 0%,transparent 60%);
}
.cta-section h2{font-size:clamp(28px,4vw,44px);font-weight:900;color:#fff;margin-bottom:16px;position:relative}
[data-theme=dark] .cta-section h2{color:#111}
.cta-section p{font-size:18px;color:rgba(255,255,255,.7);margin-bottom:36px;position:relative}
[data-theme=dark] .cta-section p{color:rgba(0,0,0,.6)}
.btn-cta{
  display:inline-flex;align-items:center;gap:10px;
  padding:16px 40px;border-radius:16px;background:#fff;
  color:#111;font-weight:700;font-size:16px;text-decoration:none;
  box-shadow:0 8px 32px rgba(0,0,0,.2);transition:all .25s;position:relative;
}
[data-theme=dark] .btn-cta{background:#111;color:#fff}
.btn-cta:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(0,0,0,.3);color:#111}
[data-theme=dark] .btn-cta:hover{color:#fff}

/* Footer */
footer{
  position:relative;z-index:1;
  border-top:1px solid var(--border);padding:32px;
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;
}
footer p{font-size:14px;color:var(--txt3)}

/* Responsive */
@media(max-width:768px){
  .lp-nav{padding:0 16px}
  .hero{padding:120px 16px 60px}
  .stats{gap:24px}
  .section{padding:60px 16px}
  .cta-section{margin:0 16px 60px;padding:48px 24px}
  footer{padding:24px 16px;flex-direction:column;text-align:center}
  .preview-body{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>
<div class="grid-bg"></div>

<!-- Nav -->
<nav class="lp-nav">
  <a href="/" class="nav-brand">
    <div class="brand-dot"><i class="bi bi-journal-text"></i></div>
    NoteApp
  </a>
  <div class="nav-actions">
    <button class="btn-theme" id="themeBtn" title="Đổi giao diện">
      <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>
    <a href="{{ route('auth.login') }}" class="btn-login">Đăng nhập</a>
    <a href="{{ route('auth.register') }}" class="btn-start">Bắt đầu miễn phí</a>
  </div>
</nav>

<!-- Hero -->
<section class="hero">
  <div class="hero-badge">
    <span></span> Ứng dụng ghi chú thế hệ mới
  </div>
  <h1>Ghi lại mọi ý tưởng<br>cộng tác thời gian thực</h1>
  <p>Quản lý ghi chú thông minh với bảo mật mật khẩu, chia sẻ cộng tác, tìm kiếm tức thì và hoạt động offline hoàn toàn.</p>
  <div class="hero-actions">
    <a href="{{ route('auth.register') }}" class="btn-hero-primary">
      <i class="bi bi-rocket-takeoff me-2"></i>Dùng thử ngay — Miễn phí
    </a>
    <a href="{{ route('auth.login') }}" class="btn-hero-secondary">
      <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
    </a>
  </div>
  <div class="stats">
    <div class="stat-item"><div class="stat-num">28</div><div class="stat-lbl">Tính năng</div></div>
    <div class="stat-item"><div class="stat-num">∞</div><div class="stat-lbl">Ghi chú</div></div>
    <div class="stat-item"><div class="stat-num">PWA</div><div class="stat-lbl">Offline Ready</div></div>
    <div class="stat-item"><div class="stat-num">100%</div><div class="stat-lbl">Bảo mật</div></div>
  </div>

  <!-- App Preview -->
  <div class="hero-preview">
    <div class="preview-card">
      <div class="preview-topbar">
        <div class="dot dot-r"></div><div class="dot dot-y"></div><div class="dot dot-g"></div>
        <span style="margin-left:12px;font-size:12px;color:var(--txt3)">NoteApp — Tất cả ghi chú</span>
      </div>
      <div class="preview-body">
        <div class="mini-note"><div class="mini-note-title"><i class="bi bi-pin-angle-fill me-1"></i> Ý tưởng dự án</div><div class="mini-note-body">Xây dựng hệ thống ghi chú thời gian thực với WebSocket...</div><span class="mini-note-tag tag-purple">Công việc</span></div>
        <div class="mini-note"><div class="mini-note-title"><i class="bi bi-bullseye me-1"></i> Mục tiêu tuần</div><div class="mini-note-body">Hoàn thành báo cáo, học thêm về Laravel Reverb...</div><span class="mini-note-tag tag-pink">Cá nhân</span></div>
        <div class="mini-note"><div class="mini-note-title"><i class="bi bi-lock-fill me-1"></i> Ghi chú bí mật</div><div class="mini-note-body">••••••••••••••••</div><span class="mini-note-tag tag-cyan">Riêng tư</span></div>
        <div class="mini-note"><div class="mini-note-title"><i class="bi bi-book-fill me-1"></i> Tài liệu Laravel</div><div class="mini-note-body">Route, Controller, Middleware, Blade templates...</div><span class="mini-note-tag tag-purple">Học tập</span></div>
        <div class="mini-note"><div class="mini-note-title"><i class="bi bi-people-fill me-1"></i> Ghi chú chia sẻ</div><div class="mini-note-body">Được chia sẻ bởi team — quyền chỉnh sửa...</div><span class="mini-note-tag tag-pink">Team</span></div>
      </div>
    </div>
  </div>
</section>

<!-- Features -->
<section class="section">
  <div class="container-xl">
    <div class="text-center mb-5">
      <div class="section-label">Tính năng nổi bật</div>
      <div class="section-title">Mọi thứ bạn cần<br>trong một ứng dụng</div>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon fi-purple"><i class="bi bi-shield-lock-fill"></i></div>
        <h3>Bảo mật mật khẩu</h3>
        <p>Khóa từng ghi chú bằng mật khẩu riêng biệt. Đổi mật khẩu bảo vệ an toàn với xác thực 2 bước.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon fi-pink"><i class="bi bi-people-fill"></i></div>
        <h3>Chia sẻ & Cộng tác</h3>
        <p>Chia sẻ ghi chú với quyền đọc hoặc chỉnh sửa. Nhiều người cùng edit đồng thời theo thời gian thực.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon fi-cyan"><i class="bi bi-wifi-off"></i></div>
        <h3>Hoạt động Offline</h3>
        <p>PWA với Service Worker. Xem và chỉnh sửa ghi chú khi mất mạng, tự động đồng bộ khi có mạng lại.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon fi-orange"><i class="bi bi-search"></i></div>
        <h3>Tìm kiếm tức thì</h3>
        <p>Live search debounce 300ms tìm trong tiêu đề và nội dung. Kết quả hiện ngay khi bạn gõ.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon fi-green"><i class="bi bi-tags-fill"></i></div>
        <h3>Nhãn dán thông minh</h3>
        <p>Tạo, đổi tên, xóa nhãn. Gắn nhiều nhãn cho một ghi chú. Lọc ghi chú theo nhãn tức thì.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon fi-blue"><i class="bi bi-pin-map-fill"></i></div>
        <h3>Ghim ưu tiên</h3>
        <p>Ghim ghi chú quan trọng lên đầu. Sắp xếp theo thời gian ghim. Biểu tượng trạng thái rõ ràng.</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<div class="cta-section">
  <h2>Sẵn sàng ghi chú thông minh hơn?</h2>
  <p>Đăng ký miễn phí và trải nghiệm ngay hôm nay.</p>
  <a href="{{ route('auth.register') }}" class="btn-cta">
    <i class="bi bi-rocket-takeoff-fill"></i>Bắt đầu ngay — Miễn phí
  </a>
</div>

<!-- Footer -->
<footer>
  <div class="nav-brand" style="font-size:16px">
    <div class="brand-dot" style="width:28px;height:28px;font-size:13px"><i class="bi bi-journal-text"></i></div>
    NoteApp
  </div>
  <p>© 2025 NoteApp — Đồ án cuối kỳ Web Programming & Applications</p>
  <div style="display:flex;gap:16px">
    <a href="{{ route('auth.login') }}" style="font-size:14px;color:var(--txt3);text-decoration:none">Đăng nhập</a>
    <a href="{{ route('auth.register') }}" style="font-size:14px;color:var(--txt);text-decoration:none;font-weight:600">Đăng ký</a>
  </div>
</footer>

<script>
const html = document.getElementById('html-root');
const icon = document.getElementById('themeIcon');
let dark = localStorage.getItem('lp-theme') === 'dark';

function applyTheme() {
  html.setAttribute('data-theme', dark ? 'dark' : 'light');
  icon.className = dark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
}
applyTheme();

document.getElementById('themeBtn').addEventListener('click', () => {
  dark = !dark;
  localStorage.setItem('lp-theme', dark ? 'dark' : 'light');
  applyTheme();
});

// Scroll reveal
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.style.opacity = '1';
      e.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.feature-card').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(24px)';
  el.style.transition = 'opacity .5s ease, transform .5s ease';
  observer.observe(el);
});
</script>
</body>
</html>
