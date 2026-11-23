@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')
@section('panel-label', 'Form lupa kata sandi')

@section('auth-content')
    <header class="card-header">
        <h2>Lupa kata sandi?</h2>
        <p>Masukkan email akun Anda. Jika terdaftar, kami akan mengirim tautan untuk membuat kata sandi baru.</p>
    </header>

    @if (session('status'))
        <p class="status-message" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required autofocus>
            @error('email') <p class="validation-error">{{ $message }}</p> @enderror
        </div>

        <button class="button button-primary" type="submit">Kirim tautan reset</button>
    </form>

    <div class="card-footer">
        <nav class="auth-links" aria-label="Tautan akun">
            <a href="{{ route('login') }}">Kembali ke halaman masuk</a>
        </nav>
    </div>
@endsection
