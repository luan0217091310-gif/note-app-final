<?php
/**
 * Trang quản lý nhãn (Labels)
 */
$baseUrl = getBaseUrl();
?>

<div class="page-header">
    <h2>Quản lý nhãn</h2>
</div>

<div class="labels-page">
    <!-- Form thêm nhãn mới -->
    <div class="label-add-form">
        <div class="form-group form-inline">
            <input type="text" id="newLabelName" placeholder="Tên nhãn mới..." class="form-input">
            <button class="btn btn-primary" id="btnAddLabel">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm
            </button>
        </div>
    </div>

    <!-- Danh sách nhãn -->
    <div class="labels-list" id="labelsList">
        <?php if (empty($labels)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏷️</div>
                <h3>Chưa có nhãn nào</h3>
                <p>Thêm nhãn để phân loại ghi chú của bạn.</p>
            </div>
        <?php else: ?>
            <?php foreach ($labels as $label): ?>
            <div class="label-item" data-id="<?= $label['id'] ?>">
                <div class="label-info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    <span class="label-name"><?= htmlspecialchars($label['name']) ?></span>
                    <input type="text" class="label-edit-input" value="<?= htmlspecialchars($label['name']) ?>" style="display:none">
                    <small class="label-count"><?= $label['note_count'] ?> ghi chú</small>
                </div>
                <div class="label-actions">
                    <button class="btn-icon btn-edit-label" title="Đổi tên">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="btn-icon btn-save-label" title="Lưu" style="display:none">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                    <button class="btn-icon btn-delete-label" title="Xóa">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
