@extends('layouts.app')
@section('title', 'Quản lý nhãn')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Quản lý nhãn</div>
            <div class="card-body">
                {{-- Thêm nhãn mới --}}
                <div class="input-group mb-4">
                    <input type="text" id="newLabelName" class="form-control" placeholder="Tên nhãn mới...">
                    <button class="btn btn-primary" id="btnAddLabel"><i class="bi bi-plus-lg me-1"></i>Thêm</button>
                </div>

                {{-- Danh sách nhãn --}}
                <div id="labelList">
                    @forelse($labels as $label)
                    <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded label-item" data-id="{{ $label->id }}">
                        <i class="bi bi-tag text-primary"></i>
                        <span class="flex-grow-1 label-name fw-semibold">{{ $label->name }}</span>
                        <input type="text" class="form-control form-control-sm label-edit-input flex-grow-1" value="{{ $label->name }}" style="display:none">
                        <span class="badge bg-secondary">{{ $label->notes_count }} ghi chú</span>
                        <button class="btn btn-sm btn-outline-secondary btn-edit-label" title="Sửa"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-primary btn-save-label" style="display:none" title="Lưu"><i class="bi bi-check"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-delete-label" title="Xóa"><i class="bi bi-trash"></i></button>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4" id="emptyLabels">
                        <i class="bi bi-tags fs-2"></i>
                        <p class="mt-2">Chưa có nhãn nào. Tạo nhãn đầu tiên!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
