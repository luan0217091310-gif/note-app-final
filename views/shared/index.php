<?php
/**
 * Ghi chú được chia sẻ với tôi
 */
$baseUrl = getBaseUrl();
?>

<div class="page-header">
    <h2>Chia sẻ với tôi</h2>
</div>

<div class="shared-notes-container">
    <?php if (empty($sharedNotes)): ?>
        <div class="empty-state">
            <div class="empty-icon">📨</div>
            <h3>Chưa có ghi chú được chia sẻ</h3>
            <p>Khi ai đó chia sẻ ghi chú với bạn, chúng sẽ xuất hiện ở đây.</p>
        </div>
    <?php else: ?>
        <?php foreach ($sharedNotes as $note): ?>
        <div class="shared-note-card" data-id="<?= $note['id'] ?>">
            <div class="shared-note-header">
                <div class="shared-note-info">
                    <span class="shared-from">
                        <strong><?= htmlspecialchars($note['owner_name']) ?></strong>
                        <small>(<?= htmlspecialchars($note['owner_email']) ?>)</small>
                    </span>
                    <span class="shared-permission badge-<?= $note['permission'] ?>">
                        <?= $note['permission'] === 'edit' ? '✏️ Có thể chỉnh sửa' : '👁️ Chỉ xem' ?>
                    </span>
                </div>
                <small class="shared-date"><?= date('d/m/Y H:i', strtotime($note['shared_at'])) ?></small>
            </div>

            <div class="shared-note-content" onclick="openSharedNote(<?= $note['id'] ?>, '<?= $note['permission'] ?>')">
                <h3><?= htmlspecialchars($note['title'] ?: 'Không có tiêu đề') ?></h3>
                <p><?= htmlspecialchars(mb_substr(strip_tags($note['content'] ?? ''), 0, 200)) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal xem/sửa ghi chú được chia sẻ -->
<div class="modal-overlay" id="sharedNoteModal">
    <div class="modal-content note-editor">
        <div class="modal-header">
            <input type="text" class="note-title-input" id="sharedNoteTitle" placeholder="Tiêu đề">
            <div class="modal-actions">
                <span class="save-status" id="sharedSaveStatus"></span>
                <span class="collab-indicator" id="collabIndicator" style="display:none">
                    <span class="collab-dot"></span> Đang cộng tác
                </span>
                <button class="btn-icon" onclick="closeSharedNoteModal()">✕</button>
            </div>
        </div>
        <div class="modal-body">
            <textarea class="note-content-input" id="sharedNoteContent" placeholder="Nội dung..."></textarea>
        </div>
    </div>
</div>
