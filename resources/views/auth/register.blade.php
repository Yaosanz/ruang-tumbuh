<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar — Ruang Tumbuh</title>
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
                <a href="{{ route('login') }}" class="button secondary">Masuk</a>
            </nav>
        </header>
        <livewire:auth.register />
    </main>
    @livewireScripts
</body>
</html>

