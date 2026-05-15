@extends('layouts.app')
@section('title', 'Ghi chú được chia sẻ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-share me-2 text-primary"></i>Ghi chú được chia sẻ với tôi</h5>
</div>

<div class="row g-3" id="notesContainer">
    @forelse($shared as $share)
    <div class="col-sm-6 col-md-4 col-lg-3 note-col" data-id="{{ $share->note->id }}">
        <div class="card h-100 shadow-sm border-0 note-card" style="cursor:pointer" onclick="openNote({{ $share->note->id }})">
            @if($share->note->images->count() > 0)
            <div style="height:160px;overflow:hidden;border-radius:0.375rem 0.375rem 0 0">
                <img src="{{ asset('storage/' . $share->note->images->first()->image_path) }}"
                     style="width:100%;height:100%;object-fit:cover" alt="">
            </div>
            @endif
            <div class="card-body">
                <div class="d-flex gap-1 mb-2">
                    <span class="badge {{ $share->permission === 'edit' ? 'bg-success' : 'bg-secondary' }}">
                        <i class="bi bi-{{ $share->permission === 'edit' ? 'pencil' : 'eye' }} me-1"></i>
                        {{ $share->permission === 'edit' ? 'Có thể sửa' : 'Chỉ xem' }}
                    </span>
                </div>
                <h6 class="card-title fw-semibold text-truncate">
                    {{ $share->note->title ?: 'Không có tiêu đề' }}
                </h6>
                <p class="card-text text-muted small" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
                    {{ Str::limit(strip_tags($share->note->content ?? ''), 120) }}
                </p>
            </div>
            <div class="card-footer bg-transparent border-0 text-muted small">
                <i class="bi bi-person me-1"></i>{{ $share->owner->name }}
                &nbsp;·&nbsp;
                <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($share->shared_at)->format('d/m/Y') }}
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <i class="bi bi-share fs-1 text-muted"></i>
        <h5 class="text-muted mt-3">Chưa có ghi chú nào được chia sẻ</h5>
        <p class="text-muted">Khi ai đó chia sẻ ghi chú với bạn, nó sẽ xuất hiện ở đây.</p>
    </div>
    @endforelse
</div>

{{-- ===== NOTE MODAL (dùng chung cho trang Shared) ===== --}}
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
                <div class="d-flex gap-2" id="ownerActions">
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
