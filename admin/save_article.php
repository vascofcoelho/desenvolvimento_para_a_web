<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/articles.php');
    exit;
}

$conn = get_db();
$uid = (int)($_SESSION['user_id'] ?? 0);
// check role
$stmt = $conn->prepare('SELECT role, username FROM Users WHERE id_user = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$role = 'user'; $username = '';
if ($row = $res->fetch_assoc()) { $role = $row['role'] ?? 'user'; $username = $row['username']; }
$stmt->close();
if ($role !== 'admin' && $role !== 'author') { echo 'Acesso negado.'; exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: articles.php'); exit; }

$id = isset($_POST['id_artigo']) ? (int)$_POST['id_artigo'] : 0;
$titulo = trim($_POST['titulo'] ?? '');
$texto = trim($_POST['texto_artigo'] ?? '');
$autor = isset($_POST['autor']) ? (int)$_POST['autor'] : 0; // expected author id
// If role is author, force autor to session user id
if ($role === 'author') {
    $autor = $uid;
}
$id_categoria = isset($_POST['id_categoria']) && $_POST['id_categoria'] !== '' ? (int)$_POST['id_categoria'] : null;
$slug = trim($_POST['URL_slug'] ?? '');

// Server-side validation: título, conteúdo, autor e categoria são obrigatórios. Foto obrigatória ao criar novo artigo.
if ($titulo === '' || $texto === '') {
    echo 'Título e conteúdo são obrigatórios.'; exit;
}
if (empty($autor) || $autor <= 0) {
    echo 'Autor inválido.'; exit;
}
if (empty($id_categoria)) {
    echo 'Categoria é obrigatória.'; exit;
}

function slugify($s){
    $s = mb_strtolower($s, 'UTF-8');
    $s = preg_replace('/[^a-z0-9\s-]/u', '', $s);
    $s = preg_replace('/[\s-]+/', '-', $s);
    $s = trim($s, '-');
    return $s ?: 'artigo';
}

// Automatically generate slug from title (authors shouldn't set slug manually)
$slug = slugify($titulo);

// Garantir slug único
$base = $slug; $i = 1;
while (true) {
    $q = $conn->prepare('SELECT id_artigo FROM Artigos WHERE URL_slug = ?' . ($id>0 ? ' AND id_artigo != ?' : '') . ' LIMIT 1');
    if ($id>0) $q->bind_param('si', $slug, $id); else $q->bind_param('s', $slug);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    $q->close();
    if (!$r) break;
    $slug = $base . '-' . $i; $i++;
}

// Handle photo upload
$foto_val = null;
if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fn = $_FILES['foto']['name'];
    $ext = pathinfo($fn, PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','gif'];
    if (!in_array(mb_strtolower($ext), $allowed)) {
        echo 'Formato de imagem não suportado.'; exit;
    }
    $newname = time() . '_' . preg_replace('/[^a-z0-9._-]/i','_', $fn);
    $target = __DIR__ . '/../imgs/' . $newname;
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        echo 'Erro ao mover ficheiro.'; exit;
    }
    $foto_val = 'imgs/' . $newname;
}

// If creating a new article, photo must be provided
if ($id === 0 && $foto_val === null) {
    echo 'A imagem do artigo é obrigatória.'; exit;
}

if ($id > 0) {
    if ($foto_val !== null) {
        $sql = 'UPDATE Artigos SET titulo = ?, texto_artigo = ?, autor = ?, id_categoria = ?, foto = ?, URL_slug = ? WHERE id_artigo = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssiissi', $titulo, $texto, $autor, $id_categoria, $foto_val, $slug, $id);
    } else {
        $sql = 'UPDATE Artigos SET titulo = ?, texto_artigo = ?, autor = ?, id_categoria = ?, URL_slug = ? WHERE id_artigo = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssiisi', $titulo, $texto, $autor, $id_categoria, $slug, $id);
    }
    $ok = $stmt->execute();
    $stmt->close();
} else {
    $sql = 'INSERT INTO Artigos (titulo, texto_artigo, autor, id_categoria, foto, URL_slug) VALUES (?, ?, ?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssiiss', $titulo, $texto, $autor, $id_categoria, $foto_val, $slug);
    $ok = $stmt->execute();
    if ($ok) $id = $stmt->insert_id;
    $stmt->close();
}

if (!$ok) { echo 'Erro ao gravar artigo.'; exit; }

header('Location: articles.php');
exit;
