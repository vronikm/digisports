<?php
/**
 * DigiSports — Servicio Central de Gestión de Archivos
 *
 * Maneja subida, almacenamiento, validación y servicio seguro de archivos
 * para todos los módulos del sistema, con aislamiento por tenant.
 *
 * Estructura de almacenamiento:
 *   storage/tenants/{tenant_id}/{entidad}/{categoria}/{archivo}
 *
 * @package DigiSports\Services
 * @version 1.0.0
 */

defined('BASE_PATH') or die('Acceso denegado');

class FileManager
{
    // -----------------------------------------------------------------------
    // Configuración
    // -----------------------------------------------------------------------

    /** Directorio base para archivos de tenants (relativo a BASE_PATH) */
    const TENANTS_DIR = 'storage/tenants';

    /** Tamaño máximo por defecto: 5 MB */
    const MAX_SIZE_DEFAULT = 5 * 1024 * 1024;

    /** Dimensiones máximas para imágenes redimensionadas */
    const IMAGE_MAX_WIDTH  = 300;
    const IMAGE_MAX_HEIGHT = 300;

    /** Calidad JPEG para imágenes procesadas (0-100) */
    const IMAGE_JPEG_QUALITY = 85;

    /**
     * MIME types permitidos por categoría.
     * La validación usa finfo_file() (magic bytes), NO $_FILES['type'].
     */
    const ALLOWED_TYPES = [
        'fotos' => [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ],
        'logos' => [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/svg+xml' => 'svg',
        ],
        'firmas' => [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
        ],
        'documentos' => [
            'application/pdf' => 'pdf',
        ],
        'comprobantes' => [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'application/pdf' => 'pdf',
        ],
        'administrativos' => [
            'application/pdf'                                                 => 'pdf',
            'application/msword'                                              => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ],
    ];

    // -----------------------------------------------------------------------
    // Propiedades de instancia
    // -----------------------------------------------------------------------

    /** @var PDO */
    private $db;

    /** @var int tenant_id activo */
    private $tenantId;

    /** @var int usu_usuario_id del usuario que realiza la acción */
    private $userId;

    /**
     * @param PDO $db        Conexión PDO activa
     * @param int $tenantId  ID del tenant
     * @param int $userId    ID del usuario que sube el archivo
     */
    public function __construct(PDO $db, int $tenantId, int $userId)
    {
        $this->db       = $db;
        $this->tenantId = $tenantId;
        $this->userId   = $userId;
    }

    // -----------------------------------------------------------------------
    // API Pública
    // -----------------------------------------------------------------------

    /**
     * Sube y procesa una imagen.
     *
     * Aplica redimensionado GD, convierte a JPEG para consistencia
     * (excepto PNG con transparencia y SVG).
     *
     * @param  array  $file        Entrada de $_FILES['campo']
     * @param  string $entidad     Ej: 'alumnos', 'academias'
     * @param  int    $entidadId   PK del registro al que pertenece
     * @param  string $categoria   Ej: 'fotos', 'logos'
     * @param  bool   $esPrincipal Marcar como archivo principal (reemplaza el anterior)
     * @return array  ['success'=>bool, 'arc_id'=>int|null, 'ruta'=>string|null, 'error'=>string|null]
     */
    public function uploadImage(
        array $file,
        string $entidad,
        int $entidadId,
        string $categoria = 'fotos',
        bool $esPrincipal = true
    ): array {
        // 1. Validar el archivo
        $validation = $this->validateFile($file, $categoria);
        if (!$validation['valid']) {
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => $validation['error']];
        }

