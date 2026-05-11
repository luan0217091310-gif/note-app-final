// =============================================
// NOTE APP - Laravel + Bootstrap 5
// =============================================

let currentNoteId = null;
let autoSaveTimer  = null;
let searchTimer    = null;
let notesRefreshTimer = null;
let keepCurrentNoteOnModalClose = false;
const unlockedByPasswordNoteIds = new Set();
const AUTO_SAVE_DELAY = 1000;
const SEARCH_DELAY    = 300;
const IS_SHARED_NOTES_PAGE = window.location.pathname.includes('/notes/shared');

// Bootstrap Modal instances
let noteModalBs, lockModalBs, verifyLockModalBs, shareModalBs, labelsModalBs, deleteConfirmModalBs;

// =============================================
// KHỞI TẠO
// =============================================
document.addEventListener('DOMContentLoaded', function () {
    // Khởi tạo Bootstrap Modals an toàn (chỉ khởi tạo nếu element tồn tại trên trang)
    const nm = document.getElementById('noteModal');
    const lm = document.getElementById('lockModal');
    const vm = document.getElementById('verifyLockModal');
    const sm = document.getElementById('shareModal');
    const bm = document.getElementById('labelsModal');
    const dm = document.getElementById('deleteConfirmModal');

    if (nm) noteModalBs = new bootstrap.Modal(nm);
    if (lm) lockModalBs = new bootstrap.Modal(lm);
    if (vm) verifyLockModalBs = new bootstrap.Modal(vm);
    if (sm) shareModalBs = new bootstrap.Modal(sm);
    if (bm) labelsModalBs = new bootstrap.Modal(bm);
    if (dm) deleteConfirmModalBs = new bootstrap.Modal(dm);

    initTheme();
    initSidebar();
    initSearch();
    initNoteEditor();
    initLockModal();
    initShareModal();
    initLabelsModal();
    initDeleteModal();
    initPreferences();
    initLabelsPage();
    initViewToggle();
    initAvatarAdjuster();
});

// =============================================
// THEME (Dark/Light)
// =============================================
function initTheme() {
    const btn = document.getElementById('themeToggle');
    if (!btn) return;
    applyTheme(USER_THEME);
    btn.addEventListener('click', function () {
        const cur = document.documentElement.getAttribute('data-theme') || 'light';
        const next = cur === 'light' ? 'dark' : 'light';
        applyTheme(next);
        fetch(BASE_URL + '/profile/preferences', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ theme: next, font_size: 'medium', note_color: '#ffffff' })
        });
    });
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.body.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
    const li = document.querySelector('.theme-icon-light');
    const di = document.querySelector('.theme-icon-dark');
    if (theme === 'dark') {
        if (li) li.style.display = 'none';
        if (di) di.style.display = '';
    } else {
        if (li) li.style.display = '';
        if (di) di.style.display = 'none';
    }
}

// =============================================
// SIDEBAR TOGGLE
// =============================================
function initSidebar() {
    const btn      = document.getElementById('sidebarToggle');
    const sidebar  = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!btn || !sidebar) return;

    function isMobile() { return window.innerWidth <= 992; }

    function closeSidebar() {
        sidebar.classList.remove('show');
        if (backdrop) backdrop.classList.remove('show');
    }

    btn.addEventListener('click', function () {
        if (isMobile()) {
            sidebar.classList.toggle('show');
            if (backdrop) backdrop.classList.toggle('show');
        } else {
            sidebar.classList.toggle('d-none');
        }
    });

    // Close sidebar when clicking backdrop
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    // Close sidebar when clicking a link (mobile)
    sidebar.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (isMobile()) closeSidebar();
        });
    });

    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (!isMobile()) closeSidebar();
    });
}

// =============================================
// LIVE SEARCH (300ms debounce)
// =============================================
function initSearch() {
    const input = document.getElementById('searchInput');
    const clear = document.getElementById('searchClear');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.trim();
        if (clear) clear.style.display = q ? '' : 'none';
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => performSearch(q), SEARCH_DELAY);
    });

    if (clear) {
        clear.addEventListener('click', function () {
            input.value = '';
            this.style.display = 'none';
            performSearch('');
        });
    }
}

function performSearch(q) {
    const labelId = new URLSearchParams(window.location.search).get('label_id') || '';
    fetch(BASE_URL + '/notes/search?q=' + encodeURIComponent(q) + (labelId ? '&label_id=' + labelId : ''), {
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    })
    .then(r => r.json())
    .then(notes => renderNotes(notes));
}

