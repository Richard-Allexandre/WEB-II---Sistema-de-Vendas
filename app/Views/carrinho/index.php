<div class="page-head"><h1>Carrinho de vendas</h1></div>

<div class="pdv-grid">
    <!-- Catálogo de produtos -->
    <section class="card">
        <h2>Produtos</h2>
        <form class="filters" method="get" action="<?= e(url('/carrinho')) ?>">
            <input type="search" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome, código ou categoria">
            <button class="btn" type="submit">Buscar</button>
            <?php if ($busca !== ''): ?><a class="btn btn-link" href="<?= e(url('/carrinho')) ?>">Limpar</a><?php endif; ?>
        </form>
        <table>
            <thead><tr><th>Produto</th><th class="num">Preço</th><th class="num">Estoque</th><th>Adicionar</th></tr></thead>
            <tbody>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?php if ($p->imagem): ?><img class="thumb" style="float:left;margin-right:.5rem" src="<?= e(url('/imagens/' . $p->imagem)) ?>" alt=""><?php endif; ?><?= e($p->nome) ?><br><small><?= e($p->cod) ?></small></td>
                    <td class="num"><?= e(money($p->precoUnitario)) ?></td>
                    <td class="num"><?= e($p->quantidade) ?></td>
                    <td>
                        <?php if ($p->quantidade > 0): ?>
                            <form class="inline" method="post" action="<?= e(url('/carrinho/adicionar')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="produto_id" value="<?= e($p->id) ?>">
                                <input class="qty" type="number" name="quantidade" value="1" min="1" max="<?= e($p->quantidade) ?>">
                                <button class="btn btn-sm btn-primary" type="submit">+</button>
                            </form>
                        <?php else: ?>
                            <span class="badge badge-off">Sem estoque</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Carrinho -->
    <section class="card">
        <h2>Carrinho</h2>

        <?php if (!$itens): ?>
            <p class="empty">O carrinho está vazio. Adicione produtos ao lado.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Item</th><th>Qtd</th><th class="num">Subtotal</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($itens as $i): ?>
                    <tr>
                        <td><?= e($i->nome) ?><br><small><?= e(money($i->precoUnitario)) ?> un.</small></td>
                        <td>
                            <form class="inline" method="post" action="<?= e(url("/carrinho/{$i->produtoId}/atualizar")) ?>">
                                <?= csrf_field() ?>
                                <input class="qty" type="number" name="quantidade" value="<?= e($i->quantidade) ?>" min="0">
                                <button class="btn btn-sm" type="submit" title="Atualizar quantidade">↻</button>
                            </form>
                        </td>
                        <td class="num"><?= e(money($i->subtotal())) ?></td>
                        <td>
                            <form class="inline" method="post" action="<?= e(url("/carrinho/{$i->produtoId}/remover")) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-danger" type="submit" title="Remover">✕</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th colspan="2">Subtotal</th><th class="num"><?= e(money($subtotal)) ?></th><th></th></tr></tfoot>
            </table>

            <form class="inline" method="post" action="<?= e(url('/carrinho/limpar')) ?>"
                  onsubmit="return confirm('Esvaziar o carrinho?');">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-link" type="submit">Esvaziar carrinho</button>
            </form>

            <hr>
            <h3>Finalizar venda</h3>
            <form method="post" action="<?= e(url('/carrinho/finalizar')) ?>" class="stack">
                <?= csrf_field() ?>
                <label>Cliente
                    <select name="cliente_id">
                        <option value="0">Consumidor (sem cadastro)</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= e($c->id) ?>"><?= e($c->nome) ?> — <?= e($c->cpfFormatado()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Forma de pagamento
                    <select name="forma_pagamento" required>
                        <?php foreach ($formas as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="row">
                    <label>Desconto
                        <select name="tipo_desconto">
                            <?php foreach ($tiposDesconto as $key => $label): ?>
                                <option value="<?= e($key) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Valor
                        <input type="text" name="valor_desconto" value="0" inputmode="decimal">
                    </label>
                </div>
                <button class="btn btn-primary btn-lg" type="submit">Finalizar venda</button>
            </form>
        <?php endif; ?>
    </section>
</div>
