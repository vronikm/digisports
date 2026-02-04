<?php
/**
 * DigiSports - Layout Principal
 * Basado en AdminLTE 3.2 con colores corporativos DigiTech
 */

// Valores por defecto
$tenant = $tenant ?? [];
$user = $user ?? [];
$modules = $modules ?? [];
$notifications = $notifications ?? [];
$notificationCount = $notificationCount ?? 0;
$title = $title ?? 'DigiSports';
$pageTitle = $pageTitle ?? 'Dashboard';
$currentController = $currentController ?? '';
$currentModule = $currentModule ?? '';
$content = $content ?? '';

// Incluir helpers para usar la versión robusta de getFlashMessage
require_once APP_PATH . '/helpers/functions.php';
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | DigiSports</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --digitech-blue: #2F39F2;
            --digitech-green: #08DC64;
            --digitech-navy: #2C285B;
            --digitech-beige: #ECE1D4;
        }
        .navbar-digitech {
            background: linear-gradient(135deg, var(--digitech-blue) 0%, var(--digitech-navy) 100%) !important;
        }
        .sidebar-digitech {
            background: var(--digitech-navy) !important;
        }
        .sidebar-digitech .nav-sidebar .nav-link.active {
            background-color: var(--digitech-blue) !important;
            color: #fff !important;
        }
        .sidebar-digitech .nav-sidebar .nav-link:hover {
            background-color: rgba(47, 57, 242, 0.3) !important;
        }
        .sidebar-digitech .nav-sidebar .nav-link {
            color: rgba(255,255,255,0.8) !important;
        }
        .brand-link-digitech {
            background: var(--digitech-navy) !important;
            border-bottom: 1px solid var(--digitech-green) !important;
        }
        .nav-header {
            color: var(--digitech-green) !important;
            font-weight: 600;
        }
        .main-footer {
            border-top: 2px solid var(--digitech-green);
        }
        a { color: var(--digitech-blue); }
        a:hover { color: var(--digitech-navy); }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark navbar-digitech">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
            <!-- Botón volver al Hub -->
            <li class="nav-item">
                <a href="<?= function_exists('url') ? url('core', 'hub', 'index') : BASE_URL . '?module=core&controller=hub&action=index' ?>" class="nav-link" title="Volver al Hub">
                    <i class="fas fa-th-large"></i> <span class="d-none d-md-inline">Hub</span>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= function_exists('url') ? url('core', 'dashboard') : '#' ?>" class="nav-link">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </li>
            <?php if (!empty($_SESSION['modulo_activo'])): ?>
            <li class="nav-item d-none d-sm-inline-block">
                <span class="nav-link" style="color: rgba(255,255,255,0.7);">
                    <i class="<?= htmlspecialchars($_SESSION['modulo_activo']['icono'] ?? 'fas fa-cube') ?>"></i>
                    <?= htmlspecialchars($_SESSION['modulo_activo']['nombre'] ?? '') ?>
                </span>
            </li>
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="badge badge-warning navbar-badge"><?= $notificationCount ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header"><?= $notificationCount ?> Notificaciones</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-info-circle mr-2"></i> Sin notificaciones nuevas
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">Ver todas</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle"></i>
                    <span class="d-none d-md-inline ml-1"><?= htmlspecialchars($user['nombres'] ?? 'Usuario') ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item"><i class="fas fa-user mr-2"></i> Mi Perfil</a>
                    <a href="#" class="dropdown-item"><i class="fas fa-cog mr-2"></i> Configuración</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= function_exists('url') ? url('core', 'auth', 'logout') : '#' ?>" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar elevation-4 sidebar-digitech">
        <a href="<?= function_exists('url') ? url('core', 'dashboard') : '#' ?>" class="brand-link text-center brand-link-digitech">
            <i class="fas fa-futbol text-white" style="font-size: 24px;"></i>
            <span class="brand-text font-weight-bold text-white">DigiSports</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-light"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block text-white">
                        <?= htmlspecialchars(($user['nombres'] ?? 'Usuario') . ' ' . ($user['apellidos'] ?? '')) ?>
                    </a>
                    <small class="text-muted"><?= htmlspecialchars($tenant['nombre_comercial'] ?? 'DigiSports Admin') ?></small>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="<?= function_exists('url') ? url('core', 'dashboard') : '#' ?>" class="nav-link <?= $currentModule === 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-header">MÓDULOS</li>
                    <li class="nav-item">
                        <a href="<?= function_exists('url') ? url('instalaciones', 'Dashboard', 'index') : '#' ?>" class="nav-link <?= $currentModule === 'instalaciones' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-building text-info"></i>
                            <p>Instalaciones</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= function_exists('url') ? url('reservas', 'reserva') : '#' ?>" class="nav-link <?= $currentModule === 'reservas' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-calendar-alt text-success"></i>
                            <p>Reservas</p>
                        </a>
                    </li>
                    <li class="nav-item has-treeview <?= $currentModule === 'facturacion' ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= $currentModule === 'facturacion' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-file-invoice-dollar text-warning"></i>
                            <p>
                                Facturación
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= function_exists('url') ? url('facturacion', 'comprobante') : '#' ?>" class="nav-link <?= ($currentModule === 'facturacion' && $currentController === 'comprobante') ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Comprobantes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= function_exists('url') ? url('facturacion', 'facturaelectronica') : '#' ?>" class="nav-link <?= ($currentModule === 'facturacion' && $currentController === 'facturaelectronica') ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>Facturación Electrónica</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= function_exists('url') ? url('facturacion', 'cliente') : '#' ?>" class="nav-link <?= ($currentModule === 'facturacion' && $currentController === 'cliente') ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon text-primary"></i>
                                    <p>Clientes</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="<?= function_exists('url') ? url('reportes', 'kpi') : '#' ?>" class="nav-link <?= $currentModule === 'reportes' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-chart-bar text-purple"></i>
                            <p>Reportes</p>
                        </a>
                    </li>
                    <li class="nav-header">ADMINISTRACIÓN</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users text-secondary"></i>
                            <p>Usuarios</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs text-secondary"></i>
                            <p>Configuración</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= function_exists('url') ? url('core', 'dashboard') : '#' ?>">Inicio</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?= $content ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; <?= date('Y') ?> <a href="https://digitech.ec" target="_blank" style="color: var(--digitech-blue);">DigiTech</a></strong> - DigiSports. Todos los derechos reservados.
        <div class="float-right d-none d-sm-inline-block">
            <span style="color: var(--digitech-green);">●</span> <b>Versión</b> 1.0.0
        </div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// =====================================================
