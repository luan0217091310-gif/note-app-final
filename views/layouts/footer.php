<?php
/**
 * Footer Layout - Đóng container + Script includes
 */
$baseUrl = getBaseUrl();
?>
</main><!-- /.main-content -->
</div><!-- /.app-container -->

<script>
    // Truyền base URL cho JavaScript
    const BASE_URL = '<?= $baseUrl ?>';
    const USER_ID = <?= $_SESSION['user_id'] ?? 0 ?>;
    const USER_THEME = '<?= $_SESSION['theme'] ?? 'light' ?>';
    const USER_NOTE_COLOR = '<?= $_SESSION['note_color'] ?? '#ffffff' ?>';
</script>
<script src="<?= $baseUrl ?>/public/js/app.js"></script>

<?php if (isset($loadCollaboration) && $loadCollaboration): ?>
<script src="<?= $baseUrl ?>/public/js/collaboration.js"></script>
<?php endif; ?>

<!-- Đăng ký Service Worker -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?= $baseUrl ?>/public/js/sw.js')
            .then(function(reg) {
                console.log('Service Worker registered:', reg.scope);
            })
            .catch(function(err) {
                console.log('Service Worker registration failed:', err);
            });
    });
}
</script>

</body>
</html>