function renderNotes(notes) {
    const container = document.getElementById('notesContainer');
    const viewBtn   = document.getElementById('viewToggle');
    if (!container) return;
    
    if (!notes.length) {
        container.innerHTML = `<div class="col-12 text-center py-5"><i class="bi bi-search fs-1 text-muted"></i><h5 class="text-muted mt-3">Không tìm thấy ghi chú nào</h5></div>`;
        return;
    }

    const isList = viewBtn && viewBtn.dataset.view === 'list';
    const colClass = isList ? 'col-12 note-col' : 'col-sm-6 col-md-4 col-lg-3 note-col';

    container.innerHTML = notes.map(n => `
        <div class="${colClass}" data-id="${n.id}">
            <div class="card h-100 shadow-sm border-0 note-card ${n.is_pinned ? 'border-warning border' : ''}" 
                 style="cursor:pointer; --note-bg:${USER_NOTE_COLOR}; background:var(--note-bg)" 
                 onclick="openNote(${n.id})">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        ${n.is_pinned ? '<span class="badge bg-warning text-dark"><i class="bi bi-pin-fill"></i></span>' : ''}
                        ${n.has_lock || n.lock_password_exists ? '<span class="badge bg-secondary"><i class="bi bi-lock-fill"></i></span>' : ''}
                        ${n.shares_count > 0 ? '<span class="badge bg-info text-dark"><i class="bi bi-share-fill"></i></span>' : ''}
                        ${(n.labels && n.labels.length) ? n.labels.map(l => '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"><i class="bi bi-tag-fill me-1"></i>' + escHtml(l.name) + '</span>').join('') : ''}
                    </div>
                    <h6 class="card-title fw-semibold text-truncate">${escHtml(n.title) || 'Không có tiêu đề'}</h6>
                    <p class="card-text text-muted small" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
                        ${escHtml(n.content || '')}
                    </p>
                </div>
                <div class="card-footer bg-transparent border-0 text-muted small">
                    <i class="bi bi-clock me-1"></i>${new Date(n.updated_at).toLocaleString('vi-VN', {hour12:false, dateStyle:'short', timeStyle:'short'})}
                </div>
            </div>
        </div>
    `).join('');
}

// =============================================
// MỞ / TẠO GHI CHÚ
// =============================================
function openNote(id) {
    fetch(BASE_URL + '/notes/' + id, { headers: { 'X-CSRF-TOKEN': csrfToken() } })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        if (data.locked) {
            unlockedByPasswordNoteIds.delete(id);
            openVerifyLock(id);
            return;
        }

        currentNoteId = id;
        if (!data.has_lock) unlockedByPasswordNoteIds.delete(id);
        document.getElementById('noteTitle').value   = data.title   || '';
        document.getElementById('noteContent').value = data.content || '';

        const pinIcon  = document.getElementById('pinIcon');
        const lockIcon = document.getElementById('lockIcon');
        if (pinIcon)  pinIcon.className  = data.is_pinned ? 'bi bi-pin-fill text-warning' : 'bi bi-pin';
        if (lockIcon) lockIcon.className = data.has_lock  ? 'bi bi-lock-fill text-danger'  : 'bi bi-unlock';

        const saveStatus = document.getElementById('saveStatus');
        if (saveStatus) saveStatus.textContent = '';

        // Render ảnh
        renderImages(data.images || []);

        noteModalBs.show();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const btnNew = document.getElementById('btnNewNote');
    if (btnNew) {
        btnNew.addEventListener('click', function () {
            // Xóa nội dung tìm kiếm để chắc chắn ghi chú mới sẽ hiện ra
            const searchInput = document.getElementById('searchInput');
            if (searchInput && searchInput.value) {
                searchInput.value = '';
                if (typeof performSearch === 'function') performSearch('');
            }

            fetch(BASE_URL + '/notes/create', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' }
            })
            .then(r => r.json())
            .then(data => { 
                if (data.success) {
                    openNote(data.id);
                }
            });
        });
    }
});

