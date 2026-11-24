<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CargoTrace - Buat Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4 font-sans">
    <main class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-slate-800">Buat Akun Customer</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar untuk melacak shipment Anda</p>
        </div>

        @if ($errors->any())
            <p role="alert" class="text-rose-700 bg-rose-100 p-3 rounded-lg text-sm mb-4">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <label for="name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="100" autocomplete="name" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mb-4">

            <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mb-4">

            <label for="password" class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
            <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mb-4">

            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase mb-1">Ulangi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mb-6">

            <button type="submit" class="w-full bg-cyan-700 hover:bg-cyan-800 text-white font-bold py-2.5 rounded-lg text-sm">
                Buat Akun
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-bold text-cyan-700 hover:underline">Masuk</a>
        </p>
    </main>
</body>
</html>
