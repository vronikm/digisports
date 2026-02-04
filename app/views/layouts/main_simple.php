<?php 
// Valores por defecto
$tenant = $tenant ?? [];
$user = $user ?? [];
$modules = $modules ?? [];
$notifications = $notifications ?? [];
$notificationCount = $notificationCount ?? 0;
$title = $title ?? 'DigiSports';
$pageTitle = $pageTitle ?? 'Dashboard';
$currentController = $currentController ?? '';
$content = $content ?? '';

// Función getFlashMessage si no existe
if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        if (session_status() === PHP_SESSION_NONE) {
            return null;
        }
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title) ?> | DigiSports</title>
    
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap 4.6 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE 3.2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- DigiTech Corporate Colors -->
    <style>
        :root {
            --digitech-blue: #2F39F2;
            --digitech-green: #08DC64;
            --digitech-navy: #2C285B;
            --digitech-beige: #ECE1D4;
        }
        
        /* Navbar */
        .navbar-digitech {
            background: linear-gradient(135deg, var(--digitech-blue) 0%, var(--digitech-navy) 100%) !important;
        }
        
        /* Sidebar */
        .sidebar-digitech {
            background: var(--digitech-navy) !important;
        }
        .sidebar-digitech .nav-link.active {
            background-color: var(--digitech-blue) !important;
        }
        .sidebar-digitech .nav-link:hover {
            background-color: rgba(47, 57, 242, 0.3) !important;
        }
        
        /* Brand Link */
        .brand-link-digitech {
            background: var(--digitech-navy) !important;
            border-bottom: 1px solid rgba(8, 220, 100, 0.3) !important;
        }
        
        /* Buttons */
        .btn-digitech {
            background-color: var(--digitech-blue);
            border-color: var(--digitech-blue);
            color: #fff;
        }
        .btn-digitech:hover {
            background-color: #2530d9;
            border-color: #2530d9;
            color: #fff;
        }
        
        .btn-digitech-success {
            background-color: var(--digitech-green);
            border-color: var(--digitech-green);
            color: #fff;
        }
        .btn-digitech-success:hover {
            background-color: #06b852;
            border-color: #06b852;
            color: #fff;
        }
        
        /* Small boxes */
        .small-box.bg-digitech-blue {
            background-color: var(--digitech-blue) !important;
        }
        .small-box.bg-digitech-green {
            background-color: var(--digitech-green) !important;
        }
        .small-box.bg-digitech-navy {
            background-color: var(--digitech-navy) !important;
        }
        
        /* Links */
        a {
            color: var(--digitech-blue);
        }
        a:hover {
            color: var(--digitech-navy);
        }
        
        /* Badge accent */
        .badge-digitech {
            background-color: var(--digitech-green);
            color: #fff;
        }
        
        /* User panel */
        .user-panel .info a {
            color: #fff !important;
        }
        
        /* Nav header */
        .nav-header {
            color: var(--digitech-green) !important;
            font-weight: 600;
        }
        
        /* Footer */
        .main-footer {
            border-top: 2px solid var(--digitech-green);
        }
        
        /* Breadcrumb */
        .breadcrumb-item.active {
            color: var(--digitech-blue);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark navbar-digitech">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="#" class="nav-link">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <?php if ($notificationCount > 0): ?>
                            <span class="badge badge-warning navbar-badge"><?php echo $notificationCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header"><?php echo $notificationCount ?> Notificaciones</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> Sin notificaciones nuevas
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Ver todas</a>
                    </div>
                </li>
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle"></i>
                        <span class="d-none d-md-inline ml-1"><?php echo htmlspecialchars($user['nombres'] ?? 'Usuario') ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Mi Perfil
                        </a>
                        <a href="#" class="dropdown-item">
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

        <!-- Sidebar -->
        <aside class="main-sidebar elevation-4 sidebar-digitech">
            <a href="<?php echo url('core', 'dashboard') ?>" class="brand-link text-center brand-link-digitech">
                <i class="fas fa-futbol" style="font-size: 24px;"></i>
                <span class="brand-text font-weight-bold">DigiSports</span>
            </a>
            <div class="sidebar">
                <!-- User Panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-light"></i>
                    </div>
                    <div class="info">
                        <a href="#" class="d-block text-light">
                            <?php echo htmlspecialchars(($user['nombres'] ?? 'Usuario') . ' ' . ($user['apellidos'] ?? '')) ?>
                        </a>
                        <small class="text-muted"><?php echo htmlspecialchars($tenant['nombre_comercial'] ?? 'DigiSports') ?></small>
                    </div>
                </div>

                <!-- Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="<?php echo url('core', 'dashboard') ?>" class="nav-link <?php echo $currentController === 'Dashboard' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-header">MÓDULOS</li>
                        <li class="nav-item">
                            <a href="<?php echo url('instalaciones', 'cancha') ?>" class="nav-link">
                                <i class="nav-icon fas fa-building text-info"></i>
                                <p>Instalaciones</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo url('reservas', 'reserva') ?>" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt text-success"></i>
                                <p>Reservas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo url('facturacion', 'comprobante') ?>" class="nav-link">
                                <i class="nav-icon fas fa-file-invoice-dollar text-warning"></i>
                                <p>Facturación</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo url('reportes', 'kpi') ?>" class="nav-link">
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

        <!-- Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?php echo htmlspecialchars($pageTitle) ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?php echo url('core', 'dashboard') ?>">Inicio</a></li>
                                <li class="breadcrumb-item active"><?php echo htmlspecialchars($pageTitle) ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <?php 
                    // Mostrar mensajes flash
                    $flash = getFlashMessage();
                    if ($flash): 
                    ?>
                        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check' : 'exclamation' ?>-circle mr-2"></i>
                            <?php echo htmlspecialchars($flash['message']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php echo $content ?>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>&copy; <?php echo date('Y') ?> <a href="https://digitech.ec" target="_blank" style="color: var(--digitech-blue);">DigiTech</a></strong> - DigiSports. Todos los derechos reservados.
            <div class="float-right d-none d-sm-inline-block">
                <span style="color: var(--digitech-green);">●</span> <b>Versión</b> 1.0.0
            </div>
        </footer>
    </div>

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <!-- Bootstrap 4.6 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE 3.2 -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Ocultar loader
        $(document).ready(function() {
            $('.page-loader').addClass('hidden');
        });
    </script>
</body>
</html>
