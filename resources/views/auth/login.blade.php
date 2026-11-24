<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#071827">
    <title>Masuk - CargoTrace</title>
    <style>
        :root{font-family:Inter,"Segoe UI",Roboto,Arial,sans-serif;color:#10243a;background:#edf3f7;font-synthesis:none;text-rendering:optimizeLegibility}
        *{box-sizing:border-box}
        body{min-height:100vh;margin:0;padding:32px;display:grid;place-items:center;background:radial-gradient(ellipse at 12% 12%,rgba(34,211,238,.15),transparent 30%),radial-gradient(ellipse at 90% 90%,rgba(14,165,233,.12),transparent 34%),#edf3f7}
        button,input{font:inherit}
        .login-shell{width:min(1060px,100%);min-height:650px;display:grid;grid-template-columns:1.04fr .96fr;overflow:hidden;border:1px solid rgba(255,255,255,.7);border-radius:26px;background:#fff;box-shadow:0 30px 90px rgba(15,38,61,.16)}
        .brand-panel{position:relative;display:flex;flex-direction:column;justify-content:space-between;overflow:hidden;padding:42px;color:#fff;background:radial-gradient(ellipse at 12% 100%,rgba(6,182,212,.22),transparent 40%),linear-gradient(145deg,#071827 0%,#102d44 58%,#0b4054 100%)}
        .brand-panel:before,.brand-panel:after{position:absolute;content:"";border:1px solid rgba(148,221,238,.12);border-radius:50%;pointer-events:none}
        .brand-panel:before{width:580px;height:580px;right:-310px;top:80px}
        .brand-panel:after{width:440px;height:440px;right:-240px;top:150px}
        .brand-top,.brand-copy,.visual-card,.brand-foot{position:relative;z-index:1}
        .brand-top{display:flex;align-items:center;gap:12px}
        .brand-mark{width:42px;height:42px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.24);border-radius:13px;background:linear-gradient(145deg,#22d3ee,#0891b2);box-shadow:0 8px 22px rgba(8,145,178,.3);font-size:15px;font-weight:900;letter-spacing:-.08em}
        .brand-name{font-size:16px;font-weight:800;letter-spacing:-.03em}
        .brand-tag{margin-top:3px;color:#9eb8c9;font-size:10px;font-weight:700;letter-spacing:.13em;text-transform:uppercase}
        .brand-copy{margin:74px 0 28px}
        .eyebrow{display:inline-flex;align-items:center;gap:8px;color:#8ce8f5;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .eyebrow:before{width:18px;height:1px;background:#22d3ee;content:""}
        .brand-copy h1{max-width:440px;margin:17px 0 13px;font-size:clamp(34px,4.2vw,49px);line-height:1.08;letter-spacing:-.055em}
        .brand-copy p{max-width:390px;margin:0;color:#b2c5d2;font-size:14px;line-height:1.75}
        .visual-card{margin:auto 0 28px;padding:20px;border:1px solid rgba(186,226,240,.18);border-radius:17px;background:linear-gradient(135deg,rgba(255,255,255,.105),rgba(255,255,255,.035));backdrop-filter:blur(8px)}
        .visual-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .visual-label{color:#c3d6e0;font-size:11px;font-weight:700}
        .live-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid rgba(74,222,128,.25);border-radius:99px;background:rgba(22,163,74,.12);color:#a7f3d0;font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .live-pill:before{width:6px;height:6px;border-radius:50%;background:#4ade80;box-shadow:0 0 10px #4ade80;content:""}
        .route-map{position:relative;height:118px;margin:14px 0 9px;overflow:hidden;border:1px solid rgba(186,226,240,.1);border-radius:12px;background:radial-gradient(ellipse at 22% 30%,rgba(34,211,238,.12),transparent 25%),radial-gradient(ellipse at 80% 75%,rgba(34,211,238,.1),transparent 28%),rgba(3,18,31,.24)}
        .route-map svg{position:absolute;inset:0;width:100%;height:100%}
        .route-caption{display:flex;justify-content:space-between;gap:10px;color:#a8bdca;font-size:10px}
        .route-caption b{display:block;margin-bottom:3px;color:#f1f8fb;font-size:11px}
        .brand-foot{color:#86a0b1;font-size:10px}
        .form-panel{display:flex;align-items:center;justify-content:center;padding:54px clamp(28px,6vw,68px);background:#fff}
        .form-inner{width:100%;max-width:370px}
        .mobile-brand{display:none}
        .form-heading{margin-bottom:28px}
        .form-heading .kicker{margin:0 0 8px;color:#0e8ca6;font-size:10px;font-weight:800;letter-spacing:.15em;text-transform:uppercase}
        .form-heading h2{margin:0;color:#10243a;font-size:30px;letter-spacing:-.045em}
        .form-heading p{margin:9px 0 0;color:#708297;font-size:13px;line-height:1.6}
        .alert{margin-bottom:18px;padding:12px 14px;border:1px solid #fecdd3;border-radius:11px;background:#fff1f2;color:#9f1239;font-size:12px;line-height:1.5}
        .field{margin-bottom:18px}
        .field label{display:block;margin-bottom:8px;color:#334b60;font-size:11px;font-weight:800;letter-spacing:.035em}
        .input-wrap{position:relative}
        .input-wrap svg{position:absolute;top:50%;left:14px;width:17px;height:17px;transform:translateY(-50%);color:#91a2b3}
        .input-wrap input{width:100%;height:49px;padding:0 14px 0 43px;border:1px solid #dce5ec;border-radius:11px;outline:0;background:#fbfdfe;color:#10243a;font-size:13px;transition:border-color .18s,box-shadow .18s,background .18s}
        .input-wrap input::placeholder{color:#a3b0bd}
        .input-wrap input:focus{border-color:#0891b2;background:#fff;box-shadow:0 0 0 4px rgba(8,145,178,.11)}
        .password-wrap input{padding-right:54px}
        .toggle-password{position:absolute;top:50%;right:10px;display:grid;width:34px;height:34px;place-items:center;transform:translateY(-50%);border:0;border-radius:8px;background:transparent;color:#73869a;cursor:pointer}
        .toggle-password:hover{background:#edf6f8;color:#087e96}
        .toggle-password svg{position:static;width:17px;height:17px;transform:none}
        .form-options{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:2px 0 22px;color:#6b7f91;font-size:11px}
        .remember{display:inline-flex;align-items:center;gap:7px;cursor:pointer}
        .remember input{width:14px;height:14px;margin:0;accent-color:#078ba4}
        .submit-button{width:100%;height:49px;display:flex;align-items:center;justify-content:center;gap:10px;border:0;border-radius:11px;background:linear-gradient(100deg,#087e96,#079bb0);box-shadow:0 9px 18px rgba(8,145,178,.2);color:#fff;font-size:13px;font-weight:800;cursor:pointer;transition:transform .18s,box-shadow .18s,filter .18s}
        .submit-button:hover{transform:translateY(-1px);filter:brightness(1.04);box-shadow:0 12px 22px rgba(8,145,178,.27)}
        .submit-button:focus-visible,.toggle-password:focus-visible,a:focus-visible{outline:3px solid rgba(8,145,178,.4);outline-offset:3px}
        .submit-button svg{width:16px;height:16px}
        .account-link{margin:21px 0 0;text-align:center;color:#718396;font-size:12px}
        .account-link a{color:#087e96;font-weight:800;text-decoration:none}
        .account-link a:hover{text-decoration:underline}
        .demo-box{margin-top:28px;padding:14px 15px;border:1px solid #e6edf2;border-radius:12px;background:#f8fbfc}
        .demo-title{display:flex;align-items:center;gap:7px;margin-bottom:10px;color:#4f6578;font-size:10px;font-weight:800}
        .demo-title svg{width:14px;height:14px;color:#0a91a8}
        .demo-list{display:grid;grid-template-columns:1fr 1fr;gap:7px 12px;color:#73869a;font-size:10px}
        .demo-list span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .demo-list b{color:#334b60}
        @media(max-width:760px){body{padding:18px}.login-shell{max-width:510px;min-height:0;grid-template-columns:1fr;border-radius:22px}.brand-panel{display:none}.form-panel{padding:38px 28px}.mobile-brand{display:flex;align-items:center;gap:10px;margin-bottom:34px}.mobile-brand .brand-mark{width:38px;height:38px}.form-heading h2{font-size:27px}}
        @media(max-width:420px){body{padding:12px}.form-panel{padding:30px 22px}.demo-list{grid-template-columns:1fr}.form-options{font-size:10px}}
        @media(prefers-reduced-motion:reduce){*,*:before,*:after{scroll-behavior:auto!important;transition:none!important}}
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand-panel" aria-label="Tentang CargoTrace">
            <div class="brand-top">
                <div class="brand-mark" aria-hidden="true">CT</div>
                <div>
                    <div class="brand-name">CargoTrace</div>
                    <div class="brand-tag">Global shipment visibility</div>
                </div>
            </div>

            <div class="brand-copy">
                <span class="eyebrow">Logistik tanpa batas</span>
                <h1>Semua kiriman, dalam satu kendali.</h1>
                <p>Pantau perjalanan kargo laut dan udara, lihat pembaruan status, dan tetap terhubung di setiap tahap pengiriman.</p>
            </div>

            <div class="visual-card" aria-label="Ilustrasi rute pengiriman">
                <div class="visual-head">
                    <span class="visual-label">Visibilitas pengiriman global</span>
                    <span class="live-pill">Sistem aktif</span>
                </div>
                <div class="route-map">
                    <svg viewBox="0 0 440 118" fill="none" aria-hidden="true">
                        <path d="M37 78C96 78 102 37 166 48C223 58 234 95 290 72C346 49 352 34 405 35" stroke="#22D3EE" stroke-width="2" stroke-dasharray="5 6" opacity=".8"/>
                        <path d="M0 94C69 84 80 103 144 87M247 20C302 31 335 13 440 22M93 19C120 28 130 20 166 29" stroke="#B6DCE8" stroke-width="1" opacity=".16"/>
                        <circle cx="37" cy="78" r="7" fill="#22D3EE" fill-opacity=".2"/><circle cx="37" cy="78" r="3" fill="#67E8F9"/>
                        <circle cx="166" cy="48" r="5" fill="#22D3EE" fill-opacity=".2"/><circle cx="166" cy="48" r="2.5" fill="#BAF5FC"/>
                        <circle cx="290" cy="72" r="5" fill="#22D3EE" fill-opacity=".2"/><circle cx="290" cy="72" r="2.5" fill="#BAF5FC"/>
                        <circle cx="405" cy="35" r="7" fill="#22D3EE" fill-opacity=".2"/><circle cx="405" cy="35" r="3" fill="#67E8F9"/>
                    </svg>
                </div>
                <div class="route-caption">
                    <span><b>Jakarta</b>Asal pengiriman</span>
                    <span style="text-align:right"><b>Seluruh dunia</b>Tujuan terpantau</span>
                </div>
            </div>
            <div class="brand-foot">CargoTrace &copy; {{ date('Y') }} &middot; Platform logistik tepercaya</div>
        </section>

        <section class="form-panel">
            <div class="form-inner">
                <div class="mobile-brand">
                    <div class="brand-mark" aria-hidden="true">CT</div>
                    <div>
                        <div class="brand-name">CargoTrace</div>
                        <div class="brand-tag" style="color:#718396">Global shipment visibility</div>
                    </div>
                </div>

                <header class="form-heading">
                    <p class="kicker">Selamat datang kembali</p>
                    <h2>Masuk ke akun</h2>
                    <p>Gunakan email dan password akun CargoTrace Anda untuk melanjutkan.</p>
                </header>

                @if (session('error'))
                    <div role="alert" class="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="field">
                        <label for="email">Alamat email</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap password-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/></svg>
                            <input id="password" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                            <button class="toggle-password" type="button" aria-label="Tampilkan password" aria-pressed="false" onclick="togglePassword()">
                                <svg id="password-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.2-6 9.5-6 9.5 6 9.5 6-3.2 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember"><input type="checkbox" name="remember"> Ingat saya</label>
                        <span>Akses aman &amp; terenkripsi</span>
                    </div>

                    <button class="submit-button" type="submit">
                        Masuk ke CargoTrace
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </button>
                </form>

                <p class="account-link">Belum punya akun? <a href="{{ route('register') }}">Buat akun customer</a></p>

                <aside class="demo-box" aria-label="Akun demo">
                    <div class="demo-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                        Coba akun demo <span style="font-weight:500;color:#8192a2">(password: password123)</span>
                    </div>
                    <div class="demo-list">
                        <span><b>Admin</b> &middot; admin@cargotrace.com</span>
                        <span><b>Operator</b> &middot; operator@cargotrace.com</span>
                        <span><b>Customer</b> &middot; customer@cargotrace.com</span>
                    </div>
                </aside>
            </div>
        </section>
    </main>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const button = document.querySelector('.toggle-password');
            const isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            button.setAttribute('aria-pressed', String(!isVisible));
            button.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
        }
    </script>
</body>
</html>
