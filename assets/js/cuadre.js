function update(input, displayId) {
    const displayElement = document.getElementById(displayId);
    displayElement.value = input.files[0] ? input.files[0].name : 'Ningún archivo seleccionado';
}
function abrirModal() {
    document.getElementById('modalUnirExcel').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modalUnirExcel').classList.add('hidden');
}

document.getElementById('files_unir').addEventListener('change', function (e) {
    const count = this.files.length;
    const label = document.getElementById('cantidadArchivos');
    if (count > 0) {
        label.textContent = `${count} archivo${count > 1 ? 's' : ''}`;
    } else {
        label.textContent = 'Ningún archivo seleccionado';
    }
    const files = Array.from(e.target.files);
    const container = document.getElementById('fileCardsContainer');
    const preview = document.getElementById('previewFiles');
    const btn = document.getElementById('submitBtnContainer');

    container.innerHTML = ''; // Limpiar anteriores

    if (files.length > 0) {
        preview.classList.remove('hidden');
        btn.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
        btn.classList.add('hidden');
    }

    files.forEach(file => {
        const card = document.createElement('div');
        card.className = "w-32 h-40 bg-center bg-no-repeat bg-contain bg-[url('https://cdn-icons-png.flaticon.com/512/732/732220.png')] flex flex-col justify-end items-center text-sm bg-gray-100 p-2 rounded-md shadow relative";

        const name = document.createElement('p');
        name.className = "text-xs mt-2 truncate w-full text-center";
        name.textContent = file.name;

        const size = document.createElement('span');
        size.className = "text-xs font-semibold bg-white/80 rounded px-2 py-0.5 mt-1";
        size.textContent = (file.size / (1024 * 1024)).toFixed(1) + ' MB';

        card.appendChild(size);
        card.appendChild(name);
        container.appendChild(card);
    });
});