<?php
/**
 * DigiSports Seguridad - Administración de Menús por Aplicativo
 * Vista Premium con selector de módulo, árbol de menús y permisos por rol
 */
$modulos    = $modulos ?? [];
$menus      = $menus ?? [];
$stats      = $stats ?? ['total' => 0, 'headers' => 0, 'items' => 0, 'submenus' => 0, 'activos' => 0, 'inactivos' => 0];
$roles      = $roles ?? [];
$permisos   = $permisos ?? [];
$moduloId   = $moduloId ?? null;
$moduloColor = $moduloColor ?? $modulo_actual['color'] ?? '#F59E0B';
$moduloIcono = $moduloIcono ?? $modulo_actual['icono'] ?? 'fas fa-shield-alt';

// Buscar nombre del módulo seleccionado
$moduloSeleccionado = null;
foreach ($modulos as $m) {
    if ($moduloId && (int)$m['mod_id'] === (int)$moduloId) {
        $moduloSeleccionado = $m;
        break;
    }
}
?>

<style>
/* ═══ Menús por Aplicativo — Estilos Premium ═══ */
.menu-admin-card { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; }
.menu-admin-card .card-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; }
.menu-admin-card .card-header h5 { margin: 0; font-weight: 700; font-size: 0.95rem; color: #1e293b; }

/* Selector de módulo */
.mod-selector { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.mod-sel-card {
    border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center;
    cursor: pointer; transition: all 0.3s; text-decoration: none; color: #1e293b;
}
.mod-sel-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-decoration: none; color: #1e293b; }
.mod-sel-card.active { border-color: <?= $moduloColor ?>; background: <?= $moduloColor ?>08; box-shadow: 0 0 0 3px <?= $moduloColor ?>20; }
.mod-sel-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-size: 1.1rem; }
.mod-sel-name { font-weight: 700; font-size: 0.82rem; }
.mod-sel-count { font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }

/* Mini KPIs */
.mk-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; margin-bottom: 1.5rem; }
.mk-item { background: white; border-radius: 10px; padding: 0.75rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.mk-item .mk-val { font-size: 1.4rem; font-weight: 800; color: #0f172a; }
.mk-item .mk-lbl { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: #94a3b8; }

/* Árbol de menú */
.menu-tree { list-style: none; padding: 0; margin: 0; }
.menu-tree-item { border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 8px; overflow: hidden; transition: all 0.2s; }
.menu-tree-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.menu-tree-row {
    display: flex; align-items: center; gap: 10px; padding: 0.65rem 1rem;
    background: white; cursor: default;
}
.menu-tree-item.is-header .menu-tree-row { background: #f1f5f9; font-weight: 700; }
.menu-tree-item.is-submenu .menu-tree-row { padding-left: 3.5rem; background: #fafbfc; }
.menu-tree-item.is-item .menu-tree-row { padding-left: 2rem; }
.menu-tree-item.inactive { opacity: 0.5; }

.mt-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; }
.mt-label { font-size: 0.85rem; font-weight: 600; color: #1e293b; flex: 1; }
.mt-tipo { font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
.mt-tipo.header { background: #dbeafe; color: #2563eb; }
.mt-tipo.item { background: #dcfce7; color: #16a34a; }
.mt-tipo.submenu { background: #fef3c7; color: #d97706; }

.mt-ruta { font-size: 0.7rem; color: #94a3b8; font-family: 'SFMono-Regular', monospace; }
.mt-actions { display: flex; gap: 4px; flex-shrink: 0; }
.mt-actions .btn { padding: 3px 8px; font-size: 0.72rem; border-radius: 6px; }

/* Permisos Tab */
.perm-matrix { width: 100%; border-collapse: separate; border-spacing: 0; }
.perm-matrix th { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; padding: 8px 10px; border-bottom: 2px solid #e2e8f0; }
.perm-matrix td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 0.82rem; }
.perm-matrix tr:hover td { background: #f8fafc; }
.perm-check { width: 18px; height: 18px; cursor: pointer; accent-color: <?= $moduloColor ?>; }

/* Responsive */
@media (max-width: 768px) {
    .mod-selector { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
    .mk-row { grid-template-columns: repeat(3, 1fr); }
}
</style>

<!-- Header Premium -->
<?php include BASE_PATH . '/app/views/seguridad/partials/header.php'; ?>

<!-- Selector de Módulo -->
<div class="menu-admin-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-th-large mr-2" style="color:<?= $moduloColor ?>"></i>Seleccionar Módulo</h5>
    </div>
    <div class="card-body">
        <div class="mod-selector">
            <?php foreach ($modulos as $mod): 
                $isActive = $moduloId && (int)$mod['mod_id'] === (int)$moduloId;
                $modColor = $mod['mod_color_fondo'] ?? '#64748b';
                $modIcon  = $mod['mod_icono'] ?? 'fas fa-cube';
            ?>
            <a href="<?= url('seguridad', 'menu', 'index') ?>&modulo_id=<?= $mod['mod_id'] ?>" 
               class="mod-sel-card <?= $isActive ? 'active' : '' ?>">
                <div class="mod-sel-icon" style="background:<?= $modColor ?>15; color:<?= $modColor ?>">
                    <i class="<?= htmlspecialchars($modIcon) ?>"></i>
                </div>
                <div class="mod-sel-name"><?= htmlspecialchars($mod['mod_nombre']) ?></div>
                <div class="mod-sel-count"><?= (int)$mod['total_menus'] ?> ítems</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($moduloId && $moduloSeleccionado): ?>

<!-- Mini KPIs -->
<div class="mk-row">
    <div class="mk-item">
        <div class="mk-val"><?= (int)$stats['total'] ?></div>
        <div class="mk-lbl">Total</div>
    </div>
    <div class="mk-item">
        <div class="mk-val" style="color:#2563eb"><?= (int)$stats['headers'] ?></div>
        <div class="mk-lbl">Secciones</div>
    </div>
    <div class="mk-item">
        <div class="mk-val" style="color:#16a34a"><?= (int)$stats['items'] ?></div>
        <div class="mk-lbl">Ítems</div>
    </div>
    <div class="mk-item">
        <div class="mk-val" style="color:#d97706"><?= (int)$stats['submenus'] ?></div>
        <div class="mk-lbl">Sub-menús</div>
    </div>
    <div class="mk-item">
        <div class="mk-val" style="color:#16a34a"><?= (int)$stats['activos'] ?></div>
        <div class="mk-lbl">Activos</div>
    </div>
    <div class="mk-item">
        <div class="mk-val" style="color:#ef4444"><?= (int)$stats['inactivos'] ?></div>
        <div class="mk-lbl">Inactivos</div>
    </div>
</div>

<!-- Tabs: Estructura / Permisos -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tab-estructura" role="tab">
            <i class="fas fa-sitemap mr-1"></i> Estructura del Menú
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-permisos" role="tab">
            <i class="fas fa-user-lock mr-1"></i> Permisos por Rol
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- TAB: Estructura -->
    <div class="tab-pane fade show active" id="tab-estructura" role="tabpanel">
        <div class="menu-admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>
                    <i class="<?= htmlspecialchars($moduloSeleccionado['mod_icono'] ?? 'fas fa-cube') ?> mr-2" 
                       style="color:<?= htmlspecialchars($moduloSeleccionado['mod_color_fondo'] ?? $moduloColor) ?>"></i>
                    Menú de <?= htmlspecialchars($moduloSeleccionado['mod_nombre']) ?>
                </h5>
                <a href="<?= url('seguridad', 'menu', 'crear') ?>&modulo_id=<?= $moduloId ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-1"></i> Nuevo Ítem
                </a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($menus)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay ítems de menú para este módulo</p>
                        <a href="<?= url('seguridad', 'menu', 'crear') ?>&modulo_id=<?= $moduloId ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Crear primer ítem
                        </a>
                    </div>
                <?php else: ?>
                    <ul class="menu-tree" id="menuTree">
                        <?php 
                        // Organizar por jerarquía: primero HEADERs (padre_id NULL), luego ITEMs, luego SUBMENUs
                        $headers = array_filter($menus, fn($m) => $m['men_tipo'] === 'HEADER');
                        $items = array_filter($menus, fn($m) => $m['men_tipo'] === 'ITEM');
                        $submenus = array_filter($menus, fn($m) => $m['men_tipo'] === 'SUBMENU');

                        foreach ($headers as $header): 
                            $headerColor = $moduloSeleccionado['mod_color_fondo'] ?? $moduloColor;
                        ?>
                        <li class="menu-tree-item is-header <?= !$header['men_activo'] ? 'inactive' : '' ?>" data-id="<?= $header['men_id'] ?>">
                            <div class="menu-tree-row">
                                <div class="mt-icon" style="background:<?= $headerColor ?>20; color:<?= $headerColor ?>">
                                    <i class="fas fa-heading"></i>
                                </div>
                                <span class="mt-label"><?= htmlspecialchars($header['men_label']) ?></span>
                                <span class="mt-tipo header">Header</span>
                                <div class="mt-actions">
                                    <a href="<?= url('seguridad', 'menu', 'editar') ?>&id=<?= $header['men_id'] ?>" class="btn btn-outline-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-warning btn-sm btn-toggle" data-id="<?= $header['men_id'] ?>" title="<?= $header['men_activo'] ? 'Desactivar' : 'Activar' ?>">
                                        <i class="fas <?= $header['men_activo'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                    </button>
                                    <?php if ((int)($header['hijos_count'] ?? 0) === 0): ?>
                                    <button class="btn btn-outline-danger btn-sm btn-delete" data-id="<?= $header['men_id'] ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>

                        <?php 
                        // Items hijos de este header
                        $headerItems = array_filter($items, fn($i) => (int)($i['men_padre_id'] ?? 0) === (int)$header['men_id']);
                        foreach ($headerItems as $item): 
                            $hasSubmenus = !empty($item['men_ruta_controller']) ? false : true;
                            $rutaStr = '';
                            if ($item['men_ruta_modulo'] || $item['men_ruta_controller']) {
                                $rutaStr = ($item['men_ruta_modulo'] ?? '') . '/' . ($item['men_ruta_controller'] ?? '') . '/' . ($item['men_ruta_action'] ?? '');
                            }
                        ?>
                        <li class="menu-tree-item is-item <?= !$item['men_activo'] ? 'inactive' : '' ?>" data-id="<?= $item['men_id'] ?>">
                            <div class="menu-tree-row">
                                <div class="mt-icon" style="background:#e2e8f0; color:#475569">
                                    <i class="<?= htmlspecialchars($item['men_icono'] ?? 'fas fa-circle') ?>"></i>
                                </div>
                                <span class="mt-label">
                                    <?= htmlspecialchars($item['men_label']) ?>
                                    <?php if ($item['men_badge']): ?>
                                        <span class="badge badge-<?= htmlspecialchars($item['men_badge_tipo'] ?? 'info') ?> ml-1"><?= htmlspecialchars($item['men_badge']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="mt-tipo item">Item</span>
                                <?php if ($rutaStr): ?>
                                    <span class="mt-ruta"><?= htmlspecialchars($rutaStr) ?></span>
                                <?php endif; ?>
                                <div class="mt-actions">
                                    <a href="<?= url('seguridad', 'menu', 'editar') ?>&id=<?= $item['men_id'] ?>" class="btn btn-outline-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-warning btn-sm btn-toggle" data-id="<?= $item['men_id'] ?>" title="Toggle">
                                        <i class="fas <?= $item['men_activo'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                    </button>
                                    <?php if ((int)($item['hijos_count'] ?? 0) === 0): ?>
                                    <button class="btn btn-outline-danger btn-sm btn-delete" data-id="<?= $item['men_id'] ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>

                        <?php 
                        // Sub-menús hijos de este item
                        $itemSubmenus = array_filter($submenus, fn($s) => (int)($s['men_padre_id'] ?? 0) === (int)$item['men_id']);
                        foreach ($itemSubmenus as $sub): 
                            $subRuta = ($sub['men_ruta_modulo'] ?? '') . '/' . ($sub['men_ruta_controller'] ?? '') . '/' . ($sub['men_ruta_action'] ?? '');
                        ?>
                        <li class="menu-tree-item is-submenu <?= !$sub['men_activo'] ? 'inactive' : '' ?>" data-id="<?= $sub['men_id'] ?>">
                            <div class="menu-tree-row">
                                <div class="mt-icon" style="background:#fef3c7; color:#d97706">
                                    <i class="far fa-circle"></i>
                                </div>
                                <span class="mt-label"><?= htmlspecialchars($sub['men_label']) ?></span>
                                <span class="mt-tipo submenu">Sub</span>
                                <span class="mt-ruta"><?= htmlspecialchars($subRuta) ?></span>
                                <div class="mt-actions">
                                    <a href="<?= url('seguridad', 'menu', 'editar') ?>&id=<?= $sub['men_id'] ?>" class="btn btn-outline-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-warning btn-sm btn-toggle" data-id="<?= $sub['men_id'] ?>" title="Toggle">
                                        <i class="fas <?= $sub['men_activo'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm btn-delete" data-id="<?= $sub['men_id'] ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; // submenus ?>

                        <?php endforeach; // items ?>
                        <?php endforeach; // headers ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TAB: Permisos por Rol -->
    <div class="tab-pane fade" id="tab-permisos" role="tabpanel">
        <div class="menu-admin-card">
            <div class="card-header">
                <h5><i class="fas fa-user-lock mr-2" style="color:<?= $moduloColor ?>"></i>Permisos de Menú por Rol</h5>
            </div>
            <div class="card-body">
                <!-- Selector de Rol -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold"><i class="fas fa-user-shield mr-1"></i> Seleccionar Rol:</label>
                    <select id="rolSelector" class="form-control form-control-sm" style="max-width:300px">
                        <option value="">-- Seleccione un rol --</option>
                        <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['rol_rol_id'] ?>"><?= htmlspecialchars($rol['rol_nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Matriz de permisos (se carga dinámicamente) -->
                <div id="permisosContainer" style="display:none">
                    <table class="perm-matrix">
                        <thead>
                            <tr>
                                <th style="width:30px"><input type="checkbox" id="checkAll" class="perm-check" title="Marcar todos"></th>
                                <th>Ítem de Menú</th>
                                <th>Tipo</th>
                                <th style="width:80px;text-align:center">Ver</th>
                                <th style="width:80px;text-align:center">Acceder</th>
                            </tr>
                        </thead>
                        <tbody id="permisosBody">
                        </tbody>
                    </table>
                    <div class="mt-3 text-right">
                        <button id="btnGuardarPermisos" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Guardar Permisos
                        </button>
                    </div>
                </div>

                <div id="permisosEmpty" class="text-center py-3 text-muted">
                    <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                    <p>Seleccione un rol para configurar permisos</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Sin módulo seleccionado -->
<div class="text-center py-5">
    <i class="fas fa-hand-pointer fa-3x" style="color:<?= $moduloColor ?>; opacity:0.5"></i>
    <h5 class="mt-3 text-muted">Seleccione un módulo para administrar su menú</h5>
    <p class="text-muted">Haga clic en cualquiera de los módulos de arriba para ver y editar su estructura de menú.</p>
</div>
<?php endif; ?>

<?php 
// Preparar menús como JSON para JS
$menusJson = json_encode($menus);
$moduloIdJs = (int)$moduloId;
?>

<script>
$(document).ready(function() {
    const moduloId = <?= $moduloIdJs ?>;
    const menus = <?= $menusJson ?>;

    // Toggle activar/desactivar
    $(document).on('click', '.btn-toggle', function() {
        const id = $(this).data('id');
        const btn = $(this);
        $.get('<?= url('seguridad', 'menu', 'toggle') ?>&id=' + id, function(resp) {
            if (resp.success) {
                const icon = btn.find('i');
                const item = btn.closest('.menu-tree-item');
                if (resp.activo) {
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    item.removeClass('inactive');
                } else {
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    item.addClass('inactive');
                }
                Swal.fire({icon:'success', title: resp.message, timer: 1200, showConfirmButton: false});
            } else {
                Swal.fire('Error', resp.error, 'error');
            }
        }, 'json');
    });

    // Eliminar item
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const row = $(this).closest('.menu-tree-item');
        Swal.fire({
            title: '¿Eliminar este ítem?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.get('<?= url('seguridad', 'menu', 'eliminar') ?>&id=' + id, function(resp) {
                    if (resp.success) {
                        row.slideUp(300, function() { $(this).remove(); });
                        Swal.fire({icon:'success', title:'Eliminado', timer:1200, showConfirmButton:false});
                    } else {
                        Swal.fire('Error', resp.error, 'error');
                    }
                }, 'json');
            }
        });
    });

    // === Permisos por Rol ===
    $('#rolSelector').on('change', function() {
        const rolId = $(this).val();
        if (!rolId || !moduloId) {
            $('#permisosContainer').hide();
            $('#permisosEmpty').show();
            return;
        }

        // Cargar permisos del rol para este módulo
        $.get('<?= url('seguridad', 'menu', 'getPermisos') ?>&rol_id=' + rolId + '&modulo_id=' + moduloId, function(resp) {
            if (resp.success) {
                renderPermisos(resp.permisos);
                $('#permisosContainer').show();
                $('#permisosEmpty').hide();
            }
        }, 'json');
    });

    function renderPermisos(permisosRol) {
        const body = $('#permisosBody');
        body.empty();

        // Crear mapa de permisos existentes
        const permMap = {};
        permisosRol.forEach(p => {
            permMap[p.rme_menu_id] = { ver: parseInt(p.rme_puede_ver), acceder: parseInt(p.rme_puede_acceder) };
        });

        // Renderizar solo ITEMs y SUBMENUs (no HEADERs)
        menus.forEach(m => {
            if (m.men_tipo === 'HEADER') return;
            const perm = permMap[m.men_id] || { ver: 0, acceder: 0 };
            const indent = m.men_tipo === 'SUBMENU' ? 'padding-left: 2rem;' : '';
            const tipoClass = m.men_tipo === 'ITEM' ? 'item' : 'submenu';
            
            body.append(`
                <tr data-menu-id="${m.men_id}">
                    <td><input type="checkbox" class="perm-check perm-row-check" ${(perm.ver || perm.acceder) ? 'checked' : ''}></td>
                    <td style="${indent}">
                        <i class="${m.men_icono || 'fas fa-circle'} mr-1 text-muted"></i>
                        ${escapeHtml(m.men_label)}
                    </td>
                    <td><span class="mt-tipo ${tipoClass}">${m.men_tipo}</span></td>
                    <td style="text-align:center">
                        <input type="checkbox" class="perm-check perm-ver" ${perm.ver ? 'checked' : ''}>
                    </td>
                    <td style="text-align:center">
                        <input type="checkbox" class="perm-check perm-acceder" ${perm.acceder ? 'checked' : ''}>
                    </td>
                </tr>
            `);
        });
    }

    // Check all
    $('#checkAll').on('change', function() {
        const checked = $(this).prop('checked');
        $('#permisosBody .perm-check').prop('checked', checked);
    });

    // Row check sincroniza ver + acceder
    $(document).on('change', '.perm-row-check', function() {
        const checked = $(this).prop('checked');
        const row = $(this).closest('tr');
        row.find('.perm-ver, .perm-acceder').prop('checked', checked);
    });

    // Guardar permisos
    $('#btnGuardarPermisos').on('click', function() {
        const rolId = $('#rolSelector').val();
        if (!rolId) return;

        const permisos = [];
        $('#permisosBody tr').each(function() {
            const menuId = $(this).data('menu-id');
            const ver = $(this).find('.perm-ver').prop('checked') ? 1 : 0;
            const acceder = $(this).find('.perm-acceder').prop('checked') ? 1 : 0;
            if (ver || acceder) {
                permisos.push({ menu_id: menuId, puede_ver: ver, puede_acceder: acceder });
            }
        });

        $.ajax({
            url: '<?= url('seguridad', 'menu', 'guardarPermisos') ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ rol_id: rolId, modulo_id: moduloId, permisos: permisos }),
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({icon:'success', title: resp.message, timer: 1500, showConfirmButton: false});
                } else {
                    Swal.fire('Error', resp.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }
});
</script>
