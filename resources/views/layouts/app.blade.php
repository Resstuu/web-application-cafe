<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Cafe PKL' }}</title>
    <style>
        :root { --ink:#1f2933; --muted:#697386; --line:#e4e7ec; --bg:#f6f7f9; --panel:#fff; --brand:#0f766e; --danger:#b42318; --warn:#b54708; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Arial, Helvetica, sans-serif; color:var(--ink); background:var(--bg); }
        a { color:inherit; text-decoration:none; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 22px; background:#fff; border-bottom:1px solid var(--line); position:sticky; top:0; z-index:10; }
        .brand { font-weight:800; letter-spacing:.2px; }
        .nav { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .nav a, .nav button, .btn { border:1px solid var(--line); background:#fff; padding:9px 12px; border-radius:7px; cursor:pointer; font-size:14px; }
        .btn.primary, button.primary { background:var(--brand); border-color:var(--brand); color:#fff; }
        .btn.danger, button.danger { background:var(--danger); border-color:var(--danger); color:#fff; }
        .btn.warn, button.warn { background:var(--warn); border-color:var(--warn); color:#fff; }
        .wrap { width:min(1180px, calc(100% - 28px)); margin:24px auto; }
        .grid { display:grid; gap:16px; }
        .grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .panel, .card { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:18px; }
        .card { display:flex; flex-direction:column; gap:10px; }
        h1, h2, h3 { margin:0 0 12px; }
        p { color:var(--muted); line-height:1.45; }
        label { display:block; font-weight:700; margin:10px 0 6px; }
        input, select, textarea { width:100%; padding:10px 11px; border:1px solid var(--line); border-radius:7px; background:#fff; font:inherit; }
        table { width:100%; border-collapse:collapse; background:#fff; border:1px solid var(--line); border-radius:8px; overflow:hidden; }
        th, td { padding:12px; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; }
        th { background:#f0f2f5; font-size:13px; text-transform:uppercase; color:#52606d; }
        .badge { display:inline-block; padding:4px 8px; border-radius:999px; background:#eef4ff; color:#1849a9; font-size:12px; font-weight:700; }
        .muted { color:var(--muted); }
        .price { font-weight:800; }
        .row { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .spacer { flex:1; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:14px; background:#ecfdf3; color:#027a48; border:1px solid #abefc6; }
        .error { padding:12px 14px; border-radius:8px; margin-bottom:14px; background:#fef3f2; color:#b42318; border:1px solid #fecdca; }
        .qty { width:86px; }
        .summary { position:sticky; top:78px; }
        @media (max-width: 820px) { .grid.two, .grid.three { grid-template-columns:1fr; } .topbar { align-items:flex-start; flex-direction:column; } .summary { position:static; } }
    </style>
    @stack('head')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('public.order') }}">Cafe PKL</a>
        <nav class="nav">
            <a href="{{ route('public.order') }}">Pesan</a>
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                @if(auth()->user()->hasRole('super_admin'))
                    <a href="{{ route('admin.menus.index') }}">Menu</a>
                    <a href="{{ route('admin.users.index') }}">User</a>
                @endif
                @if(auth()->user()->hasRole('kasir'))
                    <a href="{{ route('cashier.orders.index') }}">Kasir</a>
                    <a href="{{ route('cashier.orders.create') }}">Order Baru</a>
                @endif
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button>Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login Staf</a>
            @endauth
        </nav>
    </header>

    <main class="wrap">
        @if(session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
