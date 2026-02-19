<?php
/**
 * DigiSports - Controlador de Autenticación
 * Maneja login, 2FA, recuperación de contraseña, logout
 * 
 * @package DigiSports\Controllers\Core
 * @version 1.0.0
 */

namespace App\Controllers\Core;

require_once BASE_PATH . '/app/controllers/BaseController.php';
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/config/app.php';

class AuthController extends \BaseController {
    
    /**
     * Manejar error de autenticación
     * Si es AJAX devuelve JSON, si no redirige a login con mensaje flash
     * 
     * @param string $message Mensaje de error
     * @param int $code Código HTTP
     */
    private function authError($message, $code = 400) {
        if ($this->isAjax()) {
            $this->error($message, $code);
        } else {
            setFlashMessage('error', $message);
            header('Location: ' . url('core', 'auth', 'login'));
            exit;
        }
    }
    
    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está autenticado, redirigir al Hub
        if (isAuthenticated()) {
            $redirect = $_SESSION['redirect_after_login'] ?? url('core', 'hub', 'index');
            header('Location: ' . $redirect);
            exit;
        }
        
        // Verificar si es timeout de sesión
        if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
            $this->viewData['warning'] = 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.';
        }
        
        // Verificar si la IP está bloqueada
        $ipBlocked = false;
        if (method_exists('\Security', 'isIPBlocked') && \Security::isIPBlocked()) {
            $this->viewData['error'] = 'Su IP ha sido bloqueada temporalmente por múltiples intentos fallidos. Intente más tarde.';
            $ipBlocked = true;
        }
        
        // Generar token CSRF
        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        $this->viewData['layout'] = 'auth';
        $this->viewData['title'] = 'Iniciar Sesión';
        $this->viewData['ip_blocked'] = $ipBlocked;
        
        $this->render('auth/login', $this->viewData);
    }
    
    /**
     * Procesar login
     */
    public function authenticate() {
        if (!$this->isPost()) {
            $this->authError('Solicitud inválida');
            return;
        }
        
        // Validar CSRF
        if (!$this->validateCsrf()) {
            $this->authError('Token de seguridad inválido', 403);
            return;
        }
        
        // Verificar IP bloqueada
        if (method_exists('\Security', 'isIPBlocked') && \Security::isIPBlocked()) {
            $this->authError('Su IP ha sido bloqueada temporalmente', 403);
            return;
        }
        
        $username = trim($this->post('username'));
        $password = $this->post('password');
        $remember = $this->post('remember') === 'on' || $this->post('remember') === '1';
        
        // Validar campos requeridos
        if (empty($username) || empty($password)) {
            $this->authError('Usuario y contraseña son requeridos');
            return;
        }
        
        try {
            // Buscar usuario: username en texto plano, email mediante blind index
            $emailHash = \DataProtection::blindIndex($username);
            $stmt = $this->db->prepare("
                SELECT 
                    u.*,
                    t.ten_estado_suscripcion,
                    t.ten_fecha_vencimiento,
                    r.rol_codigo,
                    r.rol_permisos,
                    r.rol_nivel_acceso
                FROM seguridad_usuarios u
                INNER JOIN seguridad_tenants t ON u.usu_tenant_id = t.ten_tenant_id
                INNER JOIN seguridad_roles r ON u.usu_rol_id = r.rol_rol_id
                WHERE (u.usu_username = ? OR u.usu_email_hash = ?)
                AND u.usu_estado = 'A'
                AND t.ten_estado IN ('A', 'ACTIVO', 'PRUEBA')
            ");
            $stmt->execute([$username, $emailHash]);
            $user = $stmt->fetch();
            
            // Descifrar datos personales del usuario
            if ($user) {
                $user = \DataProtection::decryptRow('seguridad_usuarios', $user);
            }
            
            if (!$user) {
                $this->handleFailedLogin($username, 'Usuario o email no encontrado o inactivo');
            }
            
            // Verificar si está bloqueado
            if ($user['usu_bloqueado_hasta'] && strtotime($user['usu_bloqueado_hasta']) > time()) {
                $tiempoRestante = ceil((strtotime($user['usu_bloqueado_hasta']) - time()) / 60);
                $this->handleFailedLogin($username, "Usuario bloqueado. Intente en {$tiempoRestante} minutos");
            }
            
            // Verificar contraseña
            if (!\Security::verifyPassword($password, $user['usu_password'])) {
                $this->handleFailedLogin($username, 'Contraseña incorrecta', $user['usu_usuario_id']);
            }
            
            // Verificar expiración de contraseña
            if ($user['usu_password_expira'] && strtotime($user['usu_password_expira']) < time()) {
                $_SESSION['temp_user_id'] = $user['usu_usuario_id'];
                $_SESSION['debe_cambiar_password'] = true;
                $this->success([
                    'require_password_change' => true,
                    'redirect' => url('core', 'auth', 'cambiarPassword')
                ], 'Debe cambiar su contraseña');
            }
            
            // Verificar si requiere 2FA
            if ($user['usu_requiere_2fa'] === 'S') {
                $this->initiate2FA($user);
            } else {
                // Login directo sin 2FA
                $this->completeLogin($user, $remember);
            }
            
        } catch (\Exception $e) {
            $this->logError("Error en autenticación: " . $e->getMessage());
            $this->authError('Error en el proceso de autenticación. Por favor intente más tarde');
        }
    }
    
    /**
     * Manejar intento de login fallido
     */
    private function handleFailedLogin($username, $message, $userId = null) {
        // Registrar intento fallido
        \Security::logSecurityEvent('LOGIN_FAILED', "Usuario: {$username} - {$message}");
        
        if ($userId) {
            // Incrementar intentos fallidos
            $stmt = $this->db->prepare("
                UPDATE seguridad_usuarios 
                SET usu_intentos_fallidos = usu_intentos_fallidos + 1,
                    usu_bloqueado_hasta = CASE 
                        WHEN usu_intentos_fallidos >= 4 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        ELSE usu_bloqueado_hasta
                    END
                WHERE usu_usuario_id = ?
            ");
            $stmt->execute([$userId]);
        }
        
        $this->authError($message, 401);
    }
    
    /**
     * Iniciar proceso de 2FA
     */
    private function initiate2FA($user) {
        // Generar código 2FA
        $codigo = \Security::generate2FACode();
        $expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Guardar código en BD
        $stmt = $this->db->prepare("
            UPDATE seguridad_usuarios 
            SET usu_codigo_2fa = ?,
                usu_codigo_2fa_expira = ?,
                usu_intentos_2fa = 0
            WHERE usu_usuario_id = ?
        ");
        $stmt->execute([$codigo, $expira, $user['usu_usuario_id']]);
        
        // Enviar código por email (el email ya viene descifrado del authenticate)
        $decryptedEmail = \DataProtection::isEncrypted($user['usu_email']) 
            ? \DataProtection::decrypt($user['usu_email']) 
            : $user['usu_email'];
        $this->send2FAEmail($decryptedEmail, $codigo, $user['usu_nombres']);
        
        // Guardar temporalmente el usuario en sesión
        $_SESSION['temp_user_id'] = $user['usu_usuario_id'];
        $_SESSION['temp_2fa_required'] = true;
        
        $this->success([
            'require_2fa' => true,
            'redirect' => url('core', 'auth', '2fa')
        ], 'Código de verificación enviado a su correo');
    }
    
    /**
     * Mostrar formulario de 2FA
     */
    public function twoFactorAuth() {
        if (!isset($_SESSION['temp_2fa_required'])) {
            redirect('core', 'auth', 'login');
        }
        
        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        $this->viewData['layout'] = 'auth';
        $this->viewData['title'] = 'Verificación en dos pasos';
        
        $this->render('auth/2fa', $this->viewData);
    }
    
    /**
     * Validar código 2FA
     */
    public function validate2FA() {
        if (!$this->isPost() || !isset($_SESSION['temp_user_id'])) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $codigo = $this->post('codigo');
        
        if (empty($codigo)) {
            $this->error('Código requerido');
        }
        
        try {
            // Obtener usuario temporal
            $stmt = $this->db->prepare("
                SELECT 
                    u.*,
                    r.rol_codigo,
                    r.rol_permisos,
                    r.rol_nivel_acceso
                FROM seguridad_usuarios u
                INNER JOIN seguridad_roles r ON u.usu_rol_id = r.rol_rol_id
                WHERE u.usu_usuario_id = ?
            ");
            $stmt->execute([$_SESSION['temp_user_id']]);
            $user = $stmt->fetch();
            
            // Descifrar datos personales
            if ($user) {
                $user = \DataProtection::decryptRow('seguridad_usuarios', $user);
            }
            
            if (!$user) {
                $this->error('Usuario no encontrado');
            }
            
            // Verificar código
            if ($user['usu_codigo_2fa'] !== $codigo) {
                // Incrementar intentos
                $this->db->prepare("
                    UPDATE seguridad_usuarios 
                    SET usu_intentos_2fa = usu_intentos_2fa + 1 
                    WHERE usu_usuario_id = ?
                ")->execute([$user['usu_usuario_id']]);
                
                // Si supera 3 intentos, bloquear
                if ($user['usu_intentos_2fa'] >= 2) {
                    $this->db->prepare("
                        UPDATE seguridad_usuarios 
                        SET usu_bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        WHERE usu_usuario_id = ?
                    ")->execute([$user['usu_usuario_id']]);
                    
                    unset($_SESSION['temp_user_id']);
                    unset($_SESSION['temp_2fa_required']);
                    
                    $this->error('Demasiados intentos fallidos. Usuario bloqueado por 15 minutos', 403);
                }
                
                $this->error('Código incorrecto. Intento ' . ($user['usu_intentos_2fa'] + 1) . ' de 3');
            }
            
            // Verificar expiración
            if (strtotime($user['usu_codigo_2fa_expira']) < time()) {
                $this->error('El código ha expirado. Solicite uno nuevo');
            }
            
            // Código válido, completar login
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_2fa_required']);
            
            $this->completeLogin($user, false);
            
        } catch (\Exception $e) {
            $this->logError("Error en 2FA: " . $e->getMessage());
            $this->error('Error en la validación');
        }
    }
    
    /**
     * Reenviar código 2FA
     */
    public function resend2FA() {
        if (!$this->isPost() || !isset($_SESSION['temp_user_id'])) {
            $this->error('Solicitud inválida');
        }
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM core_usuarios WHERE usuario_id = ?");
            $stmt->execute([$_SESSION['temp_user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->error('Usuario no encontrado');
            }
            
            // Generar nuevo código
            $codigo = \Security::generate2FACode();
            $expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $stmt = $this->db->prepare("
                UPDATE core_usuarios 
                SET codigo_2fa = ?,
                    codigo_2fa_expira = ?
                WHERE usuario_id = ?
            ");
            $stmt->execute([$codigo, $expira, $user['usuario_id']]);
            
            // Enviar email
            $this->send2FAEmail($user['email'], $codigo, $user['nombres']);
            
            $this->success(null, 'Nuevo código enviado a su correo');
            
        } catch (\Exception $e) {
            $this->logError("Error al reenviar 2FA: " . $e->getMessage());
            $this->error('Error al enviar el código');
        }
    }
    
    /**
     * Completar proceso de login
     */
    private function completeLogin($user, $remember = false) {
        try {
            // Resetear intentos fallidos
            $stmt = $this->db->prepare("
                UPDATE seguridad_usuarios 
                SET usu_intentos_fallidos = 0,
                    usu_bloqueado_hasta = NULL,
                    usu_ultimo_login = NOW(),
                    usu_ip_ultimo_login = ?,
                    usu_debe_cambiar_password = 'N'
                WHERE usu_usuario_id = ?
            ");
            $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN', $user['usu_usuario_id']]);
            
            // Cargar permisos y módulos
            $permisos = json_decode($user['rol_permisos'], true) ?? [];
            $modulos = $this->getUserModules($user['tenant_id']);
            
            // Iniciar sesión segura
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            
            session_regenerate_id(true);
            
            // Descifrar datos personales si aún están cifrados
            if (\DataProtection::isEncrypted($user['usu_email'] ?? '')) {
                $user = \DataProtection::decryptRow('seguridad_usuarios', $user);
            }
            
            // Variables de sesión
            $_SESSION['user_id'] = (int)$user['usu_usuario_id'];
            $_SESSION['tenant_id'] = (int)$user['usu_tenant_id'];
            $_SESSION['rol_id'] = isset($user['usu_rol_id']) ? (int)$user['usu_rol_id'] : null;
            $_SESSION['username'] = $user['usu_username'];
            $_SESSION['nombres'] = $user['usu_nombres'];
            $_SESSION['apellidos'] = $user['usu_apellidos'];
            $_SESSION['email'] = $user['usu_email'];
            $_SESSION['telefono'] = $user['usu_telefono'] ?? '';
            $_SESSION['celular'] = $user['usu_celular'] ?? '';
            $_SESSION['role'] = $user['rol_codigo'];
            $_SESSION['permissions'] = $permisos;
            $_SESSION['modules'] = $modulos;
            $_SESSION['avatar'] = $user['usu_avatar'] ?? null;
            $_SESSION['nivel_acceso'] = $user['rol_nivel_acceso'] ?? 1;

            // Nombre del tenant
            try {
                $stmtTenant = $this->db->prepare("SELECT ten_nombre_comercial FROM seguridad_tenants WHERE ten_tenant_id = ?");
                $stmtTenant->execute([(int)$user['usu_tenant_id']]);
                $tenantRow = $stmtTenant->fetch(\PDO::FETCH_ASSOC);
                $_SESSION['tenant_nombre'] = $tenantRow['ten_nombre_comercial'] ?? 'DigiSports';
            } catch (\Exception $e) {
                $_SESSION['tenant_nombre'] = 'DigiSports';
            }
            $_SESSION['created_at'] = time();
            $_SESSION['LAST_ACTIVITY'] = time();
            
            // Cookie de recordar (30 días) - solo si se solicita
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $stmt = $this->db->prepare("
                    UPDATE seguridad_usuarios 
                    SET usu_token_sesion = ?,
                        usu_token_sesion_expira = ?
                    WHERE usu_usuario_id = ?
                ");
                
                $stmt->execute([$token, $expira, $user['usu_usuario_id']]);
                
                setcookie(
                    'remember_token',
                    $token,
                    time() + (30 * 86400),
                    '/',
                    '',
                    true, // secure
                    true  // httponly
                );
            }
            
            // Auditoría
            $this->audit('usuarios', $user['usuario_id'], 'LOGIN', [], [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
            ]);
            
            // Log de seguridad
            \Security::logSecurityEvent('LOGIN_SUCCESS', "Usuario: {$user['username']}, Tenant: {$user['tenant_id']}");
            
            // Redirigir al Hub de aplicaciones
            $redirect = $_SESSION['redirect_after_login'] ?? url('core', 'hub', 'index');
            unset($_SESSION['redirect_after_login']);
            
            // Si es AJAX, devolver JSON; si no, redirigir directamente
            if ($this->isAjax()) {
                $this->success([
                    'redirect' => $redirect
                ], '¡Bienvenido ' . $user['nombres'] . '!');
            } else {
                // Guardar mensaje flash para mostrar después de la redirección
                setFlashMessage('success', '¡Bienvenido ' . $user['nombres'] . '!');
                header('Location: ' . $redirect);
                exit;
            }
            
        } catch (\Exception $e) {
            $this->logError("Error al completar login: " . $e->getMessage());
            
            if ($this->isAjax()) {
                $this->error('Error al iniciar sesión');
            } else {
                setFlashMessage('error', 'Error al iniciar sesión. Por favor intente nuevamente.');
                header('Location: ' . url('core', 'auth', 'login'));
                exit;
            }
        }
    }
    
    /**
     * Obtener módulos del usuario
     */
    private function getUserModules($tenantId) {
        try {
            // Primero intentar con la nueva tabla 'modulos'
            $stmt = $this->db->prepare("
                SELECT m.mod_codigo 
                FROM seguridad_modulos m
                INNER JOIN seguridad_tenant_modulos tm ON m.mod_id = tm.tmo_modulo_id
                WHERE tm.tmo_tenant_id = ? AND tm.tmo_activo = 'S'
            ");
            $stmt->execute([$tenantId]);
            $modulos = array_column($stmt->fetchAll(), 'mod_codigo');
            return $modulos;
            
        } catch (\Exception $e) {
            // Si hay error, devolver array vacío en lugar de fallar
            $this->logError("Error al obtener módulos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        if (isAuthenticated()) {
            // Auditoría
            $this->audit('usuarios', $this->userId, 'LOGOUT', [], []);
            
            // Log
            \Security::logSecurityEvent('LOGOUT', "Usuario: " . $_SESSION['username']);
        }
        
        // Limpiar cookie de recordar
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destruir sesión
        \Security::destroySession();
        
        redirect('core', 'auth', 'login');
    }
    
    /**
     * Mostrar formulario de recuperación de contraseña
     */
    public function recuperar() {
        if (isAuthenticated()) {
            redirect('core', 'dashboard');
        }
        
        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        $this->viewData['layout'] = 'auth';
        $this->viewData['title'] = 'Recuperar Contraseña';
        
        $this->render('auth/recuperar', $this->viewData);
    }
    
    /**
     * Enviar email de recuperación
     */
    public function enviarRecuperacion() {
        if (!$this->isPost()) {
            redirect('core', 'auth', 'recuperar');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $email = $this->post('email');
        
        if (empty($email) || !isValidEmail($email)) {
            $this->error('Email inválido');
        }
        
        try {
            // Buscar usuario
            $stmt = $this->db->prepare("
                SELECT usuario_id, username, nombres, email 
                FROM usuarios 
                WHERE email = ? AND estado = 'A'
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Por seguridad, siempre mostrar mensaje de éxito
            // aunque el email no exista
            if (!$user) {
                $this->success(null, 'Si el email existe, recibirá instrucciones para recuperar su contraseña');
            }
            
            // Generar token
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $this->db->prepare("
                UPDATE usuarios 
                SET token_recuperacion = ?,
                    token_recuperacion_expira = ?
                WHERE usuario_id = ?
            ");
            
            $stmt->execute([$token, $expira, $user['usuario_id']]);
            
            // Enviar email
            $this->sendRecoveryEmail($user['email'], $token, $user['nombres']);
            
            // Log
            \Security::logSecurityEvent('PASSWORD_RECOVERY_REQUEST', "Email: {$email}");
            
            $this->success(null, 'Si el email existe, recibirá instrucciones para recuperar su contraseña');
            
        } catch (\Exception $e) {
            $this->logError("Error en recuperación: " . $e->getMessage());
            $this->error('Error al procesar la solicitud');
        }
    }
    
    /**
     * Mostrar formulario de reset de contraseña
     */
    public function reset() {
        $token = $this->get('token');
        
        if (empty($token)) {
            setFlashMessage('error', 'Token inválido');
            redirect('core', 'auth', 'login');
        }
        
        // Verificar token
        $stmt = $this->db->prepare("
            SELECT usuario_id, nombres 
            FROM usuarios 
            WHERE token_recuperacion = ? 
            AND token_recuperacion_expira > NOW()
        ");
        
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            setFlashMessage('error', 'El enlace ha expirado o es inválido');
            redirect('core', 'auth', 'recuperar');
        }
        
        $this->viewData['token'] = $token;
        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        $this->viewData['layout'] = 'auth';
        $this->viewData['title'] = 'Restablecer Contraseña';
        
        $this->render('auth/reset', $this->viewData);
    }
    
    /**
     * Procesar reset de contraseña
     */
    public function procesarReset() {
        if (!$this->isPost()) {
            redirect('core', 'auth', 'login');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $token = $this->post('token');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        
        if (empty($token) || empty($password) || empty($passwordConfirm)) {
            $this->error('Todos los campos son requeridos');
        }
        
        if ($password !== $passwordConfirm) {
            $this->error('Las contraseñas no coinciden');
        }
        
        // Validar fortaleza de contraseña
        $validation = \Security::validatePasswordStrength($password);
        if (!$validation['valid']) {
            $this->error(implode('. ', $validation['errors']));
        }
        
        try {
            // Verificar token
            $stmt = $this->db->prepare("
                SELECT usuario_id 
                FROM usuarios 
                WHERE token_recuperacion = ? 
                AND token_recuperacion_expira > NOW()
            ");
            
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->error('El enlace ha expirado o es inválido');
            }
            
            // Actualizar contraseña
            $hashedPassword = \Security::hashPassword($password);
            $passwordExpira = date('Y-m-d', strtotime('+90 days'));
            
            $stmt = $this->db->prepare("
                UPDATE usuarios 
                SET password = ?,
                    password_expira = ?,
                    token_recuperacion = NULL,
                    token_recuperacion_expira = NULL,
                    debe_cambiar_password = 'N'
                WHERE usuario_id = ?
            ");
            
            $stmt->execute([$hashedPassword, $passwordExpira, $user['usuario_id']]);
            
            // Auditoría
            $this->audit('usuarios', $user['usuario_id'], 'PASSWORD_RESET', [], []);
            
            // Log
            \Security::logSecurityEvent('PASSWORD_RESET_SUCCESS', "UserID: {$user['usuario_id']}");
            
            $this->success([
                'redirect' => url('core', 'auth', 'login')
            ], 'Contraseña restablecida exitosamente. Ya puede iniciar sesión');
            
        } catch (\Exception $e) {
            $this->logError("Error al restablecer contraseña: " . $e->getMessage());
            $this->error('Error al procesar la solicitud');
        }
    }
    
    /**
     * Enviar email con código 2FA
     */
    private function send2FAEmail($email, $codigo, $nombre) {
        // TODO: Implementar envío real de email
        // Por ahora solo log
        $this->logError("Código 2FA para {$email}: {$codigo}");
        
        // En producción, usar PHPMailer o similar
        /*
        $mail = new PHPMailer();
        $mail->Subject = 'Código de verificación - DigiSports';
        $mail->Body = "Hola {$nombre}, tu código de verificación es: {$codigo}";
        $mail->addAddress($email);
        $mail->send();
        */
    }
    
    /**
     * Enviar email de recuperación
     */
    private function sendRecoveryEmail($email, $token, $nombre) {
        $resetUrl = url('core', 'auth', 'reset') . '&token=' . $token;
        
        // TODO: Implementar envío real
        $this->logError("URL de recuperación para {$email}: {$resetUrl}");
    }
    
    /**
     * Mostrar formulario de registro (nuevos tenants)
     */
    public function register() {
        if (isAuthenticated()) {
            $redirect = $_SESSION['redirect_after_login'] ?? url('core', 'hub', 'index');
            header('Location: ' . $redirect);
            exit;
        }
        
        $this->viewData['csrf_token'] = \Security::generateCsrfToken();
        $this->viewData['layout'] = 'auth';
        $this->viewData['title'] = 'Registrarse';
        
        $this->render('auth/register', $this->viewData);
    }
    
    /**
     * Procesar registro de nuevo tenant
     */
    public function crearTenant() {
        if (!$this->isPost()) {
            $this->error('Solicitud inválida');
        }
        
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        // Obtener datos
        $empresa = trim($this->post('empresa'));
        $ruc = trim($this->post('ruc'));
        $email = trim($this->post('email'));
        $username = trim($this->post('username'));
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        $nombres = trim($this->post('nombres'));
        $apellidos = trim($this->post('apellidos'));
        $telefono = trim($this->post('telefono'));
        
        // Validaciones
        $errors = [];
        
        if (empty($empresa) || strlen($empresa) < 3) {
            $errors[] = 'El nombre de la empresa debe tener al menos 3 caracteres';
        }
        
        if (empty($ruc) || !$this->validarRUC($ruc)) {
            $errors[] = 'RUC inválido';
        }
        
        if (empty($email) || !isValidEmail($email)) {
            $errors[] = 'Email inválido';
        }
        
        if (empty($username) || strlen($username) < 4) {
            $errors[] = 'El usuario debe tener al menos 4 caracteres';
        }
        
        if (empty($password) || empty($passwordConfirm)) {
            $errors[] = 'Las contraseñas son requeridas';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        // Validar fortaleza
        $validation = \Security::validatePasswordStrength($password);
        if (!$validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        }
        
        if (empty($nombres) || strlen($nombres) < 2) {
            $errors[] = 'Los nombres son requeridos';
        }
        
        if (empty($apellidos) || strlen($apellidos) < 2) {
            $errors[] = 'Los apellidos son requeridos';
        }
        
        if (!empty($errors)) {
            $this->error(implode('. ', $errors));
        }
        
        try {
            // Verificar duplicados
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM tenants 
                WHERE ruc = ? OR email_contacto = ?
            ");
            $stmt->execute([$ruc, $email]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $this->error('El RUC o Email ya está registrado en el sistema');
            }
            
            // Verificar usuario
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $this->error('El usuario ya existe');
            }
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            try {
                // 1. Crear tenant
                $planPorDefecto = Config::get('PLANES.PLAN_PRUEBA', 'PRUEBA');
                $stmt = $this->db->prepare("
                    INSERT INTO tenants (
                        nombre_empresa, ruc, email_contacto, telefono,
                        plan_id, estado, fecha_registro, ip_registro
                    ) VALUES (?, ?, ?, ?, ?, 'ACTIVO', NOW(), ?)
                ");
                
                $stmt->execute([
                    $empresa, $ruc, $email, $telefono,
                    $planPorDefecto, $_SERVER['REMOTE_ADDR']
                ]);
                
                $tenantId = $this->db->lastInsertId();
                
                // 2. Asignar módulos por defecto al tenant
                $stmt = $this->db->prepare("
                    INSERT INTO seguridad_tenant_modulos (tmo_tenant_id, tmo_modulo_id, tmo_activo, tmo_fecha_inicio)
                    SELECT ?, mod_id, 'S', CURDATE()
                    FROM seguridad_modulos
                    WHERE mod_activo = 1
                ");
                
                $stmt->execute([$tenantId]);
                
                // 3. Crear usuario administrador del tenant
                $hashedPassword = \Security::hashPassword($password);
                $passwordExpira = date('Y-m-d', strtotime('+90 days'));
                
                $stmt = $this->db->prepare("
                    INSERT INTO usuarios (
                        tenant_id, username, password, password_expira,
                        email, nombres, apellidos, telefono,
                        rol_id, estado, avatar,
                        requiere_2fa, debe_cambiar_password,
                        ip_creacion, fecha_creacion
                    ) VALUES (
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        (SELECT rol_id FROM roles WHERE codigo = 'ADMIN' LIMIT 1),
                        'A', NULL, 'S', 'N', ?, NOW()
                    )
                ");
                
                $stmt->execute([
                    $tenantId, $username, $hashedPassword, $passwordExpira,
                    $email, $nombres, $apellidos, $telefono,
                    $_SERVER['REMOTE_ADDR']
                ]);
                
                $usuarioId = $this->db->lastInsertId();
                
                // Commit transacción
                $this->db->commit();
                
                // Auditoría
                \Security::logSecurityEvent('NEW_TENANT_REGISTRATION', "Tenant: {$empresa}, User: {$username}");
                
                $this->success([
                    'redirect' => url('core', 'auth', 'login')
                ], 'Registro exitoso. Ya puede iniciar sesión');
                
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->logError("Error en registro: " . $e->getMessage());
            $this->error('Error al procesar el registro. Por favor intente más tarde');
        }
    }
    
    /**
     * Validar RUC (Ecuador)
     * 
     * @param string $ruc RUC a validar
     * @return bool
     */
    private function validarRUC($ruc) {
        // Remover guiones y espacios
        $ruc = str_replace(['-', ' '], '', $ruc);
        
        // Debe tener 13 dígitos
        if (!preg_match('/^\d{13}$/', $ruc)) {
            return false;
        }
        
        // Algoritmo de validación RUC Ecuador
        $coeficientes = [3, 2, 7, 6, 5, 4, 3, 2, 7, 6, 5, 4];
        $suma = 0;
        
        for ($i = 0; $i < 12; $i++) {
            $suma += intval($ruc[$i]) * $coeficientes[$i];
        }
        
        $digito = 11 - ($suma % 11);
        
        if ($digito == 11) {
            $digito = 0;
        } elseif ($digito == 10) {
            return false;
        }
        
        return intval($ruc[12]) === $digito;
    }
    
    /**
     * Cambiar contraseña del usuario actual
     */
    public function cambiarPassword() {
        if (!isAuthenticated()) {
            redirect('core', 'auth', 'login');
        }
        
        if (!$this->isPost()) {
            // Mostrar formulario
            $this->viewData['csrf_token'] = \Security::generateCsrfToken();
            $this->viewData['layout'] = 'main';
            $this->viewData['title'] = 'Cambiar Contraseña';
            
            $this->render('auth/cambiar-password', $this->viewData);
            return;
        }
        
        // Procesar cambio
        if (!$this->validateCsrf()) {
            $this->error('Token de seguridad inválido', 403);
        }
        
        $passwordActual = $this->post('password_actual');
        $passwordNueva = $this->post('password_nueva');
        $passwordConfirm = $this->post('password_confirm');
        
        if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirm)) {
            $this->error('Todos los campos son requeridos');
        }
        
        if ($passwordNueva !== $passwordConfirm) {
            $this->error('Las contraseñas nuevas no coinciden');
        }
        
        try {
            // Obtener contraseña actual
            $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE usuario_id = ?");
            $stmt->execute([$this->userId]);
            $user = $stmt->fetch();
            
            if (!$user || \Security::verifyPassword($passwordActual, $user['password'])) {
                $this->error('La contraseña actual es incorrecta');
            }
            
            // Validar fortaleza
            $validation = \Security::validatePasswordStrength($passwordNueva);
            if (!$validation['valid']) {
                $this->error(implode('. ', $validation['errors']));
            }
            
            // Actualizar
            $hashedPassword = \Security::hashPassword($passwordNueva);
            $passwordExpira = date('Y-m-d', strtotime('+90 days'));
            
            $stmt = $this->db->prepare("
                UPDATE usuarios 
                SET password = ?,
                    password_expira = ?,
                    debe_cambiar_password = 'N'
                WHERE usuario_id = ?
            ");
            
            $stmt->execute([$hashedPassword, $passwordExpira, $this->userId]);
            
            // Auditoría
            $this->audit('usuarios', $this->userId, 'PASSWORD_CHANGE', [], []);
            
            \Security::logSecurityEvent('PASSWORD_CHANGED', "UserID: {$this->userId}");
            
            $this->success(null, 'Contraseña actualizada exitosamente');
            
        } catch (\Exception $e) {
            $this->logError("Error al cambiar contraseña: " . $e->getMessage());
            $this->error('Error al cambiar la contraseña');
        }
    }
}
