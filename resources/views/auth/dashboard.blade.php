<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Ruang Tumbuh</title>
    @fonts
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body>
<main class="admin-shell">
        <header class="topbar">
            <a class="brand" href="/">Ruang Tumbuh</a>
            <nav class="header-actions">
                <a href="{{ route('quizzes.index') }}" class="text-link">Beranda</a>
                <a href="{{ route('dashboard') }}" class="text-link">Dashboard</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="button secondary" style="margin-left:0.5rem">CMS</a>
                @endif
                <a href="{{ route('logout') }}" class="text-link danger"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
            </nav>
        </header>
        <livewire:dashboard />
    </main>
    @livewireScripts
</body>
</html>
