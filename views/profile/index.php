<?php
/**
 * Trang Hồ sơ cá nhân + Tùy chọn
 */
$baseUrl = getBaseUrl();
?>

<div class="page-header">
    <h2>Hồ sơ cá nhân</h2>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="profile-page">
    <!-- Cập nhật Profile -->
    <div class="card">
        <div class="card-header">
            <h3>Thông tin cá nhân</h3>
        </div>
        <div class="card-body">
            <form action="<?= $baseUrl ?>/profile/update" method="POST" enctype="multipart/form-data">
                <div class="profile-avatar-section">
                    <div class="avatar-preview" id="avatarPreview">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= $baseUrl ?>/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" id="avatarImg">
                        <?php else: ?>
                            <div class="avatar-placeholder large"><?= mb_substr($user['display_name'], 0, 1) ?></div>
                        <?php endif; ?>
                    </div>
                    <label class="btn btn-ghost">
                        <input type="file" name="avatar" accept="image/*" id="avatarInput" style="display:none">
                        Đổi ảnh đại diện
                    </label>
                </div>

                <div class="form-group">
                    <label for="profileName">Tên hiển thị</label>
                    <input type="text" id="profileName" name="display_name" value="<?= htmlspecialchars($user['display_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>

                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </form>
        </div>
    </div>

    <!-- Đổi mật khẩu -->
    <div class="card">
        <div class="card-header">
            <h3>Đổi mật khẩu</h3>
        </div>
        <div class="card-body">
            <form action="<?= $baseUrl ?>/profile/changePassword" method="POST">
                <div class="form-group">
                    <label for="oldPassword">Mật khẩu hiện tại</label>
                    <input type="password" id="oldPassword" name="old_password" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">Mật khẩu mới</label>
                    <input type="password" id="newPassword" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirmNewPassword" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
            </form>
        </div>
    </div>

    <!-- Tùy chọn cá nhân -->
    <div class="card">
        <div class="card-header">
            <h3>Tùy chọn cá nhân</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Kích thước chữ</label>
                <div class="radio-group" id="fontSizeGroup">
                    <label class="radio-item">
                        <input type="radio" name="font_size" value="small" <?= ($user['font_size'] ?? 'medium') === 'small' ? 'checked' : '' ?>>
                        <span style="font-size:13px">Nhỏ</span>
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="font_size" value="medium" <?= ($user['font_size'] ?? 'medium') === 'medium' ? 'checked' : '' ?>>
                        <span style="font-size:15px">Vừa</span>
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="font_size" value="large" <?= ($user['font_size'] ?? 'medium') === 'large' ? 'checked' : '' ?>>
                        <span style="font-size:18px">Lớn</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="noteColorPicker">Màu nền ghi chú</label>
                <div class="color-picker-group">
                    <input type="color" id="noteColorPicker" value="<?= htmlspecialchars($user['note_color'] ?? '#ffffff') ?>">
                    <div class="color-presets">
                        <button class="color-swatch" data-color="#ffffff" style="background:#ffffff" title="Trắng"></button>
                        <button class="color-swatch" data-color="#fff9c4" style="background:#fff9c4" title="Vàng"></button>
                        <button class="color-swatch" data-color="#c8e6c9" style="background:#c8e6c9" title="Xanh lá"></button>
                        <button class="color-swatch" data-color="#bbdefb" style="background:#bbdefb" title="Xanh dương"></button>
                        <button class="color-swatch" data-color="#f8bbd0" style="background:#f8bbd0" title="Hồng"></button>
                        <button class="color-swatch" data-color="#d1c4e9" style="background:#d1c4e9" title="Tím"></button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Giao diện</label>
                <div class="theme-toggle-group" id="themeToggleGroup">
                    <button class="theme-option <?= ($user['theme'] ?? 'light') === 'light' ? 'active' : '' ?>" data-theme="light">
                        ☀️ Sáng
                    </button>
                    <button class="theme-option <?= ($user['theme'] ?? 'light') === 'dark' ? 'active' : '' ?>" data-theme="dark">
                        🌙 Tối
                    </button>
                </div>
            </div>

            <button class="btn btn-primary" id="btnSavePreferences">Lưu tùy chọn</button>
        </div>
    </div>
</div>
