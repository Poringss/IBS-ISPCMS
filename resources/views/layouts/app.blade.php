<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>InsightBlitz</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  {{-- External --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <link href="{{ asset('css.css') }}" rel="stylesheet">
  {{-- Google Font --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  @stack('styles')

  <style>
    :root {
      --green-500: #22c55e;
      --green-600: #16a34a;
      --green-400: #4ade80;
      --green-900: #14532d;
      --dark-950: #030712;
      --dark-900: #0a0a0f;
      --dark-800: #111118;
      --dark-700: #1a1a24;
      --dark-600: #252530;
      --border-dim: rgba(255,255,255,0.06);
      --border-subtle: rgba(255,255,255,0.1);
      --ui-scale: 1.06;
    }
    html { font-size: calc(16px * var(--ui-scale)); }

    body {
      background: var(--dark-950);
      margin: 0;
      font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color: #e2e8f0;
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* ── Subtle Animated Background (no THREE.js — lightweight) ── */
    .app-bg {
      position: fixed;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(ellipse 80% 60% at 20% 10%, rgba(34,197,94,0.06) 0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 80% 90%, rgba(34,197,94,0.04) 0%, transparent 60%),
        var(--dark-950);
      pointer-events: none;
    }
    .app-bg::after {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.015'/%3E%3C/svg%3E");
      pointer-events: none;
    }

    .content-overlay {
      position: relative;
      z-index: 10;
    }

    /* ── Navbar ── */
    .app-navbar {
      background: rgba(3,7,18,0.75);
      backdrop-filter: blur(20px) saturate(1.4);
      border-bottom: 1px solid var(--border-dim);
      padding: 0.6rem 0;
      z-index: 1100;
      transition: background 0.3s;
    }
    .app-navbar .navbar-brand {
      color: var(--green-500) !important;
      font-size: 1.35rem;
      font-weight: 800;
      letter-spacing: -0.5px;
    }
    .app-navbar .nav-link {
      color: rgba(255,255,255,0.6) !important;
      font-weight: 500;
      font-size: 0.88rem;
      padding: 0.5rem 0.85rem !important;
      border-radius: 8px;
      transition: all 0.25s ease;
      position: relative;
    }
    .app-navbar .nav-link:hover {
      color: #fff !important;
      background: rgba(255,255,255,0.05);
    }
    .app-navbar .nav-link.active {
      color: var(--green-400) !important;
      background: rgba(34,197,94,0.08);
    }
    .app-navbar .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 2px;
      background: var(--green-500);
      border-radius: 2px;
    }

    /* ── Avatar ── */
    .avatar-circle {
      width: 36px;
      height: 36px;
      text-decoration: none;
      background: linear-gradient(135deg, var(--green-500), var(--green-600));
      color: #000;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      font-size: 0.82rem;
      transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 0 0 0 2px rgba(34,197,94,0.2);
    }
    .avatar-circle:hover {
      transform: scale(1.08);
      color: #000;
      box-shadow: 0 0 12px rgba(34,197,94,0.4);
    }

    /* ── Glass Form Controls ── */
    .form-control, .form-select {
      background: var(--dark-700) !important;
      color: #fff !important;
      border: 1px solid var(--border-subtle) !important;
      border-radius: 10px;
      transition: all 0.3s ease;
      font-size: 0.92rem;
    }
    .form-control:focus, .form-select:focus {
      background: var(--dark-600) !important;
      color: #fff !important;
      border-color: var(--green-500) !important;
      box-shadow: 0 0 0 3px rgba(34,197,94,0.15), 0 0 20px rgba(34,197,94,0.08) !important;
    }
    .form-control::placeholder { color: rgba(255,255,255,0.35) !important; }

    /* ── Buttons ── */
    .btn {
      border-radius: 10px;
      padding: 0.55rem 1.3rem;
      font-weight: 600;
      font-size: 0.88rem;
      transition: all 0.25s ease;
      letter-spacing: 0.01em;
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--green-500), var(--green-600)) !important;
      border: none !important;
      color: #000 !important;
      box-shadow: 0 2px 10px rgba(34,197,94,0.25);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, var(--green-400), var(--green-500)) !important;
      box-shadow: 0 4px 20px rgba(34,197,94,0.35);
      transform: translateY(-1px);
    }
    .btn-outline-light {
      border-color: var(--border-subtle) !important;
      color: rgba(255,255,255,0.8) !important;
    }
    .btn-outline-light:hover {
      background: rgba(255,255,255,0.06) !important;
      border-color: rgba(255,255,255,0.2) !important;
      color: #fff !important;
    }
    .btn-secondary {
      background: var(--dark-600) !important;
      border: 1px solid var(--border-subtle) !important;
      color: rgba(255,255,255,0.8) !important;
    }
    .btn-secondary:hover {
      background: var(--dark-700) !important;
    }
    .btn-warning {
      background: #f59e0b !important;
      border: none !important;
      color: #000 !important;
    }
    .btn-danger {
      background: #ef4444 !important;
      border: none !important;
    }

    /* ── Glass Cards ── */
    .bubble, .glass-card {
      background: rgba(10,10,18,0.65);
      backdrop-filter: blur(16px) saturate(1.2);
      border: 1px solid var(--border-dim);
      border-radius: 16px;
      padding: 1.5rem;
      color: #e2e8f0;
      transition: border-color 0.3s;
    }
    .bubble:hover, .glass-card:hover {
      border-color: rgba(34,197,94,0.15);
    }

    /* ── Glass Tables ── */
    .custom-table {
      background: rgba(10,10,18,0.5);
      backdrop-filter: blur(10px);
      border: 1px solid var(--border-dim);
      border-radius: 14px;
      overflow: hidden;
    }
    .custom-table thead {
      background: rgba(34,197,94,0.06) !important;
    }
    .custom-table thead th {
      background: transparent !important;
      color: var(--green-400) !important;
      border-color: var(--border-dim) !important;
      font-weight: 600;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 0.85rem 1rem !important;
    }
    .custom-table tbody tr {
      background: transparent !important;
      transition: background 0.2s;
    }
    .custom-table tbody tr:hover {
      background: rgba(34,197,94,0.04) !important;
    }
    .custom-table td {
      background: transparent !important;
      color: #e2e8f0 !important;
      border-color: var(--border-dim) !important;
      padding: 0.75rem 1rem !important;
      font-size: 0.9rem;
    }

    /* ── Metric Boxes ── */
    .metric-box {
      background: rgba(10,10,18,0.6);
      backdrop-filter: blur(10px);
      border: 1px solid var(--border-dim);
      border-radius: 14px;
      margin: 4px;
      padding: 1.2rem 1.4rem;
      transition: all 0.3s ease;
    }
    .metric-box:hover {
      border-color: rgba(34,197,94,0.2);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }
    .metric-box h6 { font-size: 0.82rem; color: rgba(255,255,255,0.5); font-weight: 500; letter-spacing: 0.03em; }
    .metric-box .fs-5 { font-weight: 700; color: #fff; }

    /* ── Page Loader ── */
    .page-loader {
      position: fixed;
      inset: 0;
      background: var(--dark-950);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity 0.5s ease;
    }
    .page-loader.hidden {
      opacity: 0;
      pointer-events: none;
    }

    /* ── SweetAlert Dark Theme ── */
    .swal2-popup {
      background: rgba(10,10,18,0.95) !important;
      backdrop-filter: blur(16px);
      border: 1px solid rgba(34,197,94,0.15) !important;
      color: #e2e8f0 !important;
      border-radius: 16px !important;
    }

    /* ── Dropdown menu ── */
    .dropdown-menu {
      background: rgba(10,10,18,0.95) !important;
      backdrop-filter: blur(16px);
      border: 1px solid var(--border-dim) !important;
      border-radius: 12px !important;
      padding: 0.5rem !important;
    }
    .dropdown-item {
      color: rgba(255,255,255,0.8) !important;
      border-radius: 8px;
      padding: 0.5rem 0.8rem;
      transition: background 0.2s;
    }
    .dropdown-item:hover {
      background: rgba(34,197,94,0.1) !important;
      color: #fff !important;
    }

    /* ── Badges ── */
    .glass-badge {
      backdrop-filter: blur(6px);
      padding: 5px 14px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.78rem;
      display: inline-block;
      min-width: 80px;
      text-align: center;
      letter-spacing: 0.02em;
    }

    /* ── Progress bars ── */
    .progress {
      background: var(--dark-700);
      border-radius: 8px;
      height: 10px;
    }
    .progress-bar {
      background: linear-gradient(90deg, var(--green-500), var(--green-400));
      border-radius: 8px;
      font-size: 0.65rem;
    }

    /* ── Alerts ── */
    .alert-success {
      background: rgba(34,197,94,0.12) !important;
      border: 1px solid rgba(34,197,94,0.25) !important;
      color: var(--green-400) !important;
      border-radius: 12px;
    }
    .alert-danger {
      background: rgba(239,68,68,0.12) !important;
      border: 1px solid rgba(239,68,68,0.25) !important;
      color: #fca5a5 !important;
      border-radius: 12px;
    }
    .alert-info {
      background: rgba(56,189,248,0.12) !important;
      border: 1px solid rgba(56,189,248,0.25) !important;
      color: #7dd3fc !important;
      border-radius: 12px;
    }

    /* ── Page headings ── */
    h2.text-white, h2 {
      color: #fff !important;
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.1);
      border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

    /* ── Form labels ── */
    .form-label, label {
      color: rgba(255,255,255,0.7) !important;
      font-weight: 500;
      font-size: 0.85rem;
      margin-bottom: 0.4rem;
    }

    /* ── Links ── */
    a.link-light { transition: color 0.2s; }
    a.link-light:hover { color: var(--green-400) !important; }

    /* ── Pagination ── */
    .pagination .page-link {
      background: var(--dark-700);
      border-color: var(--border-dim);
      color: rgba(255,255,255,0.7);
      border-radius: 8px;
      margin: 0 2px;
    }
    .pagination .page-item.active .page-link {
      background: var(--green-500);
      border-color: var(--green-500);
      color: #000;
    }
  </style>
</head>

<body>
<div class="page-loader" id="pageLoader"></div>

{{-- SUBTLE BACKGROUND (no THREE.js) --}}
<div class="app-bg"></div>

<div class="content-overlay">

{{-- NAVBAR --}}
@auth
@php
    $user = auth()->user();
    $role = strtolower(trim($user->role ?? 'client'));
    $isAdmin = $role === 'admin';
    $isStaff = $role === 'staff';

    $initials = collect(preg_split('/\s+/', trim($user->name ?? '')))
        ->filter()
        ->map(fn($p) => mb_substr($p, 0, 1))
        ->join('');

    $openReq = 0;
    $notifUnread = 0;
    if ($isAdmin) {
        $openReq = \App\Models\ClientRequest::where('status', 'open')->count();
        $notifUnread = $user->unreadNotifications()->count();
    }
@endphp

<nav class="navbar navbar-expand-lg app-navbar">
  <div class="container d-flex justify-content-between">

    <div class="brand-logo d-flex align-items-center">
      <img src="{{ asset('pictures/ibslogo.jpg') }}" alt="Logo" style="height: 32px; margin-right: 10px; border-radius: 6px;">
      <a class="navbar-brand fw-bold m-0" href="{{ route('home') }}">InsightBlitz</a>
    </div>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <i class="fa-solid fa-bars text-white"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="navbar-nav mx-auto gap-1">
        @if($isAdmin)
          <a class="nav-link {{ request()->routeIs('leads.index') ? 'active' : '' }}" href="{{ route('leads.index') }}">Leads</a>
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
          <a class="nav-link {{ request()->routeIs('clients.index') ? 'active' : '' }}" href="{{ route('clients.index') }}">Clients</a>
          <a class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}" href="{{ route('tasks.index') }}">Tasks</a>
          <a class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}" href="{{ route('projects.index') }}">Projects</a>
          <a class="nav-link {{ request()->routeIs('staff.index') ? 'active' : '' }}" href="{{ route('staff.index') }}">Staff</a>
          <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.requests.index') ? 'active' : '' }}" href="{{ route('admin.requests.index') }}">
            Requests
            @if($openReq > 0)
              <span class="badge bg-danger ms-2" style="font-size: 0.7rem;">{{ $openReq }}</span>
            @endif
          </a>
          <a class="nav-link position-relative d-flex align-items-center {{ request()->routeIs('messages.*') ? 'active' : '' }}"
             href="{{ route('messages.index') }}" title="Messages">
            <i class="fa-solid fa-comments me-1"></i> Messages
            <span class="badge bg-success ms-2 d-none" id="msgBadge" style="font-size: 0.65rem;"></span>
          </a>
          <a class="nav-link position-relative d-flex align-items-center" href="{{ route('admin.notifications') }}" title="Notifications">
            <i class="fa-solid fa-bell fs-5"></i>
            @if($notifUnread > 0)
              <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem;">{{ $notifUnread }}</span>
            @endif
          </a>
        @elseif($isStaff)
          <a class="nav-link {{ request()->routeIs('staff.portal.dashboard') ? 'active' : '' }}" href="{{ route('staff.portal.dashboard') }}">Staff Home</a>
          <a class="nav-link position-relative d-flex align-items-center {{ request()->routeIs('messages.*') ? 'active' : '' }}"
             href="{{ route('messages.index') }}" title="Messages">
            <i class="fa-solid fa-comments me-1"></i> Messages
            <span class="badge bg-success ms-2 d-none" id="msgBadge" style="font-size: 0.65rem;"></span>
          </a>
        @else
          <a class="nav-link {{ request()->routeIs('client.home') ? 'active' : '' }}" href="{{ route('client.home') }}">Client Home</a>
          <a class="nav-link position-relative d-flex align-items-center {{ request()->routeIs('messages.*') ? 'active' : '' }}"
             href="{{ route('messages.index') }}" title="Messages">
            <i class="fa-solid fa-comments me-1"></i> Messages
            <span class="badge bg-success ms-2 d-none" id="msgBadge" style="font-size: 0.65rem;"></span>
          </a>
        @endif
      </div>

      <div class="d-flex align-items-center gap-3 ms-auto mt-3 mt-lg-0">
        <div class="dropdown">
          <a href="#" class="avatar-circle" data-bs-toggle="dropdown" title="{{ $user->name }}">
            {{ $initials ?: 'U' }}
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="fa-solid fa-gear me-2" style="color:var(--green-500)"></i>Settings</a></li>
            <li><hr class="dropdown-divider" style="border-color: var(--border-dim)"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}" class="px-3">
                @csrf
                <button type="submit" class="btn btn-link p-0 text-danger text-decoration-none d-flex align-items-center" style="font-size:0.88rem;">
                  <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</nav>
