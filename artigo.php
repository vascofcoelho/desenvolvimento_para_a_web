<?php
// artigo.php
// Carrega um artigo dinâmico, comentários e likes com base em URL_slug

// Ligação à base de dados separada
require_once __DIR__ . '/db.php';

// Sessão para identificar utilizador (comentários exigem login)
if (session_status() === PHP_SESSION_NONE) session_start();

// Verifica role do utilizador (para mostrar controlos de moderação)
$can_moderate = false;
$current_username = '';
if (!empty($_SESSION['user_id'])) {
    $check = get_db();
    $stmt = $check->prepare('SELECT role, username FROM Users WHERE id_user = ? LIMIT 1');
    $uid = (int) $_SESSION['user_id'];
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) {
        $role = $r['role'] ?? 'user';
        $current_username = $r['username'] ?? '';
        if ($role === 'admin' || $role === 'moderator') $can_moderate = true;
    }
    $stmt->close();
    $check->close();
}


$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    http_response_code(404);
    echo 'Artigo não encontrado.';
    exit;
}

$conn = get_db();

// Busca artigo por URL_slug
$sql = "SELECT id_artigo, titulo, texto_artigo, autor, DATE_FORMAT(data, '%d/%m/%Y') as data, foto FROM Artigos WHERE URL_slug = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $slug);
$stmt->execute();
$res = $stmt->get_result();
$article = $res->fetch_assoc();
$stmt->close();

if (!$article) {
    http_response_code(404);
    echo 'Artigo não encontrado.';
    exit;
}

 $id_artigo = (int)$article['id_artigo'];

// Resolver caminho da imagem: se a BD guarda só o nome (ex: ai.jpg), assume-se a pasta `imgs/`.
$img_src = '';
if (!empty($article['foto'])) {
    $foto_val = trim($article['foto']);
    if (preg_match('#^https?://#i', $foto_val) || strpos($foto_val, '/') === 0) {
        // URL absoluta ou caminho a partir da raiz
        $img_src = $foto_val;
    } else {
        // assume ficheiro dentro da pasta imgs/
        $img_src = ltrim($foto_val, '/');
    }
}

// Resolve author display (name + avatar). The `autor` column may store user id or legacy username.
$author_name = $article['autor'] ?? '';
$author_avatar = '';
if (!empty($article['autor'])) {
    if (is_numeric($article['autor'])) {
        $s = $conn->prepare('SELECT username, first_name, last_name, avatar FROM Users WHERE id_user = ? LIMIT 1');
        $aid = (int)$article['autor'];
        $s->bind_param('i', $aid);
        $s->execute();
        $ur = $s->get_result()->fetch_assoc();
        if ($ur) {
            $author_name = trim(($ur['first_name'] ?? '') . ' ' . ($ur['last_name'] ?? '')) ?: ($ur['username'] ?? $author_name);
            if (!empty($ur['avatar'])) {
                $av = trim($ur['avatar']);
                $author_avatar = (preg_match('#^https?://#i', $av) || strpos($av, '/') === 0) ? $av : ltrim($av, '/');
            }
        }
        $s->close();
    } else {
        $s = $conn->prepare('SELECT username, first_name, last_name, avatar FROM Users WHERE username = ? LIMIT 1');
        $s->bind_param('s', $article['autor']);
        $s->execute();
        $ur = $s->get_result()->fetch_assoc();
        if ($ur) {
            $author_name = trim(($ur['first_name'] ?? '') . ' ' . ($ur['last_name'] ?? '')) ?: ($ur['username'] ?? $author_name);
            if (!empty($ur['avatar'])) {
                $av = trim($ur['avatar']);
                $author_avatar = (preg_match('#^https?://#i', $av) || strpos($av, '/') === 0) ? $av : ltrim($av, '/');
            }
        }
        $s->close();
    }
}

// Likes count
$sql = "SELECT COUNT(*) as cnt FROM Likes WHERE id_artigo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_artigo);
$stmt->execute();
$res = $stmt->get_result();
$likes = $res->fetch_assoc()['cnt'];
$stmt->close();

// Verifica se o user atual já deu like (se estiver autenticado)
$user_liked = false;
if (!empty($_SESSION['user_id'])) {
    $sql = "SELECT 1 FROM Likes WHERE id_artigo = ? AND id_user = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $uid = (int) $_SESSION['user_id'];
    $stmt->bind_param('ii', $id_artigo, $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $user_liked = (bool) $res->fetch_assoc();
    $stmt->close();
}

// Se utilizador autenticado, buscar avatar para mostrar junto ao formulário
$my_avatar = '';
if (!empty($_SESSION['user_id'])) {
    $stmt = $conn->prepare('SELECT avatar FROM Users WHERE id_user = ? LIMIT 1');
    $uid = (int) $_SESSION['user_id'];
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r && !empty($r['avatar'])) {
        $av = trim($r['avatar']);
        if (preg_match('#^https?://#i', $av) || strpos($av, '/') === 0) $my_avatar = $av; else $my_avatar = ltrim($av, '/');
    }
    $stmt->close();
}

// Comentários (junta com Users para mostrar nome)
$sql = "SELECT c.id_comentario, c.comentario, DATE_FORMAT(c.data, '%d/%m/%Y %H:%i') as data, u.username, u.avatar
    FROM Comentarios c
    LEFT JOIN Users u ON c.id_user = u.id_user
    WHERE c.id_artigo = ?
    ORDER BY c.data DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_artigo);