// =============================================
// AUTO-SAVE (debounce 1 giây)
// =============================================
function initNoteEditor() {
    const title   = document.getElementById('noteTitle');
    const content = document.getElementById('noteContent');
    const noteModalEl = document.getElementById('noteModal');
    if (!title || !content) return;

    [title, content].forEach(el => {
        el.addEventListener('input', triggerAutoSave);
    });

    // Thoát khi bấm Enter ở Tiêu đề
    title.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            noteModalBs.hide();
        }
    });

    // Thoát khi bấm Ctrl + Enter ở Nội dung
    content.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            noteModalBs.hide();
        }
    });

    const btnDone = document.getElementById('btnDone');
    if (btnDone) {
        btnDone.addEventListener('click', function() {
            // Lưu lần cuối trước khi đóng
            clearTimeout(autoSaveTimer);
            autoSaveNote();
            // Đóng modal
            setTimeout(() => {
                noteModalBs.hide();
            }, 100);
        });
    }

    // Xử lý khi đóng Modal: Kiểm tra nếu trống thì xóa
    if (noteModalEl) {
        noteModalEl.addEventListener('hidden.bs.modal', function () {
        if (keepCurrentNoteOnModalClose) {
            keepCurrentNoteOnModalClose = false;
            return;
        }

        const t = document.getElementById('noteTitle').value.trim();
        const c = document.getElementById('noteContent').value.trim();
        const imagesCount = document.getElementById('noteImages') ? document.getElementById('noteImages').children.length : 0;

        if (!t && !c && imagesCount === 0 && currentNoteId) {
            // XÓA GHI CHÚ TRỐNG NGAY LẬP TỨC
            const idToRemove = currentNoteId;
            fetch(BASE_URL + '/notes/delete/' + idToRemove, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken() }
            }).then(() => {
                const card = document.querySelector(`.note-col[data-id="${idToRemove}"]`);
                if (card) card.remove();
                showToast('ℹ Đã hủy ghi chú trống');
            });
        } else {
            // CẬP NHẬT DANH SÁCH TỨC THÌ
            refreshNotesList(0);
        }
        currentNoteId = null;
    });
    }

    const btnUpload = document.getElementById('btnUploadImg');
    const imgInput  = document.getElementById('imageUpload');
    if (btnUpload && imgInput) {
        btnUpload.addEventListener('click', () => imgInput.click());
        imgInput.addEventListener('change', uploadImages);
    }
}

function triggerAutoSave() {
    const saveStatus = document.getElementById('saveStatus');
    if (saveStatus) saveStatus.textContent = '...đang lưu';
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(autoSaveNote, AUTO_SAVE_DELAY);
}

function autoSaveNote() {
    if (!currentNoteId) return Promise.resolve(false);
    const saveStatus = document.getElementById('saveStatus');
    const titleInput = document.getElementById('noteTitle');
    const contentInput = document.getElementById('noteContent');
    const liveTitle = titleInput ? titleInput.value : '';
    const liveContent = contentInput ? contentInput.value : '';

    return fetch(BASE_URL + '/notes/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({
            id:      currentNoteId,
            title:   liveTitle,
            content: liveContent
        })
    })
    .then(r => r.json())
    .then(data => {
        if (saveStatus) saveStatus.textContent = data.success ? '✓ Đã lưu' : '✗ Lỗi';
        if (data.success) {
            patchNoteCard(currentNoteId, liveTitle, liveContent);
            refreshNotesList(200);
        }
        return !!data.success;
    });
}

function refreshNotesList(delay = 100) {
    if (IS_SHARED_NOTES_PAGE) return;
    const searchInput = document.getElementById('searchInput');
    const keyword = searchInput ? searchInput.value.trim() : '';
    clearTimeout(notesRefreshTimer);
    notesRefreshTimer = setTimeout(() => performSearch(keyword), delay);
}

function patchNoteCard(noteId, title, content) {
    const col = document.querySelector(`.note-col[data-id="${noteId}"]`);
    if (!col) return;

    const titleEl = col.querySelector('.card-title');
    const contentEl = col.querySelector('.card-text');
    const footerEl = col.querySelector('.card-footer');

    if (titleEl) {
        const finalTitle = (title || '').trim();
        titleEl.textContent = finalTitle || 'Không có tiêu đề';
    }

    if (contentEl) {
        const finalContent = (content || '').trim();
        contentEl.textContent = finalContent;
    }

    if (footerEl) {
        const now = new Date();
        footerEl.innerHTML = `<i class="bi bi-clock me-1"></i>${now.toLocaleString('vi-VN', { hour12: false, dateStyle: 'short', timeStyle: 'short' })}`;
    }
}

// =============================================
// ẢNH ĐÍNH KÈM
// =============================================
function renderImages(images) {
    const container = document.getElementById('noteImages');
    if (!container) return;
    container.innerHTML = images.map(img => `
        <div class="position-relative d-inline-block me-1 mb-1">
            <img src="${BASE_URL}/storage/${img.image_path}" style="width:80px;height:80px;object-fit:cover;border-radius:6px">
            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 px-1" style="font-size:11px"
                    onclick="removeImage(${img.id})">✕</button>
        </div>
    `).join('');
}

