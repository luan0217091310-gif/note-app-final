<?php
/**
 * Notes Index - Grid/List View với icons nhận diện
 */
$baseUrl = getBaseUrl();
$view = $_GET['view'] ?? 'grid';
$noteColor = $_SESSION['note_color'] ?? '#ffffff';
?>

<div class="notes-header">
    <div class="notes-title-bar">
        <h2>
            <?php if (!empty($activeLabelId)): ?>
                Nhãn: <?= htmlspecialchars($labels[array_search($activeLabelId, array_column($labels, 'id'))]['name'] ?? '') ?>
            <?php else: ?>
                Ghi chú của tôi
            <?php endif; ?>
        </h2>
        <div class="notes-actions">
            <!-- Toggle Grid/List -->
            <button class="btn-icon view-toggle" id="viewToggle" data-view="<?= $view ?>" title="Đổi chế độ xem">
                <svg class="icon-grid" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect>
                </svg>
                <svg class="icon-list" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Nút tạo ghi chú mới -->
<button class="fab" id="btnNewNote" title="Tạo ghi chú mới">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
    </svg>
</button>

<!-- Danh sách ghi chú -->
<div class="notes-container <?= $view === 'list' ? 'list-view' : 'grid-view' ?>" id="notesContainer">
    <?php if (empty($notes)): ?>
        <div class="empty-state">
            <div class="empty-icon">📄</div>
            <h3>Chưa có ghi chú nào</h3>
            <p>Nhấn nút <strong>+</strong> để tạo ghi chú đầu tiên.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notes as $note): ?>
        <div class="note-card <?= $note['is_pinned'] ? 'pinned' : '' ?>" 
             data-id="<?= $note['id'] ?>"
             data-locked="<?= $note['lock_password'] ? '1' : '0' ?>"
             style="--note-bg: <?= htmlspecialchars($noteColor) ?>">
            
            <!-- Icons nhận diện -->
            <div class="note-badges">
                <?php if ($note['is_pinned']): ?>
                    <span class="badge badge-pin" title="Đã ghim">📌</span>
                <?php endif; ?>
                <?php if ($note['lock_password']): ?>
                    <span class="badge badge-lock" title="Đã khóa">🔒</span>
                <?php endif; ?>
                <?php if ($note['share_count'] > 0): ?>
                    <span class="badge badge-share" title="Đã chia sẻ">🔗</span>
                <?php endif; ?>
            </div>

            <!-- Nội dung -->
            <div class="note-content" onclick="openNote(<?= $note['id'] ?>)">
                <h3 class="note-title"><?= htmlspecialchars($note['title'] ?: 'Không có tiêu đề') ?></h3>
                <?php if (!$note['lock_password']): ?>
                    <p class="note-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($note['content'] ?? ''), 0, 150)) ?></p>
                <?php else: ?>
                    <p class="note-excerpt locked-text">🔒 Ghi chú đã được khóa</p>
                <?php endif; ?>
            </div>

            <!-- Labels -->
            <?php if (!empty($note['label_names'])): ?>
            <div class="note-labels">
                <?php foreach (explode(', ', $note['label_names']) as $labelName): ?>
                    <span class="label-chip"><?= htmlspecialchars($labelName) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="note-footer">
                <small class="note-date"><?= date('d/m/Y H:i', strtotime($note['updated_at'])) ?></small>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================ -->
<!-- MODAL CHUNG CHO THÊM/SỬA GHI CHÚ -->
<!-- ============================================ -->
<div class="modal-overlay" id="noteModal">
    <div class="modal-content note-editor">
        <div class="modal-header">
            <input type="text" class="note-title-input" id="noteTitle" placeholder="Tiêu đề" autocomplete="off">
            <div class="modal-actions">
                <span class="save-status" id="saveStatus"></span>
                <button class="btn-icon" id="btnCloseModal" title="Đóng">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <div class="modal-body">
            <textarea class="note-content-input" id="noteContent" placeholder="Viết ghi chú của bạn..."></textarea>
            
            <!-- Hình ảnh đính kèm -->
            <div class="note-images" id="noteImages"></div>
        </div>

        <div class="modal-footer">
            <div class="modal-footer-left">
                <!-- Upload ảnh -->
                <label class="btn-icon" title="Đính kèm ảnh">
                    <input type="file" id="imageUpload" accept="image/*" multiple style="display:none">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </label>

                <!-- Chọn Labels -->
                <button class="btn-icon" id="btnLabels" title="Gắn nhãn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                </button>

                <!-- Pin -->
                <button class="btn-icon" id="btnPin" title="Ghim ghi chú">
                    <span id="pinIcon">📌</span>
                </button>

                <!-- Lock -->
                <button class="btn-icon" id="btnLock" title="Khóa ghi chú">
                    <span id="lockIcon">🔓</span>
                </button>

                <!-- Share -->
                <button class="btn-icon" id="btnShare" title="Chia sẻ">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle>
                        <circle cx="18" cy="19" r="3"></circle>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-footer-right">
                <button class="btn btn-danger-text" id="btnDeleteNote" title="Xóa ghi chú">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL LABELS -->