$stmt->execute();
$comments_res = $stmt->get_result();
$comments = [];
while ($row = $comments_res->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

// Escapar saída simples
function e($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($article['titulo']); ?> - Rabbit Head</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5">
        <article>
            <h1 class="text-center mb-4"><?php echo e($article['titulo']); ?></h1>
            <div class="text-center text-muted mb-5 d-flex justify-content-center align-items-center gap-2">
                <?php if (!empty($author_avatar)): ?>
                    <img src="<?php echo e($author_avatar); ?>" alt="avatar" style="width:48px;height:48px;border-radius:6px;object-fit:cover;"> 
                <?php else: ?>
                    <i class="bi bi-person-circle" style="font-size:1.6rem;"></i>
                <?php endif; ?>
                <div>
                    <div><?php echo e($author_name); ?></div>
                    <div class="text-muted"><i class="bi bi-calendar-event"></i> <?php echo e($article['data']); ?></div>
                </div>
            </div>

            <div class="row justify-content-center mb-5">
                <div class="col-12 col-lg-8">
                    <?php if (!empty($img_src)): ?>
                        <img src="<?php echo e($img_src); ?>" class="img-fluid rounded w-100 mb-3" alt="Imagem do Post">
                    <?php endif; ?>
                </div>
            </div>

            <div class="row justify-content-center mb-5">
                <div class="col-12 col-lg-8">
                    <div class="d-flex justify-content-start gap-3 align-items-center">
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <button id="like-btn" data-artigo="<?php echo $id_artigo; ?>" class="btn btn-link p-0">
                                <i id="like-icon" class="bi <?php echo $user_liked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                <span id="like-count"><?php echo (int)$likes; ?></span> gostos
                            </button>
                        <?php else: ?>
                            <span class="like-badge"><i class="bi bi-heart text-secondary"></i> <?php echo (int)$likes; ?> gostos</span>
                        <?php endif; ?>
                        <a href="#comentarios" class="text-decoration-none text-reset"><span class="comment-badge"><i class="bi bi-chat-left-text"></i> <?php echo count($comments); ?> comentários</span></a>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <?php echo nl2br(e($article['texto_artigo'])); ?>
                </div>
            </div>

        </article>

        <hr class="my-5">

        <section id="comentarios">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <h4>Comentários (<?php echo count($comments); ?>)</h4>
                    <?php if (count($comments) === 0): ?>
                        <p class="text-muted">Seja o primeiro a comentar.</p>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="mb-4 d-flex justify-content-between">
                                <div class="d-flex">
                                    <?php
                                        $avatar_html = '<i class="bi bi-person-circle text-secondary me-3" style="font-size: 2rem;"></i>';
                                        if (!empty($c['avatar'])) {
                                            $av = trim($c['avatar']);
                                            if (preg_match('#^https?://#i', $av) || strpos($av, '/') === 0) $src = $av; else $src = ltrim($av, '/');
                                            $avatar_html = '<img src="'.htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8").'" alt="avatar" style="width:48px;height:48px;border-radius:6px;object-fit:cover;margin-right:12px;">';
                                        }
                                    ?>
                                    <?php echo $avatar_html; ?>
                                    <div>
                                        <strong><?php echo e($c['username'] ?: 'Utilizador'); ?>:</strong>
                                        <p class="mb-0"><?php echo nl2br(e($c['comentario'])); ?></p>
                                        <small class="text-muted"><?php echo e($c['data']); ?></small>
                                    </div>
                                </div>
                                <?php if (!empty($can_moderate)): ?>
                                    <div class="ms-3">
                                        <?php $redir = 'artigo.php?slug=' . $slug . '#comentarios'; ?>
                                        <a class="btn btn-sm btn-danger" href="admin/delete_comment.php?id=<?php echo e($c['id_comentario']); ?>&redirect=<?php echo urlencode($redir); ?>" onclick="return confirm('Apagar comentário?');">Apagar</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <h5 class="mt-5">Deixe o seu comentário</h5>
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <form method="post" action="submit_comment.php">
                            <input type="hidden" name="id_artigo" value="<?php echo $id_artigo; ?>">
                            <div class="mb-2">
                                <label for="comentario" class="form-label">Comentário</label>
                                <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Enviar</button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted">Tem de estar autenticado para comentar. <a href="login.php">Iniciar sessão</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('like-btn');
        if (!btn) return;
        btn.addEventListener('click', function(e){
            e.preventDefault();
            var artigoId = btn.dataset.artigo;
            fetch('like.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id_artigo: artigoId })
            }).then(function(res){ return res.json(); })
            .then(function(data){
                if (!data || !data.success) {
                    alert(data && data.error ? data.error : 'Erro ao processar like.');
                    return;
                }
                var icon = document.getElementById('like-icon');
                var count = document.getElementById('like-count');
                count.textContent = data.count;
                if (data.liked) {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill','text-danger');
                } else {
                    icon.classList.remove('bi-heart-fill','text-danger');
                    icon.classList.add('bi-heart');
                }
            }).catch(function(){
                alert('Erro de rede ao enviar like.');
            });
        });
    });
    </script>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
