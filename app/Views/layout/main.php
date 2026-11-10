<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'PDV') ?> · PDV MVC</title>
    <link rel="stylesheet" href="<?= e(url('/assets/style.css')) ?>">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="<?= e(url('/carrinho')) ?>">PDV MVC</a>
        <nav>
            <a href="<?= e(url('/carrinho')) ?>">Carrinho de vendas</a>
            <a href="<?= e(url('/produtos')) ?>">Produtos</a>
            <a href="<?= e(url('/clientes')) ?>">Clientes</a>
            <a href="<?= e(url('/relatorios')) ?>">Relatórios</a>
        </nav>
    </div>
</header>

<main class="container">
    <?php foreach (flash_pull() as $msg): ?>
        <div class="alert alert-<?= e($msg['type']) ?>"><?= e($msg['message']) ?></div>
    <?php endforeach; ?>

    <?= $content ?>
</main>
</body>
</html>
