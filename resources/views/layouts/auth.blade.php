<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102f42">
    <title>@yield('title') - LaundryTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="login-layout">
        <section class="login-intro" aria-label="Tentang LaundryTrack">
            <a class="brand" href="{{ route('login') }}" aria-label="LaundryTrack beranda">
                LaundryTrack
            </a>

            <div class="intro-copy">
                <h1>Kelola pesanan laundry dengan lebih teratur.</h1>
                <p>Masuk untuk mencatat pesanan, memperbarui status cucian, dan memantau pesanan yang terlambat.</p>
            </div>

            <p class="intro-footer">Khusus pemilik dan kasir/staf</p>
        </section>

        <section class="login-content" aria-label="@yield('panel-label', 'Akun LaundryTrack')">
            <div class="login-card">
                @yield('auth-content')
            </div>
        </section>
    </main>
</body>
</html>
