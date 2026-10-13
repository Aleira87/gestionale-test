<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accedi - Gestionale</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login {
            background: #ffffff;
            padding: 2rem;
            width: min(360px, calc(100% - 4rem));
            border-radius: 12px;
            box-shadow: 0 4px 18px #00000012;
        }

        label {
            display: block;
            margin-top: 1rem;
        }

        input {
            box-sizing: border-box;
            width: 100%;
            padding: 0.7rem;
            margin-top: 0.4rem;
            border: 1px solid #9ca3af;
            border-radius: 6px;
        }

        button {
            width: 100%;
            margin-top: 1.5rem;
            padding: 0.8rem;
            border: 0;
            border-radius: 6px;
            background: #b45309;
            color: #ffffff;
            cursor: pointer;
        }

        .error {
            color: #b91c1c;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <main class="login">
        <h1>Accedi</h1>
        <p>Inserisci le tue credenziali per continuare.</p>

        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username"
                required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>

            <label>
                <input type="checkbox" name="remember" value="1">
                Ricordami
            </label>

            <button type="submit">Accedi</button>
        </form>
    </main>
</body>

</html>
