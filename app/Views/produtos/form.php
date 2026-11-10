<div class="page-head"><h1><?= e($title) ?></h1></div>

<div class="card">
    <form method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" class="form-grid" novalidate>
        <?= csrf_field() ?>

        <label>Código *
            <input type="text" name="cod" value="<?= e($produto->cod) ?>" required>
            <?php if (isset($erros['cod'])): ?><span class="error"><?= e($erros['cod']) ?></span><?php endif; ?>
        </label>

        <label>Nome *
            <input type="text" name="nome" value="<?= e($produto->nome) ?>" required>
            <?php if (isset($erros['nome'])): ?><span class="error"><?= e($erros['nome']) ?></span><?php endif; ?>
        </label>

        <label>Categoria *
            <input type="text" name="categoria" value="<?= e($produto->categoria) ?>" required>
            <?php if (isset($erros['categoria'])): ?><span class="error"><?= e($erros['categoria']) ?></span><?php endif; ?>
        </label>

        <label>Validade *
            <input type="date" name="validade" value="<?= e($produto->validade) ?>" required>
            <?php if (isset($erros['validade'])): ?><span class="error"><?= e($erros['validade']) ?></span><?php endif; ?>
        </label>

        <label>Quantidade em estoque *
            <input type="number" name="quantidade" min="0" value="<?= e($produto->quantidade) ?>" required>
            <?php if (isset($erros['quantidade'])): ?><span class="error"><?= e($erros['quantidade']) ?></span><?php endif; ?>
        </label>

        <label>Preço unitário *
            <input type="text" name="preco_unitario" inputmode="decimal" value="<?= e(number_format($produto->precoUnitario, 2, '.', '')) ?>" required>
            <?php if (isset($erros['preco_unitario'])): ?><span class="error"><?= e($erros['preco_unitario']) ?></span><?php endif; ?>
        </label>

        <label>Preço do pacote
            <input type="text" name="preco_pacote" inputmode="decimal" value="<?= e(number_format($produto->precoPacote, 2, '.', '')) ?>">
            <?php if (isset($erros['preco_pacote'])): ?><span class="error"><?= e($erros['preco_pacote']) ?></span><?php endif; ?>
        </label>

        <label>Imagem (JPG, PNG, WEBP ou GIF até 2 MB)
            <input type="file" name="imagem" accept="image/*">
            <?php if (isset($erros['imagem'])): ?><span class="error"><?= e($erros['imagem']) ?></span><?php endif; ?>
            <?php if ($produto->imagem): ?><img class="thumb" src="<?= e(url('/imagens/' . $produto->imagem)) ?>" alt="Imagem atual"><?php endif; ?>
        </label>

        <div class="span-2 form-actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn" href="<?= e(url('/produtos')) ?>">Cancelar</a>
        </div>
    </form>
</div>
