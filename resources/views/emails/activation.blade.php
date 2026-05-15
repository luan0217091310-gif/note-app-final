<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;max-width:500px;margin:auto">
<div style="background:#6c5ce7;padding:20px;text-align:center;border-radius:8px 8px 0 0">
    <h2 style="color:#fff;margin:0">📝 Note App</h2>
</div>
<div style="padding:30px;border:1px solid #eee;border-top:none;border-radius:0 0 8px 8px">
    <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
    <p>Vui lòng click vào nút bên dưới để kích hoạt tài khoản:</p>
    <div style="text-align:center;margin:30px 0">
        <a href="{{ url('/activate?token=' . $token) }}"
           style="background:#6c5ce7;color:#fff;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold">
            Kích hoạt tài khoản
        </a>
    </div>
    <p style="color:#666;font-size:13px">Link có hiệu lực trong 24 giờ. Nếu bạn không đăng ký, vui lòng bỏ qua email này.</p>
</div>
</body></html>
