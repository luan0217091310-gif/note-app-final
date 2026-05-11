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
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" id="viewToggle" data-view="grid" title="Đổi chế độ xem">
            <i class="bi bi-grid-3x3-gap" id="viewIcon"></i>
        </button>
    </div>
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

@include('notes.partials.editor-modals')
@endsection
