<div class="page-head">
    <h1>Produtos</h1>
    <a class="btn btn-primary" href="<?= e(url('/produtos/novo')) ?>">+ Novo produto</a>
</div>

<form class="filters" method="get" action="<?= e(url('/produtos')) ?>">
    <input type="search" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome, código ou categoria">
    <button class="btn" type="submit">Buscar</button>
    <?php if ($busca !== ''): ?><a class="btn btn-link" href="<?= e(url('/produtos')) ?>">Limpar</a><?php endif; ?>
</form>

<div class="card">
    <table>
        <thead>
        <tr><th></th><th>Cód.</th><th>Produto</th><th>Categoria</th><th>Validade</th><th class="num">Qtd</th><th class="num">Unitário</th><th class="num">Pacote</th><th class="actions">Ações</th></tr>
        </thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
            <tr>
                <td><?php if ($p->imagem): ?><img class="thumb" src="<?= e(url('/imagens/' . $p->imagem)) ?>" alt=""><?php endif; ?></td>
                <td><?= e($p->cod) ?></td>
                <td><?= e($p->nome) ?></td>
                <td><?= e($p->categoria) ?></td>
                <td><?= e($p->validade ? date('d/m/Y', strtotime($p->validade)) : '-') ?></td>
                <td class="num"><?= e($p->quantidade) ?></td>
                <td class="num"><?= e(money($p->precoUnitario)) ?></td>
                <td class="num"><?= e(money($p->precoPacote)) ?></td>
                <td class="actions">
                    <a class="btn btn-sm" href="<?= e(url("/produtos/{$p->id}/editar")) ?>">Editar</a>
                    <form class="inline" method="post" action="<?= e(url("/produtos/{$p->id}/excluir")) ?>"
                          onsubmit="return confirm('Excluir o produto <?= e(addslashes($p->nome)) ?>?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-danger" type="submit">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$produtos): ?><tr><td colspan="9" class="empty">Nenhum produto encontrado.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
