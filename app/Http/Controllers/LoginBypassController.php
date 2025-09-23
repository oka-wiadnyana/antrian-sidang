// app/Http/Controllers/Auth/LoginBypassController.php

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBypassController extends Controller
{
    public function login(Request $request, $email)
    {
        // Temukan pengguna berdasarkan email
        $user = User::where('email', $email)->first();

        // Cek apakah pengguna ada dan bisa mengakses Filament
        if (!$user || !$user->canAccessFilament()) {
            return redirect('/login');
        }

        // --- Perbaikan di sini ---
        // Otentikasi pengguna menggunakan guard 'filament'
        Auth::guard('filament')->login($user);
        dd(Auth::guard('filament')->check());
        // Arahkan ke dashboard Filament
        return redirect()->route('filament.admin.pages.dashboard');
    }
}
