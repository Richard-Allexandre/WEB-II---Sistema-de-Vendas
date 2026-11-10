<div class="page-head">
    <h1>Relatórios de vendas</h1>
    <a class="btn" href="<?= e(url('/relatorios/exportar?' . http_build_query(array_filter($filtros)))) ?>">⬇ Exportar CSV</a>
</div>

<form class="filters card" method="get" action="<?= e(url('/relatorios')) ?>">
    <label>De <input type="date" name="de" value="<?= e($filtros['de']) ?>"></label>
    <label>Até <input type="date" name="ate" value="<?= e($filtros['ate']) ?>"></label>
    <label>Cliente
        <select name="cliente_id">
            <option value="">Todos</option>
            <?php foreach ($clientes as $c): ?>
                <option value="<?= e($c->id) ?>" <?= (string) $filtros['cliente_id'] === (string) $c->id ? 'selected' : '' ?>><?= e($c->nome) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Pagamento
        <select name="forma_pagamento">
            <option value="">Todos</option>
            <?php foreach ($formas as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filtros['forma_pagamento'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Status
        <select name="status">
            <option value="">Todos</option>
            <option value="finalizada" <?= $filtros['status'] === 'finalizada' ? 'selected' : '' ?>>Finalizadas</option>
            <option value="cancelada" <?= $filtros['status'] === 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
        </select>
    </label>
    <button class="btn btn-primary" type="submit">Filtrar</button>
    <a class="btn btn-link" href="<?= e(url('/relatorios')) ?>">Limpar</a>
</form>

<div class="kpis">
    <div class="kpi"><span>Faturamento</span><strong><?= e(money($resumo['faturamento'])) ?></strong></div>
    <div class="kpi"><span>Vendas finalizadas</span><strong><?= e($resumo['qtd']) ?></strong></div>
    <div class="kpi"><span>Ticket médio</span><strong><?= e(money($resumo['ticket_medio'])) ?></strong></div>
    <div class="kpi"><span>Descontos concedidos</span><strong><?= e(money($resumo['descontos'])) ?></strong></div>
    <div class="kpi"><span>Canceladas</span><strong><?= e($resumo['canceladas']) ?></strong></div>
</div>

<div class="two-col">
    <section class="card">
        <h2>Faturamento por dia</h2>
        <table>
            <thead><tr><th>Dia</th><th class="num">Vendas</th><th class="num">Total</th></tr></thead>
            <tbody>
            <?php foreach ($porDia as $d): ?>
                <tr><td><?= e(date('d/m/Y', strtotime($d['dia']))) ?></td><td class="num"><?= e($d['qtd']) ?></td><td class="num"><?= e(money($d['total'])) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$porDia): ?><tr><td colspan="3" class="empty">Sem dados no período.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Produtos mais vendidos</h2>
        <table>
            <thead><tr><th>Produto</th><th class="num">Qtd</th><th class="num">Total</th></tr></thead>
            <tbody>
            <?php foreach ($top as $t): ?>
                <tr><td><?= e($t['nome']) ?></td><td class="num"><?= e($t['qtd']) ?></td><td class="num"><?= e(money($t['total'])) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$top): ?><tr><td colspan="3" class="empty">Sem dados no período.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<section class="card">
    <h2>Vendas</h2>
    <table>
        <thead>
        <tr><th>#</th><th>Data</th><th>Cliente</th><th>Pagamento</th><th class="num">Desconto</th><th class="num">Total</th><th>Status</th><th class="actions">Ações</th></tr>
        </thead>
        <tbody>
        <?php foreach ($vendas as $v): ?>
            <tr>
                <td>#<?= e($v->id) ?></td>
                <td><?= e(date_br($v->criadoEm)) ?></td>
                <td><?= e($v->clienteNome ?? 'Consumidor') ?></td>
                <td><?= e($v->formaPagamentoLabel()) ?></td>
                <td class="num"><?= e(money($v->desconto)) ?></td>
                <td class="num"><?= e(money($v->total)) ?></td>
                <td><span class="badge <?= $v->finalizada() ? 'badge-ok' : 'badge-off' ?>"><?= e($v->status) ?></span></td>
                <td class="actions"><a class="btn btn-sm" href="<?= e(url("/relatorios/{$v->id}")) ?>">Detalhes</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$vendas): ?><tr><td colspan="8" class="empty">Nenhuma venda encontrada.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
