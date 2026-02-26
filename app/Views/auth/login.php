<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login — PrintFlow</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: sans-serif;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.1);
            width: 100%;
            max-width: 360px;
        }
        h2 { margin-bottom: 1.5rem; text-align: center; color: #333; }
        label { display: block; margin-bottom: .3rem; font-size: .9rem; color: #555; }
        input {
            width: 100%;
            padding: .6rem .8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: .7rem;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover { background: #4338ca; }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: .6rem .8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-size: .9rem;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>PrintFlow</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/PrintFlow/public/login">
        <label>Usuario</label>
        <input type="text" name="username" autofocus required>

        <label>Contraseña</label>
        <input type="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>