@extends('layouts.auth')

@section('title', 'Daftar Pemilik')
@section('panel-label', 'Form pendaftaran Pemilik')

@section('auth-content')
    <header class="card-header">
        <h2>Daftar Pemilik</h2>
        <p>Buat akun untuk mulai mengelola laundry Anda.</p>
    </header>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="form-field">
            <label for="name">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required>
            @error('name') <p class="validation-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" maxlength="100" required>
            @error('username') <p class="validation-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
            @error('email') <p class="validation-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-field">
            <label for="password">Kata sandi (minimal 8 karakter)</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            @error('password') <p class="validation-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-field">
            <label for="password_confirmation">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button class="button button-primary" type="submit">Buat akun Pemilik</button>
    </form>

    <div class="card-footer">
        <p>Sudah memiliki akun?</p>
        <nav class="auth-links" aria-label="Tautan akun">
            <a href="{{ route('login') }}">Masuk</a>
        </nav>
    </div>
@endsection