        // 2. Construir ruta de destino
        $storagePath = $this->buildStoragePath($entidad, $categoria);
        if (!$this->ensureDirectory($storagePath)) {
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => 'No se pudo crear el directorio de almacenamiento'];
        }

        // 3. Generar nombre único
        $ext           = $validation['ext'];
        $nombreAlmac   = $this->generateFileName($entidadId, $ext);
        $rutaAbsoluta  = $storagePath . DIRECTORY_SEPARATOR . $nombreAlmac;

        // 4. Procesar y guardar (redimensionar si es imagen rasterizada)
        if (in_array($validation['mime'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            $stored = $this->resizeAndSave($file['tmp_name'], $rutaAbsoluta, $validation['mime']);
        } else {
            $stored = $this->storeFile($file['tmp_name'], $rutaAbsoluta);
        }

        if (!$stored['ok']) {
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => $stored['error']];
        }

        // 5. Registrar en BD
        $rutaRelativa = $this->toRelativePath($storagePath) . '/' . $nombreAlmac;

        if ($esPrincipal) {
            $this->clearPrincipal($entidad, $entidadId, $categoria);
        }

        $arcId = $this->registerFile([
            'entidad'          => $entidad,
            'entidad_id'       => $entidadId,
            'categoria'        => $categoria,
            'nombre_original'  => basename($file['name']),
            'nombre_almacenado'=> $nombreAlmac,
            'ruta_relativa'    => $rutaRelativa,
            'mime_type'        => $validation['mime'],
            'extension'        => $ext,
            'tamanio_bytes'    => $stored['size'],
            'ancho_px'         => $stored['width']  ?? null,
            'alto_px'          => $stored['height'] ?? null,
            'es_principal'     => $esPrincipal ? 1 : 0,
        ]);

        if (!$arcId) {
            // Rollback: eliminar el archivo físico si no se pudo registrar
            @unlink($rutaAbsoluta);
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => 'No se pudo registrar el archivo en base de datos'];
        }

        return [
            'success' => true,
            'arc_id'  => $arcId,
            'ruta'    => $rutaRelativa,
            'error'   => null,
        ];
    }

    /**
     * Sube un documento (PDF, DOC, etc.) sin procesamiento de imagen.
     *
     * @param  array  $file
     * @param  string $entidad
     * @param  int    $entidadId
     * @param  string $categoria  'documentos', 'comprobantes', 'administrativos'
     * @return array
     */
    public function uploadDocument(
        array $file,
        string $entidad,
        int $entidadId,
        string $categoria = 'documentos'
    ): array {
        $validation = $this->validateFile($file, $categoria);
        if (!$validation['valid']) {
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => $validation['error']];
        }

        $storagePath = $this->buildStoragePath($entidad, $categoria);
        if (!$this->ensureDirectory($storagePath)) {
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => 'No se pudo crear el directorio de almacenamiento'];
        }

        $ext          = $validation['ext'];
        $nombreAlmac  = $this->generateFileName($entidadId, $ext);
        $rutaAbsoluta = $storagePath . DIRECTORY_SEPARATOR . $nombreAlmac;

        $stored = $this->storeFile($file['tmp_name'], $rutaAbsoluta);
        if (!$stored['ok']) {
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => $stored['error']];
        }

        $rutaRelativa = $this->toRelativePath($storagePath) . '/' . $nombreAlmac;

        $arcId = $this->registerFile([
            'entidad'          => $entidad,
            'entidad_id'       => $entidadId,
            'categoria'        => $categoria,
            'nombre_original'  => basename($file['name']),
            'nombre_almacenado'=> $nombreAlmac,
            'ruta_relativa'    => $rutaRelativa,
            'mime_type'        => $validation['mime'],
            'extension'        => $ext,
            'tamanio_bytes'    => $stored['size'],
            'ancho_px'         => null,
            'alto_px'          => null,
            'es_principal'     => 0,
        ]);

        if (!$arcId) {
            @unlink($rutaAbsoluta);
            return ['success' => false, 'arc_id' => null, 'ruta' => null, 'error' => 'No se pudo registrar el archivo en base de datos'];
        }

        return [
            'success' => true,
            'arc_id'  => $arcId,
            'ruta'    => $rutaRelativa,
            'error'   => null,
        ];
    }

    /**
     * Elimina un archivo (soft delete en BD + eliminación física).
     *
     * @param  int  $arcId  arc_id del registro en core_archivos
     * @return bool
     */
    public function deleteFile(int $arcId): bool
    {
        // Obtener registro
        $stmt = $this->db->prepare(
            'SELECT arc_ruta_relativa, arc_tenant_id
               FROM core_archivos
              WHERE arc_id = ? AND arc_tenant_id = ? AND arc_estado = "activo"'
        );
        $stmt->execute([$arcId, $this->tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        // Soft delete en BD
        $upd = $this->db->prepare(
            'UPDATE core_archivos
                SET arc_estado = "eliminado", arc_fecha_eliminacion = NOW()
              WHERE arc_id = ? AND arc_tenant_id = ?'
        );
        $upd->execute([$arcId, $this->tenantId]);

        // Eliminar archivo físico
        $fullPath = BASE_PATH . '/' . $row['arc_ruta_relativa'];
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        return true;
    }

    /**
     * Obtiene la ruta absoluta de un archivo activo.
     *
     * @param  int    $arcId
     * @return string|null
     */
    public function getFilePath(int $arcId): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT arc_ruta_relativa
               FROM core_archivos
              WHERE arc_id = ? AND arc_tenant_id = ? AND arc_estado = "activo"'
        );
        $stmt->execute([$arcId, $this->tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $path = BASE_PATH . '/' . $row['arc_ruta_relativa'];
        return file_exists($path) ? $path : null;
    }

    /**
     * Obtiene la URL pública segura para servir el archivo.
     * Delega en public/archivo.php con el arc_id.
     *
     * @param  int $arcId
     * @return string
     */
    public function getPublicUrl(int $arcId): string
    {
        return \Config::baseUrl('archivo.php?id=' . $arcId);
    }

    /**
     * Busca el archivo principal de una entidad/categoría.
     *
     * @param  string $entidad
     * @param  int    $entidadId
     * @param  string $categoria
     * @return array|null  Fila de core_archivos o null
     */
    public function getPrincipal(string $entidad, int $entidadId, string $categoria = 'fotos'): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
               FROM core_archivos
              WHERE arc_tenant_id  = ?
                AND arc_entidad    = ?
                AND arc_entidad_id = ?
                AND arc_categoria  = ?
                AND arc_es_principal = 1
                AND arc_estado     = "activo"
              LIMIT 1'
        );
        $stmt->execute([$this->tenantId, $entidad, $entidadId, $categoria]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // -----------------------------------------------------------------------
    // Métodos Privados — Validación
    // -----------------------------------------------------------------------

    /**
     * Valida un archivo subido: errores PHP, tamaño, MIME y extensión.
     *
     * @param  array  $file      Entrada de $_FILES
     * @param  string $categoria Categoría para obtener MIME permitidos
     * @return array  ['valid'=>bool, 'mime'=>string|null, 'ext'=>string|null, 'error'=>string|null]
     */
    public function validateFile(array $file, string $categoria): array
    {
        // Error de upload PHP
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'mime' => null, 'ext' => null, 'error' => $this->uploadErrorMessage($file['error'] ?? -1)];
        }

        // Tamaño
        if ($file['size'] > self::MAX_SIZE_DEFAULT) {
            $maxMB = self::MAX_SIZE_DEFAULT / (1024 * 1024);
            return ['valid' => false, 'mime' => null, 'ext' => null, 'error' => "El archivo supera el tamaño máximo permitido ({$maxMB} MB)"];
        }

        // Verificar que es un upload real (no fabricado)
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'mime' => null, 'ext' => null, 'error' => 'Archivo no válido'];
        }

        // Detectar MIME real via magic bytes
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Validar contra tipos permitidos de la categoría
        $allowed = self::ALLOWED_TYPES[$categoria] ?? [];
        if (!isset($allowed[$mime])) {
            $mimesList = implode(', ', array_keys($allowed));
            return ['valid' => false, 'mime' => null, 'ext' => null, 'error' => "Tipo de archivo no permitido. Se aceptan: {$mimesList}"];
        }

        $ext = $allowed[$mime];
        return ['valid' => true, 'mime' => $mime, 'ext' => $ext, 'error' => null];
    }

    /**
     * Mensaje legible para errores de $_FILES['error'].
     */
    private function uploadErrorMessage(int $code): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'El archivo supera el tamaño máximo del servidor (upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el tamaño máximo del formulario (MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL    => 'El archivo fue subido de forma parcial',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal del servidor',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida',
        ];
        return $messages[$code] ?? 'Error desconocido al subir el archivo';
    }

    // -----------------------------------------------------------------------
    // Métodos Privados — Rutas y Nombres
    // -----------------------------------------------------------------------

    /**
     * Construye la ruta absoluta al directorio de almacenamiento.
     *   BASE_PATH/storage/tenants/{tenantId}/{entidad}/{categoria}
     */
    public function buildStoragePath(string $entidad, string $categoria): string
    {
        return BASE_PATH
            . DIRECTORY_SEPARATOR . self::TENANTS_DIR
            . DIRECTORY_SEPARATOR . $this->tenantId
            . DIRECTORY_SEPARATOR . $this->sanitizeFileName($entidad)
            . DIRECTORY_SEPARATOR . $this->sanitizeFileName($categoria);
    }

    /**
     * Convierte una ruta absoluta a ruta relativa desde BASE_PATH.
     * Ej: /var/www/.../storage/tenants/1/alumnos/fotos → storage/tenants/1/alumnos/fotos
     */
    private function toRelativePath(string $absolutePath): string
    {
        $base = rtrim(BASE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($absolutePath, $base) === 0) {
            return str_replace('\\', '/', substr($absolutePath, strlen($base)));
        }
        return str_replace('\\', '/', $absolutePath);
    }

    /**
     * Genera un nombre de archivo único: {entidadId}_{uuid}.{ext}
     */
    public function generateFileName(int $entidadId, string $ext): string
    {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        return $entidadId . '_' . $uuid . '.' . $ext;
    }

    /**
     * Sanitiza un segmento de ruta: solo letras, números, guiones y guiones bajos.
     */
    public function sanitizeFileName(string $name): string
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_\-]/', '', $name);
        return $name ?: 'general';
    }

    /**
     * Crea el directorio de almacenamiento si no existe, incluyendo padres.
     */
    public function ensureDirectory(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }
        return mkdir($path, 0750, true);
    }

    // -----------------------------------------------------------------------
    // Métodos Privados — Almacenamiento Físico
    // -----------------------------------------------------------------------

    /**
     * Mueve el archivo temporal al destino sin procesamiento.
     *
     * @return array ['ok'=>bool, 'size'=>int, 'error'=>string|null]
     */
    public function storeFile(string $tmpPath, string $destPath): array
    {
        if (!move_uploaded_file($tmpPath, $destPath)) {
            return ['ok' => false, 'size' => 0, 'error' => 'No se pudo mover el archivo al almacenamiento'];
        }
        return ['ok' => true, 'size' => filesize($destPath), 'error' => null];
    }

    /**
     * Redimensiona una imagen y la guarda como JPEG (máximo IMAGE_MAX_WIDTHxIMAGE_MAX_HEIGHT).
     * Para PNG con transparencia se mantiene como PNG.
     *
     * @return array ['ok'=>bool, 'size'=>int, 'width'=>int, 'height'=>int, 'error'=>string|null]
     */
    private function resizeAndSave(string $tmpPath, string $destPath, string $mime): array
    {
        if (!extension_loaded('gd')) {
            // GD no disponible: guardar sin redimensionar
            $result = $this->storeFile($tmpPath, $destPath);
            return array_merge($result, ['width' => null, 'height' => null]);
        }

        // Cargar imagen fuente
        switch ($mime) {
            case 'image/jpeg':
                $src = @imagecreatefromjpeg($tmpPath);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($tmpPath);
                break;
            case 'image/webp':
                $src = @imagecreatefromwebp($tmpPath);
                break;
            case 'image/gif':
                $src = @imagecreatefromgif($tmpPath);
                break;
            default:
                $src = false;
        }

        if (!$src) {
            // No se pudo leer como imagen: guardar raw
            $result = $this->storeFile($tmpPath, $destPath);
            return array_merge($result, ['width' => null, 'height' => null]);
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Calcular nuevas dimensiones manteniendo aspecto
        [$newW, $newH] = $this->calcDimensions($origW, $origH, self::IMAGE_MAX_WIDTH, self::IMAGE_MAX_HEIGHT);

        // Crear imagen destino
        $dst = imagecreatetruecolor($newW, $newH);

        // Preservar transparencia para PNG
        if ($mime === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        } else {
            // Fondo blanco para JPEG/WebP/GIF
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);

        // Guardar — PNG mantiene formato; el resto se convierte a JPEG
        if ($mime === 'image/png') {
            // Ajustar extensión en destPath si fuera necesario
            $saved = imagepng($dst, $destPath, 6); // compresión 0-9
        } else {
            // Forzar extensión .jpg en el destino (el nombre ya tiene .jpg de generateFileName)
            $saved = imagejpeg($dst, $destPath, self::IMAGE_JPEG_QUALITY);
        }
        imagedestroy($dst);

        if (!$saved) {
            return ['ok' => false, 'size' => 0, 'width' => null, 'height' => null, 'error' => 'Error al procesar la imagen'];
        }

        return [
            'ok'     => true,
            'size'   => filesize($destPath),
            'width'  => $newW,
            'height' => $newH,
            'error'  => null,
        ];
    }

    /**
     * Calcula nuevas dimensiones manteniendo el aspect ratio.
     *
     * @return int[]  [$newW, $newH]
     */
    private function calcDimensions(int $origW, int $origH, int $maxW, int $maxH): array
    {
        if ($origW <= $maxW && $origH <= $maxH) {
            return [$origW, $origH];
        }

        $ratioW = $maxW / $origW;
        $ratioH = $maxH / $origH;
        $ratio  = min($ratioW, $ratioH);

        return [(int)round($origW * $ratio), (int)round($origH * $ratio)];
    }

    // -----------------------------------------------------------------------
    // Métodos Privados — Base de Datos
    // -----------------------------------------------------------------------

    /**
     * Inserta un registro en core_archivos.
     *
     * @param  array $data  Campos del archivo
     * @return int|null  arc_id insertado o null en caso de error
     */
    public function registerFile(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO core_archivos
                    (arc_tenant_id, arc_entidad, arc_entidad_id, arc_categoria,
                     arc_nombre_original, arc_nombre_almacenado, arc_ruta_relativa,
                     arc_mime_type, arc_extension, arc_tamanio_bytes,
                     arc_ancho_px, arc_alto_px, arc_es_principal, arc_subido_por)
                 VALUES
                    (:tenant_id, :entidad, :entidad_id, :categoria,
                     :nombre_original, :nombre_almacenado, :ruta_relativa,
                     :mime_type, :extension, :tamanio_bytes,
                     :ancho_px, :alto_px, :es_principal, :subido_por)'
            );
            $stmt->execute([
                ':tenant_id'        => $this->tenantId,
                ':entidad'          => $data['entidad'],
                ':entidad_id'       => $data['entidad_id'],
                ':categoria'        => $data['categoria'],
                ':nombre_original'  => $data['nombre_original'],
                ':nombre_almacenado'=> $data['nombre_almacenado'],
                ':ruta_relativa'    => $data['ruta_relativa'],
                ':mime_type'        => $data['mime_type'],
                ':extension'        => $data['extension'],
                ':tamanio_bytes'    => $data['tamanio_bytes'],
                ':ancho_px'         => $data['ancho_px'],
                ':alto_px'          => $data['alto_px'],
                ':es_principal'     => $data['es_principal'],
                ':subido_por'       => $this->userId,
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('FileManager::registerFile error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Desmarca todos los archivos principales de una entidad/categoría.
     * Se llama antes de marcar uno nuevo como principal.
     */
    private function clearPrincipal(string $entidad, int $entidadId, string $categoria): void
    {
        $stmt = $this->db->prepare(
            'UPDATE core_archivos
                SET arc_es_principal = 0
              WHERE arc_tenant_id  = ?
                AND arc_entidad    = ?
                AND arc_entidad_id = ?
                AND arc_categoria  = ?
                AND arc_estado     = "activo"'
        );
        $stmt->execute([$this->tenantId, $entidad, $entidadId, $categoria]);
    }
}
