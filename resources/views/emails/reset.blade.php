<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;max-width:500px;margin:auto">
<div style="background:#6c5ce7;padding:20px;text-align:center;border-radius:8px 8px 0 0">
    <h2 style="color:#fff;margin:0">📝 Note App</h2>
</div>
<div style="padding:30px;border:1px solid #eee;border-top:none;border-radius:0 0 8px 8px">
    <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
    <p>Mã OTP đặt lại mật khẩu của bạn là:</p>
    <div style="text-align:center;margin:24px 0">
        <span style="font-size:36px;font-weight:bold;letter-spacing:8px;color:#6c5ce7;background:#f0eeff;padding:16px 24px;border-radius:8px">{{ $otp }}</span>
    </div>
    <p style="color:#666;font-size:13px">Mã OTP có hiệu lực trong <strong>15 phút</strong>. Không chia sẻ mã này với bất kỳ ai.</p>
</div>
</body></html>
