@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="row g-4">
    {{-- Thông tin cá nhân --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold"><i class="bi bi-person-circle me-2 text-primary"></i>Hồ sơ cá nhân</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
                    @csrf
                    <div class="text-center mb-4">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle" width="90" height="90" style="object-fit:cover" id="avatarPreview">
                        @else
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold fs-2" style="width:90px;height:90px" id="avatarPreviewPlaceholder">{{ mb_substr($user->name,0,1) }}</div>
                        @endif
                        <div class="mt-2"><label class="btn btn-sm btn-outline-secondary"><i class="bi bi-camera me-1"></i>Đổi ảnh<input type="file" name="avatar" class="d-none" accept="image/*" id="avatarInput"></label></div>
                        <div class="mt-3 text-start avatar-adjuster" id="avatarAdjuster" style="display:none">
                            <div class="small text-muted mb-2">Kéo ảnh để canh chỉnh. Lăn chuột để zoom.</div>
                            <div class="avatar-crop-wrap">
                                <canvas id="avatarCropCanvas" width="260" height="260"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên hiển thị</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-2"></i>Lưu thay đổi</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Đổi mật khẩu --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold"><i class="bi bi-shield-lock me-2 text-warning"></i>Đổi mật khẩu</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.changePassword') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                        <input type="password" name="old_password" class="form-control @error('old_password') is-invalid @enderror" required>
                        @error('old_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu mới</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100"><i class="bi bi-key me-2"></i>Đổi mật khẩu</button>
                </form>
            </div>
        </div>

        {{-- Tùy chỉnh giao diện --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent fw-bold"><i class="bi bi-palette me-2 text-info"></i>Tùy chỉnh giao diện</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cỡ chữ</label>
                    <select class="form-select" id="prefFontSize">
                        <option value="small" {{ $user->font_size=='small'?'selected':'' }}>Nhỏ</option>
                        <option value="medium" {{ $user->font_size=='medium'?'selected':'' }}>Vừa</option>
                        <option value="large" {{ $user->font_size=='large'?'selected':'' }}>Lớn</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Màu nền ghi chú</label>
                    <input type="color" class="form-control form-control-color" id="prefNoteColor" value="{{ $user->note_color ?? '#ffffff' }}">
                </div>
                <button class="btn btn-info text-white w-100" id="btnSavePrefs"><i class="bi bi-check2 me-2"></i>Lưu tùy chỉnh</button>
            </div>
        </div>
    </div>
</div>
@endsection
