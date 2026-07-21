<section class="login-panel">
    <p class="eyebrow">AKUN BARU</p>
    <h1>Daftar</h1>
    <p>Buat akun untuk menyimpan riwayat hasil assessment kamu.</p>
    <form wire:submit="register">
        <label>
            Nama
            <input wire:model="name" type="text" placeholder="Nama lengkap">
            @error('name')<small class="error">{{ $message }}</small>@enderror
        </label>
        <label>
            Email
            <input wire:model="email" type="email" placeholder="nama@email.com">
            @error('email')<small class="error">{{ $message }}</small>@enderror
        </label>
        <label>
            Password
            <input wire:model="password" type="password" placeholder="Minimal 6 karakter">
            @error('password')<small class="error">{{ $message }}</small>@enderror
        </label>
        <label>
            Konfirmasi Password
            <input wire:model="password_confirmation" type="password" placeholder="Ketik ulang password">
        </label>
        <button class="button" type="submit">Daftar</button>
    </form>
    <p class="hint">Sudah punya akun? <a href="{{ route('login') }}" class="text-link">Masuk</a></p>
</section>