function uploadImages() {
    const files = document.getElementById('imageUpload').files;
    if (!files.length || !currentNoteId) return;
    const fd = new FormData();
    fd.append('note_id', currentNoteId);
    for (const f of files) fd.append('images[]', f);
    fd.append('_token', csrfToken());
    fetch(BASE_URL + '/notes/uploadImages', { method: 'POST', body: fd })
    .then(r => r.json()).then(() => openNote(currentNoteId));
}

function removeImage(imageId) {
    fetch(BASE_URL + '/notes/removeImage/' + imageId, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken() }
    }).then(() => openNote(currentNoteId));
}

// =============================================
// PIN
// =============================================
document.addEventListener('DOMContentLoaded', function () {
    const btnPin = document.getElementById('btnPin');
    if (btnPin) {
        btnPin.addEventListener('click', function () {
            if (!currentNoteId) return;
            fetch(BASE_URL + '/notes/pin/' + currentNoteId, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() }
            })
            .then(r => r.json())
            .then(data => {
                const icon = document.getElementById('pinIcon');
                if (icon) icon.className = data.is_pinned ? 'bi bi-pin-fill text-warning' : 'bi bi-pin';
            });
        });
    }
});

// =============================================
// XÓA GHI CHÚ
// =============================================
function initDeleteModal() {
    const btnDelete = document.getElementById('btnDeleteNote');
    const btnConfirm = document.getElementById('btnConfirmDelete');
    if (btnDelete) btnDelete.addEventListener('click', () => deleteConfirmModalBs.show());
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            if (!currentNoteId) return;
            fetch(BASE_URL + '/notes/delete/' + currentNoteId, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    deleteConfirmModalBs.hide();
                    noteModalBs.hide();
                    // Xóa card khỏi DOM
                    const card = document.querySelector(`.note-col[data-id="${currentNoteId}"]`);
                    if (card) card.remove();
                    currentNoteId = null;
                }
            });
        });
    }
}

// =============================================
// KHÓA GHI CHÚ - Better Approach
// =============================================
function initLockModal() {
    const btnLock    = document.getElementById('btnLock');
    const btnConfirm = document.getElementById('btnConfirmLock');
    const btnRemove  = document.getElementById('btnRemoveLock');
    const btnVerify  = document.getElementById('btnVerifyLock');
    const btnUnlockFromLockModal = document.getElementById('btnUnlockFromLockModal');

    if (btnLock) btnLock.addEventListener('click', openLockModal);
    if (btnConfirm) btnConfirm.addEventListener('click', confirmLock);
    if (btnRemove) btnRemove.addEventListener('click', removeLock);
    if (btnVerify) btnVerify.addEventListener('click', verifyLock);
    if (btnUnlockFromLockModal) btnUnlockFromLockModal.addEventListener('click', unlockFromLockModal);
}

function openLockModal() {
    if (!currentNoteId) {
        showErr('lockErrorMsg', '⚠ Không tìm thấy ghi chú để khóa');
        return;
    }

    const lockIcon  = document.getElementById('lockIcon');
    const title     = document.getElementById('lockModalTitle');
    const setLockSection = document.getElementById('setLockSection');
    const unlockSection = document.getElementById('unlockSection');
    const removeSection = document.getElementById('removeLockSection');

    document.getElementById('lockPassword').value = '';
    document.getElementById('lockPasswordConfirm').value = '';
    const unlockInput = document.getElementById('unlockPassword');
    if (unlockInput) unlockInput.value = '';
    hideErr('lockErrorMsg');
    hideErr('unlockErrorMsg');

    const isLocked = lockIcon && lockIcon.className.includes('lock-fill');
    if (isLocked) {
        title.textContent = 'Mở khóa ghi chú';
        if (setLockSection) setLockSection.style.display = 'none';
        if (unlockSection) unlockSection.style.display = 'block';
        removeSection.style.display = 'none';
    } else {
        title.textContent = 'Khóa ghi chú';
        if (setLockSection) setLockSection.style.display = 'block';
        if (unlockSection) unlockSection.style.display = 'none';
        removeSection.style.display = 'none';
    }
    keepCurrentNoteOnModalClose = true;
    noteModalBs.hide();
    lockModalBs.show();
}

