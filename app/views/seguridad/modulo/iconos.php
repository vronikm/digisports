<?php
/**
 * DigiSports Seguridad - Galería de Iconos y Colores
 * Vista mejorada con administración completa desde iconos_colores.json
 */

$modulos = $modulos ?? [];
$iconos  = $iconos  ?? [];
$colores = $colores ?? [];

// URLs encriptadas para AJAX (generadas por PHP para compatibilidad con el Router)
$urlAjaxAdd      = url('seguridad', 'modulo', 'iconos_admin_add');
$urlAjaxAddColor = url('seguridad', 'modulo', 'iconos_admin_add_color');
$urlAjaxDelete   = url('seguridad', 'modulo', 'iconos_admin_delete');
$urlAjaxEdit     = url('seguridad', 'modulo', 'iconos_admin_edit');
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = 'Iconos y Colores';
$headerSubtitle = 'Administración visual de iconos y paleta de colores del sistema';
$headerIcon     = 'fas fa-icons';
$headerButtons  = [
    ['url' => url('seguridad', 'modulo', 'index'), 'label' => 'Volver a Módulos', 'icon' => 'fas fa-arrow-left', 'solid' => false],
];
include __DIR__ . '/../partials/header.php';
?>

        <!-- ═══════════════════════════════════════════════════════════════
             SECCIÓN 1: Módulos Actuales - Vista Rápida
        ═══════════════════════════════════════════════════════════════ -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-th-large mr-2"></i>
                    Módulos Actuales — Click para editar
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary"><?= count($modulos) ?> módulos</span>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($modulos)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-puzzle-piece fa-3x mb-2 d-block"></i>
                    <p>No hay módulos registrados</p>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($modulos as $m): ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-3">
                        <a href="<?= url('seguridad', 'modulo', 'editar', ['id' => $m['mod_id']]) ?>" class="text-decoration-none">
                            <div class="card h-100 shadow-sm modulo-card-hover" style="border-left: 4px solid <?= htmlspecialchars($m['mod_color_fondo'] ?? '#3B82F6') ?>;">
                                <div class="card-body text-center p-3">
                                    <i class="fas <?= htmlspecialchars($m['mod_icono'] ?? 'fa-cube') ?> fa-2x mb-2" style="color: <?= htmlspecialchars($m['mod_color_fondo'] ?? '#3B82F6') ?>;"></i>
                                    <div class="font-weight-bold small"><?= htmlspecialchars($m['mod_nombre'] ?? '') ?></div>
                                    <span class="badge badge-light mt-1" style="font-size:10px;"><?= htmlspecialchars($m['mod_codigo'] ?? '') ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             SECCIÓN 2: Preview Interactivo
        ═══════════════════════════════════════════════════════════════ -->
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-eye mr-2"></i> Preview Interactivo</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Selectores -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><i class="fas fa-icons mr-1 text-info"></i> Icono:</label>
                            <select id="preview-icon-select" class="form-control">
                                <?php foreach ($iconos as $categoria => $icons): ?>
                                <optgroup label="<?= htmlspecialchars($categoria) ?>">
                                    <?php foreach ($icons as $icon => $nombre): ?>
                                    <option value="<?= htmlspecialchars($icon) ?>"><?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($icon) ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-palette mr-1 text-warning"></i> Color:</label>
                            <select id="preview-color-select" class="form-control">
                                <?php foreach ($colores as $hex => $nombre): ?>
                                <option value="<?= htmlspecialchars($hex) ?>"><?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($hex) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-font mr-1 text-secondary"></i> Nombre:</label>
                            <input type="text" id="preview-name-input" class="form-control" placeholder="Mi Módulo" value="Mi Módulo">
                        </div>
                    </div>
                    <!-- Preview Card -->
                    <div class="col-md-4 text-center">
                        <div class="card shadow" id="preview-card-wrapper" style="border-top: 4px solid #22C55E;">
                            <div class="card-body py-4">
                                <div id="preview-icon-circle" style="width:90px;height:90px;border-radius:50%;margin:0 auto 15px;display:flex;align-items:center;justify-content:center;background:rgba(34,197,94,0.12);">
                                    <i id="preview-result-icon" class="fas fa-futbol fa-3x" style="color: #22C55E;"></i>
                                </div>
                                <h5 id="preview-module-name" class="mb-1">Mi Módulo</h5>
                                <small class="text-muted" id="preview-icon-code">fa-futbol</small>
                            </div>
                        </div>
                    </div>
                    <!-- Código generado -->
                    <div class="col-md-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-header py-2"><small><i class="fas fa-code mr-1"></i> Código generado</small></div>
                            <div class="card-body p-3">
