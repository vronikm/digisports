<?php
/**
 * DigiSports Seguridad - Matriz de Permisos del Rol
 * Vista unificada: permisos funcionales (authorize) + permisos de menú (visibilidad sidebar)
 */

$rol = $rol ?? [];
$permisosActuales = $permisosActuales ?? [];
$modulosConMenus = $modulosConMenus ?? [];
$permisosMenu = $permisosMenu ?? [];

// Permisos funcionales organizados por categoría (para authorize())
$categorias = [
    'core' => [
        'label' => 'Core del Sistema',
        'icono' => 'fas fa-cogs',
        'color' => '#6366F1',
        'modulos' => [
            'dashboard'     => ['label' => 'Dashboard',      'icono' => 'fas fa-tachometer-alt', 'acciones' => ['ver']],
            'usuarios'      => ['label' => 'Usuarios',       'icono' => 'fas fa-user-cog',       'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'resetear_password', 'bloquear']],
            'roles'         => ['label' => 'Roles',          'icono' => 'fas fa-user-tag',       'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'asignar_permisos']],
            'tenants'       => ['label' => 'Tenants',        'icono' => 'fas fa-building',       'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'suspender', 'renovar']],
            'modulos'       => ['label' => 'Módulos',        'icono' => 'fas fa-puzzle-piece',   'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'asignar']],
            'configuracion' => ['label' => 'Configuración',  'icono' => 'fas fa-cogs',           'acciones' => ['ver', 'editar']],
            'auditoria'     => ['label' => 'Auditoría',      'icono' => 'fas fa-history',        'acciones' => ['ver', 'exportar']],
        ]
    ],
    'operativo' => [
        'label' => 'Módulos Operativos',
        'icono' => 'fas fa-clipboard-list',
        'color' => '#10B981',
        'modulos' => [
            'instalaciones' => ['label' => 'Instalaciones',  'icono' => 'fas fa-building',            'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'reservas'      => ['label' => 'Reservas',       'icono' => 'fas fa-calendar-alt',        'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'confirmar', 'cancelar']],
            'clientes'      => ['label' => 'Clientes',       'icono' => 'fas fa-users',               'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'exportar']],
            'facturacion'   => ['label' => 'Facturación',    'icono' => 'fas fa-file-invoice-dollar',  'acciones' => ['ver', 'crear', 'anular', 'reenviar', 'exportar', 'configurar_sri']],
            'reportes'      => ['label' => 'Reportes',       'icono' => 'fas fa-chart-bar',           'acciones' => ['ver', 'exportar']],
        ]
    ],
    'deportivo' => [
        'label' => 'Subsistemas Deportivos',
        'icono' => 'fas fa-futbol',
        'color' => '#F59E0B',
        'modulos' => [
            'arena'           => ['label' => 'Arena',           'icono' => 'fas fa-map-marked-alt', 'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'futbol'          => ['label' => 'Fútbol',          'icono' => 'fas fa-futbol',          'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'basket'          => ['label' => 'Basket',          'icono' => 'fas fa-basketball-ball', 'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'natacion'        => ['label' => 'Natación',        'icono' => 'fas fa-swimmer',         'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'artes_marciales' => ['label' => 'Artes Marciales', 'icono' => 'fas fa-fist-raised',     'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'ajedrez'         => ['label' => 'Ajedrez',         'icono' => 'fas fa-chess',           'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'multideporte'    => ['label' => 'Multideporte',    'icono' => 'fas fa-running',         'acciones' => ['ver', 'crear', 'editar', 'eliminar']],
            'store'           => ['label' => 'Store',           'icono' => 'fas fa-store',           'acciones' => ['ver', 'crear', 'editar', 'eliminar', 'inventario']],
        ]
    ],
];

// Iconos para acciones
$accionesIconos = [
    'ver' => 'fas fa-eye', 'crear' => 'fas fa-plus', 'editar' => 'fas fa-edit',
    'eliminar' => 'fas fa-trash', 'exportar' => 'fas fa-download', 'confirmar' => 'fas fa-check',
    'cancelar' => 'fas fa-times', 'anular' => 'fas fa-ban', 'reenviar' => 'fas fa-paper-plane',
    'resetear_password' => 'fas fa-key', 'bloquear' => 'fas fa-lock', 'asignar_permisos' => 'fas fa-user-shield',
    'asignar' => 'fas fa-link', 'suspender' => 'fas fa-pause', 'renovar' => 'fas fa-sync',
    'configurar_sri' => 'fas fa-file-signature', 'inventario' => 'fas fa-boxes',
];

// Contar permisos totales disponibles
$totalDisponibles = 0;
foreach ($categorias as $cat) {
    foreach ($cat['modulos'] as $modConf) {
        $totalDisponibles += count($modConf['acciones']);
    }
}

// Contar menús totales (sin headers)
$totalMenus = 0;
foreach ($modulosConMenus as $mc) {
    $totalMenus += count(array_filter($mc['menus'], fn($m) => $m['men_tipo'] !== 'HEADER'));
}
?>

<style>
/* ═══ Matriz de Permisos — Estilos Premium ═══ */
.perm-card { border: none; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 1.25rem; }
.perm-card .card-header { padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; }
.perm-card .card-header h6 { margin: 0; font-weight: 700; font-size: 0.9rem; }

.cat-header { 
    border-radius: 12px; padding: 0.75rem 1.25rem; margin-bottom: 0.75rem; margin-top: 1rem;
    display: flex; align-items: center; gap: 12px;
}
.cat-header-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.cat-header h5 { margin: 0; font-weight: 800; font-size: 1rem; }
.cat-header small { opacity: 0.7; font-size: 0.75rem; }

.tbl-perm { width: 100%; border-collapse: separate; border-spacing: 0; }
.tbl-perm th { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #64748b; padding: 8px 10px; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
.tbl-perm td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 0.82rem; vertical-align: middle; }
.tbl-perm tr:hover td { background: #f8fafc; }
.tbl-perm .mod-name { font-weight: 600; color: #1e293b; white-space: nowrap; }
.tbl-perm .mod-name i { margin-right: 6px; }
.perm-cb { width: 18px; height: 18px; cursor: pointer; border-radius: 4px; }
.tbl-perm .text-center { text-align: center; }
.tbl-perm .accion-extra { display: inline-flex; align-items: center; gap: 4px; margin-right: 10px; white-space: nowrap; font-size: 0.78rem; }

.mt-tipo { font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
.mt-tipo.item { background: #dcfce7; color: #16a34a; }
.mt-tipo.submenu { background: #fef3c7; color: #d97706; }

.perm-sidebar-card { border: none; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; position: sticky; top: 1rem; }

.quick-actions { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 1rem; }
.quick-actions .btn { font-size: 0.72rem; padding: 4px 10px; border-radius: 8px; }

.perm-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 1rem; }
.perm-stat { text-align: center; background: #f8fafc; border-radius: 10px; padding: 0.6rem; }
.perm-stat .val { font-size: 1.3rem; font-weight: 800; color: #0f172a; }
.perm-stat .lbl { font-size: 0.6rem; font-weight: 600; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.3px; }

.permisos-tabs .nav-link { font-size: 0.82rem; font-weight: 600; padding: 0.6rem 1.25rem; border-radius: 10px 10px 0 0; }
.permisos-tabs .nav-link.active { background: white; border-color: #e2e8f0 #e2e8f0 white; color: #1e293b; }

@media (max-width: 768px) { .perm-stats { grid-template-columns: repeat(2, 1fr); } }
</style>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = 'Permisos: ' . htmlspecialchars($rol['rol_nombre'] ?? 'Rol sin nombre');
$headerSubtitle = 'Matriz unificada de permisos funcionales y visibilidad de menú';
$headerIcon     = 'fas fa-key';
$headerButtons  = [
    ['url' => url('seguridad', 'rol'), 'label' => 'Volver a Roles', 'icon' => 'fas fa-arrow-left', 'solid' => false],
];
include __DIR__ . '/../partials/header.php';
?>

<form method="POST" action="<?= url('seguridad', 'rol', 'guardarPermisos') ?>" id="formPermisos">
    <input type="hidden" name="rol_id" value="<?= $rol['rol_rol_id'] ?? '' ?>">

    <div class="row">
        <!-- ═══ COLUMNA PRINCIPAL ═══ -->
        <div class="col-lg-9 col-md-8">

            <!-- Acciones Rápidas -->
            <div class="quick-actions">
                <button type="button" class="btn btn-success" onclick="seleccionarTodos()">
                    <i class="fas fa-check-double mr-1"></i> Marcar Todos
                </button>
                <button type="button" class="btn btn-danger" onclick="deseleccionarTodos()">
                    <i class="fas fa-times mr-1"></i> Desmarcar Todos
                </button>
                <button type="button" class="btn btn-info" onclick="soloLectura()">
                    <i class="fas fa-eye mr-1"></i> Solo Lectura
                </button>
                <button type="button" class="btn btn-warning" onclick="seleccionarCategoriaCompleta('core')" title="Marcar todos los permisos Core">
                    <i class="fas fa-cogs mr-1"></i> Todo Core
                </button>
                <button type="button" class="btn btn-secondary" onclick="seleccionarCategoriaCompleta('operativo')" title="Marcar todos los permisos Operativos">
                    <i class="fas fa-clipboard-list mr-1"></i> Todo Operativo
                </button>
            </div>

            <!-- ═══ TABS: Funcionales + Menú ═══ -->
            <ul class="nav nav-tabs permisos-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab-funcionales">
                        <i class="fas fa-shield-alt mr-1"></i> Permisos Funcionales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-menus">
                        <i class="fas fa-bars mr-1"></i> Visibilidad de Menú
                        <span class="badge badge-pill badge-info ml-1"><?= $totalMenus ?></span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- ═══ TAB: PERMISOS FUNCIONALES ═══ -->
                <div class="tab-pane fade show active" id="tab-funcionales" role="tabpanel">
                    <?php foreach ($categorias as $catKey => $categoria): ?>
                    <div class="cat-header" style="background: <?= $categoria['color'] ?>10; border: 1px solid <?= $categoria['color'] ?>30;">
                        <div class="cat-header-icon" style="background: <?= $categoria['color'] ?>20; color: <?= $categoria['color'] ?>;">
                            <i class="<?= $categoria['icono'] ?>"></i>
                        </div>
                        <div>
                            <h5 style="color: <?= $categoria['color'] ?>"><?= $categoria['label'] ?></h5>
                            <small><?= count($categoria['modulos']) ?> módulos</small>
                        </div>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCategoria('<?= $catKey ?>')" title="Alternar todos los permisos de esta categoría">
                                <i class="fas fa-check-double mr-1"></i> Toggle
                            </button>
                        </div>
                    </div>

                    <div class="perm-card">
                        <div class="card-body p-0">
                            <table class="tbl-perm">
                                <thead>
                                    <tr>
                                        <th style="width:180px">Módulo</th>
                                        <th class="text-center" style="width:55px">
                                            <i class="fas fa-eye"></i><br><small>Ver</small>
                                        </th>
                                        <th class="text-center" style="width:55px">
                                            <i class="fas fa-plus"></i><br><small>Crear</small>
                                        </th>
                                        <th class="text-center" style="width:55px">
                                            <i class="fas fa-edit"></i><br><small>Editar</small>
                                        </th>
                                        <th class="text-center" style="width:55px">
                                            <i class="fas fa-trash"></i><br><small>Eliminar</small>
                                        </th>
                                        <th>Permisos Especiales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categoria['modulos'] as $modKey => $modConf): ?>
                                    <tr data-categoria="<?= $catKey ?>">
                                        <td class="mod-name">
                                            <i class="<?= $modConf['icono'] ?>" style="color: <?= $categoria['color'] ?>"></i>
                                            <?= $modConf['label'] ?>
                                        </td>
                                        <?php 
                                        $accionesBasicas = ['ver', 'crear', 'editar', 'eliminar'];
                                        foreach ($accionesBasicas as $accion): 
                                            if (in_array($accion, $modConf['acciones'])):
                                                $permVal = $modKey . '.' . $accion;
                                                $checked = in_array($permVal, $permisosActuales);
                                        ?>
                                        <td class="text-center">
                                            <input type="checkbox" class="perm-cb permiso-func" name="permisos[]" 
                                                   value="<?= $permVal ?>" 
                                                   data-categoria="<?= $catKey ?>" data-modulo="<?= $modKey ?>" data-accion="<?= $accion ?>"
                                                   <?= $checked ? 'checked' : '' ?>>
                                        </td>
                                        <?php else: ?>
                                        <td class="text-center" style="background:#f9fafb;">
                                            <span class="text-muted">—</span>
                                        </td>
                                        <?php endif; endforeach; ?>
                                        <td>
                                            <?php 
                                            $otrasAcciones = array_diff($modConf['acciones'], $accionesBasicas);
                                            if (!empty($otrasAcciones)):
                                                foreach ($otrasAcciones as $accion): 
                                                    $permVal = $modKey . '.' . $accion;
                                                    $checked = in_array($permVal, $permisosActuales);
                                            ?>
                                            <span class="accion-extra">
                                                <input type="checkbox" class="perm-cb permiso-func" name="permisos[]" 
                                                       value="<?= $permVal ?>"
                                                       data-categoria="<?= $catKey ?>" data-modulo="<?= $modKey ?>" data-accion="<?= $accion ?>"
                                                       id="pf_<?= $permVal ?>"
                                                       <?= $checked ? 'checked' : '' ?>>
                                                <label for="pf_<?= $permVal ?>" style="margin:0;cursor:pointer">
                                                    <i class="<?= $accionesIconos[$accion] ?? 'fas fa-check' ?>" style="font-size:0.7rem"></i>
                                                    <?= ucfirst(str_replace('_', ' ', $accion)) ?>
                                                </label>
                                            </span>
                                            <?php endforeach;
                                            else: ?>
                                            <span class="text-muted" style="font-size:0.75rem">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- ═══ TAB: VISIBILIDAD DE MENÚ ═══ -->
                <div class="tab-pane fade" id="tab-menus" role="tabpanel">
                    <div class="mt-3 mb-3">
                        <div class="alert alert-info py-2" style="border-radius:10px; font-size:0.82rem;">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Visibilidad de Menú:</strong> Controla qué opciones del sidebar ve y puede acceder este rol en cada módulo.
                            Las opciones marcadas como <strong>"Ver"</strong> aparecen en el menú lateral.
                            Las marcadas como <strong>"Acceder"</strong> permiten ejecutar la acción al hacer clic.
                        </div>
                        <div class="quick-actions">
                            <button type="button" class="btn btn-success btn-sm" onclick="marcarTodosMenus(true)">
                                <i class="fas fa-check-double mr-1"></i> Marcar Todos
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="marcarTodosMenus(false)">
                                <i class="fas fa-times mr-1"></i> Desmarcar Todos
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="soloVerMenus()">
                                <i class="fas fa-eye mr-1"></i> Solo Ver
                            </button>
                        </div>
                    </div>

                    <?php foreach ($modulosConMenus as $mc): 
                        $mod = $mc['modulo'];
                        $menus = $mc['menus'];
                        if (empty($menus)) continue;
                        
                        $modColor = $mod['mod_color_fondo'] ?? '#64748b';
                        $modIcon  = $mod['mod_icono'] ?? 'fas fa-cube';
                        
                        // Separar por tipo
                        $headers = array_filter($menus, fn($m) => $m['men_tipo'] === 'HEADER');
                        $items   = array_filter($menus, fn($m) => $m['men_tipo'] === 'ITEM');
                        $subs    = array_filter($menus, fn($m) => $m['men_tipo'] === 'SUBMENU');
                        
                        $menuCount = count($items) + count($subs);
                        if ($menuCount === 0) continue;
                    ?>
                    <div class="perm-card">
                        <div class="card-header d-flex align-items-center" style="background: <?= $modColor ?>08;">
                            <i class="<?= htmlspecialchars($modIcon) ?> mr-2" style="color:<?= $modColor ?>; font-size:1.1rem;"></i>
                            <h6 style="color:<?= $modColor ?>; margin:0;"><?= htmlspecialchars($mod['mod_nombre']) ?></h6>
                            <span class="badge badge-light ml-2"><?= $menuCount ?> opciones</span>
                            <div class="ml-auto">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleModuloMenus(<?= $mod['mod_id'] ?>)" title="Alternar todos los permisos de menú de este módulo">
                                    <i class="fas fa-check-double"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="tbl-perm">
                                <thead>
                                    <tr>
                                        <th style="width:50%">Opción de Menú</th>
                                        <th class="text-center" style="width:15%">Tipo</th>
                                        <th class="text-center" style="width:15%">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </th>
                                        <th class="text-center" style="width:15%">
                                            <i class="fas fa-sign-in-alt mr-1"></i>Acceder
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($headers as $header): ?>
                                    <tr style="background:#f1f5f9;">
                                        <td colspan="4" style="font-weight:700; font-size:0.8rem; color:<?= $modColor ?>;">
                                            <i class="fas fa-heading mr-1"></i>
                                            <?= htmlspecialchars($header['men_label']) ?>
                                        </td>
                                    </tr>
                                    <?php 
                                    // Items bajo este header
                                    $headerItems = array_filter($items, fn($i) => (int)($i['men_padre_id'] ?? 0) === (int)$header['men_id']);
                                    foreach ($headerItems as $item):
                                        $pm = $permisosMenu[(int)$item['men_id']] ?? ['ver' => 0, 'acceder' => 0];
                                    ?>
                                    <tr data-modulo-id="<?= $mod['mod_id'] ?>">
                                        <td style="padding-left:2rem;">
                                            <i class="<?= htmlspecialchars($item['men_icono'] ?? 'fas fa-circle') ?> mr-1 text-muted" style="font-size:0.8rem;"></i>
                                            <?= htmlspecialchars($item['men_label']) ?>
                                        </td>
                                        <td class="text-center"><span class="mt-tipo item">Item</span></td>
                                        <td class="text-center">
                                            <input type="checkbox" class="menu-perm-cb perm-cb" 
                                                   name="menu_permisos[<?= $item['men_id'] ?>][ver]" value="1"
                                                   data-modulo-id="<?= $mod['mod_id'] ?>"
                                                   <?= $pm['ver'] ? 'checked' : '' ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="menu-perm-cb perm-cb" 
                                                   name="menu_permisos[<?= $item['men_id'] ?>][acceder]" value="1"
                                                   data-modulo-id="<?= $mod['mod_id'] ?>"
                                                   <?= $pm['acceder'] ? 'checked' : '' ?>>
                                        </td>
                                    </tr>
                                    <?php 
                                    // Submenús bajo este item
                                    $itemSubs = array_filter($subs, fn($s) => (int)($s['men_padre_id'] ?? 0) === (int)$item['men_id']);
                                    foreach ($itemSubs as $sub):
                                        $pm = $permisosMenu[(int)$sub['men_id']] ?? ['ver' => 0, 'acceder' => 0];
                                    ?>
                                    <tr data-modulo-id="<?= $mod['mod_id'] ?>">
                                        <td style="padding-left:3.5rem;">
                                            <i class="far fa-circle mr-1 text-muted" style="font-size:0.65rem;"></i>
                                            <?= htmlspecialchars($sub['men_label']) ?>
                                        </td>
                                        <td class="text-center"><span class="mt-tipo submenu">Sub</span></td>
                                        <td class="text-center">
                                            <input type="checkbox" class="menu-perm-cb perm-cb" 
                                                   name="menu_permisos[<?= $sub['men_id'] ?>][ver]" value="1"
                                                   data-modulo-id="<?= $mod['mod_id'] ?>"
                                                   <?= $pm['ver'] ? 'checked' : '' ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="menu-perm-cb perm-cb" 
                                                   name="menu_permisos[<?= $sub['men_id'] ?>][acceder]" value="1"
                                                   data-modulo-id="<?= $mod['mod_id'] ?>"
                                                   <?= $pm['acceder'] ? 'checked' : '' ?>>
                                        </td>
                                    </tr>
                                    <?php endforeach; // subs ?>
                                    <?php endforeach; // items ?>
                                    <?php endforeach; // headers ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; // modulosConMenus ?>

                    <?php 
                    $hayMenus = false;
                    foreach ($modulosConMenus as $mc) {
                        if (count(array_filter($mc['menus'], fn($m) => $m['men_tipo'] !== 'HEADER')) > 0) { $hayMenus = true; break; }
                    }
                    if (!$hayMenus): 
                    ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No hay menús configurados. Vaya a <a href="<?= url('seguridad', 'menu', 'index') ?>"><strong>Menús por Aplicativo</strong></a> para crearlos.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ═══ COLUMNA LATERAL ═══ -->
        <div class="col-lg-3 col-md-4">
            <!-- Info del Rol -->
            <div class="perm-sidebar-card mb-3">
                <div class="card-body text-center py-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px; background: linear-gradient(135deg, #3B82F6, #8B5CF6);">
                        <i class="fas fa-user-shield text-white fa-lg"></i>
                    </div>
                    <h6 class="mb-1"><?= htmlspecialchars($rol['rol_nombre'] ?? '') ?></h6>
                    <span class="badge" style="background:#3B82F6; color:white;">Nivel <?= $rol['rol_nivel_acceso'] ?? 1 ?></span>
                    <span class="badge badge-secondary ml-1"><?= htmlspecialchars($rol['rol_codigo'] ?? '') ?></span>
                    <p class="text-muted mt-2 small mb-0"><?= htmlspecialchars($rol['rol_descripcion'] ?? '') ?></p>
                </div>
            </div>

            <!-- Resumen -->
            <div class="perm-sidebar-card mb-3">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <h6 style="margin:0; font-weight:700; font-size:0.85rem;"><i class="fas fa-chart-pie mr-1"></i> Resumen</h6>
                </div>
                <div class="card-body py-2">
                    <div class="perm-stats">
                        <div class="perm-stat">
                            <div class="val" id="count-func" style="color:#3B82F6"><?= count($permisosActuales) ?></div>
                            <div class="lbl">Funcionales</div>
                        </div>
                        <div class="perm-stat">
                            <div class="val" id="count-menu" style="color:#10B981"><?= count($permisosMenu) ?></div>
                            <div class="lbl">Menú</div>
                        </div>
                        <div class="perm-stat">
                            <div class="val" id="count-total" style="color:#6366F1"><?= count($permisosActuales) + count($permisosMenu) ?></div>
                            <div class="lbl">Total</div>
                        </div>
                    </div>
                    <div class="progress mb-1" style="height:6px; border-radius:3px;">
                        <div class="progress-bar" id="barra-func" style="width: <?= min(($totalDisponibles > 0 ? (count($permisosActuales) / $totalDisponibles) * 100 : 0), 100) ?>%; background:#3B82F6;"></div>
                    </div>
                    <small class="text-muted d-block mb-2" style="font-size:0.65rem;">Funcionales: <span id="count-func-pct"><?= count($permisosActuales) ?></span> / <?= $totalDisponibles ?></small>
                    <div class="progress mb-1" style="height:6px; border-radius:3px;">
                        <div class="progress-bar" id="barra-menu" style="width: <?= min(($totalMenus > 0 ? (count($permisosMenu) / $totalMenus) * 100 : 0), 100) ?>%; background:#10B981;"></div>
                    </div>
                    <small class="text-muted d-block" style="font-size:0.65rem;">Menú: <span id="count-menu-pct"><?= count($permisosMenu) ?></span> / <?= $totalMenus ?></small>
                </div>
            </div>

            <!-- Leyenda -->
            <div class="perm-sidebar-card mb-3">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <h6 style="margin:0; font-weight:700; font-size:0.85rem;"><i class="fas fa-info-circle mr-1"></i> Leyenda</h6>
                </div>
                <div class="card-body py-2" style="font-size:0.78rem;">
                    <p class="mb-2"><strong><i class="fas fa-shield-alt mr-1 text-primary"></i> Funcionales:</strong> Controlan qué acciones puede realizar el usuario (crear, editar, eliminar, etc.).</p>
                    <p class="mb-0"><strong><i class="fas fa-bars mr-1 text-success"></i> Menú:</strong> Controlan qué opciones aparecen en el sidebar de cada módulo.</p>
                </div>
            </div>

            <!-- Botones -->
            <div class="perm-sidebar-card">
                <div class="card-body py-3">
                    <button type="submit" class="btn btn-primary btn-block mb-2" id="btnGuardar">
                        <i class="fas fa-save mr-1"></i> Guardar Todo
                    </button>
                    <a href="<?= url('seguridad', 'rol', 'editar', ['id' => $rol['rol_rol_id'] ?? '']) ?>" class="btn btn-outline-info btn-block mb-2">
                        <i class="fas fa-edit mr-1"></i> Editar Rol
                    </a>
                    <a href="<?= url('seguridad', 'rol') ?>" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
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
    const totalFunc = <?= $totalDisponibles ?>;
    const totalMenu = <?= $totalMenus ?>;

    function actualizarContadores() {
        const funcChecked = document.querySelectorAll('.permiso-func:checked').length;
        // Contar menús únicos con al menos un permiso
        const menuRows = new Set();
        document.querySelectorAll('.menu-perm-cb:checked').forEach(function(cb) {
            const name = cb.getAttribute('name');
            const match = name.match(/menu_permisos\[(\d+)\]/);
            if (match) menuRows.add(match[1]);
        });
        const menuUniqueCount = menuRows.size;

        document.getElementById('count-func').textContent = funcChecked;
        document.getElementById('count-menu').textContent = menuUniqueCount;
        document.getElementById('count-total').textContent = funcChecked + menuUniqueCount;
        document.getElementById('count-func-pct').textContent = funcChecked;
        document.getElementById('count-menu-pct').textContent = menuUniqueCount;
        
        document.getElementById('barra-func').style.width = Math.min((funcChecked / Math.max(totalFunc, 1)) * 100, 100) + '%';
        document.getElementById('barra-menu').style.width = Math.min((menuUniqueCount / Math.max(totalMenu, 1)) * 100, 100) + '%';
    }

    document.querySelectorAll('.perm-cb').forEach(function(cb) {
        cb.addEventListener('change', actualizarContadores);
    });

    // Confirmación SweetAlert al guardar
    document.getElementById('formPermisos').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Guardar permisos?',
                html: 'Se actualizarán los <strong>permisos funcionales</strong> y la <strong>visibilidad de menú</strong> para este rol.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3B82F6',
                confirmButtonText: '<i class="fas fa-save mr-1"></i> Guardar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
        } else {
            if (confirm('¿Guardar permisos?')) form.submit();
        }
    });
});

// ═══ Acciones Rápidas — Permisos Funcionales ═══
function seleccionarTodos() {
    document.querySelectorAll('.permiso-func').forEach(function(cb) { cb.checked = true; });
    dispatchCambio();
}
function deseleccionarTodos() {
    document.querySelectorAll('.permiso-func').forEach(function(cb) { cb.checked = false; });
    dispatchCambio();
}
function soloLectura() {
    document.querySelectorAll('.permiso-func').forEach(function(cb) {
        cb.checked = cb.getAttribute('data-accion') === 'ver';
    });
    dispatchCambio();
}
function toggleCategoria(catKey) {
    var cbs = document.querySelectorAll('.permiso-func[data-categoria="' + catKey + '"]');
    var allChecked = Array.from(cbs).every(function(cb) { return cb.checked; });
    cbs.forEach(function(cb) { cb.checked = !allChecked; });
    dispatchCambio();
}
function seleccionarCategoriaCompleta(catKey) {
    document.querySelectorAll('.permiso-func[data-categoria="' + catKey + '"]').forEach(function(cb) { cb.checked = true; });
    dispatchCambio();
}

// ═══ Acciones Rápidas — Menú ═══
function marcarTodosMenus(checked) {
    document.querySelectorAll('.menu-perm-cb').forEach(function(cb) { cb.checked = checked; });
    dispatchCambio();
}
function soloVerMenus() {
    document.querySelectorAll('.menu-perm-cb').forEach(function(cb) {
        var name = cb.getAttribute('name');
        cb.checked = name.indexOf('[ver]') !== -1;
    });
    dispatchCambio();
}
function toggleModuloMenus(moduloId) {
    var cbs = document.querySelectorAll('.menu-perm-cb[data-modulo-id="' + moduloId + '"]');
    var allChecked = Array.from(cbs).every(function(cb) { return cb.checked; });
    cbs.forEach(function(cb) { cb.checked = !allChecked; });
    dispatchCambio();
}

function dispatchCambio() {
    var cb = document.querySelector('.perm-cb');
    if (cb) cb.dispatchEvent(new Event('change'));
}
</script>