function unlockFromLockModal() {
    const pwd = document.getElementById('unlockPassword').value;
    if (!pwd) {
        showErr('unlockErrorMsg', '⚠ Vui lòng nhập mật khẩu');
        return;
    }
    hideErr('unlockErrorMsg');

    fetch(BASE_URL + '/notes/removeLock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ id: currentNoteId, current_password: pwd })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            unlockedByPasswordNoteIds.delete(currentNoteId);
            const icon = document.getElementById('lockIcon');
            if (icon) icon.className = 'bi bi-unlock';
            lockModalBs.hide();
            showToast('🔓 Đã gỡ khóa ghi chú thành công');
            if (noteModalBs) noteModalBs.show();
        } else {
            showErr('unlockErrorMsg', '✗ ' + (data.error || 'Không thể gỡ khóa'));
        }
    });
}

function confirmLock() {
    const pwd     = document.getElementById('lockPassword').value;
    const confirm = document.getElementById('lockPasswordConfirm').value;

    if (!pwd)          { showErr('lockErrorMsg', '⚠ Vui lòng nhập mật khẩu'); return; }
    if (pwd !== confirm){ showErr('lockErrorMsg', '⚠ Mật khẩu xác nhận không khớp'); return; }
    if (pwd.length < 4){ showErr('lockErrorMsg', '⚠ Mật khẩu phải có ít nhất 4 ký tự'); return; }
    hideErr('lockErrorMsg');

    fetch(BASE_URL + '/notes/setLock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ id: currentNoteId, password: pwd })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const icon = document.getElementById('lockIcon');
            if (icon) icon.className = 'bi bi-lock-fill text-danger';
            unlockedByPasswordNoteIds.delete(currentNoteId);
            lockModalBs.hide();
            showToast('🔒 Đã khóa ghi chú thành công');
        } else {
            showErr('lockErrorMsg', '✗ ' + (data.error || 'Lỗi'));
            showToast('❌ Không thể khóa ghi chú');
        }
    });
}

function removeLock() {
    const pwd = document.getElementById('currentLockPassword').value;
    if (!pwd) { showErr('removeLockErrorMsg', '⚠ Nhập mật khẩu hiện tại'); return; }
    hideErr('removeLockErrorMsg');

    fetch(BASE_URL + '/notes/removeLock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ id: currentNoteId, current_password: pwd })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const icon = document.getElementById('lockIcon');
            if (icon) icon.className = 'bi bi-unlock';
            unlockedByPasswordNoteIds.delete(currentNoteId);
            lockModalBs.hide();
            showToast('🔓 Đã gỡ khóa ghi chú thành công');
        } else {
            showErr('removeLockErrorMsg', '✗ ' + (data.error || 'Mật khẩu không đúng'));
            showToast('❌ Không thể gỡ khóa ghi chú');
        }
    });
}

function openVerifyLock(id) {
    currentNoteId = id;
    document.getElementById('verifyLockPassword').value = '';
    verifyLockModalBs.show();
}

function verifyLock() {
    const pwd = document.getElementById('verifyLockPassword').value;
    if (!pwd) return;
    fetch(BASE_URL + '/notes/verifyLock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ id: currentNoteId, password: pwd })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            unlockedByPasswordNoteIds.add(currentNoteId);
            verifyLockModalBs.hide();
            showToast('🔓 Mở khóa ghi chú thành công');
            openNote(currentNoteId);
        }
        else alert('Mật khẩu không đúng');
    });
}

// =============================================
// CHIA SẺ
// =============================================
function initShareModal() {
    const btnShare   = document.getElementById('btnShare');
    const btnConfirm = document.getElementById('btnConfirmShare');
    if (btnShare)   btnShare.addEventListener('click', openShareModal);
    if (btnConfirm) btnConfirm.addEventListener('click', confirmShare);
}

function openShareModal() {
    if (!currentNoteId) {
        showToast('⚠ Không tìm thấy ghi chú để chia sẻ. Vui lòng mở lại ghi chú.');
        return;
    }

    document.getElementById('shareEmail').value = '';
    document.getElementById('shareMsg').innerHTML = '';
    loadShareList();
    keepCurrentNoteOnModalClose = true;
    noteModalBs.hide();
    shareModalBs.show();
}

function loadShareList() {
    if (!currentNoteId) return;
    fetch(BASE_URL + '/notes/' + currentNoteId, { headers: { 'X-CSRF-TOKEN': csrfToken() } })
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('shareList');
        if (!list) return;
        const shares = data.shares || [];
        if (!shares.length) { list.innerHTML = '<p class="text-muted small">Chưa chia sẻ với ai.</p>'; return; }
        list.innerHTML = '<p class="fw-semibold small mb-2">Đã chia sẻ với:</p>' + shares.map(s => `
            <div class="d-flex align-items-center justify-content-between mb-1 p-2 bg-light rounded">
                <span class="small">${escHtml(s.shared_with?.name || '')} (${s.permission})</span>
                <button class="btn btn-sm btn-outline-danger" onclick="revokeShare(${s.id})">Gỡ</button>
            </div>
        `).join('');
    });
}

