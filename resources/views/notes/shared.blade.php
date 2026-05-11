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
@include('notes.partials.editor-modals')
@endsection
