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
$userEmail = $user_email ?? '';
$userAvatar = $user_avatar ?? null;
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
        
        /* ── Dropdown de usuario ── */
        .hub-user-menu-wrapper {
            position: relative;
        }
        
        .hub-dropdown {
            display: none;
            position: absolute;
            top: 55px;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            min-width: 260px;
            z-index: 1000;
            overflow: hidden;
            animation: dropdownFadeIn 0.2s ease;
        }
        
        .hub-dropdown.show {
            display: block;
        }
        
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hub-dropdown-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
        }
        
        .hub-dropdown-header strong {
            display: block;
            font-size: 0.95rem;
        }
        
        .hub-dropdown-header small {
            opacity: 0.85;
            font-size: 0.8rem;
        }
        
        .hub-dropdown-divider {
            height: 1px;
            background: #e5e7eb;
        }
        
        .hub-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #374151;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.15s;
            cursor: pointer;
        }
        
        .hub-dropdown-item:hover {
            background: #f3f4f6;
        }
        
        .hub-dropdown-item i {
            width: 18px;
            text-align: center;
            color: #6b7280;
        }
        
        .hub-dropdown-danger {
            color: #dc2626;
        }
        
        .hub-dropdown-danger i {
            color: #dc2626;
        }
        
        .hub-dropdown-danger:hover {
            background: #fef2f2;
        }
        
        /* ── Modales ── */
        .hub-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }
        
        .hub-modal-overlay.show {
            display: flex;
        }
        
        .hub-modal {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
            overflow: hidden;
        }
        
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .hub-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
        }
        
        .hub-modal-header h3 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 600;
        }
        
        .hub-modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        
        .hub-modal-close:hover {
            background: rgba(255,255,255,0.35);
        }
        
        .hub-modal-body {
            padding: 24px;
        }
        
        .hub-form-group {
            margin-bottom: 18px;
        }
        
        .hub-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .hub-form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.2s;
        }
        
        .hub-form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
        
        .hub-form-group .field-hint {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 4px;
        }
        
        .hub-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        
        .hub-btn {
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }
        
        .hub-btn-cancel {
            background: #e5e7eb;
            color: #374151;
        }
        
        .hub-btn-cancel:hover {
            background: #d1d5db;
        }
        
        .hub-btn-primary {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
        }
        
        .hub-btn-primary:hover {
            box-shadow: 0 4px 15px rgba(59,130,246,0.4);
            transform: translateY(-1px);
        }
        
        .hub-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .hub-form-msg {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 15px;
            display: none;
        }
        
        .hub-form-msg.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .hub-form-msg.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            margin-top: 6px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 2px;
            transition: width 0.3s, background 0.3s;
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
                <div class="hub-user-menu-wrapper" style="position:relative;">
                    <div class="hub-user-avatar" title="Mi perfil" onclick="toggleUserMenu(event)">
                        <i class="fas fa-user"></i>
                    </div>
                    <!-- Dropdown -->
                    <div class="hub-dropdown" id="hubUserDropdown">
                        <div class="hub-dropdown-header">
                            <strong><?= htmlspecialchars($usuario) ?></strong>
                            <small><?= htmlspecialchars($userEmail) ?></small>
                        </div>
                        <div class="hub-dropdown-divider"></div>
                        <a href="#" class="hub-dropdown-item" onclick="abrirModalPerfil(); return false;">
                            <i class="fas fa-user-edit"></i> Actualizar Perfil
                        </a>
                        <a href="#" class="hub-dropdown-item" onclick="abrirModalClave(); return false;">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </a>
                        <div class="hub-dropdown-divider"></div>
                        <a href="<?= url('core', 'auth', 'logout') ?>" class="hub-dropdown-item hub-dropdown-danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
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
                $codigo = isset($modulo['codigo']) ? $modulo['codigo'] : '';
                $urlModulo = url('core', 'hub', 'acceder', ['modulo' => $codigo]);
                $icono = isset($modulo['icono']) ? $modulo['icono'] : '';
                $iconClass = trim((string)$icono);
                // Forzar siempre el prefijo 'fas' para FontAwesome 5
                if (strpos($iconClass, 'fas ') !== 0 && strpos($iconClass, 'fa-') === 0) {
                    $iconClass = 'fas ' . $iconClass;
                }
                $color = isset($modulo['color_fondo']) ? $modulo['color_fondo'] : '#1e40af';
                $nombre = isset($modulo['nombre']) ? $modulo['nombre'] : 'Módulo';
                $descripcion = isset($modulo['descripcion']) ? $modulo['descripcion'] : '';
            ?>
            <div class="module-card" 
                 style="--module-color: <?= htmlspecialchars((string)$color) ?>;"
                 data-url="<?= htmlspecialchars((string)$urlModulo) ?>"
                 onclick="accederModulo(this)">
                <div class="module-icon" style="background: <?= htmlspecialchars((string)$color) ?>;">
                    <i class="<?= htmlspecialchars((string)$iconClass) ?>"></i>
                </div>
                <h3 class="module-name"><?= htmlspecialchars((string)$nombre) ?></h3>
                <p class="module-description"><?= htmlspecialchars((string)$descripcion) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Footer -->
        <footer class="hub-footer">
            <p>&copy; <?= date('Y') ?> <a href="https://digitech.ec" target="_blank">DigiTech</a> - DigiSports. Todos los derechos reservados.</p>
        </footer>
    </div>
    
    <!-- Modal: Actualizar Perfil -->
    <div class="hub-modal-overlay" id="modalPerfil">
        <div class="hub-modal">
            <div class="hub-modal-header">
                <h3><i class="fas fa-user-edit"></i> Actualizar Perfil</h3>
                <button class="hub-modal-close" onclick="cerrarModal('modalPerfil')">&times;</button>
            </div>
            <div class="hub-modal-body">
                <div class="hub-form-msg" id="perfilMsg"></div>
                <form id="formPerfil" onsubmit="guardarPerfil(event)">
                    <div class="hub-form-group">
                        <label for="perfNombres"><i class="fas fa-user"></i> Nombres</label>
                        <input type="text" id="perfNombres" name="nombres" value="<?= htmlspecialchars($_SESSION['nombres'] ?? '') ?>" required>
                    </div>
                    <div class="hub-form-group">
                        <label for="perfApellidos"><i class="fas fa-user"></i> Apellidos</label>
                        <input type="text" id="perfApellidos" name="apellidos" value="<?= htmlspecialchars($_SESSION['apellidos'] ?? '') ?>" required>
                    </div>
                    <div class="hub-form-group">
                        <label for="perfEmail"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="perfEmail" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required>
                    </div>
                    <div class="hub-form-group">
                        <label for="perfTelefono"><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="text" id="perfTelefono" name="telefono" value="<?= htmlspecialchars($_SESSION['telefono'] ?? '') ?>">
                    </div>
                    <div class="hub-form-group">
                        <label for="perfCelular"><i class="fas fa-mobile-alt"></i> Celular</label>
                        <input type="text" id="perfCelular" name="celular" value="<?= htmlspecialchars($_SESSION['celular'] ?? '') ?>">
                    </div>
                    <div class="hub-modal-footer">
                        <button type="button" class="hub-btn hub-btn-cancel" onclick="cerrarModal('modalPerfil')">Cancelar</button>
                        <button type="submit" class="hub-btn hub-btn-primary" id="btnGuardarPerfil">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal: Cambiar Contraseña -->
    <div class="hub-modal-overlay" id="modalClave">
        <div class="hub-modal">
            <div class="hub-modal-header">
                <h3><i class="fas fa-key"></i> Cambiar Contraseña</h3>
                <button class="hub-modal-close" onclick="cerrarModal('modalClave')">&times;</button>
            </div>
            <div class="hub-modal-body">
                <div class="hub-form-msg" id="claveMsg"></div>
                <form id="formClave" onsubmit="guardarClave(event)">
                    <div class="hub-form-group">
                        <label for="claveActual"><i class="fas fa-lock"></i> Contraseña Actual</label>
                        <input type="password" id="claveActual" name="password_actual" required autocomplete="current-password">
                    </div>
                    <div class="hub-form-group">
                        <label for="claveNueva"><i class="fas fa-lock-open"></i> Nueva Contraseña</label>
                        <input type="password" id="claveNueva" name="password_nueva" required autocomplete="new-password"
                               oninput="evaluarFortaleza(this.value)">
                        <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
                        <div class="field-hint">Mínimo 8 caracteres, incluya mayúsculas, números y símbolos</div>
                    </div>
                    <div class="hub-form-group">
                        <label for="claveConfirmar"><i class="fas fa-check-double"></i> Confirmar Nueva Contraseña</label>
                        <input type="password" id="claveConfirmar" name="password_confirmar" required autocomplete="new-password">
                    </div>
                    <div class="hub-modal-footer">
                        <button type="button" class="hub-btn hub-btn-cancel" onclick="cerrarModal('modalClave')">Cancelar</button>
                        <button type="submit" class="hub-btn hub-btn-primary" id="btnGuardarClave">
                            <i class="fas fa-save"></i> Cambiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function accederModulo(element) {
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
        
        /* ── Dropdown de usuario ── */
        function toggleUserMenu(e) {
            e.stopPropagation();
            document.getElementById('hubUserDropdown').classList.toggle('show');
        }
        
        document.addEventListener('click', function(e) {
            const dd = document.getElementById('hubUserDropdown');
            if (dd && !dd.contains(e.target) && !e.target.closest('.hub-user-avatar')) {
                dd.classList.remove('show');
            }
        });
        
        /* ── Modales ── */
        function abrirModalPerfil() {
            document.getElementById('hubUserDropdown').classList.remove('show');
            document.getElementById('modalPerfil').classList.add('show');
            resetMsg('perfilMsg');
        }
        
        function abrirModalClave() {
            document.getElementById('hubUserDropdown').classList.remove('show');
            document.getElementById('modalClave').classList.add('show');
            document.getElementById('formClave').reset();
            resetMsg('claveMsg');
            document.getElementById('strengthBar').style.width = '0';
        }
        
        function cerrarModal(id) {
            document.getElementById(id).classList.remove('show');
        }
        
        // Cerrar modal con Escape o clic fuera
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.hub-modal-overlay.show').forEach(m => m.classList.remove('show'));
            }
        });
        
        document.querySelectorAll('.hub-modal-overlay').forEach(ov => {
            ov.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('show');
            });
        });
        
        function showMsg(id, msg, type) {
            const el = document.getElementById(id);
            el.textContent = msg;
            el.className = 'hub-form-msg ' + type;
            el.style.display = 'block';
        }
        
        function resetMsg(id) {
            const el = document.getElementById(id);
            el.style.display = 'none';
            el.className = 'hub-form-msg';
        }
        
        /* ── Guardar perfil ── */
        function guardarPerfil(e) {
            e.preventDefault();
            const btn = document.getElementById('btnGuardarPerfil');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            resetMsg('perfilMsg');
            
            const data = new FormData(document.getElementById('formPerfil'));
            
            fetch('<?= url('core', 'hub', 'actualizarPerfil') ?>', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showMsg('perfilMsg', res.message || 'Perfil actualizado correctamente', 'success');
                    // Actualizar nombre en la UI
                    const nuevoNombre = data.get('nombres') + ' ' + data.get('apellidos');
                    document.querySelectorAll('.hub-user-info .name').forEach(el => el.textContent = nuevoNombre);
                    document.querySelector('.hub-dropdown-header strong').textContent = nuevoNombre;
                    document.querySelector('.hub-dropdown-header small').textContent = data.get('email');
                    setTimeout(() => cerrarModal('modalPerfil'), 1500);
                } else {
                    showMsg('perfilMsg', res.message || 'Error al actualizar', 'error');
                }
            })
            .catch(() => showMsg('perfilMsg', 'Error de conexión', 'error'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
            });
        }
        
        /* ── Cambiar contraseña ── */
        function guardarClave(e) {
            e.preventDefault();
            const nueva = document.getElementById('claveNueva').value;
            const confirmar = document.getElementById('claveConfirmar').value;
            
            if (nueva !== confirmar) {
                showMsg('claveMsg', 'Las contraseñas no coinciden', 'error');
                return;
            }
            
            if (nueva.length < 8) {
                showMsg('claveMsg', 'La contraseña debe tener al menos 8 caracteres', 'error');
                return;
            }
            
            const btn = document.getElementById('btnGuardarClave');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
            resetMsg('claveMsg');
            
            const data = new FormData(document.getElementById('formClave'));
            
            fetch('<?= url('core', 'hub', 'cambiarClave') ?>', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showMsg('claveMsg', res.message || 'Contraseña cambiada correctamente', 'success');
                    document.getElementById('formClave').reset();
                    document.getElementById('strengthBar').style.width = '0';
                    setTimeout(() => cerrarModal('modalClave'), 1500);
                } else {
                    showMsg('claveMsg', res.message || 'Error al cambiar contraseña', 'error');
                }
            })
            .catch(() => showMsg('claveMsg', 'Error de conexión', 'error'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Cambiar';
            });
        }
        
        /* ── Indicador de fortaleza ── */
        function evaluarFortaleza(pwd) {
            let score = 0;
            if (pwd.length >= 8) score++;
            if (pwd.length >= 12) score++;
            if (/[A-Z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;
            
            const bar = document.getElementById('strengthBar');
            const pct = (score / 5) * 100;
            bar.style.width = pct + '%';
            
            if (score <= 1) bar.style.background = '#ef4444';
            else if (score <= 2) bar.style.background = '#f97316';
            else if (score <= 3) bar.style.background = '#eab308';
            else if (score <= 4) bar.style.background = '#22c55e';
            else bar.style.background = '#10b981';
        }
    </script>
</body>
</html>
