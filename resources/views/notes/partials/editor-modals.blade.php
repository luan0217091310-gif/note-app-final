{{-- ===== MODAL: EDITOR GHI CHÚ ===== --}}
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <input type="text" class="form-control form-control-lg border-0 fw-semibold fs-5 shadow-none"
                       id="noteTitle" placeholder="Tiêu đề">
                <div class="d-flex align-items-center gap-2 ms-2">
                    <small class="text-success" id="saveStatus"></small>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body pt-2">
                <textarea class="form-control border-0 shadow-none" id="noteContent" rows="12"
                          placeholder="Viết ghi chú của bạn..." style="resize:none"></textarea>
                <div class="d-flex flex-wrap gap-2 mt-2" id="noteImages"></div>
                <input type="file" id="imageUpload" accept="image/*" multiple style="display:none">
            </div>
            <div class="modal-footer border-0 justify-content-between">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="btnUploadImg" title="Đính kèm ảnh"><i class="bi bi-image"></i></button>
                    <button class="btn btn-sm btn-outline-secondary" id="btnLabels" title="Nhãn"><i class="bi bi-tag"></i></button>
                    <button class="btn btn-sm btn-outline-secondary" id="btnPin" title="Ghim"><i class="bi bi-pin" id="pinIcon"></i></button>
                    <button class="btn btn-sm btn-outline-secondary" id="btnLock" title="Khóa"><i class="bi bi-unlock" id="lockIcon"></i></button>
                    <button class="btn btn-sm btn-outline-secondary" id="btnShare" title="Chia sẻ"><i class="bi bi-share"></i></button>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary px-3" id="btnDone">
                        <i class="bi bi-check2-circle me-1"></i>Hoàn tất
                    </button>
                    <button class="btn btn-outline-danger px-3" id="btnDeleteNote">
                        <i class="bi bi-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: KHÓA GHI CHÚ (Better Approach) ===== --}}
<div class="modal fade" id="lockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header"><h5 class="modal-title" id="lockModalTitle">Khóa ghi chú</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div id="setLockSection">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Mật khẩu mới</label>
                        <input type="password" id="lockPassword" class="form-control" placeholder="Nhập mật khẩu">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Xác nhận mật khẩu</label>
                        <input type="password" id="lockPasswordConfirm" class="form-control" placeholder="Nhập lại mật khẩu">
                    </div>
                    <div id="lockErrorMsg" class="text-danger small mb-2" style="display:none"></div>
                    <button class="btn btn-primary w-100" id="btnConfirmLock">Đặt khóa</button>
                </div>
                <div id="unlockSection" style="display:none">
                    <p class="small text-muted">Nhập mật khẩu để mở khóa ghi chú.</p>
                    <input type="password" id="unlockPassword" class="form-control mb-2" placeholder="Mật khẩu">
                    <div id="unlockErrorMsg" class="text-danger small mb-2" style="display:none"></div>
                    <button class="btn btn-primary w-100" id="btnUnlockFromLockModal"><i class="bi bi-unlock me-2"></i>Mở khóa</button>
                </div>
                <div id="removeLockSection" style="display:none">
                    <hr>
                    <p class="small text-muted">Nhập mật khẩu hiện tại để gỡ bỏ khóa:</p>
                    <input type="password" id="currentLockPassword" class="form-control mb-2" placeholder="Mật khẩu hiện tại">
                    <div id="removeLockErrorMsg" class="text-danger small mb-2" style="display:none"></div>
                    <button class="btn btn-outline-danger w-100" id="btnRemoveLock">Gỡ bỏ khóa</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: MỞ KHÓA ===== --}}
<div class="modal fade" id="verifyLockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-lock-fill me-2"></i>Ghi chú đã khóa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="small text-muted">Nhập mật khẩu để xem ghi chú này.</p>
                <input type="password" id="verifyLockPassword" class="form-control mb-3" placeholder="Mật khẩu">
                <button class="btn btn-primary w-100" id="btnVerifyLock"><i class="bi bi-unlock me-2"></i>Mở khóa</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: CHIA SẺ ===== --}}
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-share me-2"></i>Chia sẻ ghi chú</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Email người nhận</label>
                    <input type="email" id="shareEmail" class="form-control" placeholder="email@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Quyền truy cập</label>
                    <select id="sharePermission" class="form-select">
                        <option value="read">Chỉ xem</option>
                        <option value="edit">Có thể chỉnh sửa</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100 mb-3" id="btnConfirmShare"><i class="bi bi-send me-2"></i>Chia sẻ</button>
                <div id="shareMsg" class="mb-2"></div>
                <div id="shareList"></div>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: NHÃN ===== --}}
<div class="modal fade" id="labelsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-tags me-2"></i>Gắn nhãn</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="labelsCheckboxList">
                @forelse($labels as $label)
                    <div class="form-check">
                        <input class="form-check-input label-checkbox" type="checkbox" value="{{ $label->id }}" id="label_{{ $label->id }}">
                        <label class="form-check-label" for="label_{{ $label->id }}">{{ $label->name }}</label>
                    </div>
                @empty
                    <p class="text-muted small">Chưa có nhãn. <a href="{{ route('labels.index') }}">Tạo nhãn mới</a></p>
                @endforelse
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-primary w-100" id="btnSaveLabels">Lưu nhãn</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: XÁC NHẬN XÓA ===== --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-4">
            <div class="fs-1 mb-3">🗑️</div>
            <h5>Xóa ghi chú?</h5>
            <p class="text-muted small">Hành động này không thể hoàn tác.</p>
            <div class="d-flex gap-2 justify-content-center mt-3">
                <button class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-danger px-4" id="btnConfirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>
