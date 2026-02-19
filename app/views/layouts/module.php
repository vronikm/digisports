<?php
// Inicialización robusta para evitar warnings
$moduloActual = isset($modulo_actual) && is_array($modulo_actual) ? $modulo_actual : [];
$menuItems = isset($menu_items) && is_array($menu_items) ? $menu_items : [];
$title = isset($title) ? $title : 'DigiSports';
$usuario = isset($usuario) ? $usuario : ($_SESSION['user_name'] ?? 'Usuario');
$tenantNombre = isset($tenant_nombre) ? $tenant_nombre : ($_SESSION['tenant_name'] ?? 'DigiSports');
// Priorizar los valores directos si existen, si no usar los del array
// Normalización robusta del icono para FontAwesome
$moduloNombre = isset($moduloNombre) ? $moduloNombre : (isset($moduloActual['nombre_personalizado']) && $moduloActual['nombre_personalizado'] ? $moduloActual['nombre_personalizado'] : (isset($moduloActual['nombre']) ? $moduloActual['nombre'] : 'Módulo'));
$moduloIcono = isset($moduloIcono) ? $moduloIcono : (isset($moduloActual['icono']) ? $moduloActual['icono'] : 'fas fa-cube');
$moduloColor = isset($moduloColor) ? $moduloColor : (isset($moduloActual['color']) ? $moduloActual['color'] : '#3B82F6');
// Validar y reforzar el icono
if (!is_string($moduloIcono) || trim($moduloIcono) === '') {
    $moduloIcono = 'fas fa-cube';
} else {
    $icono = trim($moduloIcono);
    // Si no tiene prefijo válido, anteponer 'fas'
    if (!preg_match('/^(fas|far|fab|fal|fad|fa[srbld]) /', $icono)) {
        $icono = 'fas ' . $icono;
    }
    // Si sólo tiene el nombre (ej: 'fa-futbol'), anteponer 'fas'
    if (!preg_match('/^fa[srbld]? fa-/', $icono)) {
        $icono = 'fas fa-cube';
    }
    $moduloIcono = $icono;
}
/**
 * DigiSports - Layout Base para Módulos
 * Incluye sidebar con menú específico del módulo
 * 
 * @package DigiSports\Views\Layouts
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - DigiSports</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --module-color: <?= $moduloColor ?>;
            --module-color-light: <?= $moduloColor ?>20;
            --module-color-dark: <?= $moduloColor ?>dd;
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Sidebar personalizado */
        .main-sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }
        
        .main-sidebar .brand-link {
            background: var(--module-color);
            border-bottom: none;
        }
        
        .main-sidebar .brand-link .brand-image {
            width: 33px;
            height: 33px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
            color: var(--module-color);
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background: var(--module-color);
            color: white;
        }
        
        .sidebar-dark-primary .nav-sidebar .nav-link:hover {
            background: var(--module-color-light);
        }
        
        .nav-sidebar .nav-header {
            color: #94a3b8;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1rem 0.5rem;
        }
        
        /* Cards de KPI */
        .kpi-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .kpi-card .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .kpi-card .kpi-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .kpi-card .kpi-label {
            color: #64748b;
            font-size: 0.85rem;
        }
        
        .kpi-card .kpi-trend {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 20px;
        }
        
        .kpi-trend.up {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .kpi-trend.down {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* Content header */
        .content-header {
            padding: 1rem 1.5rem;
        }
        
        .content-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        /* Navbar */
        .main-header {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .main-header .nav-link {
            color: #64748b;
        }
        
        /* Quick action buttons */
        .quick-actions .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        /* Tables */
        .table-module thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        
        /* Charts container */
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Module badge */
        .module-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            background: var(--module-color-light);
            color: var(--module-color);
        }
        
        /* User dropdown */
        .user-panel {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-panel .info {
            padding: 8px 10px;
        }
        
        .user-panel .info a {
            color: #fff;
            font-weight: 500;
        }
        
        .user-panel .info small {
            color: #94a3b8;
            display: block;
            font-size: 0.75rem;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <span class="module-badge">
                    <i class="<?= htmlspecialchars($moduloIcono) ?>"></i>
                    <?= is_array($moduloNombre) ? 'Seguridad' : htmlspecialchars($moduloNombre) ?>
                </span>
            </li>
        </ul>
        
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">3 Notificaciones</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-calendar-check mr-2 text-success"></i> 5 reservas pendientes
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">Ver todas</a>
                </div>
            </li>
            
            <!-- User Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user-circle fa-lg"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <span class="dropdown-item dropdown-header"><?php
                        if (is_array($usuario)) {
                            echo htmlspecialchars(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''));
                        } else {
                            echo htmlspecialchars((string)$usuario);
                        }
                    ?></span>
                    <div class="dropdown-divider"></div>
                    <a href="<?= url('core', 'hub', 'index') ?>" class="dropdown-item">
                        <i class="fas fa-th mr-2"></i> Volver al Hub
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user-cog mr-2"></i> Mi Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= url('core', 'auth', 'logout') ?>" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                    </a>
                </div>
            </li>
        </ul>
    </nav>
    
    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?= url('core', 'hub', 'index') ?>" class="brand-link">
            <span class="brand-image">
                <i class="<?= htmlspecialchars($moduloIcono) ?>"></i>
            </span>
            <span class="brand-text font-weight-bold"><?= htmlspecialchars($moduloNombre) ?></span>
        </a>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- User panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-light"></i>
                </div>
                <div class="info">
                    <a href="#"><?php
                        if (is_array($usuario)) {
                            echo htmlspecialchars(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''));
                        } else {
                            echo htmlspecialchars((string)$usuario);
                        }
                    ?></a>
                    <small><?php
                        if (is_array($tenantNombre)) {
                            echo htmlspecialchars($tenantNombre['nombre_comercial'] ?? '');
                        } else {
                            echo htmlspecialchars((string)$tenantNombre);
                        }
                    ?></small>
                </div>
            </div>
            
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    
                    <?php foreach ($menuItems as $section): ?>

                    <?php if (isset($section['header'])): ?>
                    <li class="nav-header"><?= htmlspecialchars($section['header']) ?></li>
                    <?php endif; ?>

                    <?php if (isset($section['items'])): ?>
                    <?php foreach ($section['items'] as $item): ?>
                    <?php
                    // Si el item tiene submenu, usar la URL del primer subitem
                    $mainUrl = isset($item['submenu']) && is_array($item['submenu']) && count($item['submenu']) > 0 ? ($item['submenu'][0]['url'] ?? '#') : ($item['url'] ?? '#');
                    // Determinar si algún submenú está activo
                    $submenuActive = false;
                    if (isset($item['submenu']) && is_array($item['submenu'])) {
                        foreach ($item['submenu'] as $sub) {
                            if (!empty($sub['active'])) { $submenuActive = true; break; }
                        }
                    }
                    $isActive = ($item['active'] ?? false) || $submenuActive;
                    ?>
                    <li class="nav-item <?= isset($item['submenu']) ? 'has-treeview' : '' ?><?= $submenuActive ? ' menu-open' : '' ?>">
                        <a href="<?= $mainUrl ?>" class="nav-link <?= $isActive ? 'active' : '' ?>">
                            <i class="nav-icon <?= $item['icon'] ?? 'fas fa-circle' ?>"></i>
                            <p>
                                <?= htmlspecialchars($item['label']) ?>
                                <?php if (isset($item['badge'])): ?>
                                <span class="badge badge-<?= $item['badge_type'] ?? 'info' ?> right"><?= $item['badge'] ?></span>
                                <?php endif; ?>
                                <?php if (isset($item['submenu'])): ?>
                                <i class="right fas fa-angle-left"></i>
                                <?php endif; ?>
                            </p>
                        </a>
                        <?php if (isset($item['submenu'])): ?>
                        <ul class="nav nav-treeview">
                            <?php foreach ($item['submenu'] as $sub): ?>
                            <li class="nav-item">
                                <a href="<?= $sub['url'] ?? '#' ?>" class="nav-link <?= ($sub['active'] ?? false) ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p><?= htmlspecialchars($sub['label']) ?></p>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php endforeach; ?>
                    
                    <!-- Separator -->
                    <li class="nav-header">SISTEMA</li>
                    <li class="nav-item">
                        <a href="<?= url('core', 'hub', 'index') ?>" class="nav-link">
                            <i class="nav-icon fas fa-th"></i>
                            <p>Volver al Hub</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <?= $content ?? '' ?>
    </div>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>&copy; <?= date('Y') ?> <a href="https://digitech.ec">DigiTech</a></strong> - DigiSports
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($scripts)): ?>
<?= $scripts ?>
<?php endif; ?>

</body>
</html>
