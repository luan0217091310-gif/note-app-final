<?php
/**
 * Sidebar Layout - Menu điều hướng + Labels
 */
$baseUrl = getBaseUrl();
$currentPage = $controllerName ?? 'notes';
$activeLabelId = $_GET['label_id'] ?? null;
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <!-- Menu chính -->
        <nav class="sidebar-nav">
            <a href="<?= $baseUrl ?>/notes" class="sidebar-item <?= $currentPage === 'notes' && !isset($_GET['label_id']) ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span>Ghi chú</span>
            </a>

            <a href="<?= $baseUrl ?>/shared" class="sidebar-item <?= $currentPage === 'shared' ? 'active' : '' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Chia sẻ với tôi</span>
            </a>
        </nav>

        <!-- Danh sách Labels -->
        <div class="sidebar-section">
            <div class="sidebar-section-header">
                <span>Nhãn</span>
                <a href="<?= $baseUrl ?>/labels" class="btn-icon-sm" title="Quản lý nhãn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </a>
            </div>
            <?php if (!empty($labels)): ?>
                <?php foreach ($labels as $label): ?>
                <a href="<?= $baseUrl ?>/notes?label_id=<?= $label['id'] ?>" 
                   class="sidebar-item sidebar-label <?= $activeLabelId == $label['id'] ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    <span><?= htmlspecialchars($label['name']) ?></span>
                    <small class="label-count"><?= $label['note_count'] ?></small>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="sidebar-empty">Chưa có nhãn nào</p>
            <?php endif; ?>
        </div>
    </div>
</aside>

<!-- Overlay cho mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<main class="main-content">
