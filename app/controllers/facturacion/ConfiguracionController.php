<?php
/**
 * DigiSports - Configuración de Facturación Electrónica
 *
 * Permite al tenant configurar:
 *   - Datos del emisor (RUC, razón social, dirección, etc.)
 *   - Ambiente SRI (pruebas / producción)
 *   - Logo para el RIDE
 *   - Certificado de firma electrónica (.p12 + contraseña)
 *
 * @package DigiSports\Controllers\Facturacion
 * @version 1.0.0
 */

namespace App\Controllers\Facturacion;

require_once BASE_PATH . '/app/controllers/ModuleController.php';
require_once BASE_PATH . '/app/services/FileManager.php';
require_once BASE_PATH . '/app/services/SRI/FirmaElectronicaService.php';
require_once BASE_PATH . '/app/services/SRI/WebServiceSRIService.php';

use App\Services\SRI\FirmaElectronicaService;
use App\Services\SRI\WebServiceSRIService;

class ConfiguracionController extends \App\Controllers\ModuleController {

    protected $moduloCodigo = 'facturacion';

    /** Ruta base donde se guardan los certificados por tenant */
    private const CERT_DIR = 'storage/certificados/';

    public function __construct() {
        parent::__construct();
        $this->moduloCodigo = 'facturacion';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX — mostrar / guardar configuración
    // ─────────────────────────────────────────────────────────────────────────

    public function index() {
        $this->authorize('configurar', 'facturacion');

        $config     = $this->cargarConfig();
        $csrfToken  = \Security::generateCsrfToken();

        // Obtener logo si existe
        $logoArcId  = $config['cfg_logo_arc_id'] ?? null;
        $logoUrl    = $logoArcId ? \Config::baseUrl('archivo.php?id=' . (int)$logoArcId) : null;

        // Estado del certificado
        $certInfo   = null;
        $certError  = null;
        if (!empty($config['cfg_certificado_ruta']) && file_exists($config['cfg_certificado_ruta'])) {
            try {
                $firmaService = new FirmaElectronicaService();
                $clave        = $this->descifrarClave($config['cfg_certificado_clave'] ?? '');
                $firmaService->cargarCertificado($config['cfg_certificado_ruta'], $clave);
                $certInfo = $firmaService->obtenerInfoCertificado();
            } catch (\Exception $e) {
                $certError = $e->getMessage();
            }
        }

        $this->viewData['config']      = $config;
        $this->viewData['logo_url']    = $logoUrl;
        $this->viewData['cert_info']   = $certInfo;
        $this->viewData['cert_error']  = $certError;
        $this->viewData['csrf_token']  = $csrfToken;
        $this->viewData['title']       = 'Configuración Facturación Electrónica';

        $this->renderModule('facturacion/configuracion/index', $this->viewData);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GUARDAR — datos del emisor + ambiente
    // ─────────────────────────────────────────────────────────────────────────

    public function guardar() {
        $this->authorize('configurar', 'facturacion');

        if (!$this->isPost()) {
            return $this->jsonError('Método no permitido', 405);
        }
        if (!$this->validateCsrf()) {
            return $this->jsonError('Token inválido', 403);
        }

        // Validar campos obligatorios
        $ruc          = trim($this->post('ruc') ?? '');
        $razonSocial  = trim($this->post('razon_social') ?? '');
        $direccMatrix = trim($this->post('direccion_matriz') ?? '');

        if (strlen($ruc) !== 13 || !ctype_digit($ruc)) {
            return $this->jsonError('El RUC debe tener exactamente 13 dígitos numéricos');
        }
        if (empty($razonSocial)) {
            return $this->jsonError('La Razón Social es obligatoria');
        }
        if (empty($direccMatrix)) {
            return $this->jsonError('La Dirección Matriz es obligatoria');
        }

        $datos = [
            'cfg_ruc'                       => $ruc,
            'cfg_razon_social'              => strtoupper($razonSocial),
            'cfg_nombre_comercial'          => trim($this->post('nombre_comercial') ?? ''),
            'cfg_direccion_matriz'          => $direccMatrix,
            'cfg_direccion_establecimiento' => trim($this->post('direccion_establecimiento') ?? $direccMatrix),
            'cfg_codigo_establecimiento'    => str_pad(preg_replace('/\D/', '', $this->post('codigo_establecimiento') ?? '1'), 3, '0', STR_PAD_LEFT),
            'cfg_punto_emision'             => str_pad(preg_replace('/\D/', '', $this->post('punto_emision') ?? '1'), 3, '0', STR_PAD_LEFT),
            'cfg_obligado_contabilidad'     => $this->post('obligado_contabilidad') === 'SI' ? 'SI' : 'NO',
            'cfg_contribuyente_especial'    => trim($this->post('contribuyente_especial') ?? ''),
            'cfg_agente_retencion'          => trim($this->post('agente_retencion') ?? ''),
            'cfg_regimen_microempresas'     => $this->post('regimen_microempresas') === 'SI' ? 'SI' : 'NO',
            'cfg_regimen_rimpe'             => $this->post('regimen_rimpe') === 'SI' ? 'SI' : 'NO',
            'cfg_ambiente'                  => (int)$this->post('ambiente') === 2 ? 2 : 1,
            'cfg_secuencial_inicio'         => max(1, (int)($this->post('secuencial_inicio') ?? 1)),
            'cfg_email_notificaciones'      => trim($this->post('email_notificaciones') ?? ''),
        ];

        try {
            $this->upsertConfig($datos);

            // Subir logo si viene en el request
            if (!empty($_FILES['logo']['name'])) {
                $this->procesarLogo($_FILES['logo']);
            }

            $this->audit('facturacion_configuracion', $this->tenantId, 'UPSERT', [], $datos);

            return $this->jsonSuccess([], 'Configuración guardada correctamente');

        } catch (\Exception $e) {
            $this->logError('Error guardar config FE: ' . $e->getMessage());
            return $this->jsonError('Error al guardar: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUBIR CERTIFICADO — upload .p12 + contraseña
    // ─────────────────────────────────────────────────────────────────────────

    public function subirCertificado() {
        $this->authorize('configurar', 'facturacion');

        if (!$this->isPost() || !$this->validateCsrf()) {
            return $this->jsonError('Solicitud inválida', 403);
        }

        if (empty($_FILES['certificado']['name'])) {
            return $this->jsonError('No se seleccionó ningún archivo');
        }

        // Leer sin sanitizar: htmlspecialchars() corrompería contraseñas con &, <, ", '
        $clave = $_POST['clave_certificado'] ?? '';
        if (empty($clave)) {
            return $this->jsonError('La contraseña del certificado es obligatoria');
        }

        $tmpFile = $_FILES['certificado']['tmp_name'];
        $origName = $_FILES['certificado']['name'];

        // Validar extensión
        if (strtolower(pathinfo($origName, PATHINFO_EXTENSION)) !== 'p12') {
            return $this->jsonError('El archivo debe tener extensión .p12');
        }

        // Validar tamaño (máx 1 MB)
        if ($_FILES['certificado']['size'] > 1 * 1024 * 1024) {
            return $this->jsonError('El certificado no debe superar 1 MB');
        }

        // Verificar que el archivo es un PKCS#12 válido y la clave es correcta
        $p12Content = file_get_contents($tmpFile);
        if ($p12Content === false || strlen($p12Content) < 10) {
            return $this->jsonError('No se pudo leer el archivo subido');
        }

        while (openssl_error_string() !== false) {} // limpiar buffer previo
        $certInfo = [];

        if (!openssl_pkcs12_read($p12Content, $certInfo, $clave)) {
            $opensslErrors = [];
            while ($err = openssl_error_string()) { $opensslErrors[] = $err; }
            $errStr = implode(' ', $opensslErrors);

            // OpenSSL 3.x no soporta por defecto los algoritmos legacy (RC2/3DES)
            // usados en certificados del SRI/BCE Ecuador → reconvertir automáticamente
            if (stripos($errStr, 'unsupported') !== false) {
                $converted = $this->convertirP12Legacy($tmpFile, $clave);
                if ($converted !== null) {
                    while (openssl_error_string() !== false) {}
                    if (!openssl_pkcs12_read($converted, $certInfo, $clave)) {
                        return $this->jsonError(
                            'Certificado en formato legacy convertido, pero la contraseña sigue siendo incorrecta. ' .
                            'Verifique la contraseña del archivo .p12.'
                        );
                    }
                    $p12Content = $converted; // guardar la versión convertida
                } else {
                    return $this->jsonError(
                        'El certificado usa cifrado legacy (RC2/3DES) incompatible con OpenSSL 3.x. ' .
                        'Conviértalo antes de subir ejecutando en WAMP: ' .
                        'openssl pkcs12 -legacy -in original.p12 -out temp.pem -nodes -passin pass:CLAVE && ' .
                        'openssl pkcs12 -export -in temp.pem -out nuevo.p12 -passout pass:CLAVE && del temp.pem'
                    );
                }
            } else {
                return $this->jsonError(
                    'La contraseña del certificado es incorrecta o el archivo está dañado. ' .
                    'Detalle: ' . ($errStr ?: 'sin información adicional')
                );
            }
        }

        // Guardar el .p12 en el filesystem
        $dirCert = BASE_PATH . '/' . self::CERT_DIR . $this->tenantId . '/';
        if (!is_dir($dirCert)) {
            mkdir($dirCert, 0750, true);

            // .htaccess de protección en el directorio del tenant
            file_put_contents($dirCert . '.htaccess', "Order Deny,Allow\nDeny from all\n");
        }

        $rutaDest = $dirCert . 'firma.p12';
        if (!file_put_contents($rutaDest, $p12Content)) {
            return $this->jsonError('No se pudo guardar el certificado en el servidor');
        }

        // Extraer vigencia del certificado
        $certParsed = openssl_x509_parse($certInfo['cert']);
        $vigencia   = isset($certParsed['validTo_time_t'])
                        ? date('Y-m-d', $certParsed['validTo_time_t'])
                        : null;
        $titular    = $certParsed['subject']['CN'] ?? 'N/A';

        // Guardar ruta y clave cifrada en BD
        $claveCifrada = \Security::encryptSensitiveData($clave);
        $this->upsertConfig([
            'cfg_certificado_ruta'     => $rutaDest,
            'cfg_certificado_clave'    => $claveCifrada,
            'cfg_certificado_vigencia' => $vigencia,
        ]);

        return $this->jsonSuccess([
            'titular'  => $titular,
            'vigencia' => $vigencia,
            'dias_restantes' => $vigencia
                ? (int) floor((strtotime($vigencia) - time()) / 86400)
                : null,
        ], 'Certificado cargado y verificado correctamente');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROBAR CERTIFICADO — verificar que el .p12 cargado es válido
    // ─────────────────────────────────────────────────────────────────────────

    public function probarCertificado() {
        $this->authorize('configurar', 'facturacion');

        $config = $this->cargarConfig();

        if (empty($config['cfg_certificado_ruta'])) {
            return $this->jsonError('No hay certificado configurado');
        }
        if (!file_exists($config['cfg_certificado_ruta'])) {
            return $this->jsonError('El archivo del certificado no se encontró en el servidor');
        }

        try {
            $firmaService = new FirmaElectronicaService();
            $clave        = $this->descifrarClave($config['cfg_certificado_clave'] ?? '');
            $firmaService->cargarCertificado($config['cfg_certificado_ruta'], $clave);
            $info = $firmaService->obtenerInfoCertificado();

            return $this->jsonSuccess($info, 'Certificado válido');

        } catch (\Exception $e) {
            return $this->jsonError('Error al leer el certificado: ' . $e->getMessage(), 400);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROBAR CONEXIÓN SRI
    // ─────────────────────────────────────────────────────────────────────────

    public function probarConexion() {
        $this->authorize('configurar', 'facturacion');

        try {
            $ws          = new WebServiceSRIService();
            $resultado   = $ws->verificarConectividad();
            $sriCfg      = require BASE_PATH . '/config/sri.php';
            $tenantCfg   = $this->cargarConfig();
            $ambiente    = !empty($tenantCfg['cfg_ambiente']) ? (int)$tenantCfg['cfg_ambiente'] : $sriCfg['ambiente'];
            $ambienteKey = $ambiente == 2 ? 'produccion' : 'pruebas';

            return $this->jsonSuccess([
                'conectividad' => $resultado,
                'ambiente'     => $ambiente == 2 ? 'PRODUCCIÓN' : 'PRUEBAS',
                'urls'         => $sriCfg['webservices'][$ambienteKey],
            ], $resultado['recepcion'] && $resultado['autorizacion']
                ? 'Conexión exitosa con el SRI'
                : 'No se pudo conectar con uno o más servicios del SRI');

        } catch (\Exception $e) {
            return $this->jsonError('Error al conectar con el SRI: ' . $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /** Carga la configuración del tenant desde BD (null si no existe aún) */
    private function cargarConfig(): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM facturacion_configuracion WHERE cfg_tenant_id = ? LIMIT 1"
            );
            $stmt->execute([$this->tenantId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Inserta o actualiza la fila de configuración del tenant.
     * Soporta actualizaciones parciales (solo los campos presentes en $datos).
     */
    private function upsertConfig(array $datos): void {
        // Verificar si ya existe
        $stmt = $this->db->prepare(
            "SELECT cfg_id FROM facturacion_configuracion WHERE cfg_tenant_id = ? LIMIT 1"
        );
        $stmt->execute([$this->tenantId]);
        $existe = $stmt->fetchColumn();

        if ($existe) {
            // UPDATE solo los campos presentes
            $setCols  = [];
            $params   = [];
            foreach ($datos as $col => $val) {
                $setCols[]     = "{$col} = ?";
                $params[]      = $val;
            }
            $setCols[]  = 'cfg_updated_by = ?';
            $params[]   = (int) ($_SESSION['user_id'] ?? 0);
            $params[]   = $this->tenantId;

            $sql = "UPDATE facturacion_configuracion SET " . implode(', ', $setCols) . " WHERE cfg_tenant_id = ?";
            $this->db->prepare($sql)->execute($params);

        } else {
            // INSERT con todos los defaults
            $datos['cfg_tenant_id'] = $this->tenantId;
            $datos['cfg_created_by'] = (int) ($_SESSION['user_id'] ?? 0);
            $datos['cfg_updated_by'] = (int) ($_SESSION['user_id'] ?? 0);

            $cols   = implode(', ', array_keys($datos));
            $places = implode(', ', array_fill(0, count($datos), '?'));
            $this->db->prepare("INSERT INTO facturacion_configuracion ({$cols}) VALUES ({$places})")
                     ->execute(array_values($datos));
        }
    }

    /** Procesa y guarda el logo usando FileManager */
    private function procesarLogo(array $file): void {
        $fm     = new \FileManager($this->db, $this->tenantId, (int) ($_SESSION['user_id'] ?? 0));
        $result = $fm->uploadImage($file, 'facturacion_configuracion', $this->tenantId, 'logos', true);

        if (!$result['success']) {
            throw new \Exception($result['error'] ?? 'Error al procesar el logo');
        }

        if ($result['arc_id']) {
            $this->upsertConfig(['cfg_logo_arc_id' => $result['arc_id']]);
        }
    }

    /**
     * Reconvierte un .p12 con cifrado legacy (RC2/3DES) a formato moderno
     * usando el binario openssl de WAMP, que sí soporta -legacy.
     * Retorna el contenido binario del nuevo .p12, o null si no es posible.
     */
    private function convertirP12Legacy(string $p12Path, string $clave): ?string {
        if (!function_exists('exec')) {
            error_log('[ConfiguracionController] convertirP12Legacy: exec() no disponible');
            return null;
        }

        // Buscar openssl.exe en WAMP
        $openssl    = null;
        $apacheBase = 'C:/wamp64/bin/apache/';
        $apacheDir  = null;
        if (is_dir($apacheBase)) {
            $entries = scandir($apacheBase) ?: [];
            rsort($entries);
            foreach ($entries as $entry) {
                if (strncmp($entry, 'apache', 6) !== 0) continue;
                $candidate = $apacheBase . $entry . '/bin/openssl.exe';
                if (file_exists($candidate)) {
                    $openssl   = $candidate;
                    $apacheDir = $apacheBase . $entry;
                    break;
                }
            }
        }

        if ($openssl === null) {
            error_log('[ConfiguracionController] convertirP12Legacy: no se encontró openssl.exe en WAMP');
            return null;
        }

        // legacy.dll en WAMP está en los directorios de PHP (extras/ssl/), no en Apache
        // Buscar en Apache primero, luego en cada versión de PHP instalada
        $modulesDir = null;
        $moduleCandidates = [
            $apacheDir . '/conf/lib/ossl-modules',
            $apacheDir . '/bin/ossl-modules',
            $apacheDir . '/lib/ossl-modules',
            $apacheDir . '/ossl-modules',
            dirname($openssl) . '/ossl-modules',
        ];
        // Agregar candidatos desde directorios de PHP en WAMP
        $phpBase = 'C:/wamp64/bin/php/';
        if (is_dir($phpBase)) {
            $phpEntries = scandir($phpBase) ?: [];
            rsort($phpEntries); // más reciente primero
            foreach ($phpEntries as $phpEntry) {
                if (strncmp($phpEntry, 'php', 3) !== 0) continue;
                $moduleCandidates[] = $phpBase . $phpEntry . '/extras/ssl';
                $moduleCandidates[] = $phpBase . $phpEntry . '/extras/openssl';
            }
        }
        foreach ($moduleCandidates as $dir) {
            if (file_exists($dir . '/legacy.dll')) {
                $modulesDir = $dir;
                break;
            }
        }
        error_log('[ConfiguracionController] convertirP12Legacy: openssl=' . $openssl . ' modulesDir=' . ($modulesDir ?? 'NOT FOUND'));

        $tmpDir     = sys_get_temp_dir();
        $prefix     = $tmpDir . '/ds_cert_' . uniqid('', true);
        $tmpPem     = $prefix . '.pem';
        $tmpP12     = $prefix . '_new.p12';
        // Dos archivos separados: OpenSSL no puede leer -passin y -passout del mismo archivo
        $tmpPassIn  = $prefix . '_in.pass';
        $tmpPassOut = $prefix . '_out.pass';
        file_put_contents($tmpPassIn,  $clave);
        file_put_contents($tmpPassOut, $clave);

        // Si encontramos legacy.dll, establecer OPENSSL_MODULES antes de ejecutar
        $prevModules = getenv('OPENSSL_MODULES');
        if ($modulesDir !== null) {
            putenv('OPENSSL_MODULES=' . str_replace('/', '\\', $modulesDir));
        }

        try {
            $opensslWin  = str_replace('/', '\\', $openssl);
            $p12Win      = str_replace('/', '\\', $p12Path);
            $pemWin      = str_replace('/', '\\', $tmpPem);
            $p12OutWin   = str_replace('/', '\\', $tmpP12);
            $passInWin   = str_replace('/', '\\', $tmpPassIn);
            $passOutWin  = str_replace('/', '\\', $tmpPassOut);

            // Paso 1: p12 legacy → PEM
            $providerArg = $modulesDir
                ? sprintf(' -provider-path "%s" -provider legacy -provider default', str_replace('/', '\\', $modulesDir))
                : '';
            $cmd1 = sprintf(
                '"%s" pkcs12 -legacy%s -in "%s" -out "%s" -passin file:"%s" -nodes 2>&1',
                $opensslWin, $providerArg, $p12Win, $pemWin, $passInWin
            );
            error_log('[ConfiguracionController] convertirP12Legacy cmd1: ' . $cmd1);
            $out1 = []; $ret1 = -1;
            exec($cmd1, $out1, $ret1);
            error_log('[ConfiguracionController] convertirP12Legacy ret1=' . $ret1 . ' out1: ' . implode(' | ', $out1));
            error_log('[ConfiguracionController] tmpPem exists=' . (file_exists($tmpPem) ? 'yes' : 'no') . ' size=' . (file_exists($tmpPem) ? filesize($tmpPem) : 0));

            if ($ret1 !== 0 || !file_exists($tmpPem) || filesize($tmpPem) < 10) {
                return null;
            }

            // Paso 2: PEM → .p12 moderno (archivos de pass separados: OpenSSL no puede leer el mismo para passin+passout)
            $cmd2 = sprintf(
                '"%s" pkcs12 -export -in "%s" -out "%s" -passin file:"%s" -passout file:"%s" 2>&1',
                $opensslWin, $pemWin, $p12OutWin, $passInWin, $passOutWin
            );
            error_log('[ConfiguracionController] convertirP12Legacy cmd2: ' . $cmd2);
            $out2 = []; $ret2 = -1;
            exec($cmd2, $out2, $ret2);
            error_log('[ConfiguracionController] convertirP12Legacy ret2=' . $ret2 . ' out2: ' . implode(' | ', $out2));
            error_log('[ConfiguracionController] tmpP12 exists=' . (file_exists($tmpP12) ? 'yes' : 'no') . ' size=' . (file_exists($tmpP12) ? filesize($tmpP12) : 0));

            if ($ret2 !== 0 || !file_exists($tmpP12) || filesize($tmpP12) < 10) {
                return null;
            }

            return file_get_contents($tmpP12) ?: null;

        } finally {
            if ($modulesDir !== null) {
                $prevModules !== false ? putenv('OPENSSL_MODULES=' . $prevModules) : putenv('OPENSSL_MODULES=');
            }
            @unlink($tmpPem);
            @unlink($tmpP12);
            @unlink($tmpPassIn);
            @unlink($tmpPassOut);
        }
    }

    /** Descifra la contraseña del certificado almacenada en BD */
    private function descifrarClave(string $cifrada): string {
        if (empty($cifrada)) return '';
        try {
            return \Security::decryptSensitiveData($cifrada) ?: '';
        } catch (\Exception $e) {
            return '';
        }
    }

    // ── Respuestas JSON ──────────────────────────────────────────────────────

    private function jsonSuccess($data = [], string $mensaje = 'OK'): void {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $mensaje, 'data' => $data]);
        exit;
    }

    private function jsonError(string $mensaje, int $codigo = 400): void {
        header('Content-Type: application/json');
        http_response_code($codigo);
        echo json_encode(['success' => false, 'message' => $mensaje]);
        exit;
    }
}
