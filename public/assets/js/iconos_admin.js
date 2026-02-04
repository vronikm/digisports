// JS para gestión dinámica de iconos, fuentes y colores

document.addEventListener('DOMContentLoaded', function() {
    // Agregar icono a grupo
    document.getElementById('add-icon-btn')?.addEventListener('click', function() {
        const grupo = document.getElementById('icon-group-select').value;
        const icono = document.getElementById('icon-name-input').value.trim();
        const nombre = document.getElementById('icon-label-input').value.trim();
        if (!grupo || !icono || !nombre) return alert('Completa todos los campos');
        fetch('index.php?r=iconos_admin_add', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({grupo, icono, nombre})
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.error || 'Error al guardar');
        });
    });
    // Agregar color
    document.getElementById('add-color-btn')?.addEventListener('click', function() {
        const hex = document.getElementById('color-hex-input').value.trim();
        const nombre = document.getElementById('color-label-input').value.trim();
        if (!hex || !nombre) return alert('Completa todos los campos');
        fetch('index.php?r=iconos_admin_add_color', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({hex, nombre})
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.error || 'Error al guardar');
        });
    });
});
