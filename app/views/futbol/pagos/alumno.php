<?php
/**
 * Vista Pagos por Alumno — info del alumno + historial + formulario de nuevo pago
 * @var array  $alumno
 * @var array  $historial
 * @var float  $total_pagado
 * @var float  $total_pendiente
 * @var array  $grupos
 * @var string $csrf_token
 * @var array  $modulo_actual
 */
$moduloColor    = $modulo_actual['color'] ?? '#22C55E';
$alumno         = $alumno ?? [];
$historial      = $historial ?? [];
$totalPagado    = $total_pagado    ?? 0;
$totalPendiente = $total_pendiente ?? 0;
$grupos         = $grupos ?? [];

$nombreCompleto = trim(($alumno['alu_nombres'] ?? '') . ' ' . ($alumno['alu_apellidos'] ?? ''));
$grupoActualId  = $alumno['fgr_grupo_id'] ?? '';
$alumnoId       = (int)($alumno['alu_alumno_id'] ?? 0);
$becasAlumno    = $becas_alumno ?? [];

// Paleta de colores para rubros dinámicos
$rubroColores = ['#22C55E','#3B82F6','#8B5CF6','#F59E0B','#EF4444','#06B6D4','#F97316','#6B7280'];

// Fallback: si no hay rubros configurados, usar tipos fijos
$tiposFallback = [
    ['rub_id' => 0, 'rub_codigo' => 'MENSUALIDAD', 'rub_nombre' => 'Mensualidad', 'tipo_sugerido' => 'MENSUALIDAD'],
    ['rub_id' => 0, 'rub_codigo' => 'MATRICULA',   'rub_nombre' => 'Matrícula',   'tipo_sugerido' => 'MATRICULA'],
    ['rub_id' => 0, 'rub_codigo' => 'UNIFORME',     'rub_nombre' => 'Uniforme',    'tipo_sugerido' => 'UNIFORME'],
    ['rub_id' => 0, 'rub_codigo' => 'TORNEO',       'rub_nombre' => 'Torneo',      'tipo_sugerido' => 'TORNEO'],
    ['rub_id' => 0, 'rub_codigo' => 'OTRO',         'rub_nombre' => 'Otro',        'tipo_sugerido' => 'OTRO'],
];
$rubrosSelector = !empty($rubros) ? $rubros : $tiposFallback;
$primerRubro    = $rubrosSelector[0];

