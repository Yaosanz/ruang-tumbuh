<?php
use function Livewire\Volt\{state, rules, actions};
?>

<section class="login-panel">
    <p class="eyebrow">SELAMAT DATANG</p>
    <h1>Masuk</h1>
    <p>Masuk untuk menyimpan riwayat hasil assessment kamu.</p>
    <form wire:submit="login">
        <label>
            Email
            <input wire:model="email" type="email" placeholder="nama@email.com">
        </label>
        <label>
            Password
            <input wire:model="password" type="password" placeholder="Password">
        </label>
        @error('email')<p class="error">{{ $message }}</p>@enderror
        <button class="button" type="submit">Masuk</button>
    </form>
    <p class="hint">Belum punya akun? <a href="{{ route('register') }}" class="text-link">Daftar</a></p>
</section>

