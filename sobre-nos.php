<?php
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós - Rabbit Head Blog</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- Conteúdo Sobre Nós -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-3 mb-md-0">
                        <!-- imagem ao lado do texto; coloque o ficheiro em imgs/sobre_nos.jpg -->
                        <img src="imgs/sobre_nos.jpg" alt="Sobre Nós" class="img-fluid rounded shadow-sm" />
                    </div>
                    <div class="col-md-7">
                        <h1 class="mb-4 text-uppercase">SOBRE NÓS</h1>

                        <p>Bem-vindo ao Rabbit Head, um espaço criado por estudantes apaixonados pelo mundo digital e pela partilha de ideias. O nosso objetivo é construir uma plataforma simples, intuitiva e envolvente, onde qualquer utilizador pode explorar conteúdos, deixar comentários e reagir às publicações da comunidade.</p>
                        <p>Este projeto nasceu no âmbito da unidade curricular de Desenvolvimento para a Web, mas rapidamente se transformou numa experiência que combina criatividade, tecnologia e comunicação. Aqui acreditamos que todos têm algo para dizer, seja uma opinião, uma reflexão ou até um simples desabafo. O Rabbit Head pretende dar voz a essas partilhas, promovendo um ambiente aberto e descontraído.</p>
                        <p>O blog é gerido por uma pequena equipa que desenvolve e organiza os artigos, mantém o backoffice e garante que tudo funciona de forma fluida. Trabalhamos continuamente para melhorar o visual, a estrutura e a experiência de navegação, seguindo as melhores práticas de desenvolvimento web.</p>

                        <h2 class="mt-4 text-uppercase">NOSSA MISSÃO</h2>

                        <p>A nossa missão é evoluir, aprender e criar um espaço cada vez mais completo, com funcionalidades úteis e um design moderno. Obrigado por fazeres parte deste projeto e por acompanhar a nossa jornada. Esperamos que o Rabbit Head seja um lugar onde encontras conteúdo que te inspira, informa e diverte.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