<pre id="preview-code" class="mb-0 text-success" style="font-size:12px;white-space:pre-wrap;">/* CSS */
.modulo-icon {
    color: #22C55E;
}

/* HTML */
&lt;i class="fas fa-futbol"&gt;&lt;/i&gt;</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             SECCIÓN 3: Galería de Iconos (Solo visual, sin botones CRUD)
        ═══════════════════════════════════════════════════════════════ -->
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-icons mr-2"></i> Galería de Iconos Disponibles</h3>
                <div class="card-tools">
                    <?php $totalIconos = 0; foreach ($iconos as $icons) $totalIconos += count($icons); ?>
                    <span class="badge badge-success"><?= $totalIconos ?> iconos</span>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($iconos)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-icons fa-3x mb-2 d-block"></i>
                    <p>No hay iconos disponibles</p>
                </div>
                <?php else: ?>
                    <?php foreach ($iconos as $categoria => $icons): ?>
                    <h6 class="mt-3 mb-2">
                        <span class="badge badge-dark px-3 py-1"><i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($categoria) ?></span>
                        <small class="text-muted ml-2">(<?= count($icons) ?>)</small>
                    </h6>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($icons as $icon => $nombre): ?>
                        <div class="text-center p-2 m-1 rounded icon-gallery-item" style="width:85px;" title="<?= htmlspecialchars($icon) ?>">
                            <i class="fas <?= htmlspecialchars($icon) ?> fa-2x text-primary mb-1 d-block"></i>
                            <small class="text-muted d-block text-truncate" style="font-size:10px;"><?= htmlspecialchars($nombre) ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             SECCIÓN 4: Paleta de Colores (Solo visual)
        ═══════════════════════════════════════════════════════════════ -->
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-palette mr-2"></i> Paleta de Colores</h3>
                <div class="card-tools">
                    <span class="badge badge-warning text-dark"><?= count($colores) ?> colores</span>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($colores)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-palette fa-3x mb-2 d-block"></i>
                    <p>No hay colores disponibles</p>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($colores as $hex => $nombre): ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 mb-3">
                        <div class="card shadow-sm color-card-hover">
                            <div style="height:55px; background:<?= htmlspecialchars($hex) ?>; border-radius:4px 4px 0 0;"></div>
                            <div class="card-body p-2 text-center">
                                <strong class="d-block small"><?= htmlspecialchars($nombre) ?></strong>
                                <code style="font-size:11px;"><?= htmlspecialchars($hex) ?></code>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             SECCIÓN 5: Administración de Iconos y Colores (CRUD)
        ═══════════════════════════════════════════════════════════════ -->
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs mr-2"></i> Administración de Iconos y Colores</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Panel Iconos CRUD -->
                    <div class="col-lg-7">
                        <div class="card card-secondary card-outline">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-icons mr-1"></i> Gestión de Iconos</h3>
                            </div>
                            <div class="card-body">
                                <!-- Agregar icono -->
                                <h6 class="text-success"><i class="fas fa-plus-circle mr-1"></i> Agregar Icono</h6>
                                <div class="form-row align-items-end mb-3">
                                    <div class="form-group col-md-3 mb-1">
                                        <label class="small">Grupo</label>
                                        <select id="admin-icon-group" class="form-control form-control-sm">
                                            <?php foreach (array_keys($iconos) as $grupo): ?>
                                            <option value="<?= htmlspecialchars($grupo) ?>"><?= htmlspecialchars($grupo) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 mb-1">
                                        <label class="small">Clase FA</label>
                                        <input type="text" id="admin-icon-class" class="form-control form-control-sm" placeholder="fa-futbol">
                                    </div>
                                    <div class="form-group col-md-3 mb-1">
                                        <label class="small">Nombre</label>
                                        <input type="text" id="admin-icon-name" class="form-control form-control-sm" placeholder="Fútbol">
                                    </div>
                                    <div class="form-group col-md-2 mb-1">
                                        <label class="small">Preview</label>
                                        <div class="form-control form-control-sm text-center" id="admin-icon-preview-box">
                                            <i id="admin-icon-preview" class="fas fa-question text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-1 mb-1">
                                        <button type="button" id="btn-add-icon" class="btn btn-success btn-sm btn-block" title="Agregar icono">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <hr>

                                <!-- Tabla editable de iconos existentes -->
                                <h6 class="text-primary"><i class="fas fa-edit mr-1"></i> Editar / Eliminar Iconos</h6>
                                <div style="max-height:400px;overflow-y:auto;">
                                    <table class="table table-sm table-hover mb-0" id="admin-icons-table">
                                        <thead class="thead-light" style="position:sticky;top:0;z-index:1;">
                                            <tr>
                                                <th width="50">Icono</th>
                                                <th>Grupo</th>
                                                <th>Clase</th>
                                                <th>Nombre</th>
                                                <th width="100" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($iconos as $grupo => $icons): ?>
                                                <?php foreach ($icons as $icon => $nombre): ?>
                                                <tr data-grupo="<?= htmlspecialchars($grupo) ?>" data-icono="<?= htmlspecialchars($icon) ?>">
                                                    <td class="text-center"><i class="fas <?= htmlspecialchars($icon) ?> fa-lg text-primary"></i></td>
                                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($grupo) ?></span></td>
                                                    <td><code class="small"><?= htmlspecialchars($icon) ?></code></td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm icon-name-edit border-0 bg-transparent"
                                                               value="<?= htmlspecialchars($nombre) ?>"
                                                               data-original="<?= htmlspecialchars($nombre) ?>"
                                                               data-grupo="<?= htmlspecialchars($grupo) ?>"
                                                               data-icono="<?= htmlspecialchars($icon) ?>">
                                                    </td>
                                                    <td class="text-center text-nowrap">
                                                        <button class="btn btn-xs btn-outline-primary btn-save-icon-name mr-1" title="Guardar nombre"
                                                                data-grupo="<?= htmlspecialchars($grupo) ?>" data-icono="<?= htmlspecialchars($icon) ?>" style="display:none;">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-xs btn-outline-danger btn-delete-icon" title="Eliminar icono"
                                                                data-grupo="<?= htmlspecialchars($grupo) ?>" data-icono="<?= htmlspecialchars($icon) ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Colores CRUD -->
                    <div class="col-lg-5">
                        <div class="card card-secondary card-outline">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-palette mr-1"></i> Gestión de Colores</h3>
                            </div>
                            <div class="card-body">
                                <!-- Agregar color -->
                                <h6 class="text-success"><i class="fas fa-plus-circle mr-1"></i> Agregar Color</h6>
                                <div class="form-row align-items-end mb-3">
                                    <div class="form-group col-md-3 mb-1">
                                        <label class="small">Color</label>
                                        <input type="color" id="admin-color-picker" class="form-control form-control-sm" value="#22C55E" style="height:31px;">
                                    </div>
                                    <div class="form-group col-md-3 mb-1">
                                        <label class="small">HEX</label>
                                        <input type="text" id="admin-color-hex" class="form-control form-control-sm" placeholder="#22C55E" value="#22C55E">
                                    </div>
                                    <div class="form-group col-md-4 mb-1">
                                        <label class="small">Nombre</label>
                                        <input type="text" id="admin-color-name" class="form-control form-control-sm" placeholder="Verde">
                                    </div>
                                    <div class="form-group col-md-2 mb-1">
                                        <button type="button" id="btn-add-color" class="btn btn-success btn-sm btn-block" title="Agregar color">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <hr>

                                <!-- Lista editable de colores -->
                                <h6 class="text-primary"><i class="fas fa-edit mr-1"></i> Colores Existentes</h6>
                                <div style="max-height:400px;overflow-y:auto;">
                                    <table class="table table-sm table-hover mb-0" id="admin-colors-table">
                                        <thead class="thead-light" style="position:sticky;top:0;z-index:1;">
                                            <tr>
                                                <th width="45">Color</th>
                                                <th>HEX</th>
                                                <th>Nombre</th>
                                                <th width="80" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($colores as $hex => $nombre): ?>
                                            <tr data-hex="<?= htmlspecialchars($hex) ?>">
                                                <td>
                                                    <div style="width:28px;height:28px;border-radius:50%;background:<?= htmlspecialchars($hex) ?>;border:2px solid #ddd;"></div>
                                                </td>
                                                <td><code class="small"><?= htmlspecialchars($hex) ?></code></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm color-name-edit border-0 bg-transparent"
                                                           value="<?= htmlspecialchars($nombre) ?>"
                                                           data-original="<?= htmlspecialchars($nombre) ?>"
                                                           data-hex="<?= htmlspecialchars($hex) ?>">
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <button class="btn btn-xs btn-outline-primary btn-save-color-name mr-1" title="Guardar"
                                                            data-hex="<?= htmlspecialchars($hex) ?>" style="display:none;">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-danger btn-delete-color" title="Eliminar"
                                                            data-hex="<?= htmlspecialchars($hex) ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Info Fuentes -->
                        <div class="card card-secondary card-outline">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-font mr-1"></i> Fuente de Iconos</h3>
                            </div>
                            <div class="card-body text-center">
                                <i class="fab fa-font-awesome fa-2x text-primary mb-2 d-block"></i>
                                <strong>FontAwesome 5 Free</strong>
                                <p class="small text-muted mb-1">Librería de iconos vectoriales utilizada en todo el sistema.</p>
                                <a href="https://fontawesome.com/v5/search?m=free&s=solid" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt mr-1"></i> Ver catálogo completo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
