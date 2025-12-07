<?php
// logout.php - encerra sessão e redireciona
session_start();
session_unset();
session_destroy();
$back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header('Location: ' . $back);
exit;