$estadoClass = [
    'PENDIENTE' => 'warning',
    'PAGADO'    => 'success',
    'VENCIDO'   => 'danger',
    'ANULADO'   => 'secondary',
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-7">
                <h1 class="m-0">
                    <i class="fas fa-dollar-sign mr-2" style="color:<?= $moduloColor ?>"></i>
                    Pagos — <?= htmlspecialchars($nombreCompleto) ?>
                </h1>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'pago', 'index') ?>">Pagos</a></li>
                    <li class="breadcrumb-item active">Alumno</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="row">

            <!-- Columna izquierda: info del alumno + formulario de pago -->
            <div class="col-lg-5">

                <!-- Tarjeta de información del alumno -->
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Datos del Alumno</h3>
                        <div class="card-tools">
                            <a href="<?= url('futbol', 'alumno', 'ver') ?>&id=<?= $alumnoId ?>"
                               class="btn btn-xs btn-outline-secondary" title="Ver ficha completa">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="d-flex align-items-start">
                            <!-- Foto -->
                            <div class="mr-3 flex-shrink-0">
                                <?php if (!empty($alumno['foto_arc_id'])): ?>
                                <img src="<?= \Config::baseUrl('archivo.php?id=' . (int)$alumno['foto_arc_id']) ?>"
                                     alt="Foto" class="img-circle elevation-2"
                                     style="width:72px;height:72px;object-fit:cover;">
                                <?php else: ?>
                                <div class="img-circle elevation-1 d-flex align-items-center justify-content-center bg-secondary"
                                     style="width:72px;height:72px;">
                                    <i class="fas fa-user fa-2x text-white"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <!-- Info -->
                            <div class="flex-grow-1 min-width-0">
                                <p class="mb-1"><strong><?= htmlspecialchars($nombreCompleto) ?></strong></p>
                                <?php if (!empty($alumno['alu_identificacion'])): ?>
                                <p class="mb-1 small text-muted"><i class="fas fa-id-badge mr-1"></i><code><?= htmlspecialchars($alumno['alu_identificacion']) ?></code></p>
                                <?php endif; ?>
                                <?php if (!empty($alumno['categoria_nombre'])): ?>
                                <p class="mb-1">
                                    <span class="badge" style="background:<?= htmlspecialchars($alumno['categoria_color'] ?? '#6c757d') ?>;color:white;">
                                        <?= htmlspecialchars($alumno['categoria_nombre']) ?>
                                    </span>
                                    <?php if (!empty($alumno['grupo_nombre'])): ?>
                                    <span class="badge badge-light border ml-1"><?= htmlspecialchars($alumno['grupo_nombre']) ?></span>
                                    <?php endif; ?>
                                </p>
                                <?php endif; ?>
                                <?php
                                $repNombre = trim(($alumno['rep_nombres'] ?? '') . ' ' . ($alumno['rep_apellidos'] ?? ''));
                                if ($repNombre !== ''): ?>
                                <p class="mb-0 small text-muted"><i class="fas fa-user-friends mr-1"></i><?= htmlspecialchars($repNombre) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($becasAlumno)): ?>
                        <hr class="my-2">
                        <div class="small">
                            <span class="font-weight-bold text-purple"><i class="fas fa-graduation-cap mr-1"></i>Beca<?= count($becasAlumno) > 1 ? 's' : '' ?> activa<?= count($becasAlumno) > 1 ? 's' : '' ?>:</span>
                            <?php foreach ($becasAlumno as $beca): ?>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <span><?= htmlspecialchars($beca['fbe_nombre']) ?></span>
                                <span>
                                    <span class="badge badge-success ml-1">
                                        <?php if ($beca['fbe_tipo'] === 'EXONERACION'): ?>
                                            100%
                                        <?php elseif ($beca['fbe_tipo'] === 'PORCENTAJE'): ?>
                                            <?= number_format($beca['fbe_valor'], 0) ?>%
                                        <?php else: ?>
                                            $<?= number_format($beca['fbe_valor'], 2) ?>
                                        <?php endif; ?>
                                    </span>
                                    <?php if (!empty($beca['rub_nombre'])): ?>
                                    <span class="badge badge-light border ml-1"><?= htmlspecialchars($beca['rub_nombre']) ?></span>
                                    <?php else: ?>
                                    <span class="badge badge-light border ml-1 text-muted">Todos los rubros</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <hr class="my-2">
                        <p class="small text-muted mb-0"><i class="fas fa-graduation-cap mr-1"></i>Sin becas asignadas</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen financiero del alumno -->
                <div class="row">
                    <div class="col-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cobrado</span>
                                <span class="info-box-number">$<?= number_format($totalPagado, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box <?= $totalPendiente > 0 ? 'bg-warning' : 'bg-secondary' ?>">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pendiente</span>
                                <span class="info-box-number">$<?= number_format($totalPendiente, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de nuevo pago -->
                <div class="card">
                    <div class="card-header py-2" style="background:<?= $moduloColor ?>;color:white;">
                        <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Registrar Nuevo Pago</h3>
                    </div>
                    <div class="card-body">

                        <!-- Selector de tipo de pago (rubros dinámicos) -->
                        <div class="mb-2">
                            <label class="small font-weight-bold d-block mb-1">
                                Rubro / Tipo de Pago
                                <?php if (!empty($rubros)): ?>
                                <span class="badge badge-info ml-1" style="font-weight:400;font-size:.7rem;">
                                    <i class="fas fa-sync-alt mr-1"></i>Desde Facturación
                                </span>
                                <?php endif; ?>
                            </label>
                            <div class="d-flex flex-wrap" id="contenedorRubros" style="gap:5px;">
                                <?php foreach ($rubrosSelector as $i => $r): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary js-tipo-pago"
                                        data-tipo="<?= htmlspecialchars($r['tipo_sugerido']) ?>"
                                        data-concepto="<?= htmlspecialchars($r['rub_nombre']) ?>"
                                        data-rubro-id="<?= (int)$r['rub_id'] ?>"
                                        style="--tipo-color:<?= $rubroColores[$i % count($rubroColores)] ?>">
                                    <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($r['rub_nombre']) ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <form id="formNuevoPago">
                            <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="alumno_id"   value="<?= $alumnoId ?>">
                            <input type="hidden" name="cliente_id"  value="<?= (int)($alumno['rep_cliente_id'] ?? 0) ?>">
                            <input type="hidden" id="fpg_tipo"      name="tipo"     value="<?= htmlspecialchars($primerRubro['tipo_sugerido']) ?>">
                            <input type="hidden" id="fpg_rubro_id"  name="rubro_id" value="<?= (int)$primerRubro['rub_id'] ?>">

                            <div class="form-group mb-2">
                                <label class="small">Concepto</label>
                                <input type="text" id="fpg_concepto" name="concepto"
                                       class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($primerRubro['rub_nombre']) ?>"
                                       placeholder="Descripción del pago" maxlength="200">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small">Grupo <span class="text-danger">*</span></label>
                                        <select name="grupo_id" class="form-control form-control-sm" required>
                                            <option value="">— Seleccionar —</option>
                                            <?php foreach ($grupos as $g): ?>
                                            <option value="<?= $g['fgr_grupo_id'] ?>"
                                                <?= $g['fgr_grupo_id'] == $grupoActualId ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['fgr_nombre']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small">Mes Correspondiente</label>
                                        <input type="month" name="mes_correspondiente" class="form-control form-control-sm"
                                               value="<?= date('Y-m') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="small">Monto ($) <span class="text-danger">*</span></label>
                                        <input type="number" id="fpg_monto" name="monto"
                                               class="form-control form-control-sm" step="0.01" min="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="small">Descuento ($)</label>
                                        <input type="number" name="descuento" value="0"
                                               class="form-control form-control-sm" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="small">Beca ($)</label>
                                        <input type="number" name="beca_descuento" value="0"
                                               class="form-control form-control-sm" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small">Método de Pago <span class="text-danger">*</span></label>
                                        <select name="metodo_pago" class="form-control form-control-sm" required>
                                            <option value="EFECTIVO">Efectivo</option>
                                            <option value="TRANSFERENCIA">Transferencia</option>
                                            <option value="TARJETA">Tarjeta</option>
                                            <option value="DEPOSITO">Depósito</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small">Referencia / Nro. Comprobante</label>
                                        <input type="text" name="referencia" class="form-control form-control-sm"
                                               placeholder="Nro. transacción...">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="small">Estado</label>
                                <select name="estado" class="form-control form-control-sm">
                                    <option value="PAGADO" selected>Pagado</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="small">Notas</label>
                                <textarea name="notas" class="form-control form-control-sm" rows="2"
                                          placeholder="Observaciones opcionales..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-block btn-success" id="btnGuardarPago">
                                <i class="fas fa-save mr-1"></i> Guardar Pago
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Columna derecha: historial de pagos -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header py-2" style="border-top: 3px solid <?= $moduloColor ?>">
                        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Historial de Pagos</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($historial)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                            <p>No hay pagos registrados para este alumno.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="tablaHistorial">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th>Mes</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center" width="90">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $p): ?>
                                    <tr id="fila-pago-<?= $p['fpg_pago_id'] ?>">
                                        <td class="small">
                                            <?= !empty($p['fpg_fecha']) ? date('d/m/Y', strtotime($p['fpg_fecha'])) : '—' ?>
                                        </td>
                                        <td class="small">
                                            <?= htmlspecialchars($p['fpg_concepto'] ?? $p['fpg_tipo'] ?? '') ?>
                                            <?php if (!empty($p['grupo_nombre'])): ?>
                                            <br><span class="text-muted"><?= htmlspecialchars($p['grupo_nombre']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small"><?= $p['fpg_mes_correspondiente'] ?? '—' ?></td>
                                        <td class="text-right small">
                                            <strong>$<?= number_format($p['fpg_total'] ?? 0, 2) ?></strong>
                                            <?php if (($p['fpg_beca_descuento'] ?? 0) > 0 || ($p['fpg_descuento'] ?? 0) > 0): ?>
                                            <br><small class="text-info">
                                                <i class="fas fa-tag"></i>
                                                -$<?= number_format(($p['fpg_beca_descuento'] ?? 0) + ($p['fpg_descuento'] ?? 0), 2) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php $est = $p['fpg_estado'] ?? 'PENDIENTE'; ?>
                                            <span class="badge badge-<?= $estadoClass[$est] ?? 'secondary' ?> badge-estado"
                                                  id="estado-<?= $p['fpg_pago_id'] ?>">
                                                <?= $est ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <?php if (in_array($est, ['PENDIENTE','VENCIDO'])): ?>
                                                <button type="button" class="btn btn-success js-cobrar"
                                                        data-id="<?= $p['fpg_pago_id'] ?>"
                                                        title="Marcar como cobrado">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if ($est !== 'ANULADO'): ?>
                                                <button type="button" class="btn btn-warning js-anular"
                                                        data-id="<?= $p['fpg_pago_id'] ?>"
                                                        title="Anular pago">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /.row -->

        <div class="mt-2">
            <a href="<?= url('futbol', 'pago', 'index') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i>Volver a Pagos
            </a>
        </div>

    </div>
</section>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function () {
    var CSRF         = '<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>';
    var URL_CREAR    = '<?= url('futbol', 'pago', 'crear') ?>';
    var URL_COBRAR   = '<?= url('futbol', 'pago', 'cobrar') ?>';
    var URL_ANULAR   = '<?= url('futbol', 'pago', 'anular') ?>';

    // --- Selector de rubro / tipo de pago ---
    var btns = document.querySelectorAll('.js-tipo-pago');
    // Activar el primer botón por defecto
    if (btns.length > 0) activarTipo(btns[0]);

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            btns.forEach(function (b) { desactivarTipo(b); });
            activarTipo(btn);
            document.getElementById('fpg_tipo').value     = btn.dataset.tipo     || 'OTRO';
            document.getElementById('fpg_rubro_id').value = btn.dataset.rubroId  || '0';
            document.getElementById('fpg_concepto').value = btn.dataset.concepto || '';
        });
    });

    function activarTipo(btn) {
        var color = btn.style.getPropertyValue('--tipo-color') || '#22C55E';
        btn.style.background    = color;
        btn.style.borderColor   = color;
        btn.style.color         = '#fff';
        btn.classList.remove('btn-outline-secondary');
    }
    function desactivarTipo(btn) {
        btn.style.background    = '';
        btn.style.borderColor   = '';
        btn.style.color         = '';
        btn.classList.add('btn-outline-secondary');
    }

    // --- Formulario de nuevo pago ---
    document.getElementById('formNuevoPago').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnGuardarPago');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...';

        var fd = new FormData(this);
        fetch(URL_CREAR, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: '¡Pago registrado!', text: res.message, timer: 1500, showConfirmButton: false })
                            .then(function () { location.reload(); });
                    } else {
                        alert(res.message);
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                    else alert('Error: ' + res.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save mr-1"></i> Guardar Pago';
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-1"></i> Guardar Pago';
            });
    });

    // --- Cobrar (marcar como pagado) ---
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-cobrar');
        if (!btn) return;
        var id = btn.dataset.id;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Marcar como cobrado?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Sí, cobrar',
                cancelButtonText: 'Cancelar'
            }).then(function (r) { if (r.isConfirmed) cobrarPago(id, btn); });
        } else {
            if (confirm('¿Marcar como cobrado?')) cobrarPago(id, btn);
        }
    });

    function cobrarPago(id, btn) {
        var fd = new FormData();
        fd.append('csrf_token', CSRF);
        fd.append('id', id);
        fetch(URL_COBRAR, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    var badge = document.getElementById('estado-' + id);
                    if (badge) { badge.textContent = 'PAGADO'; badge.className = 'badge badge-success badge-estado'; }
                    btn.closest('.btn-group').innerHTML = '';
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                }
            });
    }

    // --- Anular pago ---
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-anular');
        if (!btn) return;
        var id = btn.dataset.id;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Anular este pago?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar'
            }).then(function (r) { if (r.isConfirmed) anularPago(id, btn); });
        } else {
            if (confirm('¿Anular este pago?')) anularPago(id, btn);
        }
    });

    function anularPago(id, btn) {
        var fd = new FormData();
        fd.append('csrf_token', CSRF);
        fd.append('id', id);
        fetch(URL_ANULAR, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    var badge = document.getElementById('estado-' + id);
                    if (badge) { badge.textContent = 'ANULADO'; badge.className = 'badge badge-secondary badge-estado'; }
                    btn.closest('.btn-group').innerHTML = '';
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                }
            });
    }

    // DataTable historial
    if (typeof $ !== 'undefined' && $('#tablaHistorial').length && $('#tablaHistorial tbody tr').length > 1) {
        $('#tablaHistorial').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 10,
            order: [[0, 'desc']],
            responsive: true,
            columnDefs: [{ orderable: false, targets: [5] }]
        });
    }
}());
</script>
<?php $scripts = ob_get_clean(); ?>
