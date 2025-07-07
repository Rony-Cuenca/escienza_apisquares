<div class="w-full px-2 md:px-10 py-10 bg-gray-200 flex-1 flex flex-col">
    <div class="w-full bg-white rounded-lg shadow-2xl shadow-gray-300/40 p-2 md:p-8">
        <!-- Cabecera -->
        <div class="flex items-center justify-between w-full pt-6 pb-6 px-6 border-b border-gray-200 mb-8">
            <span class="text-xl text-gray-800 font-semibold" style="font-family: 'Montserrat', sans-serif;">LISTA DE CLIENTES</span>
        </div>
        
        <!-- Contenido -->
        <div class="space-y-4">
            <?php foreach ($clientes as $cliente): ?>
                <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <span class="font-medium"><?= htmlspecialchars($cliente['nombre']) ?></span> - 
                    <span class="text-gray-600"><?= htmlspecialchars($cliente['numero_doc']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>