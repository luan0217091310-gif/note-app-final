@extends('layouts.app')
@section('title', 'Quên mật khẩu')

@section('content')
<div class="auth-body forgot-bw">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo"><i class="bi bi-shield-lock-fill text-dark"></i></div>
                <h1>Quên mật khẩu</h1>
                <p>Nhập email của bạn để nhận mã OTP khôi phục.</p>
            </div>

            <form action="{{ route('auth.forgot.send') }}" method="POST">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger small py-2 bg-white border-dark text-dark">{{ $errors->first() }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success small py-2 bg-white border-dark text-dark">{{ session('success') }}</div>
                @endif
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold">Địa chỉ Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-dark text-dark"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control border-dark text-dark" placeholder="example@email.com" required value="{{ old('email') }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-dark w-100 mb-3">Gửi mã OTP</button>

                <div class="auth-links">
                    <a href="{{ route('auth.login') }}" class="text-decoration-none text-dark">Quay lại đăng nhập</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
