<!DOCTYPE html>
<html lang="vi" data-theme="{{ auth()->user()->theme ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Note App - Ứng dụng quản lý ghi chú cá nhân">
    <title>@yield('title', 'Note App') - Quản lý ghi chú</title>

    {{-- Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#6c5ce7">
</head>
<body class="font-{{ auth()->user()->font_size ?? 'medium' }}">

@auth
{{-- Activation Banner --}}
@if(!auth()->user()->is_activated)
<div class="alert alert-warning alert-dismissible text-center mb-0 rounded-0" id="activationBanner">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Tài khoản chưa được xác minh. Vui lòng kiểm tra email để kích hoạt.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom shadow-sm px-3" id="mainNav">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle" title="Menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <a href="{{ route('notes.index') }}" class="navbar-brand fw-bold text-decoration-none">
            <i class="bi bi-journal-text text-primary me-1"></i>Note App
        </a>
    </div>

    {{-- Search --}}
    <div class="flex-grow-1 mx-3">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
            <input type="search" id="searchInput" class="form-control border-start-0" 
                   placeholder="Tìm kiếm ghi chú..." 
                   autocomplete="new-password" 
                   name="prevent_autofill_{{ time() }}">
            <button class="btn btn-outline-secondary border-start-0" id="searchClear" style="display:none" title="Xóa">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>

    {{-- Right --}}
    <div class="d-flex align-items-center gap-2">
        {{-- View Toggle (chỉ hiện ở trang ghi chú) --}}
        @if(request()->routeIs('notes.index') || request()->routeIs('notes.shared'))
        <div class="btn-group" role="group" title="Kiểu hiển thị" id="viewToggleGroup">
            <button type="button" class="btn btn-sm btn-outline-secondary active" id="btnViewGrid" onclick="setView('grid')" title="Dạng lưới">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnViewList" onclick="setView('list')" title="Dạng danh sách">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
        @endif
        <button class="btn btn-sm btn-outline-secondary" id="themeToggle" title="Đổi giao diện">
            <i class="bi bi-sun-fill theme-icon-light"></i>
            <i class="bi bi-moon-fill theme-icon-dark" style="display:none"></i>
        </button>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" id="userAvatarBtn" data-bs-toggle="dropdown">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="rounded-circle" width="28" height="28" style="object-fit:cover">
                @else
                    <span class="fw-bold">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li class="px-3 py-2">
                    <strong class="d-block">{{ auth()->user()->name }}</strong>
                    <small class="text-muted">{{ auth()->user()->email }}</small>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>Hồ sơ cá nhân</a></li>
                <li>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item fw-bold"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="d-flex" id="appWrapper">
    {{-- Sidebar --}}
    <div id="sidebar" class="border-end bg-body-secondary" style="min-width:220px;min-height:calc(100vh - 60px)">
        <div class="p-3">
            <a href="{{ route('notes.index') }}" class="d-flex align-items-center gap-2 text-decoration-none text-body py-2 px-2 rounded sidebar-link {{ request()->routeIs('notes.index') ? 'bg-primary text-white' : '' }}">
                <i class="bi bi-house"></i> Tất cả ghi chú
            </a>
            <a href="{{ route('notes.shared') }}" class="d-flex align-items-center gap-2 text-decoration-none text-body py-2 px-2 rounded sidebar-link {{ request()->routeIs('notes.shared') ? 'bg-primary text-white' : '' }}">
                <i class="bi bi-share"></i> Được chia sẻ
            </a>
            <a href="{{ route('labels.index') }}" class="d-flex align-items-center gap-2 text-decoration-none text-body py-2 px-2 rounded sidebar-link {{ request()->routeIs('labels.index') ? 'bg-primary text-white' : '' }}">
                <i class="bi bi-tags"></i> Nhãn của tôi
            </a>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="flex-grow-1 p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @yield('content')
    </main>
</div>
@endauth

@guest
<main class="container">
    @yield('content')
</main>
@endguest

{{-- JS Variables --}}
<script>
    const BASE_URL   = '{{ url('') }}';
    const USER_ID    = {{ auth()->id() ?? 0 }};
    const USER_THEME = '{{ auth()->user()->theme ?? 'light' }}';
    const USER_NOTE_COLOR = '{{ auth()->user()->note_color ?? '#ffffff' }}';
</script>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- Custom App JS --}}
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>

@if(isset($loadCollaboration) && $loadCollaboration)
<script src="{{ asset('js/collaboration.js') }}"></script>
@endif

{{-- Service Worker (PWA) --}}
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('{{ asset('js/sw.js') }}').catch(()=>{});
}
</script>
@stack('scripts')
</body>
</html>
