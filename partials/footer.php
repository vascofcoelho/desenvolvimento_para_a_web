<?php
// partials/footer.php
?>
    </div><!-- .site-content -->

    <footer class="bg-dark text-white py-3">
        <div class="container text-center">Rabbit Head Blog &copy; <?php echo date('Y'); ?></div>
    </footer>

    <?php
    // Calcular base do site para criar paths root-relative (ex: /desenvolvimento_para_a_web)
    $scriptPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $parts = explode('/', trim($scriptPath, '/'));
    $base = '';
    if (count($parts) > 0 && $parts[0] !== '') {
        $base = '/' . $parts[0];
    }
    function asset($path) {
        global $base;
        return $base . '/' . ltrim($path, '/');
    }
    ?>

    <script src="<?php echo asset('js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
