<?php
/**
 * DigiSports Seguridad - Formulario Crear/Editar Ítem de Menú
 */
$menu        = $menu ?? null;
$modulos     = $modulos ?? [];
$padres      = $padres ?? [];
$moduloId    = $moduloId ?? null;
$modoEdicion = $modoEdicion ?? false;
$moduloColor = $moduloColor ?? $modulo_actual['color'] ?? '#F59E0B';

// Valores por defecto o del registro existente
$v = [
    'men_modulo_id'      => $menu['men_modulo_id'] ?? $moduloId ?? '',
    'men_padre_id'       => $menu['men_padre_id'] ?? '',
    'men_tipo'           => $menu['men_tipo'] ?? 'ITEM',
    'men_label'          => $menu['men_label'] ?? '',
    'men_icono'          => $menu['men_icono'] ?? '',
    'men_ruta_modulo'    => $menu['men_ruta_modulo'] ?? '',
    'men_ruta_controller'=> $menu['men_ruta_controller'] ?? '',
    'men_ruta_action'    => $menu['men_ruta_action'] ?? 'index',
    'men_url_custom'     => $menu['men_url_custom'] ?? '',
    'men_badge'          => $menu['men_badge'] ?? '',
    'men_badge_tipo'     => $menu['men_badge_tipo'] ?? '',
    'men_orden'          => $menu['men_orden'] ?? 0,
    'men_activo'         => $menu ? (int)$menu['men_activo'] : 1,
];
?>

