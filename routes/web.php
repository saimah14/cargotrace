<?php

use App\Http\Controllers\Api\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

Route::get('/', function (Request $request) {
    $apiToken = $request->session()->get('api_token');

    if (! $apiToken) {
        $apiToken = $request->user()->createToken('web-session')->plainTextToken;
        $request->session()->put('api_token', $apiToken);
    }

    return view('app', [
        'authenticatedUser' => $request->user(),
        'apiToken' => $apiToken,
    ]);
})->middleware('auth')->name('dashboard');

Route::get('/dashboard-lama', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard.lama');

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withInput($request->only('email'))->with('error', 'Email atau password salah!');
        }

        $request->session()->regenerate();
        $request->session()->forget('api_token');
        $apiToken = $request->user()->createToken('web-session')->plainTextToken;
        $request->session()->put('api_token', $apiToken);

        return redirect()->intended('/');
    })->middleware('throttle:10,1')->name('login.post');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', function (Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);
        unset($data['password_confirmation']);

        $user = new User($data);
        $user->role = User::ROLE_CUSTOMER;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();
        $apiToken = $user->createToken('web-session')->plainTextToken;
        $request->session()->put('api_token', $apiToken);

        return redirect('/');
    })->middleware('throttle:5,1')->name('register.post');
});

Route::post('/logout', function (Request $request) {
    $token = $request->session()->pull('api_token');
    if ($token) {
        PersonalAccessToken::findToken($token)?->delete();
    }

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');
