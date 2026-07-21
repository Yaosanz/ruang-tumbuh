<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Ruang Tumbuh</title>
    @fonts
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <main class="login-wrap">
        <header class="topbar">
            <a class="brand" href="/">Ruang Tumbuh</a>
            <nav class="header-actions">
                <a href="{{ route('quizzes.index') }}" class="text-link">Beranda</a>
                <a href="{{ route('register') }}" class="button">Daftar</a>
            </nav>
        </header>
        <livewire:auth.login />
    </main>
    @livewireScripts
</body>
</html>

