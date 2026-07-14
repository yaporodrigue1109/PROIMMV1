@extends('admin.layouts.auth')

@section('title', 'Connexion')

@section('auth_content')
    <section class="login-card">
        <div class="login-brand">
            <img src="{{ asset('admin/logo/playstore-icon-revised.png') }}" alt="Pros Immobilier">
            <div class="login-brand-name" aria-label="Pros Immobilier">
                <span class="brand-pros">Pros</span>
                <span class="brand-immobilier">Immobilier</span>
            </div>
        </div>

        <h1>Connexion</h1>
        <p>Accède à ton espace.</p>

        {{-- ERREUR AUTH (ex: identifiants incorrects) --}}
        @if(!empty($loginError))
            <div class="alert alert-danger">
                {{ $loginError }}
            </div>
        @elseif(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @elseif ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="login-form" action="{{ route('admin.login.post') }}" method="POST">
            @csrf

            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="nom@entreprise.com"
                        value="{{ old('email', $email ?? '') }}"
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                >
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="************"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        required
                >
            </div>

            <div class="form-row">
                <label class="remember" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1">
                    <span>Se souvenir de moi</span>
                </label>

                <a class="link" href="#">Mot de passe oublié ?</a>
            </div>

            <button class="submit-btn" type="submit">Se connecter</button>
        </form>
    </section>
@endsection
