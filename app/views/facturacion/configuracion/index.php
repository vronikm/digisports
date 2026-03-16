<?php
/**
 * DigiSports Facturación — Configuración de Facturación Electrónica
 */
$config      = $config      ?? [];
$certInfo    = $cert_info   ?? null;
$certError   = $cert_error  ?? null;
$logoUrl     = $logo_url    ?? null;
$csrfToken   = $csrf_token  ?? '';
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';

// Helper: valor del campo de configuración (con fallback a '')
function cfgVal(array $config, string $campo, $default = ''): string {
    return htmlspecialchars((string)($config[$campo] ?? $default));
}
$cfg = $config; // alias corto
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-cog mr-2" style="color:<?= $moduloColor ?>"></i>
                    Configuración · Facturación Electrónica
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right d-flex gap-2">
                    <button class="btn btn-outline-info btn-sm mr-1" id="btnProbarConexion">
                        <i class="fas fa-wifi mr-1"></i>Probar conexión SRI
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="btnProbarCert">
                        <i class="fas fa-certificate mr-1"></i>Verificar certificado
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

    <!-- ── Estado rápido ──────────────────────────────────────────────────── -->
    <div class="row mb-3">
        <!-- Estado conexión SRI -->
        <div class="col-md-4">
            <div class="info-box" id="boxConexionSRI">
                <span class="info-box-icon bg-secondary"><i class="fas fa-globe-americas"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Conexión SRI</span>
                    <span class="info-box-number" id="lblConexionSRI">Sin verificar</span>
                    <small class="text-muted" id="lblAmbiente">
                        Ambiente: <?= ($cfg['cfg_ambiente'] ?? 1) == 1 ? 'PRUEBAS' : 'PRODUCCIÓN' ?>
                    </small>
                </div>
            </div>
        </div>
        <!-- Estado certificado -->
        <div class="col-md-4">
            <?php if ($certInfo): ?>
            <div class="info-box <?= $certInfo['vigente'] ? 'bg-success' : 'bg-danger' ?> text-white">
                <span class="info-box-icon"><i class="fas fa-certificate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificado</span>
                    <span class="info-box-number" style="font-size:14px;"><?= htmlspecialchars($certInfo['titular']) ?></span>
                    <small>
                        <?= $certInfo['vigente'] ? 'Vigente' : '¡VENCIDO!' ?> —
                        vence <?= htmlspecialchars($certInfo['valido_hasta']) ?>
                        (<?= $certInfo['dias_restantes'] ?> días)
                    </small>
                </div>
            </div>
            <?php elseif ($certError): ?>
            <div class="info-box bg-danger text-white">
                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificado — ERROR</span>
                    <small><?= htmlspecialchars($certError) ?></small>
                </div>
            </div>
            <?php else: ?>
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificado</span>
                    <span class="info-box-number" style="font-size:14px;">No configurado</span>
                    <small>Suba el archivo .p12 en la sección de firma</small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- Logo actual -->
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-light border">
                    <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="Logo" style="width:60px;height:60px;object-fit:contain;padding:4px;">
                    <?php else: ?>
                    <i class="fas fa-image text-muted"></i>
                    <?php endif; ?>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Logo Emisor (RIDE)</span>
                    <span class="info-box-number" style="font-size:13px;">
                        <?= $logoUrl ? 'Configurado' : 'Sin logo' ?>
                    </span>
                    <small class="text-muted">Se imprime en el encabezado de la factura</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Formulario principal ───────────────────────────────────────────── -->
    <form id="formConfig" enctype="multipart/form-data"
          data-url="<?= url('facturacion', 'configuracion', 'guardar') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="row">
            <!-- ────── Columna izquierda ──────────────────────────────────── -->
            <div class="col-lg-7">

                <!-- Datos del emisor -->
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-building mr-2"></i>Datos del Emisor</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>RUC <span class="text-danger">*</span>
                                        <small class="text-muted">(13 dígitos)</small>
                                    </label>
                                    <input type="text" name="ruc" id="inpRuc" class="form-control"
                                           maxlength="13" pattern="\d{13}"
                                           value="<?= cfgVal($cfg,'cfg_ruc') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Razón Social <span class="text-danger">*</span></label>
                                    <input type="text" name="razon_social" class="form-control"
                                           style="text-transform:uppercase"
                                           value="<?= cfgVal($cfg,'cfg_razon_social') ?>" required>
                                    <small class="text-muted">Exactamente como aparece en el SRI, en mayúsculas</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nombre Comercial</label>
                            <input type="text" name="nombre_comercial" class="form-control"
                                   value="<?= cfgVal($cfg,'cfg_nombre_comercial') ?>"
                                   placeholder="Si difiere de la razón social">
                        </div>
                        <div class="form-group">
                            <label>Dirección Matriz <span class="text-danger">*</span></label>
                            <input type="text" name="direccion_matriz" id="inpDirMatriz" class="form-control"
                                   value="<?= cfgVal($cfg,'cfg_direccion_matriz') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Dirección Establecimiento
                                <small class="text-muted">(si difiere de la matriz)</small>
                            </label>
                            <input type="text" name="direccion_establecimiento" class="form-control"
                                   id="inpDirEstab"
                                   value="<?= cfgVal($cfg,'cfg_direccion_establecimiento') ?>"
                                   placeholder="Se copia de la Dirección Matriz si está vacío">
                        </div>
                        <div class="form-group">
                            <label>Email para notificaciones de FE</label>
                            <input type="email" name="email_notificaciones" class="form-control"
                                   value="<?= cfgVal($cfg,'cfg_email_notificaciones') ?>"
                                   placeholder="notificaciones@tuempresa.com">
                        </div>
                    </div>
                </div>

                <!-- Configuración fiscal -->
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-receipt mr-2"></i>Configuración Fiscal</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Establecimiento</label>
                                    <input type="text" name="codigo_establecimiento" class="form-control text-center"
                                           maxlength="3" pattern="\d{1,3}"
                                           value="<?= cfgVal($cfg,'cfg_codigo_establecimiento','001') ?>">
                                    <small class="text-muted">3 dígitos (ej. 001)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Punto Emisión</label>
                                    <input type="text" name="punto_emision" class="form-control text-center"
                                           maxlength="3" pattern="\d{1,3}"
                                           value="<?= cfgVal($cfg,'cfg_punto_emision','001') ?>">
                                    <small class="text-muted">3 dígitos (ej. 001)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Nro. Inicio</label>
                                    <input type="number" name="secuencial_inicio" class="form-control text-center"
                                           min="1" value="<?= cfgVal($cfg,'cfg_secuencial_inicio','1') ?>">
                                    <small class="text-muted">Secuencial inicial</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Obligado Contab.</label>
                                    <select name="obligado_contabilidad" class="form-control">
                                        <option value="SI" <?= ($cfg['cfg_obligado_contabilidad']??'SI')==='SI'?'selected':'' ?>>SI</option>
                                        <option value="NO" <?= ($cfg['cfg_obligado_contabilidad']??'')==='NO'?'selected':'' ?>>NO</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Contribuyente Especial</label>
                                    <input type="text" name="contribuyente_especial" class="form-control"
                                           value="<?= cfgVal($cfg,'cfg_contribuyente_especial') ?>"
                                           placeholder="Nro. resolución (vacío si no aplica)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Agente de Retención</label>
                                    <input type="text" name="agente_retencion" class="form-control"
                                           value="<?= cfgVal($cfg,'cfg_agente_retencion') ?>"
                                           placeholder="Nro. resolución (vacío si no aplica)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Régimen</label>
                                    <select name="regimen_microempresas" class="form-control mb-1">
                                        <option value="NO" <?= ($cfg['cfg_regimen_microempresas']??'NO')==='NO'?'selected':'' ?>>Normal / RIMPE Negocio Popular: NO</option>
                                        <option value="SI" <?= ($cfg['cfg_regimen_microempresas']??'')==='SI'?'selected':'' ?>>RIMPE Negocio Popular: SI</option>
                                    </select>
                                    <select name="regimen_rimpe" class="form-control">
                                        <option value="NO" <?= ($cfg['cfg_regimen_rimpe']??'NO')==='NO'?'selected':'' ?>>RIMPE Emprendedor: NO</option>
                                        <option value="SI" <?= ($cfg['cfg_regimen_rimpe']??'')==='SI'?'selected':'' ?>>RIMPE Emprendedor: SI</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ────── Columna derecha ─────────────────────────────────────── -->
            <div class="col-lg-5">

                <!-- Ambiente SRI -->
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-server mr-2"></i>Ambiente SRI</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group text-center">
                                    <label class="d-block mb-2">Ambiente</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-warning <?= ($cfg['cfg_ambiente']??1)==1?'active':'' ?>">
                                            <input type="radio" name="ambiente" value="1"
                                                   <?= ($cfg['cfg_ambiente']??1)==1?'checked':'' ?>>
                                            <i class="fas fa-flask mr-1"></i>Pruebas
                                        </label>
                                        <label class="btn btn-outline-success <?= ($cfg['cfg_ambiente']??1)==2?'active':'' ?>">
                                            <input type="radio" name="ambiente" value="2"
                                                   <?= ($cfg['cfg_ambiente']??1)==2?'checked':'' ?>>
                                            <i class="fas fa-check mr-1"></i>Producción
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Pruebas: celcer.sri.gob.ec<br>
                                        Producción: cel.sri.gob.ec
                                    </small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:12px;">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>¡Importante!</strong> Cambia a <em>Producción</em> solo cuando el SRI haya
                                    habilitado tu RUC para facturación electrónica y hayas completado las pruebas.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo -->
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image mr-2"></i>Logo para el RIDE</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <img id="logoPreview"
                                     src="<?= $logoUrl ?: '' ?>"
                                     alt="Logo"
                                     class="rounded border"
                                     style="width:80px;height:80px;object-fit:contain;background:#f8f9fa;padding:4px;<?= $logoUrl?'':'display:none' ?>">
                                <div id="logoPlaceholder"
                                     class="rounded border d-flex align-items-center justify-content-center"
                                     style="width:80px;height:80px;background:#f8f9fa;<?= $logoUrl?'display:none':'' ?>">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <label class="d-block mb-1">Subir logo
                                    <small class="text-muted">(JPG, PNG, SVG — máx. 2 MB)</small>
                                </label>
                                <input type="file" name="logo" id="inpLogo"
                                       class="form-control-file"
                                       accept="image/jpeg,image/png,image/svg+xml">
                                <small class="text-muted">Se mostrará en el encabezado izquierdo del RIDE</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Firma electrónica -->
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-signature mr-2"></i>Firma Electrónica (.p12)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($cfg['cfg_certificado_ruta'])): ?>
                        <div class="alert alert-<?= $certInfo && $certInfo['vigente'] ? 'success' : 'danger' ?> py-2 mb-3" style="font-size:12px;">
                            <i class="fas fa-<?= $certInfo && $certInfo['vigente'] ? 'check-circle' : 'times-circle' ?> mr-1"></i>
                            <?php if ($certInfo): ?>
                                <strong><?= htmlspecialchars($certInfo['titular']) ?></strong> —
                                vence <?= htmlspecialchars($certInfo['valido_hasta']) ?>
                                (<?= $certInfo['dias_restantes'] ?> días restantes)
                            <?php else: ?>
                                <?= htmlspecialchars($certError ?? 'Error al leer el certificado') ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <p class="text-muted small mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            El certificado de firma electrónica (.p12) lo emite el BCE, Security Data,
                            Anfac, Uanataca u otra entidad certificadora autorizada por el SRI.
                        </p>

                        <div class="form-group">
                            <label>Archivo .p12
                                <span class="text-danger">*</span>
                                <small class="text-muted">(máx. 1 MB)</small>
                            </label>
                            <input type="file" id="inpCertificado" class="form-control-file"
                                   accept=".p12,application/x-pkcs12">
                        </div>
                        <div class="form-group">
                            <label>Contraseña del certificado <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="inpClaveCert" class="form-control"
                                       placeholder="Contraseña del archivo .p12"
                                       autocomplete="new-password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btnToggleClave">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning btn-sm mr-2" id="btnSubirCert">
                                <i class="fas fa-upload mr-1"></i>Subir y verificar certificado
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-lock mr-1"></i>
                            La contraseña se cifra con AES-256-GCM antes de almacenarse. El archivo .p12
                            nunca es accesible desde el navegador.
                        </small>
                    </div>
                </div>

            </div>
        </div><!-- /row -->

        <!-- ── Botones de acción ──────────────────────────────────────────── -->
        <div class="row mb-4">
            <div class="col-12 text-right">
                <button type="submit" class="btn btn-lg" id="btnGuardar"
                        style="background:<?= $moduloColor ?>;color:white;">
                    <i class="fas fa-save mr-2"></i>Guardar Configuración
                </button>
            </div>
        </div>

    </form>