@endauth

{{-- MAIN CONTENT --}}
<main class="py-4">
  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success', title: 'Success', text: '{{ session('success') }}',
        confirmButtonColor: '#22c55e', toast: true, position: 'top-end',
        timer: 3000, showConfirmButton: false
      });
    </script>
  @endif
  @if ($errors->any())
    <script>
      Swal.fire({
        icon: 'error', title: 'Error', text: '{{ $errors->first() }}',
        confirmButtonColor: '#22c55e'
      });
    </script>
  @endif
  @yield('content')
</main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Page loader --}}
<script>
  window.addEventListener('load', () => {
    const loader = document.getElementById('pageLoader');
    if (loader) { loader.classList.add('hidden'); setTimeout(() => loader.remove(), 600); }
  });
</script>

{{-- Unread messages polling (every 15 seconds) --}}
@auth
<script>
  (function pollUnread() {
    function update() {
      fetch('{{ route("messages.unread") }}')
        .then(r => r.json())
        .then(d => {
          const badge = document.getElementById('msgBadge');
          if (!badge) return;
          if (d.unread > 0) {
            badge.textContent = d.unread > 99 ? '99+' : d.unread;
            badge.classList.remove('d-none');
          } else {
            badge.classList.add('d-none');
          }
        })
        .catch(() => {});
    }
    update();
    setInterval(update, 15000);
  })();
</script>
@endauth

@stack('scripts')
</body>
</html>
