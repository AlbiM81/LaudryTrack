@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')
@section('panel-label', 'Form atur ulang kata sandi')

@section('auth-content')
    <header class="card-header">
        <h2>Buat kata sandi baru</h2>
        <p>Gunakan kata sandi baru minimal 8 karakter.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" maxlength="255" required>
            @error('email') <p class="validation-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-field">
            <label for="password">Kata sandi baru</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            @error('password') <p class="validation-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-field">
            <label for="password_confirmation">Konfirmasi kata sandi baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button class="button button-primary" type="submit">Simpan kata sandi baru</button>
    </form>

    <div class="card-footer">
        <nav class="auth-links" aria-label="Tautan akun">
            <a href="{{ route('login') }}">Kembali ke halaman masuk</a>
        </nav>
    </div>
@endsection
