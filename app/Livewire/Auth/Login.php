<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], true)) {
            $this->addError('email', 'Email atau password tidak tepat.');
            return;
        }

        session()->regenerate();

        $redirectRoute = auth()->user()?->role === 'admin' ? 'admin.dashboard' : 'dashboard';
        $this->redirectRoute($redirectRoute);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
