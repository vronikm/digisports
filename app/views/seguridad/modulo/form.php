<?php
/**
 * DigiSports Seguridad - Formulario de Módulo
 */

$modulo = $modulo ?? null;
$iconos = $iconos ?? [];
$colores = $colores ?? [];
$esEdicion = !empty($modulo);
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = ($esEdicion ? 'Editar' : 'Nuevo') . ' Módulo';
$headerSubtitle = $esEdicion ? 'Modificar configuración del módulo' : 'Registrar un nuevo módulo en el sistema';
$headerIcon     = 'fas fa-puzzle-piece';
$headerButtons  = [
    ['url' => url('seguridad', 'modulo', 'index'), 'label' => 'Volver a Módulos', 'icon' => 'fas fa-arrow-left', 'solid' => false],
];
include __DIR__ . '/../partials/header.php';
?>
        <form method="POST" id="formModulo" action="<?= $esEdicion ? url('seguridad', 'modulo', 'update') : url('seguridad', 'modulo', 'store') ?>">
            <?php if ($esEdicion): ?>
            <input type="hidden" name="mod_id" value="<?= $modulo['mod_id'] ?>">
            <?php endif; ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Información del Módulo</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                             <label>Código <span class="text-danger">*</span></label>
                                             <input type="text" name="mod_codigo" class="form-control" required
                                                 value="<?= htmlspecialchars($modulo['mod_codigo'] ?? '') ?>"
                                                 style="text-transform: uppercase;">
                                             <small class="text-muted">Identificador único (ej: FUTBOL, STORE)</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                             <label>Nombre <span class="text-danger">*</span></label>
                                             <input type="text" name="mod_nombre" class="form-control" required
                                                 value="<?= htmlspecialchars($modulo['mod_nombre'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="mod_descripcion" class="form-control" rows="3"><?= htmlspecialchars($modulo['mod_descripcion'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                             <label>URL Externa</label>
                                             <input type="text" name="mod_url_externa" class="form-control"
                                                 value="<?= htmlspecialchars($modulo['mod_url_externa'] ?? '') ?>"
                                                 placeholder="URL si es módulo externo">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                             <label>Orden</label>
                                             <input type="number" name="mod_orden" class="form-control"
                                                 value="<?= $modulo['mod_orden'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="mod_activo" class="form-control">
                                            <option value="1" <?= ($modulo['mod_activo'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                                            <option value="0" <?= ($modulo['mod_activo'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sistema externo -->
                            <div class="card card-secondary card-outline mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">Sistema Externo (Legacy)</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch">
                                                       <input type="checkbox" class="custom-control-input" id="mod_es_externo" name="mod_es_externo"
                                                           <?= ($modulo['mod_es_externo'] ?? 0) == 1 ? 'checked' : '' ?>>
                                                       <label class="custom-control-label" for="mod_es_externo">Es Sistema Externo</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                      <label>URL Externa</label>
                                                      <input type="text" name="mod_url_externa_legacy" class="form-control"
                                                          value="<?= htmlspecialchars($modulo['mod_url_externa'] ?? '') ?>"
                                                          placeholder="URL del sistema externo">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                              <input type="checkbox" class="custom-control-input" id="mod_requiere_licencia" name="mod_requiere_licencia"
                                                  <?= ($modulo['mod_requiere_licencia'] ?? 1) == 1 ? 'checked' : '' ?>>
                                              <label class="custom-control-label" for="mod_requiere_licencia">Requiere Licencia</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Vista previa -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Vista Previa</h3>
                        </div>
                        <div class="card-body text-center" id="preview-card">
                            <div class="mb-3">
                                <i class="fas <?= $modulo['mod_icono'] ?? 'fa-puzzle-piece' ?> fa-5x" id="preview-icon"
                                   style="color: <?= $modulo['mod_color_fondo'] ?? '#007bff' ?>;"></i>
                            </div>
                            <h4 id="preview-nombre"><?= htmlspecialchars($modulo['mod_nombre'] ?? 'Nombre del Módulo') ?></h4>
                            <span class="badge badge-light" id="preview-codigo"><?= $modulo['mod_codigo'] ?? 'CODIGO' ?></span>
                        </div>
                    </div>
                    
                    <!-- Icono -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-icons mr-2"></i>Icono</h3>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="mod_icono" id="icono-input" value="<?= $modulo['mod_icono'] ?? 'fa-puzzle-piece' ?>">
                            <div class="icon-selector" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($iconos as $categoria => $icons): ?>
                                <small class="text-muted d-block mt-2 mb-1"><?= $categoria ?></small>
                                <?php foreach ($icons as $icon => $nombre): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn <?= ($modulo['mod_icono'] ?? 'fa-puzzle-piece') == $icon ? 'active' : '' ?>" 
                                        data-icon="<?= $icon ?>" title="<?= $nombre ?>">
                                    <i class="fas <?= $icon ?>"></i>
                                </button>
                                <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Color -->
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-palette mr-2"></i>Color</h3>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="mod_color_fondo" id="color-input" value="<?= $modulo['mod_color_fondo'] ?? '#007bff' ?>">
                            <div class="color-selector">
                                <?php foreach ($colores as $hex => $nombre): ?>
                                <button type="button" class="btn m-1 color-btn <?= ($modulo['mod_color_fondo'] ?? '#007bff') == $hex ? 'active' : '' ?>"
                                        data-color="<?= $hex ?>" title="<?= $nombre ?>"
                                        style="background: <?= $hex ?>; width: 40px; height: 40px; border-radius: 50%;">
                                    <?php if (($modulo['mod_color_fondo'] ?? '#007bff') == $hex): ?>
                                    <i class="fas fa-check text-white"></i>
                                    <?php endif; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-2">
                                    <input type="color" id="color-custom" class="form-control form-control-sm" 
                                        value="<?= $modulo['mod_color_fondo'] ?? '#007bff' ?>" style="width: 100px;">
                                <small class="text-muted">Color personalizado</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Acciones -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>
                                <?= $esEdicion ? 'Actualizar' : 'Crear' ?> Módulo
                            </button>
                            <a href="<?= url('seguridad', 'modulo', 'index') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // =============================================
    // Selector de iconos
    // =============================================
    document.querySelectorAll('.icon-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.icon-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const icon = this.dataset.icon;
            document.getElementById('icono-input').value = icon;
            document.getElementById('preview-icon').className = 'fas ' + icon + ' fa-5x';
        });
    });
    
    // =============================================
    // Selector de colores
    // =============================================
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.color-btn').forEach(b => {
                b.classList.remove('active');
                b.innerHTML = '';
            });
            this.classList.add('active');
            this.innerHTML = '<i class="fas fa-check text-white"></i>';
            const color = this.dataset.color;
            document.getElementById('color-input').value = color;
            document.getElementById('preview-icon').style.color = color;
            document.getElementById('color-custom').value = color;
        });
    });
    
    // Color personalizado
    document.getElementById('color-custom').addEventListener('input', function() {
        const color = this.value;
        document.getElementById('color-input').value = color;
        document.getElementById('preview-icon').style.color = color;
        document.querySelectorAll('.color-btn').forEach(b => {
            b.classList.remove('active');
            b.innerHTML = '';
        });
    });
    
    // Preview nombre
    document.querySelector('input[name="mod_nombre"]').addEventListener('input', function() {
        document.getElementById('preview-nombre').textContent = this.value || 'Nombre del Módulo';
    });
    
    // Preview código
    document.querySelector('input[name="mod_codigo"]').addEventListener('input', function() {
        document.getElementById('preview-codigo').textContent = this.value.toUpperCase() || 'CODIGO';
    });

    // =============================================
    // Interceptar submit → SweetAlert2 + AJAX + Toast
    // =============================================
    const form = document.getElementById('formModulo');
    const btnSubmit = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validación HTML5 nativa
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const esEdicion = <?= $esEdicion ? 'true' : 'false' ?>;
        const nombreModulo = form.querySelector('input[name="mod_nombre"]').value.trim();

        Swal.fire({
            title: esEdicion ? '¿Actualizar módulo?' : '¿Crear módulo?',
            html: esEdicion 
                ? `Se guardarán los cambios del módulo <strong>${nombreModulo}</strong>.`
                : `Se creará el nuevo módulo <strong>${nombreModulo}</strong>.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save mr-1"></i> Sí, guardar',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> No, cancelar',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-primary px-4',
                cancelButton: 'btn btn-secondary px-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarFormulario();
            }
        });
    });

    function enviarFormulario() {
        // Deshabilitar botón para evitar doble envío
        const textoOriginal = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Error de red: ' + response.status);
            return response.json();
        })
        .then(data => {
            // Toast SweetAlert2 en la esquina superior derecha
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            if (data.success) {
                Toast.fire({
                    icon: 'success',
                    title: data.message || 'Módulo guardado correctamente'
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: data.message || 'Error al guardar el módulo'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de comunicación',
                text: 'No se pudo conectar con el servidor. Intenta de nuevo.',
                confirmButtonColor: '#d33'
            });
        })
        .finally(() => {
            // Rehabilitar botón
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = textoOriginal;
        });
    }
});
</script>

<style>
.icon-btn.active {
    background: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
}
.color-btn {
    border: 3px solid transparent !important;
}
.color-btn.active {
    border-color: #333 !important;
}
</style>
