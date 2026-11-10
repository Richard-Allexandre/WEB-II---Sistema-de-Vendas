<div class="page-head"><h1><?= e($title) ?></h1></div>

<div class="card">
    <form method="post" action="<?= e(url($action)) ?>" class="form-grid" novalidate>
        <?= csrf_field() ?>

        <label>Nome *
            <input type="text" name="nome" value="<?= e($cliente->nome) ?>" required>
            <?php if (isset($erros['nome'])): ?><span class="error"><?= e($erros['nome']) ?></span><?php endif; ?>
        </label>

        <label>CPF *
            <input type="text" name="cpf" value="<?= e($cliente->cpf) ?>" placeholder="000.000.000-00" inputmode="numeric" required>
            <?php if (isset($erros['cpf'])): ?><span class="error"><?= e($erros['cpf']) ?></span><?php endif; ?>
        </label>

        <label>E-mail
            <input type="email" name="email" value="<?= e($cliente->email) ?>">
            <?php if (isset($erros['email'])): ?><span class="error"><?= e($erros['email']) ?></span><?php endif; ?>
        </label>

        <label>Telefone (com DDD)
            <input type="text" name="telefone" value="<?= e($cliente->telefone) ?>" inputmode="tel">
            <?php if (isset($erros['telefone'])): ?><span class="error"><?= e($erros['telefone']) ?></span><?php endif; ?>
        </label>

        <label class="span-2">Endereço
            <input type="text" name="endereco" value="<?= e($cliente->endereco) ?>">
        </label>

        <div class="span-2 form-actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn" href="<?= e(url('/clientes')) ?>">Cancelar</a>
        </div>
    </form>
</div>
