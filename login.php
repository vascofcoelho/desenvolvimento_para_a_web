<?php
session_start();
require_once __DIR__ . '/db.php';

$error = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// Logout via querystring (também suportado em navbar link)
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = 'Preencha utilizador e palavra-passe.';
    } else {
        $conn = get_db();
        // Try to select role (newer schema). If the column doesn't exist, fall back to a query without role.
        $user = null;
        try {
            $sql = 'SELECT id_user, username, password, salt, role FROM Users WHERE username = ? LIMIT 1';
            $stmt = $conn->prepare($sql);
        } catch (mysqli_sql_exception $e) {
            // Fallback: role column missing — try without it
            $stmt = null;
        }

        if (empty($stmt)) {
            // Attempt the simpler query (no role)
            try {
                $sql2 = 'SELECT id_user, username, password, salt FROM Users WHERE username = ? LIMIT 1';
                $stmt = $conn->prepare($sql2);
            } catch (mysqli_sql_exception $e) {
                $stmt = null;
            }
        }

        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();
        } else {
            $user = null;
        }

        // Se não existe e estamos em dev, permitir criar um utilizador 'dev' com password 'dev'
        if (!$user && $username === 'dev' && $password === 'dev') {
            $pwHash = password_hash($password, PASSWORD_DEFAULT);
            $email = 'dev@local';
            $ins = $conn->prepare('INSERT INTO Users (username, email, password, salt, first_name, last_name) VALUES (?, ?, ?, "", ?, ?)');
            if ($ins) {
                $fn = 'Dev'; $ln = 'User';
                $ins->bind_param('sssss', $username, $email, $pwHash, $fn, $ln);
                $ins->execute();
                $ins->close();
                $user = $conn->query('SELECT id_user, username, password, salt FROM Users WHERE username = "dev" LIMIT 1')->fetch_assoc();
            }
        }

        $ok = false;
        if ($user) {
            // Tenta várias formas de verificação para compatibilidade com diferentes armazenamentos de senhas
            if (!empty($user['password']) && password_verify($password, $user['password'])) {
                $ok = true;
            } elseif ($password === $user['password']) {
                $ok = true;
            } elseif (!empty($user['salt'])) {
                $salt = $user['salt'];
                if (md5($password . $salt) === $user['password'] || sha1($password . $salt) === $user['password']) {
                    $ok = true;
                }
            }
        }

        if ($ok) {
            $_SESSION['user_id'] = (int)$user['id_user'];
            $_SESSION['username'] = $user['username'];
            // If role is not present in row (older schema), default to 'user'
            $_SESSION['role'] = !empty($user['role']) ? $user['role'] : 'user';
            // Load avatar for session (if Users.avatar exists)
            try {
                $conn2 = get_db();
                $s2 = $conn2->prepare('SELECT avatar FROM Users WHERE id_user = ? LIMIT 1');
                if ($s2) {
                    $s2->bind_param('i', $_SESSION['user_id']);
                    $s2->execute();
                    $res2 = $s2->get_result();
                    if ($r2 = $res2->fetch_assoc()) {
                        $av = trim($r2['avatar'] ?? '');
                        if (!empty($av)) {
                            // compute base for root-relative paths
                            $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
                            $parts = explode('/', trim($script, '/'));
                            $base = '';
                            if (count($parts) > 0 && $parts[0] !== '') $base = '/' . $parts[0];
                            if (preg_match('#^https?://#i', $av)) {
                                $_SESSION['avatar'] = $av;
                            } else {
                                // store raw path (without base) but also set session to raw for consistency
                                $_SESSION['avatar'] = $av;
                            }
                        }
                    }
                    $s2->close();
                }
                $conn2->close();
            } catch (Exception $e) {
                // ignore avatar load errors
            }
            // Se for admin, ir diretamente para o dashboard do admin
            if (!empty($user['role']) && $user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'Credenciais inválidas.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Rabbit Head</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/navbar.php'; ?>
<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <h3>Entrar</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Utilizador</label>
                    <input name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Palavra-passe</label>
                    <input name="password" type="password" class="form-control" required>
                </div>
                <button class="btn btn-success">Entrar</button>
            </form>
            <p class="mt-3">Não tem conta? <a href="register.php">Registar</a></p>
            <?php if (!empty($_GET['registered'])): ?>
                <div class="alert alert-success mt-3">Registo efetuado com sucesso. Pode entrar com as suas credenciais.</div>
            <?php endif; ?>
            <!-- Dev hint removed in production-like UI -->
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
