@extends('layouts.app')
@section('title', 'Đăng ký')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card shadow-lg border-0" style="width:460px">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus fs-1 text-primary"></i>
                <h2 class="fw-bold mt-2">Tạo tài khoản</h2>
            </div>

            <form method="POST" action="{{ route('auth.register.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên hiển thị</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Nhập tên của bạn" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="email@example.com" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Tối thiểu 8 ký tự" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <small class="text-muted">Phải có chữ HOA, chữ thường, số và ký tự đặc biệt.</small>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-person-check me-2"></i>Đăng ký
                </button>
            </form>
            <hr class="my-4">
            <p class="text-center mb-0">
                Đã có tài khoản? <a href="{{ route('auth.login') }}" class="text-decoration-none fw-semibold">Đăng nhập</a>
            </p>
        </div>
    </div>
</div>
@endsection
