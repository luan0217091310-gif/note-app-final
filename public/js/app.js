/**
 * App.js - Logic JavaScript chính
 * Live Search (300ms delay), Auto-save, Theme Toggle, Grid/List,
 * Note CRUD, Labels, Lock, Share, Image Upload
 */

// =============================================
// GLOBAL STATE
// =============================================
let currentNoteId = null;
let autoSaveTimer = null;
let searchTimer = null;
const AUTO_SAVE_DELAY = 1000;  // 1 giây
const SEARCH_DELAY = 300;      // 300ms

// =============================================
// DOM READY
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initThemeToggle();
    initSearch();
    initViewToggle();
    initNoteModal();
    initLabelsPage();
    initProfilePage();
    initUserMenu();
});

// =============================================
// SIDEBAR TOGGLE
// =============================================
function initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggle) return;
    if (!sidebar) return;

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        if (window.innerWidth > 992) {
            sidebar.classList.toggle('collapsed');
            const mc = document.querySelector('.main-content');
            if (mc) mc.classList.toggle('expanded');
        } else {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('show');
        }
    });

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }
}

// =============================================
// THEME TOGGLE (Dark/Light)
// =============================================
function initThemeToggle() {
    const toggle = document.getElementById('themeToggle');
    if (!toggle) return;

    toggle.addEventListener('click', function() {
        const html = document.documentElement;
        const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);

        // Lưu lên server
        fetch(BASE_URL + '/profile/updatePreferences', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                font_size: document.body.className.replace('font-', ''),
                note_color: USER_NOTE_COLOR,
                theme: newTheme
            })
        });
    });
}

// =============================================
// USER MENU DROPDOWN
// =============================================
function initUserMenu() {
    const btn = document.getElementById('userAvatarBtn');
    const dropdown = document.getElementById('userDropdown');
    if (!btn || !dropdown) return;

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
}

// =============================================
// LIVE SEARCH (300ms delay)
// =============================================
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('searchClear');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearBtn.style.display = query ? 'block' : 'none';

        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            performSearch(query);
        }, SEARCH_DELAY);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            performSearch('');
        });
    }
}

function performSearch(query) {
    const container = document.getElementById('notesContainer');
    if (!container) return;

    const urlParams = new URLSearchParams(window.location.search);
    const labelId = urlParams.get('label_id') || '';

    fetch(BASE_URL + '/notes/search?q=' + encodeURIComponent(query) + '&label_id=' + labelId)
        .then(res => res.json())
        .then(notes => {
            renderNotes(notes, container);
        })
        .catch(err => console.error('Search error:', err));
}