// DigiSports - SweetAlert2 Helper Functions
// =====================================================

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Colores corporativos DigiTech
const DigiColors = {
    blue: '#2F39F2',
    green: '#08DC64',
    navy: '#2C285B',
    beige: '#ECE1D4'
};

// Funciones de utilidad para mensajes
const DigiAlert = {
    // Toast de éxito
    success: function(message, title = '¡Éxito!') {
        Toast.fire({
            icon: 'success',
            title: message,
            iconColor: DigiColors.green
        });
    },
    
    // Toast de error
    error: function(message, title = 'Error') {
        Toast.fire({
            icon: 'error',
            title: message,
            iconColor: '#dc3545'
        });
    },
    
    // Toast de advertencia
    warning: function(message, title = 'Advertencia') {
        Toast.fire({
            icon: 'warning',
            title: message,
            iconColor: '#ffc107'
        });
    },
    
    // Toast de información
    info: function(message, title = 'Información') {
        Toast.fire({
            icon: 'info',
            title: message,
            iconColor: DigiColors.blue
        });
    },
    
    // Alerta de confirmación para eliminar
    confirmDelete: function(callback, options = {}) {
        const defaults = {
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: DigiColors.navy,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        };
        
        Swal.fire({...defaults, ...options}).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    },
    
    // Alerta de confirmación genérica
    confirm: function(options, callback) {
        const defaults = {
            title: '¿Confirmar acción?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: DigiColors.blue,
            cancelButtonColor: DigiColors.navy,
            confirmButtonText: '<i class="fas fa-check"></i> Confirmar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        };
        
        Swal.fire({...defaults, ...options}).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    },
    
    // Alerta de éxito con acción
    successAction: function(message, callback = null) {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: message,
            confirmButtonColor: DigiColors.green,
            timer: 2000,
            timerProgressBar: true
        }).then(() => {
            if (typeof callback === 'function') callback();
        });
    },
    
    // Alerta de error detallado
    errorDetail: function(title, message) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonColor: DigiColors.blue
        });
    },
    
    // Loading
    loading: function(message = 'Procesando...') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },
    
    // Cerrar loading
    closeLoading: function() {
        Swal.close();
    }
};

// Forzar cierre de overlays SweetAlert2 al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swal !== 'undefined') {
        Swal.close(); // Cierra cualquier overlay atascado
    }
});


<!-- Mostrar flash message si existe -->
<?php 
$flash = getFlashMessage();
if ($flash): 
    $type = $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : 'success');
    $msg = addslashes(htmlspecialchars($flash['message']));
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof DigiAlert !== 'undefined') {
        DigiAlert.<?= $type ?>('<?= $msg ?>');
    } else {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: '<?= $type ?>',
            title: '<?= $msg ?>',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    }
});
</script>
<?php endif; ?>

// DEBUG: Mostrar variables de sesión y flash para diagnóstico
<?php if (isset($_SESSION) && is_array($_SESSION)): ?>
<div style="background:#ffeeba;color:#856404;padding:10px;margin:10px 0;border:1px solid #ffeeba;">
  <strong>DEBUG SESSION:</strong><br>
  <pre><?= print_r($_SESSION, true) ?></pre>
</div>
<?php endif; ?>
</script>
</body>
</html>