<style>
.form-card { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; }
.form-card .card-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; }
.form-card .card-header h5 { margin: 0; font-weight: 700; font-size: 0.95rem; color: #1e293b; }
.form-section { background: #f8fafc; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; }
.form-section-title { font-size: 0.82rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem; }
.icon-preview { width: 42px; height: 42px; border-radius: 10px; background: <?= $moduloColor ?>15; color: <?= $moduloColor ?>; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.ruta-preview { background: #1e293b; color: #a5f3fc; border-radius: 8px; padding: 0.5rem 1rem; font-family: 'SFMono-Regular', monospace; font-size: 0.82rem; margin-top: 0.5rem; }
</style>

<!-- Header Premium -->
<?php include BASE_PATH . '/app/views/seguridad/partials/header.php'; ?>

<form method="POST" action="" id="menuForm">
    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <div class="form-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle mr-2" style="color:<?= $moduloColor ?>"></i>Información del Ítem</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Módulo -->
                    <div class="form-group">
                        <label class="font-weight-bold"><i class="fas fa-cube mr-1"></i> Módulo <span class="text-danger">*</span></label>
                        <select name="men_modulo_id" id="men_modulo_id" class="form-control" required>
                            <option value="">-- Seleccionar módulo --</option>
                            <?php foreach ($modulos as $mod): ?>
                            <option value="<?= $mod['mod_id'] ?>" <?= (int)$v['men_modulo_id'] === (int)$mod['mod_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mod['mod_nombre']) ?> (<?= htmlspecialchars($mod['mod_codigo']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <!-- Tipo -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-layer-group mr-1"></i> Tipo <span class="text-danger">*</span></label>
                                <select name="men_tipo" id="men_tipo" class="form-control" required>
                                    <option value="HEADER" <?= $v['men_tipo'] === 'HEADER' ? 'selected' : '' ?>>HEADER (Sección/Título)</option>
                                    <option value="ITEM" <?= $v['men_tipo'] === 'ITEM' ? 'selected' : '' ?>>ITEM (Elemento de menú)</option>
                                    <option value="SUBMENU" <?= $v['men_tipo'] === 'SUBMENU' ? 'selected' : '' ?>>SUBMENU (Sub-elemento)</option>
                                </select>
                                <small class="form-text text-muted">HEADER = título de sección · ITEM = enlace directo o padre de submenú · SUBMENU = hijo de un ITEM</small>
                            </div>
                        </div>
                        <!-- Padre -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-sitemap mr-1"></i> Padre</label>
                                <select name="men_padre_id" id="men_padre_id" class="form-control">
                                    <option value="">-- Raíz (sin padre) --</option>
                                    <?php foreach ($padres as $padre): ?>
                                    <option value="<?= $padre['men_id'] ?>" <?= (int)$v['men_padre_id'] === (int)$padre['men_id'] ? 'selected' : '' ?>>
                                        <?= $padre['men_tipo'] === 'HEADER' ? '📌 ' : '   ├─ ' ?><?= htmlspecialchars($padre['men_label']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Si es ITEM, seleccione un HEADER como padre. Si es SUBMENU, seleccione un ITEM como padre.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Label -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-tag mr-1"></i> Etiqueta <span class="text-danger">*</span></label>
                                <input type="text" name="men_label" class="form-control" value="<?= htmlspecialchars($v['men_label']) ?>" required maxlength="100" placeholder="Ej: Dashboard, Canchas, Listado...">
                            </div>
                        </div>
                        <!-- Orden -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-sort-numeric-up mr-1"></i> Orden</label>
                                <input type="number" name="men_orden" class="form-control" value="<?= (int)$v['men_orden'] ?>" min="0" max="999">
                            </div>
                        </div>
                    </div>

                    <!-- Icono -->
                    <div class="form-group" id="iconoGroup">
                        <label class="font-weight-bold"><i class="fas fa-icons mr-1"></i> Icono (FontAwesome)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="iconPreviewSpan">
                                    <i class="<?= htmlspecialchars($v['men_icono'] ?: 'fas fa-circle') ?>" id="iconPreview"></i>
                                </span>
                            </div>
                            <input type="text" name="men_icono" id="men_icono" class="form-control" value="<?= htmlspecialchars($v['men_icono']) ?>" placeholder="fas fa-tachometer-alt">
                        </div>
                        <small class="form-text text-muted">Clase CSS de FontAwesome. Ej: fas fa-users, fas fa-chart-bar</small>
                    </div>
                </div>
            </div>

            <!-- Ruta / URL -->
            <div class="form-card mb-4" id="rutaCard">
                <div class="card-header">
                    <h5><i class="fas fa-route mr-2" style="color:<?= $moduloColor ?>"></i>Ruta / Navegación</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-link mr-1"></i> Ruta Encriptada (url())</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Módulo (ruta)</label>
                                    <input type="text" name="men_ruta_modulo" id="men_ruta_modulo" class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($v['men_ruta_modulo']) ?>" placeholder="Ej: seguridad, futbol">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Controlador</label>
                                    <input type="text" name="men_ruta_controller" id="men_ruta_controller" class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($v['men_ruta_controller']) ?>" placeholder="Ej: dashboard, cancha">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Acción</label>
                                    <input type="text" name="men_ruta_action" id="men_ruta_action" class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($v['men_ruta_action']) ?>" placeholder="Ej: index, crear">
                                </div>
                            </div>
                        </div>
                        <div class="ruta-preview" id="rutaPreview">
                            url('<?= htmlspecialchars($v['men_ruta_modulo']) ?>', '<?= htmlspecialchars($v['men_ruta_controller']) ?>', '<?= htmlspecialchars($v['men_ruta_action']) ?>')
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-external-link-alt mr-1"></i> URL Personalizada (opcional)</div>
                        <input type="text" name="men_url_custom" class="form-control form-control-sm" value="<?= htmlspecialchars($v['men_url_custom']) ?>" placeholder="URL absoluta o relativa personalizada">
                        <small class="form-text text-muted">Solo si no usa la ruta encriptada. Tiene prioridad menor que la ruta.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Lateral -->
        <div class="col-lg-4">
            <!-- Estado -->
            <div class="form-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-toggle-on mr-2" style="color:<?= $moduloColor ?>"></i>Estado</h5>
                </div>
                <div class="card-body p-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="men_activo" name="men_activo" <?= $v['men_activo'] ? 'checked' : '' ?>>
                        <label class="custom-control-label font-weight-bold" for="men_activo">
                            <?= $v['men_activo'] ? 'Activo' : 'Inactivo' ?>
                        </label>
                    </div>
                    <small class="text-muted">Los ítems inactivos no se muestran en el sidebar.</small>
                </div>
            </div>

            <!-- Badge -->
            <div class="form-card mb-4" id="badgeCard">
                <div class="card-header">
                    <h5><i class="fas fa-certificate mr-2" style="color:<?= $moduloColor ?>"></i>Badge (opcional)</h5>
                </div>
                <div class="card-body p-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Texto del Badge</label>
                        <input type="text" name="men_badge" class="form-control form-control-sm" value="<?= htmlspecialchars($v['men_badge']) ?>" placeholder="Ej: 3, Nuevo, !">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Tipo / Color</label>
                        <select name="men_badge_tipo" class="form-control form-control-sm">
                            <option value="">-- Sin badge --</option>
                            <option value="primary" <?= $v['men_badge_tipo'] === 'primary' ? 'selected' : '' ?>>Primary (Azul)</option>
                            <option value="success" <?= $v['men_badge_tipo'] === 'success' ? 'selected' : '' ?>>Success (Verde)</option>
                            <option value="warning" <?= $v['men_badge_tipo'] === 'warning' ? 'selected' : '' ?>>Warning (Amarillo)</option>
                            <option value="danger" <?= $v['men_badge_tipo'] === 'danger' ? 'selected' : '' ?>>Danger (Rojo)</option>
                            <option value="info" <?= $v['men_badge_tipo'] === 'info' ? 'selected' : '' ?>>Info (Celeste)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="form-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-eye mr-2" style="color:<?= $moduloColor ?>"></i>Vista Previa</h5>
                </div>
                <div class="card-body p-3">
                    <div style="background:#343a40; border-radius:8px; padding:8px; color:white;">
                        <ul class="nav nav-pills nav-sidebar flex-column" style="font-size:0.85rem;">
                            <li class="nav-item">
                                <a href="#" class="nav-link" id="menuPreview" style="color:rgba(255,255,255,0.8)">
                                    <i class="nav-icon <?= htmlspecialchars($v['men_icono'] ?: 'fas fa-circle') ?>" id="menuPreviewIcon"></i>
                                    <p>
                                        <span id="menuPreviewLabel"><?= htmlspecialchars($v['men_label'] ?: 'Etiqueta') ?></span>
                                        <span class="badge badge-<?= htmlspecialchars($v['men_badge_tipo'] ?: 'info') ?> right" id="menuPreviewBadge" style="<?= empty($v['men_badge']) ? 'display:none' : '' ?>"><?= htmlspecialchars($v['men_badge']) ?></span>
                                    </p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="form-card">
                <div class="card-body p-3">
                    <button type="submit" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-save mr-1"></i> <?= $modoEdicion ? 'Actualizar' : 'Crear' ?> Ítem
                    </button>
                    <a href="<?= url('seguridad', 'menu', 'index') ?><?= $moduloId ? '&modulo_id=' . $moduloId : '' ?>" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Preview del icono en tiempo real
    $('#men_icono').on('input', function() {
        const val = $(this).val() || 'fas fa-circle';
        $('#iconPreview').attr('class', val);
        $('#menuPreviewIcon').attr('class', 'nav-icon ' + val);
    });

    // Preview de la etiqueta
    $('[name="men_label"]').on('input', function() {
        $('#menuPreviewLabel').text($(this).val() || 'Etiqueta');
    });

    // Preview de la ruta
    function updateRutaPreview() {
        const mod = $('#men_ruta_modulo').val() || '...';
        const ctrl = $('#men_ruta_controller').val() || '...';
        const act = $('#men_ruta_action').val() || 'index';
        $('#rutaPreview').text("url('" + mod + "', '" + ctrl + "', '" + act + "')");
    }
    $('#men_ruta_modulo, #men_ruta_controller, #men_ruta_action').on('input', updateRutaPreview);

    // Toggle de estado label
    $('#men_activo').on('change', function() {
        $(this).next('label').text(this.checked ? 'Activo' : 'Inactivo');
    });

    // Mostrar/ocultar campos según tipo
    $('#men_tipo').on('change', function() {
        const tipo = $(this).val();
        if (tipo === 'HEADER') {
            $('#iconoGroup, #rutaCard, #badgeCard').hide();
        } else {
            $('#iconoGroup, #rutaCard, #badgeCard').show();
        }
    }).trigger('change');

    // Cargar padres al cambiar módulo
    $('#men_modulo_id').on('change', function() {
        const modId = $(this).val();
        const excluir = <?= $modoEdicion && $menu ? (int)$menu['men_id'] : 0 ?>;
        if (!modId) {
            $('#men_padre_id').html('<option value="">-- Raíz (sin padre) --</option>');
            return;
        }

        $.get('<?= url('seguridad', 'menu', 'getPadres') ?>&modulo_id=' + modId + '&excluir_id=' + excluir, function(padres) {
            let html = '<option value="">-- Raíz (sin padre) --</option>';
            padres.forEach(p => {
                const prefix = p.men_tipo === 'HEADER' ? '📌 ' : '   ├─ ';
                html += `<option value="${p.men_id}">${prefix}${p.men_label}</option>`;
            });
            $('#men_padre_id').html(html);
        }, 'json');
    });
});
</script>