.modulo-card-hover { transition: all 0.2s ease; }
.modulo-card-hover:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important; }
.color-card-hover { transition: all 0.2s ease; }
.color-card-hover:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; }
.icon-gallery-item { transition: background 0.15s; cursor: default; }
.icon-gallery-item:hover { background: #e9ecef; }
.icon-name-edit:focus, .color-name-edit:focus { background: #fff !important; border: 1px solid #80bdff !important; }
#admin-icons-table tr:hover td, #admin-colors-table tr:hover td { background: #f8f9fa; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ─── URLs AJAX (encriptadas por PHP) ───
    const URLS = {
        addIcon:    '<?= $urlAjaxAdd ?>',
        addColor:   '<?= $urlAjaxAddColor ?>',
        deleteIcon: '<?= $urlAjaxDelete ?>',
        editIcon:   '<?= $urlAjaxEdit ?>'
    };

    function ajaxPost(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        }).then(r => r.json());
    }

    function toast(icon, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast:true, position:'top-end', icon:icon, title:title, showConfirmButton:false, timer:2200 });
        } else {
            alert(title);
        }
    }

    // ─── PREVIEW INTERACTIVO ───
    const iconSel    = document.getElementById('preview-icon-select');
    const colorSel   = document.getElementById('preview-color-select');
    const nameInp    = document.getElementById('preview-name-input');
    const prevIcon   = document.getElementById('preview-result-icon');
    const prevCard   = document.getElementById('preview-card-wrapper');
    const prevCircle = document.getElementById('preview-icon-circle');
    const prevName   = document.getElementById('preview-module-name');
    const prevICode  = document.getElementById('preview-icon-code');
    const codeBlock  = document.getElementById('preview-code');

    function updatePreview() {
        var icon  = iconSel  ? iconSel.value  : 'fa-futbol';
        var color = colorSel ? colorSel.value  : '#22C55E';
        var name  = nameInp  ? (nameInp.value || 'Mi Módulo') : 'Mi Módulo';

        if (prevIcon)   { prevIcon.className = 'fas ' + icon + ' fa-3x'; prevIcon.style.color = color; }
        if (prevCard)   { prevCard.style.borderTopColor = color; }
        if (prevCircle) { prevCircle.style.background = color + '1A'; }
        if (prevName)   { prevName.textContent = name; }
        if (prevICode)  { prevICode.textContent = icon; }
        if (codeBlock)  {
            codeBlock.innerHTML = '/* CSS */\n.modulo-icon {\n    color: ' + color + ';\n}\n\n/* HTML */\n&lt;i class="fas ' + icon + '"&gt;&lt;/i&gt;';
        }
    }

    if (iconSel)  iconSel.addEventListener('change', updatePreview);
    if (colorSel) colorSel.addEventListener('change', updatePreview);
    if (nameInp)  nameInp.addEventListener('input', updatePreview);
    updatePreview();

    // ─── AGREGAR ICONO ───
    var iconClassInput = document.getElementById('admin-icon-class');
    var iconPreviewEl  = document.getElementById('admin-icon-preview');

    if (iconClassInput && iconPreviewEl) {
        iconClassInput.addEventListener('input', function() {
            var val = this.value.trim();
            iconPreviewEl.className = val ? ('fas ' + val + ' text-primary') : 'fas fa-question text-muted';
        });
    }

    var btnAddIcon = document.getElementById('btn-add-icon');
    if (btnAddIcon) {
        btnAddIcon.addEventListener('click', function() {
            var grupo  = document.getElementById('admin-icon-group').value;
            var icono  = document.getElementById('admin-icon-class').value.trim();
            var nombre = document.getElementById('admin-icon-name').value.trim();
            if (!grupo || !icono || !nombre) return toast('warning', 'Completa todos los campos');
            ajaxPost(URLS.addIcon, { grupo: grupo, icono: icono, nombre: nombre }).then(function(data) {
                if (data.success) { toast('success', 'Icono agregado'); setTimeout(function(){ location.reload(); }, 800); }
                else toast('error', data.error || 'Error al agregar');
            }).catch(function() { toast('error', 'Error de conexión'); });
        });
    }

    // ─── AGREGAR COLOR ───
    var colorPicker = document.getElementById('admin-color-picker');
    var colorHexInp = document.getElementById('admin-color-hex');

    if (colorPicker && colorHexInp) {
        colorPicker.addEventListener('input', function() { colorHexInp.value = this.value.toUpperCase(); });
        colorHexInp.addEventListener('input', function() {
            var v = this.value.trim();
            if (/^#[0-9A-Fa-f]{6}$/.test(v)) colorPicker.value = v;
        });
    }

    var btnAddColor = document.getElementById('btn-add-color');
    if (btnAddColor) {
        btnAddColor.addEventListener('click', function() {
            var hex    = document.getElementById('admin-color-hex').value.trim().toUpperCase();
            var nombre = document.getElementById('admin-color-name').value.trim();
            if (!hex || !nombre) return toast('warning', 'Completa HEX y nombre');
            if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) return toast('warning', 'Formato HEX inválido (ej: #22C55E)');
            ajaxPost(URLS.addColor, { hex: hex, nombre: nombre }).then(function(data) {
                if (data.success) { toast('success', 'Color agregado'); setTimeout(function(){ location.reload(); }, 800); }
                else toast('error', data.error || 'Error al agregar');
            }).catch(function() { toast('error', 'Error de conexión'); });
        });
    }

    // ─── EDITAR NOMBRE ICONO (inline) ───
    document.querySelectorAll('.icon-name-edit').forEach(function(input) {
        var saveBtn = input.closest('tr').querySelector('.btn-save-icon-name');
        input.addEventListener('input', function() {
            if (this.value !== this.dataset.original) {
                saveBtn.style.display = 'inline-block';
                this.classList.add('bg-white');
            } else {
                saveBtn.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.btn-save-icon-name').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var grupo = this.dataset.grupo;
            var icono = this.dataset.icono;
            var input = this.closest('tr').querySelector('.icon-name-edit');
            var nombre = input.value.trim();
            var self = this;
            if (!nombre) return toast('warning', 'El nombre no puede estar vacío');
            ajaxPost(URLS.editIcon, { grupo: grupo, icono: icono, nombre: nombre }).then(function(data) {
                if (data.success) {
                    toast('success', 'Nombre actualizado');
                    input.dataset.original = nombre;
                    self.style.display = 'none';
                    input.classList.remove('bg-white');
                } else toast('error', data.error || 'Error al guardar');
            }).catch(function() { toast('error', 'Error de conexión'); });
        });
    });

    // ─── ELIMINAR ICONO ───
    document.querySelectorAll('.btn-delete-icon').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var grupo = this.dataset.grupo;
            var icono = this.dataset.icono;
            var self = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar icono?',
                    html: '<i class="fas ' + icono + ' fa-2x text-danger mb-2"></i><br><code>' + icono + '</code> del grupo <b>' + grupo + '</b>',
                    icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) doDeleteIcon(grupo, icono, self);
                });
            } else {
                if (confirm('¿Eliminar este icono?')) doDeleteIcon(grupo, icono, self);
            }
        });
    });

    function doDeleteIcon(grupo, icono, btn) {
        ajaxPost(URLS.deleteIcon, { grupo: grupo, icono: icono }).then(function(data) {
            if (data.success) {
                toast('success', 'Icono eliminado');
                btn.closest('tr').remove();
            } else toast('error', data.error || 'Error al eliminar');
        }).catch(function() { toast('error', 'Error de conexión'); });
    }

    // ─── EDITAR NOMBRE COLOR (inline) ───
    document.querySelectorAll('.color-name-edit').forEach(function(input) {
        var saveBtn = input.closest('tr').querySelector('.btn-save-color-name');
        input.addEventListener('input', function() {
            if (this.value !== this.dataset.original) {
                saveBtn.style.display = 'inline-block';
                this.classList.add('bg-white');
            } else {
                saveBtn.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.btn-save-color-name').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var hex = this.dataset.hex;
            var input = this.closest('tr').querySelector('.color-name-edit');
            var nombre = input.value.trim();
            var self = this;
            if (!nombre) return toast('warning', 'El nombre no puede estar vacío');
            ajaxPost(URLS.addColor, { hex: hex, nombre: nombre }).then(function(data) {
                if (data.success) {
                    toast('success', 'Color actualizado');
                    input.dataset.original = nombre;
                    self.style.display = 'none';
                    input.classList.remove('bg-white');
                } else toast('error', data.error || 'Error al guardar');
            }).catch(function() { toast('error', 'Error de conexión'); });
        });
    });

    // ─── ELIMINAR COLOR ───
    document.querySelectorAll('.btn-delete-color').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var hex = this.dataset.hex;
            var self = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar color?',
                    html: '<div style="width:50px;height:50px;border-radius:50%;background:' + hex + ';margin:0 auto 10px;border:3px solid #ddd;"></div><code>' + hex + '</code>',
                    icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) doDeleteColor(hex, self);
                });
            } else {
                if (confirm('¿Eliminar este color?')) doDeleteColor(hex, self);
            }
        });
    });

    function doDeleteColor(hex, btn) {
        ajaxPost(URLS.deleteIcon, { color_hex: hex }).then(function(data) {
            if (data.success) {
                toast('success', 'Color eliminado');
                btn.closest('tr').remove();
            } else toast('error', data.error || 'Error al eliminar');
        }).catch(function() { toast('error', 'Error de conexión'); });
    }

});
</script>