function confirmShare() {
    const email = document.getElementById('shareEmail').value.trim();
    const perm  = document.getElementById('sharePermission').value;
    const msg   = document.getElementById('shareMsg');
    if (!email) return;

    fetch(BASE_URL + '/notes/share', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken()
        },
        body: JSON.stringify({ note_id: currentNoteId, email, permission: perm })
    })
    .then(async r => {
        const contentType = r.headers.get('content-type') || '';
        const data = contentType.includes('application/json')
            ? await r.json()
            : { error: 'Máy chủ trả về dữ liệu không hợp lệ' };
        return { ok: r.ok, data };
    })
    .then(({ ok, data }) => {
        if (ok && data.success) {
            const sharedWith = escHtml(data.shared_with || email);
            msg.innerHTML = `<div class="alert alert-success py-1 small">✓ Đã chia sẻ với ${sharedWith}</div>`;
            showToast(`✅ Chia sẻ ghi chú thành công với ${sharedWith}`);
            document.getElementById('shareEmail').value = '';
            loadShareList();
        } else {
            msg.innerHTML = `<div class="alert alert-danger py-1 small">✗ ${escHtml(data.error || 'Không thể chia sẻ ghi chú')}</div>`;
        }
    })
    .catch(() => {
        msg.innerHTML = '<div class="alert alert-danger py-1 small">✗ Không thể kết nối máy chủ</div>';
    });
}

function revokeShare(shareId) {
    fetch(BASE_URL + '/notes/revokeShare/' + shareId, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() }
    }).then(() => loadShareList());
}

// =============================================
// NHÃN (Labels)
// =============================================
function initLabelsModal() {
    const btnLabels = document.getElementById('btnLabels');
    const btnSave   = document.getElementById('btnSaveLabels');
    if (btnLabels) btnLabels.addEventListener('click', openLabelsModal);
    if (btnSave)   btnSave.addEventListener('click', saveLabels);
}

function openLabelsModal() {
    if (!currentNoteId) return;
    // Fetch current note to pre-check its labels
    fetch(BASE_URL + '/notes/' + currentNoteId, { headers: { 'X-CSRF-TOKEN': csrfToken() } })
    .then(r => r.json())
    .then(data => {
        const noteLabels = (data.labels || []).map(l => String(l.id));
        document.querySelectorAll('.label-checkbox').forEach(cb => {
            cb.checked = noteLabels.includes(cb.value);
        });
        keepCurrentNoteOnModalClose = true;
        noteModalBs.hide();
        labelsModalBs.show();
    });
}

function saveLabels() {
    const checked = [...document.querySelectorAll('.label-checkbox:checked')].map(el => el.value);
    fetch(BASE_URL + '/notes/syncLabels', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ note_id: currentNoteId, label_ids: checked })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            showToast('🏷️ Đã lưu nhãn thành công');
            refreshNotesList(0);
        }
        labelsModalBs.hide();
    });
}

// =============================================
// TRANG QUẢN LÝ NHÃN
// =============================================
function initLabelsPage() {
    const btnAdd = document.getElementById('btnAddLabel');
    if (!btnAdd) return;

    btnAdd.addEventListener('click', function () {
        const name = document.getElementById('newLabelName').value.trim();
        if (!name) return;
        fetch(BASE_URL + '/labels/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ name })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('newLabelName').value = '';
                const empty = document.getElementById('emptyLabels');
                if (empty) empty.remove();
                const list = document.getElementById('labelList');
                if (list) {
                    list.insertAdjacentHTML('beforeend', `
                        <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded label-item" data-id="${data.id}">
                            <i class="bi bi-tag text-primary"></i>
                            <span class="flex-grow-1 label-name fw-semibold">${escHtml(data.name)}</span>
                            <input type="text" class="form-control form-control-sm label-edit-input flex-grow-1" value="${escHtml(data.name)}" style="display:none">
                            <span class="badge bg-secondary">0 ghi chú</span>
                            <button class="btn btn-sm btn-outline-secondary btn-edit-label"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-primary btn-save-label" style="display:none"><i class="bi bi-check"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-label"><i class="bi bi-trash"></i></button>
                        </div>
                    `);
                }
            }
        });
    });

    document.getElementById('labelList')?.addEventListener('click', function (e) {
        const item = e.target.closest('.label-item');
        if (!item) return;
        const id = item.dataset.id;

        if (e.target.closest('.btn-edit-label')) {
            item.querySelector('.label-name').style.display = 'none';
            item.querySelector('.label-edit-input').style.display = '';
            item.querySelector('.btn-edit-label').style.display = 'none';
            item.querySelector('.btn-save-label').style.display = '';
        }
        if (e.target.closest('.btn-save-label')) {
            const newName = item.querySelector('.label-edit-input').value.trim();
            if (!newName) return;
            fetch(BASE_URL + '/labels/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ name: newName })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    item.querySelector('.label-name').textContent = newName;
                    item.querySelector('.label-name').style.display = '';
                    item.querySelector('.label-edit-input').style.display = 'none';
                    item.querySelector('.btn-edit-label').style.display = '';
                    item.querySelector('.btn-save-label').style.display = 'none';
                }
            });
        }
        if (e.target.closest('.btn-delete-label')) {
            if (!confirm('Xóa nhãn này?')) return;
            fetch(BASE_URL + '/labels/delete/' + id, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() }
            }).then(r => r.json()).then(data => { if (data.success) item.remove(); });
        }
    });
}

