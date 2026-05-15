@extends('layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card shadow-lg border-0" style="width:420px">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-journal-text fs-1 text-primary"></i>
                <h2 class="fw-bold mt-2">Note App</h2>
                <p class="text-muted">Đăng nhập vào tài khoản của bạn</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('auth.login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="email@example.com" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Nhớ đăng nhập</label>
                    </div>
                    <a href="{{ route('auth.forgot') }}" class="text-decoration-none small">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                </button>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted mb-0">
                Chưa có tài khoản? <a href="{{ route('auth.register') }}" class="text-decoration-none fw-semibold">Đăng ký</a>
            </p>
        </div>
    </div>
</div>
@endsection
