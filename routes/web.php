<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\DashboardController;

// 1. Route Dashboard Utama
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// 2. Halaman Login (Tampilan Form Login)
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/');
    }

    $errorHtml = session('error') ? '<div style="color:#e11d48; background:#ffe4e6; padding:10px; border-radius:6px; font-size:12px; margin-bottom:15px; text-align:center; font-weight:bold;">'.session('error').'</div>' : '';
    $csrf = csrf_token();

    return response('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>CargoTrace - Login</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-100 flex items-center justify-center h-screen font-sans">
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">CargoTrace</h2>
                <p class="text-xs text-slate-500 mt-1">Global Shipment Visibility Platform</p>
            </div>
            
            '.$errorHtml.'

            <form method="POST" action="'.url('/login').'">
                <input type="hidden" name="_token" value="'.$csrf.'">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" value="admin@cargotrace.com" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
                    <input type="password" name="password" value="password123" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg text-sm transition shadow-md">
                    Sign In to Platform
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
                <p class="font-semibold mb-1">Akun Demo (Password: password123):</p>
                <p class="font-mono">admin@cargotrace.com</p>
                <p class="font-mono">operator@cargotrace.com</p>
                <p class="font-mono">customer@cargotrace.com</p>
            </div>
        </div>
    </body>
    </html>
    ');
})->name('login');

// 3. Proses Login POST
Route::post('/login', function (Request $request) {
    $credentials =$request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {$request->session()->regenerate();
        return redirect('/');
    }

    return back()->with('error', 'Email atau password salah!');
})->name('login.post');

// 4. Logout (Mengarahkan balik ke /login)
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();$request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();$request->session()->regenerateToken();
    return redirect()->route('login');
});