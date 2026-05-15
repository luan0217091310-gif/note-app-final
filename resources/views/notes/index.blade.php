@extends('layouts.app')
@section('title', 'Ghi chú của tôi')

@section('content')
{{-- FAB Button --}}
<button class="btn btn-primary rounded-circle shadow position-fixed"
        id="btnNewNote" title="Tạo ghi chú mới"
        style="bottom:32px;right:32px;width:56px;height:56px;z-index:1050;font-size:24px">
    <i class="bi bi-plus"></i>
</button>

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Ghi chú của tôi</h5>
</div>

{{-- Notes Container --}}
<div class="row g-3" id="notesContainer">
    @forelse($notes as $note)
    <div class="{{ session('view', 'grid') === 'list' ? 'col-12' : 'col-sm-6 col-md-4 col-lg-3' }} note-col"
         data-id="{{ $note->id }}"
         data-locked="{{ $note->isLocked() ? '1' : '0' }}">
        <div class="card h-100 shadow-sm border-0 note-card {{ $note->is_pinned ? 'border-warning border' : '' }}"
             style="--note-bg:{{ auth()->user()->note_color ?? '#fff' }}; background:var(--note-bg); cursor:pointer"
             onclick="openNote({{ $note->id }})">
            {{-- Thumbnail ảnh (nếu có) --}}
            @if(!$note->isLocked() && $note->images->count() > 0)
            <div style="height:160px;overflow:hidden;border-radius:0.375rem 0.375rem 0 0">
                <img src="{{ asset('storage/' . $note->images->first()->image_path) }}"
                     style="width:100%;height:100%;object-fit:cover" alt="">
            </div>
            @endif
            <div class="card-body">
                <div class="d-flex gap-1 mb-2">
                    @if($note->is_pinned) <span class="badge bg-warning text-dark"><i class="bi bi-pin-fill"></i> Ghim</span> @endif
                    @if($note->isLocked()) <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i></span> @endif
                    @if($note->shares->count() > 0) <span class="badge bg-info text-dark"><i class="bi bi-share-fill"></i></span> @endif
                </div>
                <h6 class="card-title fw-semibold text-truncate">{{ $note->title ?: 'Không có tiêu đề' }}</h6>
                @if(!$note->isLocked())
                    <p class="card-text text-muted small" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
                        {{ Str::limit(strip_tags($note->content ?? ''), 150) }}
                    </p>
                @else
                    <p class="card-text text-muted small"><i class="bi bi-lock"></i> Ghi chú đã được khóa</p>
                @endif
                @if($note->images->count() > 1)
                <p class="text-muted small mb-1"><i class="bi bi-images me-1"></i>{{ $note->images->count() }} ảnh</p>
                @endif
                @if($note->labels->count() > 0)
                <div class="d-flex flex-wrap gap-1 mt-2">
                    @foreach($note->labels as $label)
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $label->name }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="card-footer bg-transparent border-0 text-muted small">
                <i class="bi bi-clock me-1"></i>{{ $note->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <i class="bi bi-journal-x fs-1 text-muted"></i>
        <h5 class="text-muted mt-3">Chưa có ghi chú nào</h5>
        <p class="text-muted">Nhấn nút <strong>+</strong> để tạo ghi chú đầu tiên.</p>
    </div>
    @endforelse
</div>

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
@endsection
