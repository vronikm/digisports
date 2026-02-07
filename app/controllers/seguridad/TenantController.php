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
        $stmt = $this->db->prepare("SELECT * FROM seguridad_tenants WHERE ten_tenant_id = ?");
        $stmt->execute([$id]);
        $tenant = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$tenant) {
            setFlashMessage('error', 'Tenant no encontrado');
            redirect('seguridad', 'tenant', 'index');
            return;
        }
        // Obtener planes activos
        $planes = $this->db->query("SELECT sus_plan_id, sus_nombre, sus_precio_mensual, sus_usuarios_incluidos FROM seguridad_planes_suscripcion WHERE sus_estado = 'A' ORDER BY sus_nombre")->fetchAll(\PDO::FETCH_ASSOC);
        // Obtener solo módulos activos y operativos
        $stmtMod = $this->db->prepare("
            SELECT m.mod_id AS id, m.mod_nombre AS nombre, m.mod_codigo AS codigo,
                   m.mod_icono AS icono, m.mod_color_fondo AS color_fondo,
                   tm.tmo_icono_personalizado AS icono_personalizado, 
                   tm.tmo_color_personalizado AS color_personalizado, 
                   tm.tmo_activo AS activo
            FROM seguridad_modulos m
            LEFT JOIN seguridad_tenant_modulos tm ON m.mod_id = tm.tmo_modulo_id AND tm.tmo_tenant_id = ?
            WHERE m.mod_activo = 1 ORDER BY m.mod_nombre
        ");
        $stmtMod->execute([$id]);
        $modulos = $stmtMod->fetchAll(\PDO::FETCH_ASSOC);
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
            $stmt = $this->db->prepare("SELECT * FROM seguridad_tenants WHERE ten_tenant_id = ?");
            $stmt->execute([$id]);
            $previo = $stmt->fetch(\PDO::FETCH_ASSOC);
            // Actualizar tenant
            $sql = "UPDATE seguridad_tenants SET 
                ten_ruc = ?, ten_razon_social = ?, ten_nombre_comercial = ?, ten_tipo_empresa = ?,
                ten_direccion = ?, ten_telefono = ?, ten_celular = ?, ten_email = ?, ten_sitio_web = ?,
                ten_representante_nombre = ?, ten_representante_identificacion = ?, ten_representante_email = ?, ten_representante_telefono = ?,
                ten_plan_id = ?, ten_fecha_inicio = ?, ten_fecha_vencimiento = ?,
                ten_usuarios_permitidos = ?, ten_sedes_permitidas = ?, ten_monto_mensual = ?,
                ten_color_primario = ?, ten_color_secundario = ?, ten_estado = ?
                WHERE ten_tenant_id = ?";
            $params = array_values($data);
            $params[] = $id;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            // Eliminar todos los registros de módulos del tenant
            $stmt = $this->db->prepare("DELETE FROM seguridad_tenant_modulos WHERE tmo_tenant_id = ?");
            $stmt->execute([$id]);
            // Insertar todos los módulos activos con el estado correcto y fecha_inicio actual
            $modulosSistema = $this->db->query("SELECT sis_modulo_id FROM seguridad_modulos_sistema WHERE sis_estado = 'A'")->fetchAll(\PDO::FETCH_COLUMN);
            $modulosSeleccionados = isset($_POST['modulos']) && is_array($_POST['modulos']) ? array_map('intval', $_POST['modulos']) : [];
            $fechaHoy = date('Y-m-d');
            foreach ($modulosSistema as $modulo_id) {
                $activo = in_array((int)$modulo_id, $modulosSeleccionados) ? 'S' : 'N';
                $logMsg = "Intentando insertar: tenant_id=$id, modulo_id=$modulo_id, activo=$activo, fecha_inicio=$fechaHoy\n";
                file_put_contents(BASE_PATH . '/storage/logs/tenant_modulos_debug.log', $logMsg, FILE_APPEND);
                try {
                    $stmt = $this->db->prepare("INSERT INTO seguridad_tenant_modulos (tmo_tenant_id, tmo_modulo_id, tmo_activo, tmo_fecha_inicio) VALUES (?, ?, ?, ?)");
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
                $where .= " AND t.ten_estado = ?";
                $params[] = $estado;
            }
        }
        if ($plan) {
            if (is_numeric($plan)) {
                $where .= " AND t.ten_plan_id = ?";
                $params[] = $plan;
            }
        }
        if ($buscar) {
            $where .= " AND (t.ten_nombre_comercial LIKE ? OR t.ten_razon_social LIKE ? OR t.ten_ruc LIKE ?)";
            $params[] = "%$buscar%";
            $params[] = "%$buscar%";
            $params[] = "%$buscar%";
        }
        $countSql = "SELECT COUNT(*) FROM seguridad_tenants t $where";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        $sql = "SELECT t.ten_tenant_id, t.ten_ruc, t.ten_razon_social, t.ten_nombre_comercial, 
                t.ten_email, t.ten_telefono, t.ten_direccion, t.ten_usuarios_permitidos, 
                t.ten_estado, t.ten_fecha_vencimiento, t.ten_plan_id,
                DATEDIFF(t.ten_fecha_vencimiento, CURDATE()) AS dias_restantes,
                p.sus_nombre AS plan_nombre,
                (SELECT COUNT(*) FROM seguridad_usuarios u WHERE u.usu_tenant_id = t.ten_tenant_id) AS usuarios_count,
                (SELECT COUNT(*) FROM seguridad_tenant_modulos tm WHERE tm.tmo_tenant_id = t.ten_tenant_id AND tm.tmo_activo = 'S') AS modulos_count
                FROM seguridad_tenants t
                LEFT JOIN seguridad_planes_suscripcion p ON p.sus_plan_id = t.ten_plan_id
                $where ORDER BY t.ten_fecha_registro DESC LIMIT $porPagina OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tenants = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $planes = $this->db->query("SELECT sus_plan_id, sus_nombre FROM seguridad_planes_suscripcion WHERE sus_estado = 'A' ORDER BY sus_nombre")->fetchAll(\PDO::FETCH_ASSOC);
        
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
                $sql = "INSERT INTO seguridad_tenants (ten_ruc, ten_razon_social, ten_nombre_comercial, ten_tipo_empresa, ten_direccion, ten_telefono, ten_celular, ten_email, ten_sitio_web, ten_representante_nombre, ten_representante_identificacion, ten_representante_email, ten_representante_telefono, ten_plan_id, ten_fecha_inicio, ten_fecha_vencimiento, ten_usuarios_permitidos, ten_sedes_permitidas, ten_monto_mensual, ten_color_primario, ten_color_secundario, ten_estado)
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
                    $stmt = $this->db->prepare("UPDATE seguridad_tenant_modulos SET tmo_activo = 'N' WHERE tmo_tenant_id = ?");
                    $stmt->execute([$id]);
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
                $sql = "UPDATE seguridad_tenants SET 
                    ten_ruc = ?, ten_razon_social = ?, ten_nombre_comercial = ?, ten_tipo_empresa = ?,
                    ten_direccion = ?, ten_telefono = ?, ten_celular = ?, ten_email = ?, ten_sitio_web = ?,
                    ten_representante_nombre = ?, ten_representante_identificacion = ?, ten_representante_email = ?, ten_representante_telefono = ?,
                    ten_plan_id = ?, ten_fecha_inicio = ?, ten_fecha_vencimiento = ?,
                    ten_usuarios_permitidos = ?, ten_sedes_permitidas = ?, ten_monto_mensual = ?,
                    ten_color_primario = ?, ten_color_secundario = ?, ten_estado = ?
                    WHERE ten_tenant_id = ?";
                $params = array_values($data);
                $params[] = $id;
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                
            } else {
                // Crear
                $sql = "INSERT INTO seguridad_tenants (ten_ruc, ten_razon_social, ten_nombre_comercial, ten_tipo_empresa, ten_direccion, ten_telefono, ten_celular, ten_email, ten_sitio_web, ten_representante_nombre, ten_representante_identificacion, ten_representante_email, ten_representante_telefono, ten_plan_id, ten_fecha_inicio, ten_fecha_vencimiento, ten_usuarios_permitidos, ten_sedes_permitidas, ten_monto_mensual, ten_color_primario, ten_color_secundario, ten_estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array_values($data);
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $id = $this->db->lastInsertId();
            }
            
            // Actualizar módulos
            if (isset($_POST['modulos']) && is_array($_POST['modulos'])) {
                // Desactivar todos los módulos actuales
                $stmt = $this->db->prepare("UPDATE seguridad_tenant_modulos SET tmo_activo = 'N' WHERE tmo_tenant_id = ?");
                $stmt->execute([$id]);
                
                // Activar los seleccionados
                foreach ($_POST['modulos'] as $moduloId) {
                    // Verificar si existe
                    $stmt = $this->db->prepare("SELECT tmo_id FROM seguridad_tenant_modulos WHERE tmo_tenant_id = ? AND tmo_modulo_id = ?");
                    $stmt->execute([$id, $moduloId]);
                    
                    if ($stmt->fetch()) {
                        $stmt = $this->db->prepare("UPDATE seguridad_tenant_modulos SET tmo_activo = 'S' WHERE tmo_tenant_id = ? AND tmo_modulo_id = ?");
                    } else {
                        $stmt = $this->db->prepare("INSERT INTO seguridad_tenant_modulos (tmo_tenant_id, tmo_modulo_id, tmo_activo, tmo_fecha_inicio) VALUES (?, ?, 'S', CURDATE())");
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
                SELECT t.*, 
                    p.sus_nombre AS plan_nombre,
                    DATEDIFF(t.ten_fecha_vencimiento, CURDATE()) AS dias_restantes
                FROM seguridad_tenants t
                LEFT JOIN seguridad_planes_suscripcion p ON t.ten_plan_id = p.sus_plan_id
                WHERE t.ten_tenant_id = ?
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
                SELECT u.*, r.rol_nombre AS rol_nombre,
                    CONCAT(u.usu_nombres, ' ', u.usu_apellidos) AS nombre_completo
                FROM seguridad_usuarios u
                LEFT JOIN seguridad_roles r ON u.usu_rol_id = r.rol_rol_id
                WHERE u.usu_tenant_id = ?
                ORDER BY u.usu_usuario_id DESC
            ");
            $stmt->execute([$id]);
            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Módulos del tenant (solo sistemas principales, con icono y color reales)
            $stmt = $this->db->prepare("
                SELECT tm.*, m.mod_nombre AS nombre, m.mod_codigo AS codigo,
                    m.mod_icono AS icono_sistema, m.mod_color_fondo AS color_sistema
                FROM seguridad_tenant_modulos tm
                JOIN seguridad_modulos m ON tm.tmo_modulo_id = m.mod_id
                WHERE tm.tmo_tenant_id = ? AND tm.tmo_activo = 'S'
                ORDER BY m.mod_orden
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
                $stmt = $this->db->prepare("UPDATE seguridad_tenants SET ten_estado = 'S', ten_estado_suscripcion = 'SUSPENDIDA', ten_motivo_suspension = ?, ten_fecha_suspension = NOW() WHERE ten_tenant_id = ?");
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
                $stmt = $this->db->prepare("UPDATE seguridad_tenants SET ten_estado = 'A', ten_estado_suscripcion = 'ACTIVA', ten_motivo_suspension = NULL, ten_fecha_suspension = NULL WHERE ten_tenant_id = ?");
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
                SELECT t.*, p.sus_nombre AS plan_nombre, DATEDIFF(t.ten_fecha_vencimiento, CURDATE()) AS dias_restantes
                FROM seguridad_tenants t
                LEFT JOIN seguridad_planes_suscripcion p ON t.ten_plan_id = p.sus_plan_id
                WHERE t.ten_estado = 'A' 
                AND t.ten_fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY t.ten_fecha_vencimiento ASC
            ");
            $porVencer = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Suscripciones vencidas
            $stmt = $this->db->query("
                SELECT t.*, p.sus_nombre AS plan_nombre, DATEDIFF(CURDATE(), t.ten_fecha_vencimiento) AS dias_restantes
                FROM seguridad_tenants t
                LEFT JOIN seguridad_planes_suscripcion p ON t.ten_plan_id = p.sus_plan_id
                WHERE t.ten_fecha_vencimiento < CURDATE()
                ORDER BY t.ten_fecha_vencimiento DESC
            ");
            $vencidas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Resumen por plan
            $stmt = $this->db->query("
                SELECT p.sus_nombre AS nombre, p.sus_precio_mensual AS precio_mensual, COUNT(t.ten_tenant_id) AS total,
                       SUM(t.ten_monto_mensual) AS ingresos_mensuales
                FROM seguridad_planes_suscripcion p
                LEFT JOIN seguridad_tenants t ON p.sus_plan_id = t.ten_plan_id AND t.ten_estado = 'A'
                WHERE p.sus_estado = 'A'
                GROUP BY p.sus_plan_id
                ORDER BY p.sus_precio_mensual
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
                    UPDATE seguridad_tenants 
                    SET ten_fecha_vencimiento = DATE_ADD(
                        CASE WHEN ten_fecha_vencimiento < CURDATE() THEN CURDATE() ELSE ten_fecha_vencimiento END, 
                        INTERVAL ? MONTH
                    ),
                    ten_estado_suscripcion = 'ACTIVA'
                    WHERE ten_tenant_id = ?
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
