<?php
/**
 * DigiSports - Vista del Hub de Aplicaciones
 * Diseño con fondo azul degradado e iconos de módulos
 * 
 * @package DigiSports\Views\Core\Hub
 */

$modulos = $modulos ?? [];
$modulosOrganizados = $modulos_organizados ?? [];
$usuario = $usuario ?? 'Usuario';
$tenantNombre = $tenant_nombre ?? 'DigiSports';
$title = $title ?? 'DigiSports Hub';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 5.x para compatibilidad con iconos deportivos gratuitos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            padding: 20px;
        }
        
        .hub-container {
            max-width: 1400px;
            margin: 0 auto;
            padding-bottom: 40px;
            min-height: 100vh;
            overflow-x: auto;
        }
        
        /* Header */
        .hub-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0 40px;
        }
        
        .hub-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .hub-logo-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #3b82f6;
        }
        
        .hub-logo-text {
            color: white;
        }
        
        .hub-logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2px;
        }
        
        .hub-logo-text span {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .hub-user {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
        }
        
        .hub-user-info {
            text-align: right;
        }
        
        .hub-user-info .name {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .hub-user-info .tenant {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .hub-user-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .hub-user-avatar:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }
        
        /* Título central */
        .hub-title {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .hub-title h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .hub-title p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Grid de módulos */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
            width: 100%;
            min-width: 320px;
        }
        
        @media (max-width: 1200px) {
            .modules-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 900px) {
            .modules-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 600px) {
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Card de módulo */
        .module-card {
            background: white;
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--module-color, #3b82f6);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        
        .module-card:hover::before {
            transform: scaleX(1);
        }
        
        .module-icon {
            width: 80px;
            height: 80px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .module-card:hover .module-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .module-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .module-description {
            font-size: 0.85rem;
            color: #6b7280;
            line-height: 1.5;
            min-height: 60px;
        }
        
        /* Footer */
        .hub-footer {
            text-align: center;
            color: rgba(255,255,255,0.7);
            padding: 20px 0;
            font-size: 0.85rem;
        }
        
        .hub-footer a {
            color: white;
            text-decoration: none;
        }
        
        /* Animación de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .module-card {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }
        
        .module-card:nth-child(1) { animation-delay: 0.1s; }
        .module-card:nth-child(2) { animation-delay: 0.2s; }
        .module-card:nth-child(3) { animation-delay: 0.3s; }
        .module-card:nth-child(4) { animation-delay: 0.4s; }
        .module-card:nth-child(5) { animation-delay: 0.5s; }
        .module-card:nth-child(6) { animation-delay: 0.6s; }
        .module-card:nth-child(7) { animation-delay: 0.7s; }
        .module-card:nth-child(8) { animation-delay: 0.8s; }
        
        /* Alert de error/success */
        .hub-alert {
            max-width: 600px;
            margin: 0 auto 30px;
            padding: 15px 20px;
            border-radius: 12px;
            text-align: center;
            animation: fadeInUp 0.4s ease;
        }
        
        .hub-alert.error {
            background: rgba(239, 68, 68, 0.9);
            color: white;
        }
        
        .hub-alert.success {
            background: rgba(16, 185, 129, 0.9);
            color: white;
        }
    </style>
</head>
<body>
    <div class="hub-container">
        <!-- Header -->
        <header class="hub-header">
            <div class="hub-logo">
                <div class="hub-logo-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="hub-logo-text">
                    <h1>DigiSports</h1>
                    <span>Plataforma de Gestión Deportiva</span>
                </div>
            </div>
            
            <div class="hub-user">
                <div class="hub-user-info">
                    <div class="name"><?= htmlspecialchars($usuario) ?></div>
                    <div class="tenant"><?= htmlspecialchars($tenantNombre) ?></div>
                </div>
                <div class="hub-user-avatar" title="Mi perfil">
                    <i class="fas fa-user"></i>
                </div>
                <a href="<?= url('core', 'auth', 'logout') ?>" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Salir
                </a>
            </div>
        </header>
        
        <!-- Alertas -->
        <?php if (!empty($_SESSION['error'])): ?>
        <div class="hub-alert error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['success'])): ?>
        <div class="hub-alert success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- Título -->
        <div class="hub-title">
            <h2>¿Qué deseas gestionar hoy?</h2>
            <p>Selecciona un módulo para comenzar</p>
        </div>
        
        <!-- Grid de módulos -->
        <div class="modules-grid">
            <?php foreach ($modulos as $modulo): 
                // Generar URL encriptada para cada módulo
                $urlModulo = url('core', 'hub', 'acceder', ['modulo' => $modulo['codigo']]);
                $iconClass = trim($modulo['icono']);
                // Forzar siempre el prefijo 'fas' para FontAwesome 5
                if (strpos($iconClass, 'fas ') !== 0 && strpos($iconClass, 'fa-') === 0) {
                    $iconClass = 'fas ' . $iconClass;
                }
            ?>
            <div class="module-card" 
                 style="--module-color: <?= htmlspecialchars($modulo['color_fondo']) ?>;"
                 data-url="<?= htmlspecialchars($urlModulo) ?>"
                 onclick="accederModulo(this)">
                <div class="module-icon" style="background: <?= htmlspecialchars($modulo['color_fondo']) ?>;">
                    <i class="<?= htmlspecialchars($iconClass) ?>"></i>
                </div>
                <h3 class="module-name"><?= htmlspecialchars($modulo['nombre']) ?></h3>
                <p class="module-description"><?= htmlspecialchars($modulo['descripcion']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Footer -->
        <footer class="hub-footer">
            <p>&copy; <?= date('Y') ?> <a href="https://digitech.ec" target="_blank">DigiTech</a> - DigiSports. Todos los derechos reservados.</p>
        </footer>
    </div>
    
    <script>
        function accederModulo(element) {
            // Obtener la URL encriptada del atributo data
            const url = element.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        }
        
        // Efecto de hover en móviles
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('touchstart', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
    </script>
</body>
</html>
