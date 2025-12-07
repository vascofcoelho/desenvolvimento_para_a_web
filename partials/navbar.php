<?php
// partials/navbar.php
if (session_status() === PHP_SESSION_NONE) session_start();

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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo href('index.php'); ?>">Início</a>
                <a class="nav-link" href="<?php echo href('sobre-nos.php'); ?>">Sobre Nós</a>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="<?php echo href('profile.php'); ?>">Perfil</a>
                    <?php
                    // Show a dashboard link for any non-'user' role. Map role -> dashboard page + label.
                    $nav_role = $_SESSION['role'] ?? 'user';
                    if ($nav_role !== 'user') {
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
                        } else {
                            // generic fallback for other roles
                            $dash_label = ucfirst(htmlspecialchars($nav_role)) . ' Dashboard';
                            $dash_href = href('admin/index.php');
                        }
                        echo '<a class="nav-link" href="' . $dash_href . '">' . htmlspecialchars($dash_label) . '</a>';
                    }
                    ?>
                    <a class="nav-link" href="<?php echo href('login.php'); ?>?logout=1">Sair (<?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilizador'); ?>)</a>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo href('login.php'); ?>">Entrar</a>
                    <a class="nav-link" href="<?php echo href('register.php'); ?>">Registar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
