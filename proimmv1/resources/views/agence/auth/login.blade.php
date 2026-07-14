@extends('agence.layouts.auth')

@section('title', 'Connexion — Espace Agence')

@section('auth_content')
    <section class="login-card">
        <div class="login-brand">
            <img src="{{ asset('admin/logo/playstore-icon-revised.png') }}" alt="Pros Immobilier">
            <div class="login-brand-name" aria-label="Pros Immobilier">
                <span class="brand-pros">Pros</span>
                <span class="brand-immobilier">Immobilier</span>
            </div>
        </div>

        <h1>Espace Agence</h1>
        <p>Connectez-vous à votre espace de gestion.</p>

        @if(!empty($loginError))
            <div class="alert alert-danger">
                {{ $loginError }}
            </div>
        @elseif(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @elseif($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form class="login-form" action="{{ route('agence.login.post') }}" method="POST">
            @csrf

            <div class="field">
                <label for="email">Adresse e-mail</label>
                <x-ui.input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="nom@agence.com"
                        :value="old('email', $email ?? '')"
                        :required="true"
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                />
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <x-ui.input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="************"
                        :required="true"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                />
            </div>

            <div class="form-row">
                <x-ui.checkbox name="remember" id="remember" value="1" label="Se souvenir de moi" />
                <a class="link" href="#">Mot de passe oublié ?</a>
            </div>

            <button class="submit-btn" type="submit">Se connecter</button>
        </form>
    </section>
@endsection