<div class="modal-overlay modal-sm" id="labelsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Gắn nhãn</h3>
            <button class="btn-icon" onclick="closeLabelsModal()">✕</button>
        </div>
        <div class="modal-body" id="labelsCheckboxList">
            <?php foreach ($labels as $label): ?>
            <label class="checkbox-item">
                <input type="checkbox" class="label-checkbox" value="<?= $label['id'] ?>" 
                       data-name="<?= htmlspecialchars($label['name']) ?>">
                <span><?= htmlspecialchars($label['name']) ?></span>
            </label>
            <?php endforeach; ?>
            <?php if (empty($labels)): ?>
                <p class="text-muted">Chưa có nhãn. <a href="<?= $baseUrl ?>/labels">Tạo nhãn mới</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL LOCK PASSWORD -->
<div class="modal-overlay modal-sm" id="lockModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="lockModalTitle">Khóa ghi chú</h3>
            <button class="btn-icon" onclick="closeLockModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="lockPassword">Mật khẩu</label>
                <input type="password" id="lockPassword" placeholder="Nhập mật khẩu">
            </div>
            <button class="btn btn-primary btn-block" id="btnConfirmLock">Xác nhận</button>
            <button class="btn btn-text btn-block" id="btnRemoveLock" style="display:none">Gỡ khóa</button>
        </div>
    </div>
</div>

<!-- MODAL VERIFY LOCK (Mở ghi chú bị khóa) -->
<div class="modal-overlay modal-sm" id="verifyLockModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔒 Ghi chú đã khóa</h3>
            <button class="btn-icon" onclick="closeVerifyLockModal()">✕</button>
        </div>
        <div class="modal-body">
            <p>Vui lòng nhập mật khẩu để xem ghi chú này.</p>
            <div class="form-group">
                <input type="password" id="verifyLockPassword" placeholder="Nhập mật khẩu">
            </div>
            <button class="btn btn-primary btn-block" id="btnVerifyLock">Mở khóa</button>
        </div>
    </div>
</div>

<!-- MODAL SHARE -->
<div class="modal-overlay modal-sm" id="shareModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Chia sẻ ghi chú</h3>
            <button class="btn-icon" onclick="closeShareModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="shareEmail">Email người nhận</label>
                <input type="email" id="shareEmail" placeholder="Nhập email">
            </div>
            <div class="form-group">
                <label for="sharePermission">Quyền</label>
                <select id="sharePermission" class="form-select">
                    <option value="read">Chỉ xem</option>
                    <option value="edit">Có thể chỉnh sửa</option>
                </select>
            </div>
            <button class="btn btn-primary btn-block" id="btnConfirmShare">Chia sẻ</button>
            <div id="shareMsg" class="share-msg"></div>

            <!-- Danh sách đã chia sẻ -->
            <div class="share-list" id="shareList"></div>
        </div>
    </div>
</div>

<!-- MODAL XÁC NHẬN XÓA -->
<div class="modal-overlay modal-sm" id="deleteConfirmModal">
    <div class="modal-content">
        <div class="modal-body" style="text-align:center; padding: 30px;">
            <div style="font-size: 48px; margin-bottom: 16px;">🗑️</div>
            <h3>Xóa ghi chú?</h3>
            <p>Hành động này không thể hoàn tác.</p>
            <div style="display:flex; gap:12px; justify-content:center; margin-top:20px;">
                <button class="btn btn-ghost" onclick="closeDeleteModal()">Hủy</button>
                <button class="btn btn-danger" id="btnConfirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>
