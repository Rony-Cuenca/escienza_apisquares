<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <h2>Lista de Clientes</h2>
    <ul>
        <?php foreach ($clientes as $cliente): ?>
            <li><?= $cliente['nombre'] ?> - $<?= $cliente['numero_doc'] ?></li>
        <?php endforeach; ?>
    </ul>
</div>