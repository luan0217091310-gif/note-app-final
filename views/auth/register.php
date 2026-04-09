<?php
/**
 * Trang Đăng ký
 */
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Note App</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">📝</div>
                <h1>Tạo tài khoản</h1>
                <p>Đăng ký để bắt đầu quản lý ghi chú của bạn.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/auth/doRegister" method="POST" class="auth-form" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="vidu@gmail.com" required autofocus autocomplete="email">
                    <div class="field-hint">Nhập địa chỉ email hợp lệ (vd: abc@gmail.com)</div>
                    <div class="field-error" id="emailError"></div>
                </div>

                <div class="form-group">
                    <label for="display_name">Tên hiển thị <span class="required">*</span></label>
                    <input type="text" id="display_name" name="display_name" placeholder="Họ và tên của bạn" required minlength="2" maxlength="100">
                    <div class="field-hint">Tên sẽ hiển thị trên ứng dụng (2–100 ký tự)</div>
                    <div class="field-error" id="nameError"></div>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu <span class="required">*</span></label>
                    <div class="input-password-wrap">
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu..." required autocomplete="new-password">
                        <button type="button" class="btn-toggle-pw" onclick="togglePw('password','eyePw')" title="Hiện/Ẩn mật khẩu">
                            <svg id="eyePw" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <!-- Thanh mức độ mật khẩu -->
                    <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwFill"></div></div>
                    <div class="pw-strength-text" id="pwStrengthText"></div>
                    <!-- Checklist yêu cầu mật khẩu -->
                    <ul class="pw-rules" id="pwRules">
                        <li id="rule-len">✗ Ít nhất 8 ký tự</li>
                        <li id="rule-upper">✗ Có chữ HOA (A–Z)</li>
                        <li id="rule-lower">✗ Có chữ thường (a–z)</li>
                        <li id="rule-num">✗ Có chữ số (0–9)</li>
                        <li id="rule-special">✗ Có ký tự đặc biệt (!@#$%^&*...)</li>
                    </ul>
                    <div class="field-error" id="passwordError"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                    <div class="input-password-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required autocomplete="new-password">
                        <button type="button" class="btn-toggle-pw" onclick="togglePw('confirm_password','eyeCf')" title="Hiện/Ẩn mật khẩu">
                            <svg id="eyeCf" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="field-error" id="confirmError"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">Đăng ký</button>
            </form>

            <div class="auth-links">
                <span>Đã có tài khoản?</span>
                <a href="<?= $baseUrl ?>/auth/login">Đăng nhập</a>
            </div>
        </div>
    </div>

<script>
/* Email validation */
document.getElementById('email').addEventListener('blur', function() {
    const val = this.value.trim();
    const err = document.getElementById('emailError');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (val && !emailRe.test(val)) {
        err.textContent = '⚠ Email không đúng định dạng (vd: abc@gmail.com)';
        this.classList.add('input-invalid');
    } else {
        err.textContent = '';
        this.classList.remove('input-invalid');
    }
});

/* Password strength */
const pwInput = document.getElementById('password');
pwInput.addEventListener('input', function() {
    const pw = this.value;
    const rules = {
        'rule-len':     pw.length >= 8,
        'rule-upper':   /[A-Z]/.test(pw),
        'rule-lower':   /[a-z]/.test(pw),
        'rule-num':     /[0-9]/.test(pw),
        'rule-special': /[^A-Za-z0-9]/.test(pw)
    };

    let score = 0;
    for (const [id, ok] of Object.entries(rules)) {
        const li = document.getElementById(id);
        li.textContent = (ok ? '✓ ' : '✗ ') + li.textContent.slice(2);
        li.style.color = ok ? 'var(--success)' : '';
        if (ok) score++;
    }

    const fill = document.getElementById('pwFill');
    const text = document.getElementById('pwStrengthText');
    const pct  = (score / 5) * 100;
    fill.style.width = pct + '%';
    const labels = ['', 'Rất yếu', 'Yếu', 'Trung bình', 'Mạnh', 'Rất mạnh'];
    const colors = ['', '#e74c3c', '#e67e22', '#f1c40f', '#27ae60', '#00b894'];
    fill.style.background = colors[score] || '#e74c3c';
    text.textContent = labels[score] || '';
    text.style.color = colors[score] || '';
});

/* Confirm password match */
document.getElementById('confirm_password').addEventListener('input', function() {
    const err = document.getElementById('confirmError');
    if (this.value && this.value !== pwInput.value) {
        err.textContent = '⚠ Mật khẩu xác nhận không khớp';
    } else {
        err.textContent = '';
    }
});

/* Toggle show/hide password */
function togglePw(inputId, iconId) {
    const inp = document.getElementById(inputId);
    const eye = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        inp.type = 'password';
        eye.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

/* Client-side submit validation */
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;
    const email = document.getElementById('email').value.trim();
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRe.test(email)) {
        document.getElementById('emailError').textContent = '⚠ Email không đúng định dạng';
        valid = false;
    }

    const pw = pwInput.value;
    const pwOk = pw.length >= 8 && /[A-Z]/.test(pw) && /[a-z]/.test(pw) && /[0-9]/.test(pw) && /[^A-Za-z0-9]/.test(pw);
    if (!pwOk) {
        document.getElementById('passwordError').textContent = '⚠ Mật khẩu chưa đạt yêu cầu';
        valid = false;
    } else {
        document.getElementById('passwordError').textContent = '';
    }

    const cf = document.getElementById('confirm_password').value;
    if (cf !== pw) {
        document.getElementById('confirmError').textContent = '⚠ Mật khẩu xác nhận không khớp';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
