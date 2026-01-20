<?php
session_start();
require_once __DIR__ . '/../db.php';

// Autenticação + verificação de roles (admin/author)
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/article_edit.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT role, username FROM users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user'; $username = '';
if ($row = $res->fetch_assoc()) { $role = $row['role'] ?? 'user'; $username = $row['username']; }
$stmt->close();

if ($role !== 'admin' && $role !== 'author') { echo 'Acesso negado.'; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = ['titulo'=>'','texto_artigo'=>'','autor'=>'','foto'=>'','id_categoria'=>null,'URL_slug'=>''];
if ($id > 0) {
    $stmt = $conn->prepare('SELECT id_artigo, titulo, texto_artigo, autor, foto, id_categoria, URL_slug FROM artigos WHERE id_artigo = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) $article = $r;
    $stmt->close();
    // If the stored autor is a username (legacy), try to resolve to user id
    if (!empty($article['autor']) && !is_numeric($article['autor'])) {
        $s = $conn->prepare('SELECT id_user FROM users WHERE username = ? LIMIT 1');
        $s->bind_param('s', $article['autor']);
        $s->execute();
        $rr = $s->get_result()->fetch_assoc();
        if ($rr) $article['autor'] = (int)$rr['id_user'];
        $s->close();
    }
    // If user is author, ensure they own this article (compare by user id)
    if ($role === 'author' && !empty($article['autor']) && (int)$article['autor'] !== $uid) {
        echo 'Acesso negado. Só pode editar os seus próprios artigos.'; exit;
    }
}

// Carregar categorias
$categories = [];
$cres = $conn->query('SELECT id_categoria, categoria FROM categorias ORDER BY categoria');
if ($cres) { while ($c = $cres->fetch_assoc()) $categories[] = $c; $cres->free(); }

function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $id ? 'Editar' : 'Novo'; ?> Artigo</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><?php echo $id ? 'Editar' : 'Criar'; ?> Artigo</h3>
        <div>
            <a class="btn btn-success" href="articles.php">Voltar</a>
        </div>
    </div>
    <form method="post" action="save_article.php" enctype="multipart/form-data">
        <input type="hidden" name="id_artigo" value="<?php echo e($id); ?>">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input name="titulo" class="form-control" required value="<?php echo e($article['titulo']); ?>">
        </div>
        <!-- Slug is generated automatically from the title -->
        <div class="mb-3">
            <label class="form-label">Autor</label>
            <?php
            // Load users for selection (only authors)
            $users = [];
            $ustmt = $conn->prepare('SELECT id_user, username, first_name, last_name FROM users WHERE role = ? ORDER BY username');
            $author_role = 'author';
            $ustmt->bind_param('s', $author_role);
            $ustmt->execute();
            $ures = $ustmt->get_result();
            if ($ures) { while ($u = $ures->fetch_assoc()) $users[] = $u; $ures->free(); }
            $ustmt->close();
            $selected_author = (int)($article['autor'] ?? 0);
            if ($role === 'author') {
                // authors must be themselves
                echo '<input type="hidden" name="autor" value="' . $uid . '">';
                echo '<div class="form-control">' . htmlspecialchars($username) . '</div>';
            } else {
                echo '<select name="autor" class="form-select">';
                echo '<option value="0">-- Selecionar autor --</option>';
                foreach ($users as $u) {
                    $label = $u['username'];
                    if (!empty($u['first_name']) || !empty($u['last_name'])) $label = trim($u['first_name'] . ' ' . $u['last_name']) . ' (' . $u['username'] . ')';
                    $sel = ($selected_author === (int)$u['id_user']) ? ' selected' : '';
                    echo '<option value="' . (int)$u['id_user'] . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
                }
                echo '</select>';
            }
            ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoria</label>
            <select name="id_categoria" class="form-select" required>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo e($c['id_categoria']); ?>" <?php echo ($article['id_categoria']==$c['id_categoria']) ? 'selected' : ''; ?>><?php echo e($c['categoria']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagem (jpg/png)</label>
            <?php if (!empty($article['foto'])): ?>
                <div class="mb-2"><img src="../imgs/<?php echo e(basename($article['foto'])); ?>" style="max-height:120px;"></div>
            <?php endif; ?>
            <input type="file" name="foto" accept="image/*" class="form-control" <?php echo $id>0 ? '' : 'required'; ?>>
        </div>
        <div class="mb-3">
            <label class="form-label">Conteúdo</label>
            <textarea name="texto_artigo" rows="10" class="form-control"><?php echo e($article['texto_artigo']); ?></textarea>
        </div>
        <button class="btn btn-success">Guardar</button>
    </form>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
