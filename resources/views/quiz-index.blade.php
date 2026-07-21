<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ruang Tumbuh</title>
    @fonts
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <main class="site-shell">
        <header class="topbar">
            <a class="brand" href="/">Ruang Tumbuh</a>
            <nav class="header-actions">
                @auth
                    <span class="user-badge">{{ auth()->user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="text-link">Dashboard</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="button secondary" style="margin-left:0.5rem">CMS</a>
                    @endif
                    <a href="{{ route('logout') }}" class="text-link"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
                @else
                    <a href="{{ route('login') }}" class="button secondary">Masuk</a>
                    <a href="{{ route('register') }}" class="button">Daftar</a>
                @endauth
            </nav>
        </header>

        <section class="hero">
            <div class="hero-text">
                <p class="eyebrow">ASSESSMENT CENTER</p>
                <h1>Kenali langkah kecil yang berarti.</h1>
                <p>Pilih assessment yang tersedia dan jawab dengan jujur. Hasil ini adalah refleksi awal, bukan diagnosis klinis.</p>
                <div class="hero-actions">
                    @guest
                        <a href="{{ route('register') }}" class="button">Buat Akun Gratis</a>
                        <a href="{{ route('login') }}" class="button secondary">Masuk</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="button">Dashboard Saya</a>
                        <a href="#quiz-list" class="button secondary">Jelajahi Quiz</a>
                    @endguest
                </div>
        </section>

        <section id="quiz-list">
            <livewire:quiz-list />
        </section>

        @guest
        <section class="cta-banner">
            <div class="cta-banner-content">
                <h2>Simpan riwayat hasil kamu</h2>
                <p>Daftar gratis untuk menyimpan semua hasil assessment dan lihat perkembangan kamu dari waktu ke waktu.</p>
                <div class="cta-actions">
                    <a href="{{ route('register') }}" class="button">Buat Akun Gratis</a>
                    <a href="{{ route('login') }}" class="button secondary">Sudah punya akun? Masuk</a>
                </div>
        </section>
        @else
        <section class="cta-banner user-cta">
            <div class="cta-banner-content">
                <h2>Lanjutkan perjalananmu</h2>
                <p>Cek riwayat hasil assessment kamu di dashboard atau kerjakan quiz baru.</p>
                <div class="cta-actions">
                    <a href="{{ route('dashboard') }}" class="button">Dashboard Saya</a>
                    <a href="{{ route('quizzes.index') }}" class="button secondary">Muat Ulang Quiz</a>
                </div>
        </section>
        @endguest

        <footer class="site-footer">
            <nav class="footer-nav">
                <a href="{{ route('quizzes.index') }}">Beranda</a>
                @guest
                    <a href="{{ route('login') }}">Masuk</a>
                    <a href="{{ route('register') }}">Daftar</a>
                @else
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                @endguest
            </nav>
            <p>&copy; {{ date('Y') }} Ruang Tumbuh. Refleksi awal, bukan diagnosis klinis.</p>
        </footer>
    </main>
    @livewireScripts
</body>
</html>
