<div class="page-head">
    <h1>Clientes</h1>
    <a class="btn btn-primary" href="<?= e(url('/clientes/novo')) ?>">+ Novo cliente</a>
</div>

<form class="filters" method="get" action="<?= e(url('/clientes')) ?>">
    <input type="search" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome, CPF ou e-mail">
    <button class="btn" type="submit">Buscar</button>
    <?php if ($busca !== ''): ?><a class="btn btn-link" href="<?= e(url('/clientes')) ?>">Limpar</a><?php endif; ?>
</form>

<div class="card">
    <table>
        <thead>
        <tr><th>Nome</th><th>CPF</th><th>E-mail</th><th>Telefone</th><th class="actions">Ações</th></tr>
        </thead>
        <tbody>
        <?php foreach ($clientes as $c): ?>
            <tr>
                <td><?= e($c->nome) ?></td>
                <td><?= e($c->cpfFormatado()) ?></td>
                <td><?= e($c->email ?? '-') ?></td>
                <td><?= e($c->telefone ?? '-') ?></td>
                <td class="actions">
                    <a class="btn btn-sm" href="<?= e(url("/clientes/{$c->id}/editar")) ?>">Editar</a>
                    <form class="inline" method="post" action="<?= e(url("/clientes/{$c->id}/excluir")) ?>"
                          onsubmit="return confirm('Excluir o cliente <?= e(addslashes($c->nome)) ?>?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-danger" type="submit">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$clientes): ?>
            <tr><td colspan="5" class="empty">Nenhum cliente encontrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
