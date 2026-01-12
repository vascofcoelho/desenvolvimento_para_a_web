<?php
// partials/navbar.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';

// Calcular base do site (pasta do projecto na URL) para criar links root-relative
$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$parts = explode('/', trim($script, '/'));
$base = '';
if (count($parts) > 0 && $parts[0] !== '') {
    $base = '/' . $parts[0];
}
function href($path) {
    global $base;
    return $base . '/' . ltrim($path, '/');
}
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?php echo href('index.php'); ?>"><img src="<?php echo href('imgs/logo.png'); ?>" alt="Logo" style="max-height:50px;"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex me-3" method="get" action="<?php echo href('search.php'); ?>">
                <input name="q" class="form-control form-control-sm" type="search" placeholder="Pesquisar..." aria-label="Pesquisar" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button class="btn btn-sm btn-success ms-2" type="submit">Pesquisar</button>
            </form>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo href('index.php'); ?>">Início</a>
                <a class="nav-link" href="<?php echo href('sobre-nos.php'); ?>">Sobre Nós</a>
                <?php if (!empty($_SESSION['user_id'])):
                    // Prefer avatar saved in session to avoid a DB query on every request
                    $user_avatar = '';
                    if (!empty($_SESSION['avatar'])) {
                        $sessAv = trim($_SESSION['avatar']);
                        if (preg_match('#^https?://#i', $sessAv) || strpos($sessAv, '/') === 0) {
                            $user_avatar = $sessAv;
                        } else {
                            $user_avatar = $base . '/' . ltrim($sessAv, '/');
                        }
                    } else {
                        // Fallback: query DB once and store in session for subsequent requests
                        $uid = (int)($_SESSION['user_id'] ?? 0);
                        if ($uid > 0) {
                            $navConn = get_db();
                            $s = $navConn->prepare('SELECT avatar FROM users WHERE id_user = ? LIMIT 1');
                            if ($s) {
                                $s->bind_param('i', $uid);
                                $s->execute();
                                $res = $s->get_result();
                                $r = $res->fetch_assoc();
                                if ($r && !empty($r['avatar'])) {
                                    $rawAv = trim($r['avatar']);
                                    if (preg_match('#^https?://#i', $rawAv)) {
                                        $user_avatar = $rawAv;
                                        $_SESSION['avatar'] = $rawAv;
                                    } else {
                                        $user_avatar = $base . '/' . ltrim($rawAv, '/');
                                        $_SESSION['avatar'] = $rawAv; // store raw path in session
                                    }
                                }
                                $s->close();
                            }
                            $navConn->close();
                        }
                    }

                    // Show dropdown with avatar + username; include dashboard link when appropriate
                    $nav_role = $_SESSION['role'] ?? 'user';
                    $dash_label = '';
                    $dash_href = href('admin/index.php');
                    if ($nav_role === 'admin') {
                        $dash_label = 'Admin Dashboard';
                        $dash_href = href('admin/index.php');
                    } elseif ($nav_role === 'author') {
                        $dash_label = 'Meus Artigos';
                        $dash_href = href('admin/articles.php');
                    } elseif ($nav_role === 'moderator') {
                        $dash_label = 'Moderar Comentários';
                        $dash_href = href('admin/comments.php');
                    }
                ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (!empty($user_avatar)): ?>
                                <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-right:8px;">
                            <?php else: ?>
                                <span class="bi bi-person-circle" style="font-size:1.3rem;margin-right:8px;"></span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilizador'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="<?php echo href('profile.php'); ?>">Perfil</a></li>
                            <?php if ($dash_label !== ''): ?><li><a class="dropdown-item" href="<?php echo $dash_href; ?>"><?php echo htmlspecialchars($dash_label); ?></a></li><?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo href('login.php'); ?>?logout=1">Sair</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo href('login.php'); ?>">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class="site-content">
