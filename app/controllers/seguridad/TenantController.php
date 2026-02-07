<?php

namespace App\Controllers\Seguridad;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/controllers/seguridad/DashboardController.php';

class TenantController extends \App\Controllers\ModuleController {
    /**
     * Notificación masiva por correo (por vencer/vencidos)
     * Requiere PHPMailer instalado vía Composer
     * composer require phpmailer/phpmailer
     */
    public function notificarMasivo() {
        $this->authorize('editar', 'tenants');
        // DEBUG: Confirmar ejecución real del método
        setFlashMessage('info', 'Entró a notificarMasivo (debug)');
        try {
                            $logStmt = $this->db->prepare("INSERT INTO notificaciones_log (usuario_id, tenant_id, destinatario_email, tipo_notificacion, asunto, mensaje, enviado, error) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $logStmt->execute([
                                $_SESSION['usuario_id'] ?? null,
                                null,
                                null,
                                'debug',
                                'DEBUG: notificarMasivo',
                                'Entró a notificarMasivo (debug)',
                                0,
                                null
                            ]);
                        } catch (\Exception $e) {}
                $tipo = $_GET['tipo'] ?? '';
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                $resultados = [];
                try {
                    // Validar tipo
                    if (!in_array($tipo, ['por_vencer', 'vencidos'])) {
                        setFlashMessage('error', 'Tipo de notificación no válido');
                        redirect('seguridad', 'tenant', 'suscripciones');
                        return;
                    }
                    // Obtener destinatarios según tipo
                    if ($tipo === 'por_vencer') {
                        $stmt = $this->db->query("
                            SELECT ten_id, ten_email, ten_nombre_comercial, ten_razon_social, ten_fecha_vencimiento
                            FROM core_tenants
                            WHERE ten_estado = 'A' AND ten_fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        ");
                    } else {
                        $stmt = $this->db->query("
                            SELECT ten_id, ten_email, ten_nombre_comercial, ten_razon_social, ten_fecha_vencimiento
                            FROM core_tenants
                            WHERE ten_fecha_vencimiento < CURDATE()
                        ");
                    }
                    $destinatarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (empty($destinatarios)) {
                        // Registrar intento de notificación sin destinatarios
                        $logStmt = $this->db->prepare("INSERT INTO notificaciones_log (usuario_id, tenant_id, destinatario_email, tipo_notificacion, asunto, mensaje, enviado, error) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $logStmt->execute([
                            $usuarioId,
                            null,
                            null,
                            $tipo,
                            'Notificación masiva sin destinatarios',
                            'Intento de notificación masiva, pero no se encontraron destinatarios.',
                            0,
                            'No hay destinatarios para notificar.'
                        ]);
                        setFlashMessage('warning', 'No hay destinatarios para notificar.');
                        redirect('seguridad', 'tenant', 'suscripciones');
                        return;
                    }
                    // Configuración PHPMailer
                    require_once BASE_PATH . '/vendor/autoload.php';
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    // Cargar configuración SMTP externa
                    $smtp = require BASE_PATH . '/config/smtp.php';
                    $mail->isSMTP();
                    $mail->Host = $smtp['host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp['username'];
                    $mail->Password = $smtp['password'];
                    $mail->SMTPSecure = $smtp['secure'];
                    $mail->Port = $smtp['port'];
                    $mail->setFrom($smtp['from'], $smtp['from_name']);
                    $mail->isHTML(true);
                    $asunto = ($tipo === 'por_vencer') ? 'Tu suscripción está por vencer' : 'Tu suscripción ha vencido';
                    foreach ($destinatarios as $d) {
                        $to = $d['email'];
                        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) continue;
                        $nombre = $d['nombre_comercial'] ?: $d['razon_social'];
                        $fecha = date('d/m/Y', strtotime($d['fecha_vencimiento']));
                        $mensaje = '<div style="font-family:Arial,sans-serif;background:#f7f7f7;padding:30px;">
                            <div style="max-width:500px;margin:auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px #ddd;padding:30px;">
                                <div style="text-align:center;margin-bottom:20px;">
                                    <img src="https://i.imgur.com/1Q9Z1Zm.png" alt="DigiSports" style="height:48px;">
                                </div>
                                <h2 style="color:#2563eb;font-size:22px;margin-bottom:10px;">Hola, ' . htmlspecialchars($nombre) . '!</h2>';
                        if ($tipo === 'por_vencer') {
                            $mensaje .= '<p style="font-size:16px;color:#333;">Te recordamos que tu suscripción vence el <b style="color:#eab308;">' . $fecha . '</b>.<br>Por favor, realiza la renovación a tiempo para no perder acceso a DigiSports.</p>';
                        } else {
                            $mensaje .= '<p style="font-size:16px;color:#333;">Tu suscripción venció el <b style="color:#ef4444;">' . $fecha . '</b>.<br>Contacta a soporte para renovarla y seguir disfrutando de nuestros servicios.</p>';
                        }
                        $mensaje .= '<div style="margin:30px 0 10px 0;text-align:center;">
                            <a href="https://tudominio.com/renovar" style="background:#2563eb;color:#fff;padding:12px 28px;border-radius:5px;text-decoration:none;font-weight:bold;">Renovar Suscripción</a>
                        </div>
                        <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">
                        <p style="font-size:13px;color:#888;text-align:center;">Este es un mensaje automático de DigiSports.<br>Si tienes dudas, contáctanos a soporte@tudominio.com</p>
                        </div>
                        </div>';
                        try {
                            $mail->clearAddresses();
                            $mail->addAddress($to, $nombre);
                            $mail->Subject = $asunto;
                            $mail->Body = $mensaje;
                            $mail->send();
                            $enviado = 1;
                            $error = null;
                        } catch (\Exception $e) {
                            $enviado = 0;
                            $error = $mail->ErrorInfo;
                        }
                        // Log en base de datos
                        $logStmt = $this->db->prepare("INSERT INTO notificaciones_log (usuario_id, tenant_id, destinatario_email, tipo_notificacion, asunto, mensaje, enviado, error) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $logStmt->execute([
                            $usuarioId,
                            $d['tenant_id'],
                            $to,
                            $tipo,
                            $asunto,
                            $mensaje,
                            $enviado,
                            $error
                        ]);
                        $resultados[] = [
                            'email' => $to,
                            'enviado' => $enviado,
                            'error' => $error
                        ];
                    }
                    // Resumen visual
                    $total = count($resultados);
                    $ok = count(array_filter($resultados, fn($r) => $r['enviado']));
                    $fail = $total - $ok;
                    if ($ok > 0) {
                        setFlashMessage('success', "Notificaciones enviadas: $ok / $total. Fallidas: $fail");
                    } else {
                        setFlashMessage('error', "No se pudo enviar ninguna notificación. Verifica la configuración SMTP o los correos de destino.");
                    }
                    redirect('seguridad', 'tenant', 'suscripciones');
                } catch (\Exception $ex) {
                    setFlashMessage('error', 'Error inesperado al enviar notificaciones: ' . $ex->getMessage());
                    redirect('seguridad', 'tenant', 'suscripciones');
                }
            }
        /**
         * Registrar acción en auditoría (igual que UsuarioController)
         */
        private function registrarAuditoria($accion, $entidad, $entidadId, $datosAntes = null, $datosDespues = null, $resultado = 'exito', $mensaje = '') {
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            $tenantId = $_SESSION['tenant_id'] ?? null;
            $sql = "INSERT INTO auditoria_acciones (usuario_id, tenant_id, accion, entidad, entidad_id, datos_antes, datos_despues, ip, resultado, mensaje) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $usuarioId,
                $tenantId,
                $accion,
                $entidad,
                $entidadId,
                $datosAntes ? json_encode($datosAntes) : null,
                $datosDespues ? json_encode($datosDespues) : null,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $resultado,
                $mensaje
            ];
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            } catch (\Exception $e) {
                // Si falla la auditoría, no interrumpir el flujo principal
            }
        }
    /**
     * Editar tenant (GET: muestra formulario, POST: redirige a actualizar)
     */
    public function editar() {
        $id = $_GET['id'] ?? 0;
        if (!$id || !is_numeric($id)) {
            setFlashMessage('error', 'ID inválido');
            redirect('seguridad', 'tenant', 'index');
            return;
        }
        // Obtener datos del tenant
        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$id]);
        $tenant = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$tenant) {
            setFlashMessage('error', 'Tenant no encontrado');
            redirect('seguridad', 'tenant', 'index');
            return;
        }
        // Obtener planes activos
        $planes = $this->db->query("SELECT plan_id, nombre FROM planes_suscripcion WHERE estado = 'A' ORDER BY nombre")->fetchAll(\PDO::FETCH_ASSOC);
        // Obtener solo módulos activos y operativos
        $modulos = $this->db->query("
            SELECT m.*, tm.icono_personalizado, tm.color_personalizado, tm.activo
            FROM modulos m
            LEFT JOIN tenant_modulos tm ON m.id = tm.modulo_id AND tm.tenant_id = $id
            WHERE m.activo = 1 ORDER BY m.nombre
        ")->fetchAll(\PDO::FETCH_ASSOC);
        $modulosAsignados = [];
        foreach ($modulos as $m) {
            if (isset($m['activo']) && $m['activo'] === 'S') {
                $modulosAsignados[] = (int)$m['id'];
            }
        }
        // Log temporal para depuración de módulos asignados
        file_put_contents(
            BASE_PATH . '/storage/logs/tenant_modulos_debug.log',
            "==== " . date('Y-m-d H:i:s') . " ====" . PHP_EOL .
            'modulosAsignados: ' . print_r($modulosAsignados, true) . PHP_EOL,
            FILE_APPEND
        );
        $this->renderModule('seguridad/tenant/form', [
            'tenant' => $tenant,
            'planes' => $planes,
            'modulos' => $modulos,
            'modulosAsignados' => $modulosAsignados,
            'esEdicion' => true,
            'pageTitle' => 'Editar Tenant'
        ]);
    }

    /**
     * Actualizar tenant (POST seguro, con logs de auditoría)
     */
    public function actualizar() {
                // DEBUG: Log temporal para depuración
                file_put_contents(
                    BASE_PATH . '/storage/logs/tenant_modulos_debug.log',
                    "==== " . date('Y-m-d H:i:s') . " ====" . PHP_EOL .
                    'POST: ' . print_r($_POST, true) . PHP_EOL,
                    FILE_APPEND
                );
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashMessage('error', 'Método no permitido');
            redirect('seguridad', 'tenant', 'index');
            return;
        }
        $id = $_POST['tenant_id'] ?? 0;
        if (!$id || !is_numeric($id)) {
            setFlashMessage('error', 'ID inválido');
            redirect('seguridad', 'tenant', 'index');
            return;
        }
        // Validar y sanitizar campos
        $data = [
            'ruc' => trim($_POST['ruc'] ?? ''),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'nombre_comercial' => trim($_POST['nombre_comercial'] ?? ''),
            'tipo_empresa' => trim($_POST['tipo_empresa'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'celular' => trim($_POST['celular'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'sitio_web' => trim($_POST['sitio_web'] ?? ''),
            'representante_nombre' => trim($_POST['representante_nombre'] ?? ''),
            'representante_identificacion' => trim($_POST['representante_identificacion'] ?? ''),
            'representante_email' => trim($_POST['representante_email'] ?? ''),
            'representante_telefono' => trim($_POST['representante_telefono'] ?? ''),
            'plan_id' => trim($_POST['plan_id'] ?? ''),
            'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
            'fecha_vencimiento' => trim($_POST['fecha_vencimiento'] ?? ''),
            'usuarios_permitidos' => trim($_POST['usuarios_permitidos'] ?? 0),
            'sedes_permitidas' => trim($_POST['sedes_permitidas'] ?? 0),
            'monto_mensual' => trim($_POST['monto_mensual'] ?? 0),
            'color_primario' => trim($_POST['color_primario'] ?? ''),
            'color_secundario' => trim($_POST['color_secundario'] ?? ''),
            'estado' => trim($_POST['estado'] ?? 'A')
        ];
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        try {
            $this->db->beginTransaction();
            // Obtener datos previos para auditoría
            $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
            $stmt->execute([$id]);
            $previo = $stmt->fetch(\PDO::FETCH_ASSOC);
            // Actualizar tenant
            $sql = "UPDATE tenants SET 
                ruc = ?, razon_social = ?, nombre_comercial = ?, tipo_empresa = ?,
                direccion = ?, telefono = ?, celular = ?, email = ?, sitio_web = ?,
                representante_nombre = ?, representante_identificacion = ?, representante_email = ?, representante_telefono = ?,
                plan_id = ?, fecha_inicio = ?, fecha_vencimiento = ?,
                usuarios_permitidos = ?, sedes_permitidas = ?, monto_mensual = ?,
                color_primario = ?, color_secundario = ?, estado = ?
                WHERE tenant_id = ?";
            $params = array_values($data);
            $params[] = $id;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            // Eliminar todos los registros de módulos del tenant
            $stmt = $this->db->prepare("DELETE FROM tenant_modulos WHERE tenant_id = ?");
            $stmt->execute([$id]);
            // Insertar todos los módulos activos con el estado correcto y fecha_inicio actual
            $modulosSistema = $this->db->query("SELECT modulo_id FROM modulos_sistema WHERE estado = 'A'")->fetchAll(\PDO::FETCH_COLUMN);
            $modulosSeleccionados = isset($_POST['modulos']) && is_array($_POST['modulos']) ? array_map('intval', $_POST['modulos']) : [];
            $fechaHoy = date('Y-m-d');
            foreach ($modulosSistema as $modulo_id) {
                $activo = in_array((int)$modulo_id, $modulosSeleccionados) ? 'S' : 'N';
                $logMsg = "Intentando insertar: tenant_id=$id, modulo_id=$modulo_id, activo=$activo, fecha_inicio=$fechaHoy\n";
                file_put_contents(BASE_PATH . '/storage/logs/tenant_modulos_debug.log', $logMsg, FILE_APPEND);
                try {
                    $stmt = $this->db->prepare("INSERT INTO tenant_modulos (tenant_id, modulo_id, activo, fecha_inicio) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$id, $modulo_id, $activo, $fechaHoy]);
                    file_put_contents(BASE_PATH . '/storage/logs/tenant_modulos_debug.log', "OK: modulo_id=$modulo_id insertado.\n", FILE_APPEND);
                } catch (\Exception $e) {
                    file_put_contents(BASE_PATH . '/storage/logs/tenant_modulos_debug.log', "ERROR: modulo_id=$modulo_id - " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
            // Auditoría
            if (method_exists($this, 'registrarAuditoria')) {
                $this->registrarAuditoria('editar_tenant', 'tenant', $id, $previo, $data, 'exito', 'Edición de tenant');
            }
            $this->db->commit();
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Tenant actualizado correctamente']);
                exit;
            } else {
                setFlashMessage('success', 'Tenant actualizado correctamente');
                redirect('seguridad', 'tenant', 'index');
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            if (method_exists($this, 'registrarAuditoria')) {
                $this->registrarAuditoria('editar_tenant', 'tenant', $id, $previo ?? [], $data, 'error', $e->getMessage());
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
                exit;
            } else {
                setFlashMessage('error', 'Error al actualizar: ' . $e->getMessage());
                redirect('seguridad', 'tenant', 'editar', ['id' => $id]);
            }
        }
    }
// ...resto del código del controlador, asegurando que todo esté dentro de la clase...
    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'seguridad';
        $this->moduloNombre = 'Seguridad';
        $this->moduloIcono = 'fas fa-shield-alt';
        $this->moduloColor = '#F59E0B';
    }
    
    /**
     * Lista de tenants
     */
    public function index() {
        $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
        $plan = isset($_GET['plan_id']) ? trim($_GET['plan_id']) : '';
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $pagina = $_GET['pagina'] ?? 1;
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($estado) {
            if (in_array($estado, ['A','S','I'])) {
                $where .= " AND t.estado = ?";
                $params[] = $estado;
            }
        }
        
        if ($plan) {
            if (is_numeric($plan)) {
                $where .= " AND t.plan_id = ?";
                $params[] = $plan;
            }
        }
        
        if ($buscar) {
            $where .= " AND (t.razon_social LIKE ? OR t.nombre_comercial LIKE ? OR t.ruc LIKE ? OR t.email LIKE ?)";
            $buscarLike = "%$buscar%";
            $params = array_merge($params, [$buscarLike, $buscarLike, $buscarLike, $buscarLike]);
        }
        
        try {
            // Log de depuración: SQL y parámetros (solo después de definir $countSql)
            // Total de registros
            $countSql = "SELECT COUNT(*) FROM tenants t $where";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Obtener tenants
            $sql = "
                SELECT * FROM tenants t
                $where
                ORDER BY t.fecha_registro DESC
                LIMIT $porPagina OFFSET $offset
            ";
            error_log('DEBUG_SQL_TENANTS: ' . $sql);
            error_log('DEBUG_SQL_PARAMS_TENANTS: ' . print_r($params, true));
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $tenants = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Obtener planes para filtro
            $planes = $this->db->query("SELECT plan_id, nombre FROM planes_suscripcion WHERE estado = 'A' ORDER BY nombre")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $tenants = [];
            $planes = [];
            $total = 0;
        }
        // DEBUG: Log temporal para ver el contenido de $tenants
        error_log('DEBUG_TENANTS: ' . print_r($tenants, true));
        $this->renderModule('seguridad/tenant/index', [
            'tenants' => $tenants,
            'planes' => $planes,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => ceil($total / $porPagina),
            'filtros' => ['estado' => $estado, 'plan_id' => $plan, 'buscar' => $buscar],
            'pageTitle' => 'Gestión de Tenants'
        ]);
    }
    
    /**
     * Crear tenant
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'ruc' => $_POST['ruc'],
                    'razon_social' => $_POST['razon_social'],
                    'nombre_comercial' => $_POST['nombre_comercial'] ?? null,
                    'tipo_empresa' => $_POST['tipo_empresa'] ?? null,
                    'direccion' => $_POST['direccion'] ?? null,
                    'telefono' => $_POST['telefono'] ?? null,
                    'celular' => $_POST['celular'] ?? null,
                    'email' => $_POST['email'],
                    'sitio_web' => $_POST['sitio_web'] ?? null,
                    'representante_nombre' => $_POST['representante_nombre'] ?? null,
                    'representante_identificacion' => $_POST['representante_identificacion'] ?? null,
                    'representante_email' => $_POST['representante_email'] ?? null,
                    'representante_telefono' => $_POST['representante_telefono'] ?? null,
                    'plan_id' => $_POST['plan_id'],
                    'fecha_inicio' => $_POST['fecha_inicio'],
                    'fecha_vencimiento' => $_POST['fecha_vencimiento'],
                    'usuarios_permitidos' => $_POST['usuarios_permitidos'] ?? 5,
                    'sedes_permitidas' => $_POST['sedes_permitidas'] ?? 1,
                    'monto_mensual' => $_POST['monto_mensual'],
                    'color_primario' => $_POST['color_primario'] ?? '#007bff',
                    'color_secundario' => $_POST['color_secundario'] ?? '#6c757d',
                    'estado' => $_POST['estado'] ?? 'A'
                ];
                $this->db->beginTransaction();
                $sql = "INSERT INTO tenants (ruc, razon_social, nombre_comercial, tipo_empresa, direccion, telefono, celular, email, sitio_web, representante_nombre, representante_identificacion, representante_email, representante_telefono, plan_id, fecha_inicio, fecha_vencimiento, usuarios_permitidos, sedes_permitidas, monto_mensual, color_primario, color_secundario, estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array_values($data);
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $id = $this->db->lastInsertId();
                // Auditoría (si existe el método)
                if (method_exists($this, 'registrarAuditoria')) {
                    $this->registrarAuditoria('crear_tenant', 'tenant', $id, null, $data);
                }
                // Actualizar módulos si corresponde
                if (isset($_POST['modulos']) && is_array($_POST['modulos'])) {
                    $stmt = $this->db->prepare("UPDATE tenant_modulos SET activo = 'N' WHERE tenant_id = ?");
                    $stmt->execute([$id]);
                    // ...aquí podrías agregar lógica para activar módulos seleccionados...
                }
                $this->db->commit();
                setFlashMessage('success', 'Tenant creado correctamente');
                redirect('seguridad', 'tenant', 'index');
                return;
            } catch (\Exception $e) {
                $this->db->rollBack();
                if (method_exists($this, 'registrarAuditoria')) {
                    $this->registrarAuditoria('crear_tenant', 'tenant', null, null, null, 'error', $e->getMessage());
                }
                setFlashMessage('error', 'Error al guardar: ' . $e->getMessage());
                redirect('seguridad', 'tenant', 'crear');
                return;
            }
        }
        // Siempre mostrar el formulario en GET
        $this->renderModule('seguridad/tenant/crear', [
            'pageTitle' => 'Nuevo Tenant'
        ]);
    }
    
    /**
     * Guardar tenant
     */
    private function guardar($id = null) {
        $data = [
            'ruc' => $_POST['ruc'],
            'razon_social' => $_POST['razon_social'],
            'nombre_comercial' => $_POST['nombre_comercial'] ?? null,
            'tipo_empresa' => $_POST['tipo_empresa'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'celular' => $_POST['celular'] ?? null,
            'email' => $_POST['email'],
            'sitio_web' => $_POST['sitio_web'] ?? null,
            'representante_nombre' => $_POST['representante_nombre'] ?? null,
            'representante_identificacion' => $_POST['representante_identificacion'] ?? null,
            'representante_email' => $_POST['representante_email'] ?? null,
            'representante_telefono' => $_POST['representante_telefono'] ?? null,
            'plan_id' => $_POST['plan_id'],
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
            'usuarios_permitidos' => $_POST['usuarios_permitidos'] ?? 5,
            'sedes_permitidas' => $_POST['sedes_permitidas'] ?? 1,
            'monto_mensual' => $_POST['monto_mensual'],
            'color_primario' => $_POST['color_primario'] ?? '#007bff',
            'color_secundario' => $_POST['color_secundario'] ?? '#6c757d',
            'estado' => $_POST['estado'] ?? 'A'
        ];
        
        try {
            $this->db->beginTransaction();
            
            if ($id) {
                // Actualizar
                $sql = "UPDATE tenants SET 
                    ruc = ?, razon_social = ?, nombre_comercial = ?, tipo_empresa = ?,
                    direccion = ?, telefono = ?, celular = ?, email = ?, sitio_web = ?,
                    representante_nombre = ?, representante_identificacion = ?, representante_email = ?, representante_telefono = ?,
                    plan_id = ?, fecha_inicio = ?, fecha_vencimiento = ?,
                    usuarios_permitidos = ?, sedes_permitidas = ?, monto_mensual = ?,
                    color_primario = ?, color_secundario = ?, estado = ?
                    WHERE tenant_id = ?";
                $params = array_values($data);
                $params[] = $id;
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                
            } else {
                // Crear
                $sql = "INSERT INTO tenants (ruc, razon_social, nombre_comercial, tipo_empresa, direccion, telefono, celular, email, sitio_web, representante_nombre, representante_identificacion, representante_email, representante_telefono, plan_id, fecha_inicio, fecha_vencimiento, usuarios_permitidos, sedes_permitidas, monto_mensual, color_primario, color_secundario, estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array_values($data);
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $id = $this->db->lastInsertId();
            }
            
            // Actualizar módulos
            if (isset($_POST['modulos']) && is_array($_POST['modulos'])) {
                // Desactivar todos los módulos actuales
                $stmt = $this->db->prepare("UPDATE tenant_modulos SET activo = 'N' WHERE tenant_id = ?");
                $stmt->execute([$id]);
                
                // Activar los seleccionados
                foreach ($_POST['modulos'] as $moduloId) {
                    // Verificar si existe
                    $stmt = $this->db->prepare("SELECT tenant_modulo_id FROM tenant_modulos WHERE tenant_id = ? AND modulo_id = ?");
                    $stmt->execute([$id, $moduloId]);
                    
                    if ($stmt->fetch()) {
                        $stmt = $this->db->prepare("UPDATE tenant_modulos SET activo = 'S', fecha_activacion = CURDATE() WHERE tenant_id = ? AND modulo_id = ?");
                    } else {
                        $stmt = $this->db->prepare("INSERT INTO tenant_modulos (tenant_id, modulo_id, activo, fecha_activacion) VALUES (?, ?, 'S', CURDATE())");
                    }
                    $stmt->execute([$id, $moduloId]);
                }
            }
            
            $this->db->commit();
            setFlashMessage('success', $id ? 'Tenant actualizado correctamente' : 'Tenant creado correctamente');
            redirect('seguridad', 'tenant', 'index');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            setFlashMessage('error', 'Error al guardar: ' . $e->getMessage());
            if ($id) {
                redirect('seguridad', 'tenant', 'editar', ['id' => $id]);
            } else {
                redirect('seguridad', 'tenant', 'crear');
            }
        }
    }
    
    /**
     * Ver detalle de tenant
     */
    public function ver() {
        $id = $_GET['id'] ?? 0;
        
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, p.nombre as plan_nombre
                FROM tenants t
                LEFT JOIN planes_suscripcion p ON t.plan_id = p.plan_id
                WHERE t.tenant_id = ?
            ");
            $stmt->execute([$id]);
            $tenant = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$tenant) {
                setFlashMessage('error', 'Tenant no encontrado');
                redirect('seguridad', 'tenant', 'index');
                return;
            }
            
            // Usuarios del tenant
            $stmt = $this->db->prepare("
                SELECT u.*, r.nombre as rol_nombre
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.rol_id
                WHERE u.tenant_id = ?
                ORDER BY u.fecha_registro DESC
            ");
            $stmt->execute([$id]);
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Módulos del tenant (solo sistemas principales, con icono y color reales)
            $stmt = $this->db->prepare("
                SELECT tm.*, m.nombre, m.icono AS icono_sistema, m.color_fondo AS color_sistema
                FROM tenant_modulos tm
                JOIN modulos m ON tm.modulo_id = m.id
                WHERE tm.tenant_id = ? AND tm.activo = 'S'
                ORDER BY m.orden
            ");
            $stmt->execute([$id]);
            $modulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $tenant = null;
            $usuarios = [];
            $modulos = [];
        }
        
        $this->renderModule('seguridad/tenant/ver', [
            'tenant' => $tenant,
            'usuarios' => $usuarios,
            'modulos' => $modulos,
            'pageTitle' => 'Detalle Tenant'
        ]);
    }
    
    /**
     * Suspender tenant
     */
    public function suspender() {
        $id = $_GET['id'] ?? 0;
        $motivo = $_POST['motivo'] ?? 'Suspendido por administrador';
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $this->db->prepare("UPDATE tenants SET estado = 'S', estado_suscripcion = 'SUSPENDIDA', motivo_suspension = ?, fecha_suspension = NOW() WHERE tenant_id = ?");
                $stmt->execute([$motivo, $id]);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                } else {
                    setFlashMessage('success', 'Tenant suspendido');
                    redirect('seguridad', 'tenant', 'index');
                    return;
                }
            } catch (\Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error al suspender tenant']);
                    exit;
                } else {
                    setFlashMessage('error', 'Error al suspender tenant');
                    redirect('seguridad', 'tenant', 'index');
                    return;
                }
            }
        }
        // Mostrar vista de confirmación
        $this->renderModule('seguridad/tenant/suspender', [
            'tenant_id' => $id,
            'pageTitle' => 'Confirmar Suspensión de Tenant'
        ]);
    }
    
    /**
     * Reactivar tenant
     */
    public function reactivar() {
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            try {
                $stmt = $this->db->prepare("UPDATE tenants SET estado = 'A', estado_suscripcion = 'ACTIVA', motivo_suspension = NULL, fecha_suspension = NULL WHERE tenant_id = ?");
                $stmt->execute([$id]);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                } else {
                    setFlashMessage('success', 'Tenant reactivado');
                    redirect('seguridad', 'tenant', 'index');
                    return;
                }
            } catch (\Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error al reactivar tenant']);
                    exit;
                } else {
                    setFlashMessage('error', 'Error al reactivar tenant');
                    redirect('seguridad', 'tenant', 'index');
                    return;
                }
            }
        }
        // Mostrar vista de confirmación
        $this->renderModule('seguridad/tenant/reactivar', [
            'tenant_id' => $id,
            'pageTitle' => 'Confirmar Reactivación de Tenant'
        ]);
    }
    
    /**
     * Gestión de suscripciones
     */
    public function suscripciones() {
        try {
            // Suscripciones por vencer en 30 días
            $stmt = $this->db->query("
                SELECT t.*, p.nombre as plan_nombre, DATEDIFF(t.fecha_vencimiento, CURDATE()) as dias_restantes
                FROM tenants t
                LEFT JOIN planes_suscripcion p ON t.plan_id = p.plan_id
                WHERE t.estado = 'A' 
                AND t.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY t.fecha_vencimiento ASC
            ");
            $porVencer = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Suscripciones vencidas
            $stmt = $this->db->query("
                SELECT t.*, p.nombre as plan_nombre, DATEDIFF(CURDATE(), t.fecha_vencimiento) as dias_restantes
                FROM tenants t
                LEFT JOIN planes_suscripcion p ON t.plan_id = p.plan_id
                WHERE t.fecha_vencimiento < CURDATE()
                ORDER BY t.fecha_vencimiento DESC
            ");
            $vencidas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Resumen por plan
            $stmt = $this->db->query("
                SELECT p.nombre, p.precio_mensual, COUNT(t.tenant_id) as total,
                       SUM(t.monto_mensual) as ingresos_mensuales
                FROM planes_suscripcion p
                LEFT JOIN tenants t ON p.plan_id = t.plan_id AND t.estado = 'A'
                WHERE p.estado = 'A'
                GROUP BY p.plan_id
                ORDER BY p.precio_mensual
            ");
            $resumenPlanes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $porVencer = [];
            $vencidas = [];
            $resumenPlanes = [];
        }
        
        // Calcular resumen para KPIs
        $resumen = [
            'activos' => 0,
            'por_vencer' => 0,
            'vencidos' => 0,
            'ingresos_mes' => 0
        ];
        foreach ($porVencer as $t) {
            if (($t['dias_restantes'] ?? 0) <= 7) $resumen['por_vencer']++;
        }
        foreach ($vencidas as $t) {
            $resumen['vencidos']++;
        }
        foreach ($resumenPlanes as $plan) {
            $resumen['activos'] += $plan['total'] ?? 0;
            $resumen['ingresos_mes'] += $plan['ingresos_mensuales'] ?? 0;
        }
        $this->renderModule('seguridad/tenant/suscripciones', [
            'porVencer' => $porVencer,
            'vencidos' => $vencidas,
            'resumenPlanes' => $resumenPlanes,
            'resumen' => $resumen,
            'pageTitle' => 'Gestión de Suscripciones'
        ]);
    }
    
    /**
     * Renovar suscripción
     */
    public function renovar() {
        $id = $_GET['id'] ?? 0;
        $meses = $_POST['meses'] ?? 12;
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $this->db->prepare("
                    UPDATE tenants 
                    SET fecha_vencimiento = DATE_ADD(
                        CASE WHEN fecha_vencimiento < CURDATE() THEN CURDATE() ELSE fecha_vencimiento END, 
                        INTERVAL ? MONTH
                    ),
                    estado_suscripcion = 'ACTIVA'
                    WHERE tenant_id = ?
                ");
                $stmt->execute([$meses, $id]);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                } else {
                    setFlashMessage('success', "Suscripción renovada por $meses meses");
                    redirect('seguridad', 'tenant', 'suscripciones');
                    return;
                }
            } catch (\Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error al renovar suscripción']);
                    exit;
                } else {
                    setFlashMessage('error', 'Error al renovar suscripción');
                    redirect('seguridad', 'tenant', 'suscripciones');
                    return;
                }
            }
        }
        // Mostrar vista de confirmación
        $this->renderModule('seguridad/tenant/renovar', [
            'tenant_id' => $id,
            'pageTitle' => 'Confirmar Renovación de Tenant'
        ]);
    }
    
    /**
     * Obtener items del menú
     */
    protected function getMenuItems() {
        return (new \App\Controllers\Seguridad\DashboardController())->getMenuItems();
    }
}