function renderNotes(notes, container) {
    if (notes.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3>Không tìm thấy kết quả</h3>
                <p>Thử tìm kiếm với từ khóa khác.</p>
            </div>`;
        return;
    }

    container.innerHTML = notes.map(note => `
        <div class="note-card ${note.is_pinned ? 'pinned' : ''}" 
             data-id="${note.id}" 
             data-locked="${note.lock_password ? '1' : '0'}"
             style="--note-bg: ${USER_NOTE_COLOR}">
            <div class="note-badges">
                ${note.is_pinned ? '<span class="badge badge-pin" title="Đã ghim">📌</span>' : ''}
                ${note.lock_password ? '<span class="badge badge-lock" title="Đã khóa">🔒</span>' : ''}
                ${note.share_count > 0 ? '<span class="badge badge-share" title="Đã chia sẻ">🔗</span>' : ''}
            </div>
            <div class="note-content" onclick="openNote(${note.id})">
                <h3 class="note-title">${escapeHtml(note.title || 'Không có tiêu đề')}</h3>
                ${!note.lock_password 
                    ? `<p class="note-excerpt">${escapeHtml((note.content || '').substring(0, 150))}</p>` 
                    : '<p class="note-excerpt locked-text">🔒 Ghi chú đã được khóa</p>'}
            </div>
            ${note.label_names ? `
            <div class="note-labels">
                ${note.label_names.split(', ').map(l => `<span class="label-chip">${escapeHtml(l)}</span>`).join('')}
            </div>` : ''}
            <div class="note-footer">
                <small class="note-date">${formatDate(note.updated_at)}</small>
            </div>
        </div>
    `).join('');
}

// =============================================
// VIEW TOGGLE (Grid/List)
// =============================================
function initViewToggle() {
    const toggle = document.getElementById('viewToggle');
    const container = document.getElementById('notesContainer');
    if (!toggle || !container) return;

    toggle.addEventListener('click', function() {
        const current = this.getAttribute('data-view');
        const newView = current === 'grid' ? 'list' : 'grid';
        this.setAttribute('data-view', newView);
        container.classList.remove('grid-view', 'list-view');
        container.classList.add(newView + '-view');
    });
}

// =============================================
// NOTE MODAL (Add/Edit - Giao diện chung)
// =============================================
function initNoteModal() {
    const modal = document.getElementById('noteModal');
    const closeBtn = document.getElementById('btnCloseModal');
    const newNoteBtn = document.getElementById('btnNewNote');
    const titleInput = document.getElementById('noteTitle');
    const contentInput = document.getElementById('noteContent');

    if (!modal) return;

    // Nút tạo ghi chú mới
    if (newNoteBtn) {
        newNoteBtn.addEventListener('click', createNewNote);
    }

    // Đóng modal
    if (closeBtn) {
        closeBtn.addEventListener('click', closeNoteModal);
    }

    // Click overlay đóng modal
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeNoteModal();
    });

    // Auto-save khi gõ
    if (titleInput) {
        titleInput.addEventListener('input', triggerAutoSave);
    }
    if (contentInput) {
        contentInput.addEventListener('input', triggerAutoSave);
    }

    // Upload ảnh
    const imageUpload = document.getElementById('imageUpload');
    if (imageUpload) {
        imageUpload.addEventListener('change', handleImageUpload);
    }

    // Pin
    const btnPin = document.getElementById('btnPin');
    if (btnPin) {
        btnPin.addEventListener('click', togglePin);
    }

    // Lock
    const btnLock = document.getElementById('btnLock');
    if (btnLock) {
        btnLock.addEventListener('click', openLockModal);
    }

    // Lock modal confirm
    const btnConfirmLock = document.getElementById('btnConfirmLock');
    if (btnConfirmLock) {
        btnConfirmLock.addEventListener('click', confirmLock);
    }

    const btnRemoveLock = document.getElementById('btnRemoveLock');
    if (btnRemoveLock) {
        btnRemoveLock.addEventListener('click', removeLock);
    }

    // Share
    const btnShare = document.getElementById('btnShare');
    if (btnShare) {
        btnShare.addEventListener('click', openShareModal);
    }

    const btnConfirmShare = document.getElementById('btnConfirmShare');
    if (btnConfirmShare) {
        btnConfirmShare.addEventListener('click', confirmShare);
    }

    // Delete
    const btnDelete = document.getElementById('btnDeleteNote');
    if (btnDelete) {
        btnDelete.addEventListener('click', openDeleteModal);
    }

    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', confirmDelete);
    }

    // Labels
    const btnLabels = document.getElementById('btnLabels');
    if (btnLabels) {
        btnLabels.addEventListener('click', openLabelsModal);
    }

    // Verify Lock
    const btnVerifyLock = document.getElementById('btnVerifyLock');
    if (btnVerifyLock) {
        btnVerifyLock.addEventListener('click', verifyLock);
    }
}

// =============================================
// NOTE CRUD
// =============================================
function createNewNote() {
    fetch(BASE_URL + '/notes/create', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentNoteId = data.id;
                openNoteEditor({ id: data.id, title: '', content: '', images: [], labels: [], shares: [], is_pinned: 0, lock_password: null });
            }
        })
        .catch(err => console.error('Create error:', err));
}

function openNote(noteId) {
    const card = document.querySelector(`.note-card[data-id="${noteId}"]`);
    const isLocked = card && card.getAttribute('data-locked') === '1';

    if (isLocked) {
        currentNoteId = noteId;
        openVerifyLockModal();
        return;
    }

    fetch(BASE_URL + '/notes/get/' + noteId)
        .then(res => res.json())
        .then(data => {
            if (data.locked) {
                currentNoteId = noteId;
                openVerifyLockModal();
                return;
            }
            if (data.error) {
                alert(data.error);
                return;
            }
            currentNoteId = data.id;
            openNoteEditor(data);
        })
        .catch(err => console.error('Get error:', err));
}

function openNoteEditor(note) {
    const modal = document.getElementById('noteModal');
    const titleInput = document.getElementById('noteTitle');
    const contentInput = document.getElementById('noteContent');
    const saveStatus = document.getElementById('saveStatus');
    const imagesContainer = document.getElementById('noteImages');

    titleInput.value = note.title || '';
    contentInput.value = note.content || '';
    saveStatus.textContent = '';

    // Cập nhật icon pin
    const pinIcon = document.getElementById('pinIcon');
    if (pinIcon) {
        pinIcon.textContent = note.is_pinned ? '📌' : '📌';
        pinIcon.parentElement.classList.toggle('active', !!note.is_pinned);
    }

    // Cập nhật icon lock
    const lockIcon = document.getElementById('lockIcon');
    if (lockIcon) {
        lockIcon.textContent = note.lock_password ? '🔒' : '🔓';
    }

    // Hiển thị hình ảnh
    renderNoteImages(note.images || [], imagesContainer);

    // Check labels
    const checkboxes = document.querySelectorAll('.label-checkbox');
    const noteLabels = (note.labels || []).map(l => l.id);
    checkboxes.forEach(cb => {
        cb.checked = noteLabels.includes(parseInt(cb.value));
    });

    // Load shares
    renderShareList(note.shares || []);

    modal.classList.add('show');
    titleInput.focus();
}

function closeNoteModal() {
    const modal = document.getElementById('noteModal');
    modal.classList.remove('show');
    currentNoteId = null;
    clearTimeout(autoSaveTimer);
    // Reload trang để hiển thị các thay đổi
    location.reload();
}

// =============================================
// AUTO-SAVE (debounce 1s)
// =============================================
function triggerAutoSave() {
    const saveStatus = document.getElementById('saveStatus');
    if (saveStatus) saveStatus.textContent = 'Đang lưu...';

    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        autoSaveNote();
    }, AUTO_SAVE_DELAY);
}

function autoSaveNote() {
    if (!currentNoteId) return;

    const title = document.getElementById('noteTitle').value;
    const content = document.getElementById('noteContent').value;
    const saveStatus = document.getElementById('saveStatus');

    fetch(BASE_URL + '/notes/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentNoteId, title: title, content: content })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && saveStatus) {
            saveStatus.textContent = '✓ Đã lưu';
            setTimeout(() => { saveStatus.textContent = ''; }, 2000);
        }
    })
    .catch(err => {
        console.error('Auto-save error:', err);
        if (saveStatus) saveStatus.textContent = '✕ Lỗi lưu';
    });
}

// =============================================
// PIN TOGGLE
// =============================================
function togglePin() {
    if (!currentNoteId) return;

    fetch(BASE_URL + '/notes/togglePin/' + currentNoteId, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const pinIcon = document.getElementById('pinIcon');
                const btn = pinIcon.parentElement;
                btn.classList.toggle('active');
            }
        });
}

// =============================================
// LOCK/UNLOCK
// =============================================
function openLockModal() {
    const modal = document.getElementById('lockModal');
    const lockIcon = document.getElementById('lockIcon');
    const btnRemove = document.getElementById('btnRemoveLock');
    const title = document.getElementById('lockModalTitle');
    const passwordInput = document.getElementById('lockPassword');

    passwordInput.value = '';
    
    if (lockIcon.textContent === '🔒') {
        title.textContent = 'Đổi/Gỡ mật khẩu';
        btnRemove.style.display = 'block';
    } else {
        title.textContent = 'Khóa ghi chú';
        btnRemove.style.display = 'none';
    }

    modal.classList.add('show');
}

function closeLockModal() {
    document.getElementById('lockModal').classList.remove('show');
}

function confirmLock() {
    const password = document.getElementById('lockPassword').value;
    if (!password) { alert('Vui lòng nhập mật khẩu'); return; }

    fetch(BASE_URL + '/notes/setLock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentNoteId, password: password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('lockIcon').textContent = '🔒';
            closeLockModal();
        }
    });
}

function removeLock() {
    fetch(BASE_URL + '/notes/removeLock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentNoteId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('lockIcon').textContent = '🔓';
            closeLockModal();
        }
    });
}

function openVerifyLockModal() {
    document.getElementById('verifyLockPassword').value = '';
    document.getElementById('verifyLockModal').classList.add('show');
}

function closeVerifyLockModal() {
    document.getElementById('verifyLockModal').classList.remove('show');
}

function verifyLock() {
    const password = document.getElementById('verifyLockPassword').value;
    if (!password) { alert('Vui lòng nhập mật khẩu'); return; }

    fetch(BASE_URL + '/notes/verifyLock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentNoteId, password: password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeVerifyLockModal();
            // Giờ mở lại note bình thường
            fetch(BASE_URL + '/notes/get/' + currentNoteId)
                .then(res => res.json())
                .then(note => {
                    openNoteEditor(note);
                });
        } else {
            alert(data.error || 'Mật khẩu không đúng');
        }
    });
}

// =============================================
// SHARE
// =============================================
function openShareModal() {
    document.getElementById('shareEmail').value = '';
    document.getElementById('sharePermission').value = 'read';
    const msg = document.getElementById('shareMsg');
    if (msg) { msg.textContent = ''; msg.className = 'share-msg'; }
    document.getElementById('shareModal').classList.add('show');
    loadShareList();
}

function loadShareList() {
    fetch(BASE_URL + '/notes/get/' + currentNoteId)
        .then(res => res.json())
        .then(data => {
            renderShareList(data.shares || []);
        })
        .catch(() => {});
}

function closeShareModal() {
    document.getElementById('shareModal').classList.remove('show');
}

function confirmShare() {
    const email = document.getElementById('shareEmail').value.trim();
    const permission = document.getElementById('sharePermission').value;
    const msgEl = document.getElementById('shareMsg');

    if (!email) {
        if (msgEl) { msgEl.textContent = '⚠ Vui lòng nhập email'; msgEl.className = 'share-msg error'; }
        return;
    }
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRe.test(email)) {
        if (msgEl) { msgEl.textContent = '⚠ Email không đúng định dạng'; msgEl.className = 'share-msg error'; }
        return;
    }

    fetch(BASE_URL + '/notes/share', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ note_id: currentNoteId, email: email, permission: permission })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('shareEmail').value = '';
            if (msgEl) { msgEl.textContent = '✓ Đã chia sẻ với ' + data.shared_with; msgEl.className = 'share-msg success'; }
            // Reload share list
            loadShareList();
        } else {
            if (msgEl) { msgEl.textContent = '✗ ' + (data.error || 'Không thể chia sẻ'); msgEl.className = 'share-msg error'; }
        }
    })
    .catch(() => {
        if (msgEl) { msgEl.textContent = '✗ Lỗi kết nối'; msgEl.className = 'share-msg error'; }
    });
}

function renderShareList(shares) {
    const container = document.getElementById('shareList');
    if (!container) return;

    if (shares.length === 0) {
        container.innerHTML = '<p class="text-muted" style="font-size:13px">Chưa chia sẻ với ai</p>';
        return;
    }

    container.innerHTML = '<h4 style="font-size:13px; margin-bottom:8px; color:var(--text-secondary)">Đã chia sẻ với:</h4>' +
        shares.map(s => `
        <div class="share-item">
            <div class="share-item-info">
                <strong>${escapeHtml(s.display_name)}</strong>
                <small>${escapeHtml(s.email)}</small>
            </div>
            <div class="share-item-actions">
                <select class="form-select" style="width:auto; padding:4px 28px 4px 8px; font-size:12px" 
                        onchange="updatePermission(${s.id}, this.value)">
                    <option value="read" ${s.permission === 'read' ? 'selected' : ''}>Chỉ xem</option>
                    <option value="edit" ${s.permission === 'edit' ? 'selected' : ''}>Chỉnh sửa</option>
                </select>
                <button class="btn-icon-sm" onclick="revokeShare(${s.id})" title="Thu hồi">✕</button>
            </div>
        </div>
    `).join('');
}

function updatePermission(shareId, permission) {
    fetch(BASE_URL + '/notes/updatePermission', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ share_id: shareId, permission: permission })
    });
}

function revokeShare(shareId) {
    if (!confirm('Thu hồi quyền chia sẻ?')) return;

    fetch(BASE_URL + '/notes/revokeShare/' + shareId, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) openShareModal(); // Reload list
        });
}

// =============================================
// DELETE
// =============================================
function openDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.remove('show');
}

function confirmDelete() {
    if (!currentNoteId) return;

    fetch(BASE_URL + '/notes/delete/' + currentNoteId, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                document.getElementById('noteModal').classList.remove('show');
                currentNoteId = null;
                location.reload();
            }
        });
}

// =============================================
// IMAGES UPLOAD
// =============================================
function handleImageUpload() {
    const input = document.getElementById('imageUpload');
    if (!input.files.length || !currentNoteId) return;

    const formData = new FormData();
    formData.append('note_id', currentNoteId);
    for (let i = 0; i < input.files.length; i++) {
        formData.append('images[]', input.files[i]);
    }

    fetch(BASE_URL + '/notes/uploadImages', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Reload note images
            fetch(BASE_URL + '/notes/get/' + currentNoteId)
                .then(res => res.json())
                .then(note => {
                    renderNoteImages(note.images || [], document.getElementById('noteImages'));
                });
        }
        input.value = ''; // Reset input
    });
}

function renderNoteImages(images, container) {
    if (!container) return;

    container.innerHTML = images.map(img => `
        <div class="note-image-item">
            <img src="${BASE_URL}/${img.image_path}" alt="Attached image">
            <button class="remove-image" onclick="removeImage(${img.id})" title="Xóa ảnh">✕</button>
        </div>
    `).join('');
}

function removeImage(imageId) {
    fetch(BASE_URL + '/notes/removeImage/' + imageId, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fetch(BASE_URL + '/notes/get/' + currentNoteId)
                    .then(res => res.json())
                    .then(note => {
                        renderNoteImages(note.images || [], document.getElementById('noteImages'));
                    });
            }
        });
}

// =============================================
// LABELS MODAL (trong Note Editor)
// =============================================
function openLabelsModal() {
    document.getElementById('labelsModal').classList.add('show');
}

function closeLabelsModal() {
    document.getElementById('labelsModal').classList.remove('show');

    // Đồng bộ labels với server
    const checkboxes = document.querySelectorAll('.label-checkbox:checked');
    const labelIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

    fetch(BASE_URL + '/notes/syncLabels', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ note_id: currentNoteId, label_ids: labelIds })
    });
}

// =============================================
// LABELS PAGE (CRUD)
// =============================================
function initLabelsPage() {
    const btnAdd = document.getElementById('btnAddLabel');
    if (!btnAdd) return;

    // Thêm label
    btnAdd.addEventListener('click', function() {
        const input = document.getElementById('newLabelName');
        const name = input.value.trim();
        if (!name) return;

        fetch(BASE_URL + '/labels/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });

    // Enter key
    const newLabelInput = document.getElementById('newLabelName');
    if (newLabelInput) {
        newLabelInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') btnAdd.click();
        });
    }

    // Edit labels (delegate)
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit-label')) {
            const item = e.target.closest('.label-item');
            item.querySelector('.label-name').style.display = 'none';
            item.querySelector('.label-edit-input').style.display = 'block';
            item.querySelector('.label-edit-input').focus();
            item.querySelector('.btn-edit-label').style.display = 'none';
            item.querySelector('.btn-save-label').style.display = 'inline-flex';
        }

        // Save button
        if (e.target.closest('.btn-save-label')) {
            const item = e.target.closest('.label-item');
            const id = item.getAttribute('data-id');
            const newName = item.querySelector('.label-edit-input').value.trim();

            if (!newName) return;

            fetch(BASE_URL + '/labels/update/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: newName })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
            });
        }

        // Delete button
        if (e.target.closest('.btn-delete-label')) {
            if (!confirm('Xóa nhãn này? Ghi chú liên kết sẽ không bị ảnh hưởng.')) return;

            const item = e.target.closest('.label-item');
            const id = item.getAttribute('data-id');

            fetch(BASE_URL + '/labels/delete/' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) item.remove();
                });
        }
    });
}

// =============================================
// PROFILE PAGE
// =============================================
function initProfilePage() {
    // Avatar preview
    const avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" id="avatarImg">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Color swatches
    document.querySelectorAll('.color-swatch').forEach(swatch => {
        swatch.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            const picker = document.getElementById('noteColorPicker');
            if (picker) picker.value = color;
        });
    });

    // Theme options
    document.querySelectorAll('.theme-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.theme-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            document.documentElement.setAttribute('data-theme', this.getAttribute('data-theme'));
        });
    });

    // Save preferences
    const btnSave = document.getElementById('btnSavePreferences');
    if (btnSave) {
        btnSave.addEventListener('click', function() {
            const fontSize = document.querySelector('input[name="font_size"]:checked')?.value || 'medium';
            const noteColor = document.getElementById('noteColorPicker')?.value || '#ffffff';
            const theme = document.querySelector('.theme-option.active')?.getAttribute('data-theme') || 'light';

            fetch(BASE_URL + '/profile/updatePreferences', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    font_size: fontSize,
                    note_color: noteColor,
                    theme: theme
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.body.className = 'font-' + fontSize;
                    alert('Tùy chọn đã được lưu!');
                }
            });
        });
    }
}

// =============================================
// SHARED NOTES
// =============================================
function openSharedNote(noteId, permission) {
    fetch(BASE_URL + '/notes/get/' + noteId)
        .then(res => res.json())
        .then(note => {
            const modal = document.getElementById('sharedNoteModal');
            const titleInput = document.getElementById('sharedNoteTitle');
            const contentInput = document.getElementById('sharedNoteContent');
            const saveStatus = document.getElementById('sharedSaveStatus');

            titleInput.value = note.title || '';
            contentInput.value = note.content || '';
            currentNoteId = noteId;

            if (permission === 'read') {
                titleInput.readOnly = true;
                contentInput.readOnly = true;
                saveStatus.textContent = 'Chỉ xem';
            } else {
                titleInput.readOnly = false;
                contentInput.readOnly = false;
                saveStatus.textContent = '';

                // Auto-save cho shared note
                titleInput.addEventListener('input', triggerSharedAutoSave);
                contentInput.addEventListener('input', triggerSharedAutoSave);

                // Kích hoạt WebSocket cho real-time collaboration
                if (typeof initCollaboration === 'function') {
                    initCollaboration(noteId);
                    document.getElementById('collabIndicator').style.display = 'flex';
                }
            }

            modal.classList.add('show');
        });
}

function closeSharedNoteModal() {
    document.getElementById('sharedNoteModal').classList.remove('show');
    currentNoteId = null;

    if (typeof disconnectCollaboration === 'function') {
        disconnectCollaboration();
    }
}

function triggerSharedAutoSave() {
    const saveStatus = document.getElementById('sharedSaveStatus');
    if (saveStatus) saveStatus.textContent = 'Đang lưu...';

    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        const title = document.getElementById('sharedNoteTitle').value;
        const content = document.getElementById('sharedNoteContent').value;

        fetch(BASE_URL + '/notes/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentNoteId, title: title, content: content })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && saveStatus) {
                saveStatus.textContent = '✓ Đã lưu';
                setTimeout(() => { saveStatus.textContent = ''; }, 2000);
            }

            // Gửi update qua WebSocket
            if (typeof sendCollabUpdate === 'function') {
                sendCollabUpdate({ title: title, content: content });
            }
        });

    }, AUTO_SAVE_DELAY);
}

// =============================================
// UTILITY FUNCTIONS
// =============================================
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const pad = n => n.toString().padStart(2, '0');
    return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
