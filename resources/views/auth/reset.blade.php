@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo"><i class="bi bi-key-fill text-warning"></i></div>
                <h1>Đặt lại mật khẩu</h1>
                <p>Mã OTP đã được gửi đến email của bạn.</p>
            </div>

            <form action="{{ route('auth.reset.process') }}" method="POST">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
                @endif
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group mb-3">
                    <label class="form-label fw-semibold">Mã OTP (6 số)</label>
                    <input type="text" name="otp" class="form-control text-center fw-bold fs-4" placeholder="000000" maxlength="6" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label fw-semibold">Mật khẩu mới</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-lock-check"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Cập nhật mật khẩu</button>

                <div class="auth-links">
                    <p>Chưa nhận được mã? <a href="{{ route('auth.forgot') }}" class="text-decoration-none">Gửi lại</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
