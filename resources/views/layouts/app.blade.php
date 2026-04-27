<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Hocky Guest House</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

<div class="admin-wrapper">
    {{-- SIDEBAR --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Hocky Guest House" class="brand-logo">
            <div class="brand-text">
                <span class="brand-name">Hocky</span>
                <span class="brand-sub">Guest House</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group">
                <span class="nav-group-label">Menu Utama</span>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">⊞</span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="{{ route('kamar.index') }}" class="nav-item {{ request()->routeIs('kamar.*') ? 'active' : '' }}">
                    <span class="nav-icon">🛏</span>
                    <span class="nav-label">Kamar</span>
                </a>
                <a href="{{ route('pelanggan.index') }}" class="nav-item {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                    <span class="nav-icon">👤</span>
                    <span class="nav-label">Pelanggan</span>
                </a>
            </div>
            <div class="nav-group">
                <span class="nav-group-label">Transaksi</span>
                <a href="{{ route('pemesanan.create') }}" class="nav-item {{ request()->routeIs('pemesanan.*') ? 'active' : '' }}">
                    <span class="nav-icon">✚</span>
                    <span class="nav-label">Check-In</span>
                </a>
                <a href="{{ route('faktur.index') }}" class="nav-item {{ request()->routeIs('faktur.*') ? 'active' : '' }}">
                    <span class="nav-icon">🧾</span>
                    <span class="nav-label">Faktur</span>
                </a>
                <a href="{{ route('pendapatan.index') }}" class="nav-item {{ request()->routeIs('pendapatan.*') ? 'active' : '' }}">
                    <span class="nav-icon">💰</span>
                    <span class="nav-label">Pendapatan</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-detail">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">☰</button>
                <div class="page-breadcrumb">
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-date">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
        </header>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    {{ session('success') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <span class="alert-icon">✕</span>
                    {{ session('error') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script>
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => el.remove());
    }, 5000);
</script>
@stack('scripts')
</body>
</html>
