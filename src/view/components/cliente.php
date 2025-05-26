<h2>Lista de Clientes</h2>
<ul>
    <?php foreach ($clientes as $cliente): ?>
        <li><?= $cliente['nombre'] ?> - $<?= $cliente['numero_doc'] ?></li>
    <?php endforeach; ?>
</ul>
