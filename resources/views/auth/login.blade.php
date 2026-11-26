@extends('layouts.auth')

@section('title', 'Masuk')
@section('panel-label', 'Form masuk')

@section('auth-content')
    <div data-login-view>
        <header class="card-header">
            <h2>Masuk</h2>
            <p>Gunakan akun yang diberikan pemilik laundry.</p>
        </header>

        @if (session('status'))
            <p class="status-message" role="status">{{ session('status') }}</p>
        @endif

        <form id="login-form" novalidate>
            <div class="form-field">
                <label for="username">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    autocomplete="username"
                    placeholder="Masukkan username"
                    maxlength="100"
                    required
                >
            </div>

            <div class="form-field">
                <label for="password">Kata sandi</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                    required
                >
            </div>

            <p class="form-message form-message-error" id="login-error" role="alert" hidden></p>

            <button class="button button-primary" type="submit" id="login-submit">
                Masuk
            </button>
        </form>

        <div class="card-footer">
            <p>Pelanggan? <span>Pelacakan pesanan tanpa login segera tersedia.</span></p>
            <nav class="auth-links" aria-label="Tautan akun">
                <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
                <a href="{{ route('register') }}">Daftar sebagai Pemilik</a>
            </nav>
        </div>
    </div>

    <div class="authenticated-view" data-authenticated-view hidden>
        <p class="success-badge">Berhasil masuk</p>
        <h2>Selamat datang, <span data-user-name></span></h2>
        <p class="authenticated-description">Akun LaundryTrack Anda sudah terhubung.</p>
        <p class="role-list" data-user-roles></p>
        <p class="form-message form-message-error" id="logout-error" role="alert" hidden></p>
        <button class="button button-secondary" type="button" id="logout-button">Keluar</button>
    </div>
@endsection
