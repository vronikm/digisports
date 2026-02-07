<?php
/**
 * DigiSports Seguridad - Galería de Iconos y Colores
 */

$modulos = $modulos ?? [];
$iconos = $iconos ?? [];
$colores = $colores ?? [];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-icons mr-2"></i>
                    Iconos y Colores
                </h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
        <!-- Sección de administración de iconos, colores y fuentes -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-cogs mr-2"></i> Administración de Iconos, Colores y Fuentes
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Agregar Icono a Grupo</h5>
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-4">
                                <label>Grupo</label>
                                <select id="icon-group-select" class="form-control">
                                    <?php foreach(array_keys($iconos) as $grupo): ?>
                                        <option value="<?= $grupo ?>"><?= $grupo ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Clase Icono (FontAwesome)</label>
                                <input type="text" id="icon-name-input" class="form-control" placeholder="fa-futbol">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Nombre</label>
                                <input type="text" id="icon-label-input" class="form-control" placeholder="Ej: Fútbol">
                            </div>
                            <div class="form-group col-md-1">
                                <button id="add-icon-btn" class="btn btn-success" type="button"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Agregar Color</h5>
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-5">
                                <label>HEX</label>
                                <input type="text" id="color-hex-input" class="form-control" placeholder="#22C55E">
                            </div>
                            <div class="form-group col-md-5">
                                <label>Nombre</label>
                                <input type="text" id="color-label-input" class="form-control" placeholder="Verde">
                            </div>
                            <div class="form-group col-md-2">
                                <button id="add-color-btn" class="btn btn-success" type="button"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>Fuentes de Iconos</h5>
                        <p>Actualmente se usa <b>FontAwesome</b> (puedes agregar más fuentes en el código si es necesario).</p>
                    </div>
                </div>
            </div>
        </div>
    <div class="container-fluid">
        <!-- Módulos actuales -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-th-large mr-2"></i>
                    Módulos Actuales - Vista Rápida para Editar
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($modulos as $m): ?>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                        <div class="card h-100 modulo-card" data-id="<?= $m['mod_id'] ?>" style="cursor: pointer; border: 2px solid <?= $m['mod_color'] ?>;">
                            <div class="card-body text-center p-2">
                                <i class="fas <?= $m['mod_icono'] ?> fa-2x mb-2" style="color: <?= $m['mod_color'] ?>;"></i>
                                <div class="small font-weight-bold"><?= htmlspecialchars($m['mod_nombre']) ?></div>
                                <div class="badge badge-light"><?= $m['mod_codigo'] ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Galería de Iconos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-icons mr-2"></i>
                    Galería de Iconos Disponibles
                </h3>
            </div>
            <div class="card-body">
                <?php foreach ($iconos as $categoria => $icons): ?>
                <h5 class="mt-3 mb-2">
                    <span class="badge badge-primary"><?= $categoria ?></span>
                </h5>
                <div class="row">
                    <?php foreach ($icons as $icon => $nombre): ?>
                    <div class="col-auto mb-2">
                        <div class="card text-center p-2 position-relative" style="width: 90px;" title="<?= $icon ?>">
                            <i class="fas <?= $icon ?> fa-2x text-primary mb-1"></i>
                            <small class="text-muted text-truncate d-block mb-1 icon-label" data-grupo="<?= $categoria ?>" data-icono="<?= $icon ?>"><?= $nombre ?></small>
                            <button class="btn btn-xs btn-outline-danger btn-icon-delete position-absolute" style="top:2px;right:2px;padding:0 4px;" data-grupo="<?= $categoria ?>" data-icono="<?= $icon ?>"><i class="fas fa-trash"></i></button>
                            <button class="btn btn-xs btn-outline-secondary btn-icon-edit position-absolute" style="top:2px;left:2px;padding:0 4px;" data-grupo="<?= $categoria ?>" data-icono="<?= $icon ?>" data-nombre="<?= htmlspecialchars($nombre) ?>"><i class="fas fa-edit"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Paleta de Colores -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-palette mr-2"></i>
                    Paleta de Colores
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($colores as $hex => $nombre): ?>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                        <div class="card">
                            <div style="height: 60px; background: <?= $hex ?>;"></div>
                            <div class="card-body p-2 text-center">
                                <strong><?= $nombre ?></strong>
                                <br>
                                <code><?= $hex ?></code>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Preview interactivo -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye mr-2"></i>
                    Preview Interactivo
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label>Selecciona un icono:</label>
                        <select id="preview-icon-select" class="form-control mb-3">
                            <?php foreach ($iconos as $categoria => $icons): ?>
                            <optgroup label="<?= $categoria ?>">
                                <?php foreach ($icons as $icon => $nombre): ?>
                                <option value="<?= $icon ?>"><?= $nombre ?> (<?= $icon ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                        
                        <label>Selecciona un color:</label>
                        <select id="preview-color-select" class="form-control">
                            <?php foreach ($colores as $hex => $nombre): ?>
                            <option value="<?= $hex ?>"><?= $nombre ?> (<?= $hex ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center d-flex align-items-center justify-content-center">
                            <div class="card-body">
                                <i id="preview-result-icon" class="fas fa-futbol fa-5x mb-3" style="color: #22C55E;"></i>
                                <h4>Mi Módulo</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-header">Código CSS</div>
                            <div class="card-body">
                                <pre id="preview-code" class="mb-0 text-success">.modulo-icon {
    color: #22C55E;
}

&lt;i class="fas fa-futbol"&gt;&lt;/i&gt;</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar icono -->
        <div class="modal fade" id="editIconModal" tabindex="-1" role="dialog" aria-labelledby="editIconModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editIconModalLabel">Editar nombre de icono</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <input type="text" id="editIconNameInput" class="form-control" placeholder="Nuevo nombre">
                <input type="hidden" id="editIconGrupo">
                <input type="hidden" id="editIconId">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveEditIconBtn">Guardar</button>
              </div>
            </div>
          </div>
        </div>
    </div>
</section>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Gestión dinámica de iconos y colores
// (Requiere backend para persistencia, aquí solo frontend)
</script>
<script src="/digisports/public/assets/js/iconos_admin.js"></script>
<script>
// Edición y eliminación de iconos
document.addEventListener('DOMContentLoaded', function() {
    // Eliminar icono
    document.querySelectorAll('.btn-icon-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('¿Eliminar este icono?')) return;
            fetch('index.php?r=iconos_admin_delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({grupo: this.dataset.grupo, icono: this.dataset.icono})
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.error || 'Error al eliminar');
            });
        });
    });
    // Editar icono
    document.querySelectorAll('.btn-icon-edit').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('editIconNameInput').value = this.dataset.nombre;
            document.getElementById('editIconGrupo').value = this.dataset.grupo;
            document.getElementById('editIconId').value = this.dataset.icono;
            $('#editIconModal').modal('show');
        });
    });
    document.getElementById('saveEditIconBtn').addEventListener('click', function() {
        const nombre = document.getElementById('editIconNameInput').value.trim();
        const grupo = document.getElementById('editIconGrupo').value;
        const icono = document.getElementById('editIconId').value;
        if (!nombre) return Swal.fire('Advertencia', 'Debes ingresar un nombre', 'warning');
        Swal.fire({
            title: '¿Confirmar cambio?',
            text: '¿Deseas guardar el nuevo nombre del icono?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('index.php?r=iconos_admin_edit', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({grupo, icono, nombre})
                }).then(r => r.json()).then(data => {
                    $('#editIconModal').modal('hide');
                    if (data.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Nombre de icono actualizado',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        // Recarga parcial: actualizar solo el nombre en la tarjeta
                        document.querySelectorAll('.icon-label[data-grupo="'+grupo+'"][data-icono="'+icono+'"]').forEach(el => {
                            el.textContent = nombre;
                        });
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: data.error || 'Error al editar',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                });
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {

    const iconSelect = document.getElementById('preview-icon-select');
    const colorSelect = document.getElementById('preview-color-select');
    const previewSection = document.querySelector('.preview-interactivo-row');
    const resultIcon = document.getElementById('preview-result-icon');
    const codeBlock = document.getElementById('preview-code');
    // Solo ejecutar si todos los elementos existen y hay opciones
    if (iconSelect && colorSelect && resultIcon && codeBlock && iconSelect.options.length > 0 && colorSelect.options.length > 0) {
        function updatePreview() {
            const icon = iconSelect.value;
            const color = colorSelect.value;
            resultIcon.className = 'fas ' + icon + ' fa-5x mb-3';
            resultIcon.style.color = color;
            codeBlock.innerHTML = `.modulo-icon {\n    color: ${color};\n}\n`;
        }
        iconSelect.addEventListener('change', updatePreview);
        colorSelect.addEventListener('change', updatePreview);
        updatePreview();
    } else {
        // Oculta el preview si no hay datos
        if (previewSection) previewSection.style.display = 'none';
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'No hay iconos o colores disponibles para el preview',
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Click en módulo para editar
    document.querySelectorAll('.modulo-card').forEach(card => {
        card.addEventListener('click', function() {
            const id = this.dataset.id;
            window.location.href = '<?= url('seguridad', 'modulo', 'editar') ?>&id=' + id;
        });
    });
});

// --- Preview Interactivo de Iconos y Colores ---
document.addEventListener('DOMContentLoaded', function() {
    const iconSelect = document.getElementById('preview-icon-select');
    const colorSelect = document.getElementById('preview-color-select');
    const previewSection = document.querySelector('.preview-interactivo-row');
    const resultIcon = document.getElementById('preview-result-icon');
    const codeBlock = document.getElementById('preview-code');
    // Solo ejecutar si todos los elementos existen y hay opciones
    if (iconSelect && colorSelect && resultIcon && codeBlock && iconSelect.options.length > 0 && colorSelect.options.length > 0) {
        function updatePreview() {
            const icon = iconSelect.value;
            const color = colorSelect.value;
            resultIcon.className = 'fas ' + icon + ' fa-5x mb-3';
            resultIcon.style.color = color;
            codeBlock.innerHTML = `.modulo-icon {\n    color: ${color};\n}\n`;
        }
        iconSelect.addEventListener('change', updatePreview);
        colorSelect.addEventListener('change', updatePreview);
        updatePreview();
    } else {
        // Oculta el preview si no hay datos
        if (previewSection) previewSection.style.display = 'none';
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'No hay iconos o colores disponibles para el preview',
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Click en módulo para editar
    document.querySelectorAll('.modulo-card').forEach(card => {
        card.addEventListener('click', function() {
            const id = this.dataset.id;
            window.location.href = '<?= url('seguridad', 'modulo', 'editar') ?>&id=' + id;
        });
    });
});
