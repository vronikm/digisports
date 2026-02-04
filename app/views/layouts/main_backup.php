<?php 
// Valores por defecto para evitar errores
$tenant = $tenant ?? [];
$user = $user ?? [];
$modules = $modules ?? [];
$notifications = $notifications ?? [];
$notificationCount = $notificationCount ?? 0;
$title = $title ?? 'DigiSports';
$pageTitle = $pageTitle ?? '';
$currentController = $currentController ?? '';
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($title) ?> | DigiSports</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap 4.6 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE 3.2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        :root {
            --primary: <?php echo $tenant['color_primario'] ?? '#007bff' ?>;
            --secondary: <?php echo $tenant['color_secundario'] ?? '#6c757d' ?>;
            --accent: <?php echo $tenant['color_acento'] ?? '#28a745' ?>;
        }
        
        .brand-link {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .brand-text {
            font-weight: 700;
        }
        
        .nav-sidebar .nav-link.active {
            background-color: var(--primary) !important;
            color: #fff !important;
        }
        
        .small-box {
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        
        .small-box:hover {
            transform: translateY(-5px);
        }
        
        .module-card {
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .module-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .user-panel .info a {
            color: #fff;
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--primary);
        }
        
        .content-wrapper {
            background: #f4f6f9;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary);
            filter: brightness(90%);
        }
        
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 10px;
        }
        
        /* Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .page-loader.hidden {
            display: none;
        }
    </style>
    
    <?php if (isset($extraCss)): ?>
        <?php echo $extraCss; ?>
    <?php endif; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <!-- Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
    </div>

    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark" style="background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?php echo url('core', 'dashboard') ?>" class="nav-link">
                        <i class="fas fa-home me-1"></i> Inicio
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" title="Notificaciones">
                        <i class="far fa-bell"></i>
                        <?php if (($notificationCount ?? 0) > 0): ?>
                            <span class="badge badge-warning navbar-badge"><?php echo $notificationCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">
                            <?php echo ($notificationCount ?? 0) ?> Notificaciones
                        </span>
                        <div class="dropdown-divider"></div>
                        <?php if (!empty($notifications)): ?>
                            <?php foreach (array_slice($notifications, 0, 5) as $notif): ?>
                                <a href="<?php echo $notif['url_accion'] ?? '#' ?>" class="dropdown-item">
                                    <i class="<?php echo $notif['icono'] ?? 'fas fa-bell' ?> mr-2" style="color: <?php echo $notif['color'] ?? '#6c757d' ?>"></i>
                                    <?php echo htmlspecialchars($notif['titulo']) ?>
                                    <span class="float-right text-muted text-sm">
                                        <?php echo timeAgo($notif['fecha_creacion']) ?>
                                    </span>
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a href="#" class="dropdown-item text-center text-muted">
                                <i class="fas fa-check-circle mr-2"></i> Sin notificaciones nuevas
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <a href="<?php echo url('core', 'notificaciones') ?>" class="dropdown-item dropdown-footer">
                            Ver todas las notificaciones
                        </a>
                    </div>
                </li>
                
                <!-- Fullscreen -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Pantalla completa">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <img src="<?php echo $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode(($user['nombres'] ?? 'U') . '+' . ($user['apellidos'] ?? '')) . '&background=random' ?>" 
                             class="img-circle" alt="User" style="width: 30px; height: 30px; object-fit: cover;">
                        <span class="d-none d-md-inline ml-1">
                            <?php echo htmlspecialchars($user['nombres'] ?? 'Usuario') ?>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="<?php echo url('core', 'perfil') ?>" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Mi Perfil
                        </a>
                        <a href="<?php echo url('core', 'configuracion') ?>" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo url('core', 'auth', 'logout') ?>" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);">
            <!-- Brand Logo -->
            <a href="<?php echo url('core', 'dashboard') ?>" class="brand-link text-center">
                <i class="fas fa-futbol brand-image" style="font-size: 28px; color: #fff;"></i>
                <span class="brand-text font-weight-bold text-white">DigiSports</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="<?php echo $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode(($user['nombres'] ?? 'U') . '+' . ($user['apellidos'] ?? '')) . '&background=random' ?>" 
                             class="img-circle elevation-2" alt="User" style="width: 35px; height: 35px; object-fit: cover;">
                    </div>
                    <div class="info">
                        <a href="<?php echo url('core', 'perfil') ?>" class="d-block">
                            <?php echo htmlspecialchars(($user['nombres'] ?? 'Usuario') . ' ' . ($user['apellidos'] ?? '')) ?>
                        </a>
                        <small class="text-muted"><?php echo htmlspecialchars($tenant['nombre_comercial'] ?? 'DigiSports') ?></small>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                        
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="<?php echo url('core', 'dashboard') ?>" class="nav-link <?php echo ($currentController ?? '') === 'Dashboard' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <!-- Módulos dinámicos -->
                        <?php if (!empty($modules)): ?>
                            <li class="nav-header">MÓDULOS</li>
                            <?php foreach ($modules as $module): ?>
                                <?php if ($module['codigo'] !== 'CORE'): ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $module['es_externo'] === 'S' ? $module['url_base'] : url(strtolower($module['codigo']), 'dashboard') ?>" 
                                           class="nav-link"
                                           <?php echo $module['es_externo'] === 'S' ? 'target="_blank"' : '' ?>>
                                            <i class="nav-icon fas <?php echo $module['icono_personalizado'] ?? $module['icono'] ?? 'fa-cube' ?>" 
                                               style="color: <?php echo $module['color'] ?? '#6c757d' ?>"></i>
                                            <p>
                                                <?php echo htmlspecialchars($module['nombre_personalizado'] ?? $module['nombre']) ?>
                                                <?php if ($module['es_externo'] === 'S'): ?>
                                                    <i class="fas fa-external-link-alt right" style="font-size: 10px;"></i>
                                                <?php endif; ?>
                                            </p>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Instalaciones -->
                        <li class="nav-item <?php echo strpos($currentController ?? '', 'Cancha') !== false || strpos($currentController ?? '', 'Instalacion') !== false ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-building" style="color: #007bff;"></i>
                                <p>
                                    Instalaciones
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo url('instalaciones', 'cancha') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Canchas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo url('instalaciones', 'mantenimiento') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Mantenimiento</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Reservas -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt" style="color: #28a745;"></i>
                                <p>
                                    Reservas
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo url('reservas', 'reserva') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Gestión de Reservas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo url('reservas', 'calendario') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Calendario</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo url('reservas', 'abono') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Abonos</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Facturación -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-file-invoice-dollar" style="color: #ffc107;"></i>
                                <p>
                                    Facturación
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo url('facturacion', 'comprobante') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Comprobantes</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo url('facturacion', 'pago') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pagos</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Reportes -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar" style="color: #6f42c1;"></i>
                                <p>
                                    Reportes
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo url('reportes', 'kpi') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>KPIs</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo url('reportes', 'ocupacion') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Ocupación</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo url('reportes', 'ingresos') ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Ingresos</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <li class="nav-header">ADMINISTRACIÓN</li>
                        
                        <!-- Clientes -->
                        <li class="nav-item">
                            <a href="<?php echo url('core', 'cliente') ?>" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Clientes</p>
                            </a>
                        </li>
                        
                        <!-- Usuarios (solo admin) -->
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a href="<?php echo url('core', 'usuario') ?>" class="nav-link">
                                    <i class="nav-icon fas fa-user-cog"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>
                            
                            <!-- Configuración -->
                            <li class="nav-item">
                                <a href="<?php echo url('core', 'configuracion') ?>" class="nav-link">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>Configuración</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Super Admin -->
                        <?php if (isSuperAdmin()): ?>
                            <li class="nav-header">SUPER ADMIN</li>
                            <li class="nav-item">
                                <a href="<?php echo url('core', 'tenant') ?>" class="nav-link">
                                    <i class="nav-icon fas fa-building text-danger"></i>
                                    <p>Tenants</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo url('core', 'modulo') ?>" class="nav-link">
                                    <i class="nav-icon fas fa-puzzle-piece text-info"></i>
                                    <p>Módulos</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?php echo htmlspecialchars($pageTitle ?? $title ?? 'DigiSports') ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?php echo url('core', 'dashboard') ?>">Inicio</a></li>
                                <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                                    <?php foreach ($breadcrumb as $item): ?>
                                        <?php if (isset($item['active']) && $item['active']): ?>
                                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($item['title']) ?></li>
                                        <?php else: ?>
                                            <li class="breadcrumb-item">
                                                <a href="<?php echo $item['url'] ?? '#' ?>"><?php echo htmlspecialchars($item['title']) ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($pageTitle ?? $title ?? 'Página') ?></li>
                                <?php endif; ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Mensajes Flash -->
                    <?php if ($flashMessage = getFlashMessage('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo htmlspecialchars($flashMessage) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($flashMessage = getFlashMessage('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo htmlspecialchars($flashMessage) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($flashMessage = getFlashMessage('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?php echo htmlspecialchars($flashMessage) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($flashMessage = getFlashMessage('info')): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle mr-2"></i>
                            <?php echo htmlspecialchars($flashMessage) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Contenido de la página -->
                    <?php echo $content ?? '' ?>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Footer -->
        <footer class="main-footer">
            <strong>
                &copy; <?php echo date('Y') ?> 
                <a href="#"><?php echo htmlspecialchars($tenant['nombre_comercial'] ?? 'DigiSports') ?></a>
            </strong>
            <span class="float-right d-none d-sm-inline-block">
                <b>Versión</b> 1.0.0 | Powered by <a href="#" class="text-primary">DigiSports</a>
            </span>
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery 3.6 -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <!-- Bootstrap 4.6 Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE 3.2 App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- Chart.js 4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Ocultar loader cuando la página cargue
        window.addEventListener('load', function() {
            document.getElementById('pageLoader').classList.add('hidden');
        });
        
        // Token CSRF global para AJAX
        const CSRF_TOKEN = '<?php echo Security::generateCsrfToken() ?>';
        
        // Configurar AJAX para incluir CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });
        
        // Helper para mostrar notificaciones
        function showNotification(type, message, title = '') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: type,
                title: title || message,
                text: title ? message : ''
            });
        }
        
        // Helper para confirmar acciones
        function confirmAction(message, callback) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && typeof callback === 'function') {
                    callback();
                }
            });
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
    
    <?php if (isset($extraJs)): ?>
        <?php echo $extraJs; ?>
    <?php endif; ?>
</body>
</html>
