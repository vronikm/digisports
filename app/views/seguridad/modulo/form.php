<?php
/**
 * DigiSports Seguridad - Formulario de Módulo
 */

$modulo = $modulo ?? null;
$iconos = $iconos ?? [];
$colores = $colores ?? [];
$esEdicion = !empty($modulo);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-puzzle-piece mr-2"></i>
                    <?= $esEdicion ? 'Editar' : 'Nuevo' ?> Módulo
                </h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST">
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
                                        <input type="text" name="codigo" class="form-control" required
                                               value="<?= htmlspecialchars($modulo['codigo'] ?? '') ?>"
                                               style="text-transform: uppercase;">
                                        <small class="text-muted">Identificador único (ej: FUTBOL, STORE)</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Nombre <span class="text-danger">*</span></label>
                                        <input type="text" name="nombre" class="form-control" required
                                               value="<?= htmlspecialchars($modulo['nombre'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($modulo['descripcion'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>URL Base</label>
                                        <input type="text" name="url_base" class="form-control"
                                               value="<?= htmlspecialchars($modulo['url_base'] ?? '') ?>"
                                               placeholder="/modulo/">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Orden</label>
                                        <input type="number" name="orden_visualizacion" class="form-control"
                                               value="<?= $modulo['orden_visualizacion'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="A" <?= ($modulo['estado'] ?? 'A') == 'A' ? 'selected' : '' ?>>Activo</option>
                                            <option value="I" <?= ($modulo['estado'] ?? '') == 'I' ? 'selected' : '' ?>>Inactivo</option>
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
                                                    <input type="checkbox" class="custom-control-input" id="es_externo" name="es_externo"
                                                           <?= ($modulo['es_externo'] ?? 'N') == 'S' ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="es_externo">Es Sistema Externo</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Base de Datos Externa</label>
                                                <input type="text" name="base_datos_externa" class="form-control"
                                                       value="<?= htmlspecialchars($modulo['base_datos_externa'] ?? '') ?>"
                                                       placeholder="digisports_legacy">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="requiere_suscripcion" name="requiere_suscripcion"
                                                   <?= ($modulo['requiere_suscripcion'] ?? 'S') == 'S' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="requiere_suscripcion">Requiere Suscripción</label>
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
                                <i class="fas <?= $modulo['icono'] ?? 'fa-puzzle-piece' ?> fa-5x" id="preview-icon"
                                   style="color: <?= $modulo['color'] ?? '#007bff' ?>;"></i>
                            </div>
                            <h4 id="preview-nombre"><?= htmlspecialchars($modulo['nombre'] ?? 'Nombre del Módulo') ?></h4>
                            <span class="badge badge-light" id="preview-codigo"><?= $modulo['codigo'] ?? 'CODIGO' ?></span>
                        </div>
                    </div>
                    
                    <!-- Icono -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-icons mr-2"></i>Icono</h3>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="icono" id="icono-input" value="<?= $modulo['icono'] ?? 'fa-puzzle-piece' ?>">
                            <div class="icon-selector" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($iconos as $categoria => $icons): ?>
                                <small class="text-muted d-block mt-2 mb-1"><?= $categoria ?></small>
                                <?php foreach ($icons as $icon => $nombre): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn <?= ($modulo['icono'] ?? 'fa-puzzle-piece') == $icon ? 'active' : '' ?>" 
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
                            <input type="hidden" name="color" id="color-input" value="<?= $modulo['color'] ?? '#007bff' ?>">
                            <div class="color-selector">
                                <?php foreach ($colores as $hex => $nombre): ?>
                                <button type="button" class="btn m-1 color-btn <?= ($modulo['color'] ?? '#007bff') == $hex ? 'active' : '' ?>"
                                        data-color="<?= $hex ?>" title="<?= $nombre ?>"
                                        style="background: <?= $hex ?>; width: 40px; height: 40px; border-radius: 50%;">
                                    <?php if (($modulo['color'] ?? '#007bff') == $hex): ?>
                                    <i class="fas fa-check text-white"></i>
                                    <?php endif; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-2">
                                <input type="color" id="color-custom" class="form-control form-control-sm" 
                                       value="<?= $modulo['color'] ?? '#007bff' ?>" style="width: 100px;">
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
    // Selector de iconos
    document.querySelectorAll('.icon-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.icon-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const icon = this.dataset.icon;
            document.getElementById('icono-input').value = icon;
            document.getElementById('preview-icon').className = 'fas ' + icon + ' fa-5x';
        });
    });
    
    // Selector de colores
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
    document.querySelector('input[name="nombre"]').addEventListener('input', function() {
        document.getElementById('preview-nombre').textContent = this.value || 'Nombre del Módulo';
    });
    
    // Preview código
    document.querySelector('input[name="codigo"]').addEventListener('input', function() {
        document.getElementById('preview-codigo').textContent = this.value.toUpperCase() || 'CODIGO';
    });
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
