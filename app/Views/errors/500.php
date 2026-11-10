<div class="card">
    <h1>Erro interno</h1>
    <p>Algo deu errado. Verifique se o banco de dados está configurado (veja o README).</p>
    <?php if (!empty($message)): ?>
        <pre class="debug"><?= e($message) ?></pre>
    <?php endif; ?>
</div>
