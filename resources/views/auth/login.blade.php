<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen font-sans">
    <main class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">CargoTrace</h2>
            <p class="text-xs text-slate-500 mt-1">Global Shipment Visibility Platform</p>
        </div>

        @if (session('error'))
            <div role="alert" class="text-rose-700 bg-rose-100 p-3 rounded-lg text-sm mb-4 text-center font-bold">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="text-rose-700 bg-rose-100 p-3 rounded-lg text-sm mb-4 text-center font-bold">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email', 'admin@cargotrace.com') }}" autocomplete="email" required autofocus
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4">

            <label for="password" class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
            <input id="password" type="password" name="password" value="password123" autocomplete="current-password" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6">

            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg text-sm transition shadow-md">
                Sign In to Platform
            </button>
        </form>

        <p class="mt-4 text-center text-xs text-slate-600">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-bold text-cyan-700 hover:underline">Create New Account</a>
        </p>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
            <p class="font-semibold mb-1">Akun Demo (Password: password123):</p>
            <p class="font-mono">admin@cargotrace.com</p>
            <p class="font-mono">operator@cargotrace.com</p>
            <p class="font-mono">customer@cargotrace.com</p>
        </div>
    </main>
</body>
</html>
