<h2>Lista de Usuarios</h2>
<ul>
    <?php foreach ($usuarios as $usuario): ?>
        <li><?= $usuario['usuario'] ?> - <?= $usuario['contraseña'] ?></li>
    <?php endforeach; ?>
</ul>
