<?php
// partials/footer.php
?>
    </div><!-- .site-content -->

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">Rabbit Head Blog &copy; <?php echo date('Y'); ?></div>
    </footer>

    <?php
    // Calcular base do site para criar paths root-relative
    // Compute base from filesystem: project root relative to document root when possible
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $projectRoot = realpath(__DIR__ . '/..');
    $base = '';
    if ($docRoot && $projectRoot && strpos(str_replace('\\','/',$projectRoot), str_replace('\\','/',$docRoot)) === 0) {
        $rel = trim(str_replace('\\','/',substr($projectRoot, strlen($docRoot))), '/');
        if ($rel !== '') $base = '/' . $rel;
    } else {
        // Fallback: use first URL path segment if it looks like a folder (no dot)
        $scriptPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $parts = explode('/', trim($scriptPath, '/'));
        if (count($parts) > 0 && $parts[0] !== '' && strpos($parts[0], '.') === false) {
            $base = '/' . $parts[0];
        }
    }
    function asset($path) {
        global $base;
        return $base . '/' . ltrim($path, '/');
    }
    ?>

    <script src="<?php echo asset('js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