</div>
</section>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function () {
    var Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 4000, timerProgressBar: true
    });
    var csrfToken   = '<?= addslashes($csrfToken) ?>';
    var urlGuardar  = '<?= url('facturacion', 'configuracion', 'guardar') ?>';
    var urlCert     = '<?= url('facturacion', 'configuracion', 'subirCertificado') ?>';
    var urlProbarCert = '<?= url('facturacion', 'configuracion', 'probarCertificado') ?>';
    var urlConexion   = '<?= url('facturacion', 'configuracion', 'probarConexion') ?>';

    // ── Copiar dirección matriz → establecimiento si vacía ─────────────────
    $('#inpDirMatriz').on('blur', function () {
        if (!$('#inpDirEstab').val().trim()) {
            $('#inpDirEstab').val($(this).val());
        }
    });

    // ── RUC — solo dígitos ─────────────────────────────────────────────────
    $('#inpRuc').on('input', function () {
        this.value = this.value.replace(/\D/g, '').substr(0, 13);
    });

    // ── Preview logo ───────────────────────────────────────────────────────
    $('#inpLogo').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#logoPreview').attr('src', e.target.result).show();
            $('#logoPlaceholder').hide();
        };
        reader.readAsDataURL(file);
    });

    // ── Mostrar / ocultar contraseña ───────────────────────────────────────
    $('#btnToggleClave').on('click', function () {
        var inp  = $('#inpClaveCert');
        var icon = $(this).find('i');
        if (inp.attr('type') === 'password') {
            inp.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            inp.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // ── Guardar configuración general ──────────────────────────────────────
    $('#formConfig').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btnGuardar').prop('disabled', true);
        var fd   = new FormData(this);
        fetch(urlGuardar, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                Toast.fire({ icon: res.success ? 'success' : 'error', title: res.message });
                if (res.success) setTimeout(function () { location.reload(); }, 1500);
            })
            .catch(function () { Toast.fire({ icon: 'error', title: 'Error de comunicación' }); })
            .finally(function () { $btn.prop('disabled', false); });
    });

    // ── Subir certificado .p12 ─────────────────────────────────────────────
    $('#btnSubirCert').on('click', function () {
        var file  = $('#inpCertificado')[0].files[0];
        var clave = $('#inpClaveCert').val();

        if (!file) {
            Toast.fire({ icon: 'warning', title: 'Seleccione el archivo .p12' });
            return;
        }
        if (!clave) {
            Toast.fire({ icon: 'warning', title: 'Ingrese la contraseña del certificado' });
            return;
        }

        var fd = new FormData();
        fd.append('csrf_token',        csrfToken);
        fd.append('certificado',       file);
        fd.append('clave_certificado', clave);

        var $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Verificando...');

        fetch(urlCert, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Certificado cargado',
                        html: '<b>Titular:</b> ' + res.data.titular + '<br>'
                            + '<b>Vigencia:</b> ' + (res.data.vigencia || 'N/A') + '<br>'
                            + '<b>Días restantes:</b> ' + (res.data.dias_restantes ?? 'N/A'),
                        confirmButtonText: 'Recargar'
                    }).then(function () { location.reload(); });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            })
            .catch(function () { Toast.fire({ icon: 'error', title: 'Error al subir el certificado' }); })
            .finally(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Subir y verificar certificado');
            });
    });

    // ── Probar certificado existente ───────────────────────────────────────
    $('#btnProbarCert').on('click', function () {
        var $btn = $(this).prop('disabled', true);
        fetch(urlProbarCert)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    var d = res.data;
                    Swal.fire({
                        icon: d.vigente ? 'success' : 'warning',
                        title: 'Certificado ' + (d.vigente ? 'Vigente' : '¡Vencido!'),
                        html: '<b>Titular:</b> ' + d.titular + '<br>'
                            + '<b>Emisor:</b> ' + d.emisor + '<br>'
                            + '<b>Vence:</b> ' + d.valido_hasta + '<br>'
                            + '<b>Días restantes:</b> ' + d.dias_restantes,
                    });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            })
            .catch(function () { Toast.fire({ icon: 'error', title: 'Error al verificar' }); })
            .finally(function () { $btn.prop('disabled', false); });
    });

    // ── Probar conexión SRI ────────────────────────────────────────────────
    $('#btnProbarConexion').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Probando...');
        $('#boxConexionSRI').removeClass('bg-success bg-danger bg-warning').addClass('bg-secondary');
        $('#lblConexionSRI').text('Verificando...');

        fetch(urlConexion)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var c = res.data.conectividad;
                var ok = c && c.recepcion && c.autorizacion;
                $('#boxConexionSRI').removeClass('bg-secondary').addClass(ok ? 'bg-success' : 'bg-danger');
                $('#lblConexionSRI').text(ok ? 'Conectado' : 'Sin conexión');
                if (res.data.ambiente) {
                    $('#lblAmbiente').text('Ambiente: ' + res.data.ambiente);
                }

                var detalleHtml = '<ul class="text-left mb-0">'
                    + '<li>Recepción: ' + (c.recepcion ? '✅' : '❌') + '</li>'
                    + '<li>Autorización: ' + (c.autorizacion ? '✅' : '❌') + '</li>'
                    + '</ul>';

                Toast.fire({
                    icon: ok ? 'success' : 'error',
                    title: res.message,
                    html: detalleHtml,
                    timer: 6000,
                });
            })
            .catch(function () {
                $('#boxConexionSRI').removeClass('bg-secondary').addClass('bg-danger');
                $('#lblConexionSRI').text('Error de red');
                Toast.fire({ icon: 'error', title: 'No se pudo conectar con el SRI' });
            })
            .finally(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-wifi mr-1"></i>Probar conexión SRI');
            });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
