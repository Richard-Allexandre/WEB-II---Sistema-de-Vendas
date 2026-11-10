<div class="page-head">
    <h1>Venda #<?= e($venda->id) ?>
        <span class="badge <?= $venda->finalizada() ? 'badge-ok' : 'badge-off' ?>"><?= e($venda->status) ?></span>
    </h1>
    <a class="btn" href="<?= e(url('/relatorios')) ?>">← Voltar aos relatórios</a>
</div>

<div class="two-col">
    <section class="card">
        <h2>Itens</h2>
        <table>
            <thead><tr><th>Produto</th><th class="num">Preço</th><th class="num">Qtd</th><th class="num">Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($venda->itens as $i): ?>
                <tr>
                    <td><?= e($i->nome) ?></td>
                    <td class="num"><?= e(money($i->precoUnitario)) ?></td>
                    <td class="num"><?= e($i->quantidade) ?></td>
                    <td class="num"><?= e(money($i->subtotal())) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr><th colspan="3">Subtotal</th><th class="num"><?= e(money($venda->subtotal)) ?></th></tr>
            <tr><th colspan="3">Desconto</th><th class="num">− <?= e(money($venda->desconto)) ?></th></tr>
            <tr><th colspan="3">Total</th><th class="num"><?= e(money($venda->total)) ?></th></tr>
            </tfoot>
        </table>
        <p><small>Registrada em <?= e(date_br($venda->criadoEm)) ?></small></p>
    </section>

    <section class="card">
        <h2>Dados da venda</h2>
        <?php if ($venda->finalizada()): ?>
            <form method="post" action="<?= e(url("/relatorios/{$venda->id}/atualizar")) ?>" class="stack">
                <?= csrf_field() ?>
                <label>Cliente
                    <select name="cliente_id">
                        <option value="0">Consumidor (sem cadastro)</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= e($c->id) ?>" <?= $venda->clienteId === $c->id ? 'selected' : '' ?>><?= e($c->nome) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Forma de pagamento
                    <select name="forma_pagamento">
                        <?php foreach ($formas as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $venda->formaPagamento === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Salvar alterações</button>
            </form>
            <hr>
            <form method="post" action="<?= e(url("/relatorios/{$venda->id}/cancelar")) ?>"
                  onsubmit="return confirm('Cancelar esta venda? Os itens voltarão ao estoque.');">
                <?= csrf_field() ?>
                <button class="btn btn-danger" type="submit">Cancelar venda</button>
            </form>
        <?php else: ?>
            <p><strong>Cliente:</strong> <?= e($venda->clienteNome ?? 'Consumidor') ?></p>
            <p><strong>Pagamento:</strong> <?= e($venda->formaPagamentoLabel()) ?></p>
            <hr>
            <form method="post" action="<?= e(url("/relatorios/{$venda->id}/excluir")) ?>"
                  onsubmit="return confirm('Excluir definitivamente esta venda?');">
                <?= csrf_field() ?>
                <button class="btn btn-danger" type="submit">Excluir venda</button>
            </form>
        <?php endif; ?>
    </section>
</div>
