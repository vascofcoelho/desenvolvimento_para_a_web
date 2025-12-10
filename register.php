<?php
require_once __DIR__ . '/db.php';
session_start();

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$error = isset($_GET['error']) ? $_GET['error'] : '';

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registar - Rabbit Head</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>
<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <h3>Registar</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>
            <form method="post" action="register_submit.php">
                <div class="mb-3">
                    <label class="form-label">Nome de Utilizador</label>
                    <input name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Palavra-passe</label>
                    <input name="password" type="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirme Palavra-passe</label>
                    <input name="password_confirm" type="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input name="first_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Sobrenome</label>
                    <input name="last_name" class="form-control">
                </div>
                <button class="btn btn-success">Registar</button>
            </form>
            <p class="mt-3">Já tem conta? <a href="login.php">Entrar</a></p>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