// =============================================
// PREFERENCES (Profile page)
// =============================================
function initPreferences() {
    const btnSave = document.getElementById('btnSavePrefs');
    if (!btnSave) return;
    btnSave.addEventListener('click', function () {
        const fontSize  = document.getElementById('prefFontSize')?.value  || 'medium';
        const noteColor = document.getElementById('prefNoteColor')?.value || '#ffffff';
        
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';

        fetch(BASE_URL + '/profile/preferences', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ 
                font_size: fontSize, 
                note_color: noteColor, 
                theme: document.documentElement.getAttribute('data-theme') || 'light' 
            })
        }).then(r => r.json()).then(data => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="bi bi-check2 me-2"></i>Lưu tùy chỉnh';

            if (data.success) {
                // Áp dụng font chữ mới tức thì
                document.body.className = document.body.className.replace(/font-\w+/, 'font-' + fontSize);
                
                // Hiển thị thông báo đẹp
                showToast('✅ Đã lưu tùy chỉnh thành công!');
            }
        });
    });
}

function initAvatarAdjuster() {
    const form = document.getElementById('profileForm');
    const fileInput = document.getElementById('avatarInput');
    const previewImg = document.getElementById('avatarPreview');
    const previewPlaceholder = document.getElementById('avatarPreviewPlaceholder');
    const adjuster = document.getElementById('avatarAdjuster');
    const cropCanvas = document.getElementById('avatarCropCanvas');

    if (!form || !fileInput || !adjuster || !cropCanvas) return;

    const previewCanvas = document.createElement('canvas');
    const previewCtx = previewCanvas.getContext('2d');
    previewCanvas.width = 300;
    previewCanvas.height = 300;
    const cropCtx = cropCanvas.getContext('2d');

    let sourceImg = null;
    let objectUrl = null;
    let zoom = 1;
    let offsetX = 0;
    let offsetY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let pinchDistance = 0;
    let pointerState = null;

    const ensurePreviewImage = () => {
        if (previewImg) return previewImg;
        if (previewPlaceholder) {
            const img = document.createElement('img');
            img.id = 'avatarPreview';
            img.width = 90;
            img.height = 90;
            img.className = 'rounded-circle';
            img.style.objectFit = 'cover';
            previewPlaceholder.replaceWith(img);
            return img;
        }
        return null;
    };

    const drawToContext = (ctx, width, height) => {
        if (!sourceImg || !ctx) return;

        const imgW = sourceImg.naturalWidth;
        const imgH = sourceImg.naturalHeight;
        const baseScale = Math.max(width / imgW, height / imgH);
        const drawScale = baseScale * zoom;
        const drawW = imgW * drawScale;
        const drawH = imgH * drawScale;

        const freeX = Math.max(0, drawW - width);
        const freeY = Math.max(0, drawH - height);

        const maxOffsetX = freeX / 2;
        const maxOffsetY = freeY / 2;
        offsetX = Math.max(-maxOffsetX, Math.min(maxOffsetX, offsetX));
        offsetY = Math.max(-maxOffsetY, Math.min(maxOffsetY, offsetY));

        const x = -(freeX / 2) + offsetX;
        const y = -(freeY / 2) + offsetY;

        ctx.clearRect(0, 0, width, height);
        ctx.drawImage(sourceImg, x, y, drawW, drawH);
    };

    const drawPreview = () => {
        drawToContext(cropCtx, cropCanvas.width, cropCanvas.height);
        drawToContext(previewCtx, previewCanvas.width, previewCanvas.height);
        const activePreview = ensurePreviewImage();
        if (activePreview) activePreview.src = previewCanvas.toDataURL('image/jpeg', 0.9);
    };

    fileInput.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;

        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        sourceImg = new Image();
        sourceImg.onload = () => {
            zoom = 1;
            offsetX = 0;
            offsetY = 0;
            adjuster.style.display = '';
            drawPreview();
        };
        sourceImg.src = objectUrl;
    });

    cropCanvas.addEventListener('pointerdown', (e) => {
        if (!sourceImg) return;
        pointerState = e.pointerType || 'mouse';
        isDragging = true;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        cropCanvas.setPointerCapture(e.pointerId);
    });

    cropCanvas.addEventListener('pointermove', (e) => {
        if (!sourceImg || !isDragging || pointerState === 'touch') return;
        const dx = e.clientX - dragStartX;
        const dy = e.clientY - dragStartY;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        offsetX += dx;
        offsetY += dy;
        drawPreview();
    });

    const stopDrag = (e) => {
        isDragging = false;
        pointerState = null;
        if (e && typeof e.pointerId === 'number') {
            try { cropCanvas.releasePointerCapture(e.pointerId); } catch (_) {}
        }
    };
    cropCanvas.addEventListener('pointerup', stopDrag);
    cropCanvas.addEventListener('pointercancel', stopDrag);

    cropCanvas.addEventListener('wheel', (e) => {
        if (!sourceImg) return;
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.08 : 0.08;
        zoom = Math.max(1, Math.min(3, zoom + delta));
        drawPreview();
    }, { passive: false });

    cropCanvas.addEventListener('touchstart', (e) => {
        if (!sourceImg) return;
        if (e.touches.length === 1) {
            isDragging = true;
            dragStartX = e.touches[0].clientX;
            dragStartY = e.touches[0].clientY;
        } else if (e.touches.length === 2) {
            pinchDistance = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
        }
    }, { passive: true });

    cropCanvas.addEventListener('touchmove', (e) => {
        if (!sourceImg) return;
        if (e.touches.length === 1 && isDragging) {
            const dx = e.touches[0].clientX - dragStartX;
            const dy = e.touches[0].clientY - dragStartY;
            dragStartX = e.touches[0].clientX;
            dragStartY = e.touches[0].clientY;
            offsetX += dx;
            offsetY += dy;
            drawPreview();
        } else if (e.touches.length === 2) {
            const nextDistance = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            if (pinchDistance > 0) {
                const ratio = nextDistance / pinchDistance;
                zoom = Math.max(1, Math.min(3, zoom * ratio));
                drawPreview();
            }
            pinchDistance = nextDistance;
        }
    }, { passive: true });

    cropCanvas.addEventListener('touchend', () => {
        isDragging = false;
        pinchDistance = 0;
    }, { passive: true });

    form.addEventListener('submit', function (e) {
        if (!sourceImg) return;
        e.preventDefault();
        drawPreview();

        previewCanvas.toBlob((blob) => {
            if (!blob) {
                form.submit();
                return;
            }
            const croppedFile = new File([blob], 'avatar-cropped.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(croppedFile);
            fileInput.files = dt.files;
            form.submit();
        }, 'image/jpeg', 0.9);
    });
}

// =============================================
// VIEW TOGGLE (Grid / List)
// =============================================
function initViewToggle() {
    const btn = document.getElementById('viewToggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
        const container = document.getElementById('notesContainer');
        const icon = document.getElementById('viewIcon');
        const isCurrentlyGrid = btn.dataset.view === 'grid';
        
        const newView = isCurrentlyGrid ? 'list' : 'grid';
        btn.dataset.view = newView;
        
        if (icon) icon.className = newView === 'list' ? 'bi bi-list' : 'bi bi-grid-3x3-gap';
        
        const cols = container?.querySelectorAll('.note-col');
        cols?.forEach(col => {
            col.className = newView === 'list' 
                ? 'col-12 note-col' 
                : 'col-sm-6 col-md-4 col-lg-3 note-col';
        });
    });
}

// =============================================
// HELPERS
// =============================================
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}
function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function showErr(id, msg) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg; el.style.display = 'block'; }
}
function hideErr(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'none'; el.textContent = ''; }
}

function showToast(msg) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'position-fixed bottom-0 start-50 translate-middle-x p-3';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
    }
    
    const toastId = 'toast_' + Date.now();
    const html = `
        <div id="${toastId}" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
    bsToast.show();
    
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}
