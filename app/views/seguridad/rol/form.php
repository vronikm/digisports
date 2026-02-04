<?php
/**
 * DigiSports Seguridad - Formulario de Rol
 */

$rol = $rol ?? [];
$esEdicion = !empty($rol['rol_id']);
$titulo = $esEdicion ? 'Editar Rol' : 'Nuevo Rol';

$colores = [
    '#EF4444' => 'Rojo',
    '#F97316' => 'Naranja',
    '#F59E0B' => 'Ámbar',
    '#84CC16' => 'Lima',
    '#22C55E' => 'Verde',
    '#10B981' => 'Esmeralda',
    '#14B8A6' => 'Teal',
    '#06B6D4' => 'Cyan',
    '#0EA5E9' => 'Sky',
    '#3B82F6' => 'Azul',
    '#6366F1' => 'Índigo',
    '#8B5CF6' => 'Violeta',
    '#A855F7' => 'Púrpura',
    '#D946EF' => 'Fucsia',
    '#EC4899' => 'Rosa',
    '#6B7280' => 'Gris',
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-<?= $esEdicion ? 'edit' : 'plus' ?> mr-2"></i>
                    <?= $titulo ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard', 'index') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'rol') ?>">Roles</a></li>
                    <li class="breadcrumb-item active"><?= $titulo ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= url('seguridad', 'rol', $esEdicion ? 'actualizar' : 'guardar') ?>">
            <?php if ($esEdicion): ?>
            <input type="hidden" name="rol_id" value="<?= $rol['rol_id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-tag mr-2"></i>Datos del Rol</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre del Rol <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($rol['nombre'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigo">Código</label>
                                        <input type="text" class="form-control" id="codigo" name="codigo" value="<?= htmlspecialchars($rol['codigo'] ?? '') ?>" placeholder="Auto-generado si se deja vacío">
                                        <small class="text-muted">Identificador único del rol</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe las responsabilidades de este rol..."><?= htmlspecialchars($rol['descripcion'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Color Identificador</label>
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($colores as $hex => $nombre): ?>
                                    <div class="mr-2 mb-2">
                                        <input type="radio" name="color" value="<?= $hex ?>" id="color_<?= substr($hex, 1) ?>" class="d-none" <?= ($rol['color'] ?? '#3B82F6') == $hex ? 'checked' : '' ?>>
                                        <label for="color_<?= substr($hex, 1) ?>" class="btn btn-sm color-selector" style="background-color: <?= $hex ?>; width: 40px; height: 40px; border-radius: 50%; cursor: pointer;" title="<?= $nombre ?>">
                                            <i class="fas fa-check text-white d-none"></i>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Configuración</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="nivel">Nivel de Acceso</label>
                                <select class="form-control" id="nivel" name="nivel">
                                    <option value="1" <?= ($rol['nivel'] ?? 1) == 1 ? 'selected' : '' ?>>1 - Básico</option>
                                    <option value="2" <?= ($rol['nivel'] ?? 1) == 2 ? 'selected' : '' ?>>2 - Intermedio</option>
                                    <option value="3" <?= ($rol['nivel'] ?? 1) == 3 ? 'selected' : '' ?>>3 - Avanzado</option>
                                    <option value="4" <?= ($rol['nivel'] ?? 1) == 4 ? 'selected' : '' ?>>4 - Administrador</option>
                                    <option value="5" <?= ($rol['nivel'] ?? 1) == 5 ? 'selected' : '' ?>>5 - Super Admin</option>
                                </select>
                                <small class="text-muted">Determina la jerarquía del rol</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="es_sistema" name="es_sistema" value="1" <?= !empty($rol['es_sistema']) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="es_sistema">Rol de Sistema</label>
                                </div>
                                <small class="text-muted">Los roles de sistema no pueden ser eliminados</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="estado" name="estado" value="A" <?= ($rol['estado'] ?? 'A') == 'A' ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="estado">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vista Previa -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Vista Previa</h3>
                        </div>
                        <div class="card-body text-center">
                            <div id="preview-icon" class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: <?= isset($rol['color']) && $rol['color'] ? $rol['color'] : '#3B82F6' ?>;">
                                <i class="fas fa-user-shield text-white fa-2x"></i>
                            </div>
                            <h5 id="preview-nombre" class="mb-1"><?= isset($rol['nombre']) && $rol['nombre'] ? htmlspecialchars($rol['nombre']) : 'Nombre del Rol' ?></h5>
                            <span class="badge" id="preview-nivel" style="background-color: <?= isset($rol['color']) && $rol['color'] ? $rol['color'] : '#3B82F6' ?>; color: white;">
                                Nivel <?= isset($rol['nivel']) && $rol['nivel'] ? $rol['nivel'] : 1 ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>
                                <?= $esEdicion ? 'Actualizar' : 'Crear' ?> Rol
                            </button>
                            <?php if ($esEdicion): ?>
                            <a href="<?= url('seguridad', 'rol', 'permisos', ['id' => $rol['rol_id']]) ?>" class="btn btn-info btn-block">
                                <i class="fas fa-key mr-1"></i> Configurar Permisos
                            </a>
                            <?php endif; ?>
                            <a href="<?= url('seguridad', 'rol') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.color-selector {
    transition: transform 0.2s, box-shadow 0.2s;
}
.color-selector:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
input[type="radio"]:checked + .color-selector {
    transform: scale(1.1);
    box-shadow: 0 0 0 3px rgba(0,0,0,0.2);
}
input[type="radio"]:checked + .color-selector .fa-check {
    display: inline-block !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nombreInput = document.getElementById('nombre');
    const nivelSelect = document.getElementById('nivel');
    const colorRadios = document.querySelectorAll('input[name="color"]');
    const previewNombre = document.getElementById('preview-nombre');
    const previewNivel = document.getElementById('preview-nivel');
    const previewIcon = document.getElementById('preview-icon');
    
    // Actualizar nombre
    nombreInput.addEventListener('input', function() {
        previewNombre.textContent = this.value || 'Nombre del Rol';
    });
    
    // Actualizar nivel
    nivelSelect.addEventListener('change', function() {
        previewNivel.textContent = 'Nivel ' + this.value;
    });
    
    // Actualizar color
    colorRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            previewIcon.style.backgroundColor = this.value;
            previewNivel.style.backgroundColor = this.value;
        });
    });
});
</script>
