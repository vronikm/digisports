<?php
/**
 * DigiSports Seguridad - Formulario de Plan
 */

$plan = $plan ?? [];
$modulos = $modulos ?? [];
$modulosIncluidos = $modulosIncluidos ?? [];
$esEdicion = !empty($plan['sus_plan_id']);
$titulo = $esEdicion ? 'Editar Plan' : 'Nuevo Plan';

$colores = [
    '#22C55E' => 'Verde',
    '#3B82F6' => 'Azul',
    '#F97316' => 'Naranja',
    '#8B5CF6' => 'Púrpura',
    '#EC4899' => 'Rosa',
    '#14B8A6' => 'Teal',
    '#EF4444' => 'Rojo',
    '#F59E0B' => 'Ámbar',
];

$iconos = [
    'fas fa-star' => 'Estrella',
    'fas fa-crown' => 'Corona',
    'fas fa-gem' => 'Diamante',
    'fas fa-rocket' => 'Cohete',
    'fas fa-bolt' => 'Rayo',
    'fas fa-medal' => 'Medalla',
    'fas fa-trophy' => 'Trofeo',
    'fas fa-certificate' => 'Certificado',
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
                    <li class="breadcrumb-item"><a href="<?= url('seguridad') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'plan') ?>">Planes</a></li>
                    <li class="breadcrumb-item active"><?= $titulo ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= url('seguridad', 'plan', $esEdicion ? 'actualizar' : 'guardar') ?>">
            <?php if ($esEdicion): ?>
            <input type="hidden" name="plan_id" value="<?= $plan['sus_plan_id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Datos Básicos -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-crown mr-2"></i>Datos del Plan</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre del Plan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($plan['sus_nombre'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigo">Código</label>
                                        <input type="text" class="form-control" id="codigo" name="codigo" value="<?= htmlspecialchars($plan['sus_codigo'] ?? '') ?>" placeholder="Auto-generado si se deja vacío">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?= htmlspecialchars($plan['sus_descripcion'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="precio_mensual">Precio Mensual ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="precio_mensual" name="precio_mensual" value="<?= $plan['sus_precio_mensual'] ?? '0.00' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="precio_anual">Precio Anual ($)</label>
                                        <input type="number" step="0.01" class="form-control" id="precio_anual" name="precio_anual" value="<?= $plan['sus_precio_anual'] ?? '0.00' ?>">
                                        <small class="text-muted">Precio con descuento anual</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="periodo_prueba">Días de Prueba</label>
                                        <input type="number" class="form-control" id="periodo_prueba" name="periodo_prueba" value="0" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Límites y Recursos -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Límites y Recursos</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="usuarios_incluidos">Usuarios <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="usuarios_incluidos" name="usuarios_incluidos" value="<?= $plan['sus_usuarios_incluidos'] ?? 5 ?>" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="almacenamiento_gb">Almacenamiento (GB)</label>
                                        <input type="number" class="form-control" id="almacenamiento_gb" name="almacenamiento_gb" value="<?= $plan['sus_almacenamiento_gb'] ?? 1 ?>" min="1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sedes_incluidas">Sedes Incluidas</label>
                                        <input type="number" class="form-control" id="sedes_incluidas" name="sedes_incluidas" value="<?= $plan['sus_sedes_incluidas'] ?? 1 ?>" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Módulos Incluidos -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-puzzle-piece mr-2"></i>Módulos Incluidos</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Seleccione los módulos que incluye este plan:</p>
                            
                            <div id="lista-modulos">
                                <div class="row">
                                    <?php foreach ($modulos as $m): ?>
                                    <?php $checked = in_array($m['mod_codigo'] ?? '', $plan['modulos_array'] ?? []); ?>
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input modulo-check" id="mod_<?= $m['mod_id'] ?>" name="modulos[]" value="<?= $m['mod_codigo'] ?>" <?= $checked ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="mod_<?= $m['mod_id'] ?>">
                                                <i class="<?= $m['mod_icono'] ?? 'fas fa-cube' ?> mr-1" style="color: <?= $m['mod_color_fondo'] ?? '#6c757d' ?>"></i>
                                                <?= htmlspecialchars($m['mod_nombre'] ?? '') ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Características -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list-check mr-2"></i>Características Destacadas</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="agregarCaracteristica()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="caracteristicas-container">
                                <?php 
                                $caracteristicas = is_string($plan['sus_caracteristicas'] ?? '') ? json_decode($plan['sus_caracteristicas'], true) : ($plan['sus_caracteristicas'] ?? []);
                                if (empty($caracteristicas)) $caracteristicas = [''];
                                foreach ($caracteristicas as $i => $c): 
                                ?>
                                <div class="input-group mb-2 caracteristica-item">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check text-success"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="caracteristicas[]" value="<?= htmlspecialchars($c) ?>" placeholder="Ej: Soporte técnico incluido">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger" onclick="eliminarCaracteristica(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Apariencia -->
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-palette mr-2"></i>Apariencia</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Icono</label>
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($iconos as $ico => $nombre): ?>
                                    <div class="mr-2 mb-2">
                                        <input type="radio" name="icono" value="<?= $ico ?>" id="ico_<?= md5($ico) ?>" class="d-none" <?= 'fas fa-crown' == $ico ? 'checked' : '' ?>>
                                        <label for="ico_<?= md5($ico) ?>" class="btn btn-outline-secondary icon-selector" title="<?= $nombre ?>">
                                            <i class="<?= $ico ?>"></i>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Color</label>
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($colores as $hex => $nombre): ?>
                                    <div class="mr-2 mb-2">
                                        <input type="radio" name="color" value="<?= $hex ?>" id="col_<?= substr($hex, 1) ?>" class="d-none" <?= ($plan['sus_color'] ?? '#3B82F6') == $hex ? 'checked' : '' ?>>
                                        <label for="col_<?= substr($hex, 1) ?>" class="btn color-selector" style="background-color: <?= $hex ?>; width: 35px; height: 35px; border-radius: 50%;" title="<?= $nombre ?>">
                                            <i class="fas fa-check text-white d-none"></i>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="es_destacado" name="es_destacado" value="1" <?= ($plan['sus_es_destacado'] ?? 'N') === 'S' ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="destacado">Plan Destacado</label>
                                </div>
                                <small class="text-muted">Se mostrará con etiqueta "Popular"</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vista Previa -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Vista Previa</h3>
                        </div>
                        <div class="card-body text-center p-4" id="preview-card" style="background-color: <?= $plan['sus_color'] ?? '#3B82F6' ?>20;">
                            <i id="preview-icon" class="fas fa-crown fa-3x mb-3" style="color: <?= $plan['sus_color'] ?? '#3B82F6' ?>"></i>
                            <h4 id="preview-nombre"><?= htmlspecialchars($plan['sus_nombre'] ?? 'Nombre del Plan') ?></h4>
                            <h2 id="preview-precio" style="color: <?= $plan['sus_color'] ?? '#3B82F6' ?>">$<?= number_format($plan['sus_precio_mensual'] ?? 0, 2) ?><small>/mes</small></h2>
                            <p class="text-muted" id="preview-usuarios"><?= $plan['sus_usuarios_incluidos'] ?? 5 ?> usuarios</p>
                        </div>
                    </div>
                    
                    <!-- Estado y Botones -->
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="estado" name="estado" value="A" <?= ($plan['sus_estado'] ?? 'A') == 'A' ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="estado">Plan Activo</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>
                                <?= $esEdicion ? 'Actualizar' : 'Crear' ?> Plan
                            </button>
                            <a href="<?= url('seguridad', 'plan') ?>" class="btn btn-secondary btn-block">
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
.icon-selector {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}
input[type="radio"]:checked + .icon-selector {
    background-color: var(--primary) !important;
    color: white !important;
    border-color: var(--primary) !important;
}
.color-selector {
    cursor: pointer;
    transition: transform 0.2s;
}
.color-selector:hover {
    transform: scale(1.1);
}
input[type="radio"]:checked + .color-selector .fa-check {
    display: inline-block !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle módulos
    const todosModulos = document.getElementById('todos_modulos');
    const listaModulos = document.getElementById('lista-modulos');
    
    todosModulos.addEventListener('change', function() {
        listaModulos.style.display = this.checked ? 'none' : 'block';
    });
    
    // Vista previa en tiempo real
    const nombre = document.getElementById('nombre');
    const precio = document.getElementById('precio_mensual');
    const usuarios = document.getElementById('usuarios_incluidos');
    const iconoRadios = document.querySelectorAll('input[name="icono"]');
    const colorRadios = document.querySelectorAll('input[name="color"]');
    
    const previewNombre = document.getElementById('preview-nombre');
    const previewPrecio = document.getElementById('preview-precio');
    const previewUsuarios = document.getElementById('preview-usuarios');
    const previewIcon = document.getElementById('preview-icon');
    const previewCard = document.getElementById('preview-card');
    
    nombre.addEventListener('input', () => previewNombre.textContent = nombre.value || 'Nombre del Plan');
    precio.addEventListener('input', () => previewPrecio.innerHTML = '$' + parseFloat(precio.value || 0).toFixed(2) + '<small>/mes</small>');
    usuarios.addEventListener('input', () => previewUsuarios.textContent = usuarios.value + ' usuarios');
    
    iconoRadios.forEach(r => r.addEventListener('change', function() {
        previewIcon.className = this.value + ' fa-3x mb-3';
    }));
    
    colorRadios.forEach(r => r.addEventListener('change', function() {
        previewIcon.style.color = this.value;
        previewPrecio.style.color = this.value;
        previewCard.style.backgroundColor = this.value + '20';
    }));
});

function agregarCaracteristica() {
    const container = document.getElementById('caracteristicas-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2 caracteristica-item';
    div.innerHTML = `
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-check text-success"></i></span>
        </div>
        <input type="text" class="form-control" name="caracteristicas[]" placeholder="Nueva característica">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="eliminarCaracteristica(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
}

function eliminarCaracteristica(btn) {
    const items = document.querySelectorAll('.caracteristica-item');
    if (items.length > 1) {
        btn.closest('.caracteristica-item').remove();
    }
}
</script>
