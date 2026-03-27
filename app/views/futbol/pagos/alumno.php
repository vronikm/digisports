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
$alumno         ??= [];
$historial      ??= [];
$totalPagado    = $total_pagado    ?? 0;
$totalPendiente = $total_pendiente ?? 0;
$grupos         ??= [];

$nombreCompleto = trim(($alumno['alu_nombres'] ?? '') . ' ' . ($alumno['alu_apellidos'] ?? ''));
$grupoActualId  = $alumno['fgr_grupo_id'] ?? '';
$alumnoId       = (int)($alumno['alu_alumno_id'] ?? 0);
$becasAlumno    = $becas_alumno    ?? [];
$sedeMontos     = $sede_montos     ?? ['sed_monto_mensualidad' => 0, 'sed_monto_matricula' => 0];
$imagenesPagos  = $imagenes_pagos  ?? [];
$abonosPorPago  = $abonos_por_pago ?? [];
$inactividades  = $inactividades   ?? [];
$enLicenciaHoy  = $en_licencia_hoy ?? false;
$licenciaActiva = $licencia_activa ?? null;

$tipoInacLabel  = [
    'VACACIONES' => 'Vacaciones',
    'VIAJE'      => 'Viaje',
    'ENFERMEDAD' => 'Enfermedad',
    'ECONOMICO'  => 'Motivo económico',
    'OTRO'       => 'Otro motivo',
];

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

                <!-- Banner de licencia activa -->
                <?php if ($enLicenciaHoy): ?>
                <div class="alert alert-secondary d-flex align-items-center justify-content-between py-2 mb-2" style="border-left:4px solid #6c757d;">
                    <div>
                        <i class="fas fa-pause-circle mr-2 text-secondary"></i>
                        <strong>En licencia / inactivo</strong>
                        <span class="ml-2 text-muted small">
                            <?= $tipoInacLabel[$licenciaActiva['fin_tipo']] ?? 'Otro motivo' ?>
                            — desde <?= date('d/m/Y', strtotime($licenciaActiva['fin_fecha_desde'])) ?>
                            <?= $licenciaActiva['fin_fecha_hasta'] ? ' hasta ' . date('d/m/Y', strtotime($licenciaActiva['fin_fecha_hasta'])) : ' (sin fecha de fin)' ?>
                        </span>
                        <?php if (!empty($licenciaActiva['fin_motivo'])): ?>
                        <br><small class="text-muted ml-4"><?= htmlspecialchars($licenciaActiva['fin_motivo']) ?></small>
                        <?php endif; ?>
                    </div>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary js-finalizar-inactividad ml-2"
                            data-id="<?= (int)$licenciaActiva['fin_id'] ?>"
                            title="Marcar como finalizada hoy" style="white-space:nowrap;">
                        <i class="fas fa-play-circle mr-1"></i>Reactivar
                    </button>
                </div>
                <?php endif; ?>

                <!-- Sección: Inactividades / Licencias -->
                <div class="card card-outline border-secondary mb-2">
                    <div class="card-header py-2 d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-pause-circle mr-1 text-secondary"></i>Inactividades / Licencias
                            <?php if (!empty($inactividades)): ?>
                            <span class="badge badge-secondary ml-1"><?= count($inactividades) ?></span>
                            <?php endif; ?>
                        </h3>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-nueva-inactividad"
                                title="Registrar nueva inactividad">
                            <i class="fas fa-plus mr-1"></i>Nueva
                        </button>
                    </div>
                    <?php if (!empty($inactividades)): ?>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="small">Tipo</th>
                                    <th class="small">Desde</th>
                                    <th class="small">Hasta</th>
                                    <th class="small">Motivo</th>
                                    <th class="text-center small" style="width:70px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($inactividades as $in):
                                $inActiva = $in['fin_fecha_desde'] <= date('Y-m-d') && ($in['fin_fecha_hasta'] === null || $in['fin_fecha_hasta'] >= date('Y-m-d'));
                            ?>
                                <tr class="<?= $inActiva ? 'table-secondary' : '' ?>">
                                    <td class="small align-middle">
                                        <?php if ($inActiva): ?>
                                        <span class="badge badge-secondary"><i class="fas fa-pause-circle mr-1"></i><?= $tipoInacLabel[$in['fin_tipo']] ?? $in['fin_tipo'] ?></span>
                                        <?php else: ?>
                                        <span class="text-muted"><?= $tipoInacLabel[$in['fin_tipo']] ?? $in['fin_tipo'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small align-middle"><?= date('d/m/Y', strtotime($in['fin_fecha_desde'])) ?></td>
                                    <td class="small align-middle">
                                        <?= $in['fin_fecha_hasta'] ? date('d/m/Y', strtotime($in['fin_fecha_hasta'])) : '<span class="text-warning small">Indefinida</span>' ?>
                                    </td>
                                    <td class="small align-middle text-muted">
                                        <?= !empty($in['fin_motivo']) ? htmlspecialchars(mb_strimwidth($in['fin_motivo'], 0, 40, '…')) : '—' ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($inActiva): ?>
                                            <button type="button"
                                                    class="btn btn-outline-success js-finalizar-inactividad"
                                                    data-id="<?= (int)$in['fin_id'] ?>"
                                                    title="Finalizar hoy">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button type="button"
                                                    class="btn btn-outline-danger js-eliminar-inactividad"
                                                    data-id="<?= (int)$in['fin_id'] ?>"
                                                    title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="card-body py-2 text-muted small">
                        <i class="fas fa-info-circle mr-1"></i>Sin inactividades registradas.
                    </div>
                    <?php endif; ?>
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
                                <?php foreach ($rubrosSelector as $i => $r):
                                    $montoRubro = match($r['tipo_sugerido']) {
                                        'MENSUALIDAD' => (float)$sedeMontos['sed_monto_mensualidad'],
                                        'MATRICULA'   => (float)$sedeMontos['sed_monto_matricula'],
                                        default       => 0,
                                    };
                                ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary js-tipo-pago"
                                        data-tipo="<?= htmlspecialchars($r['tipo_sugerido']) ?>"
                                        data-concepto="<?= htmlspecialchars($r['rub_nombre']) ?>"
                                        data-rubro-id="<?= (int)$r['rub_id'] ?>"
                                        data-monto="<?= $montoRubro ?>"
                                        style="--tipo-color:<?= $rubroColores[$i % count($rubroColores)] ?>">
                                    <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($r['rub_nombre']) ?>
                                    <?php if ($montoRubro > 0): ?>
                                    <span class="badge badge-light ml-1">$<?= number_format($montoRubro, 2) ?></span>
                                    <?php endif; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <form id="formNuevoPago">
                            <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="alumno_id"   value="<?= $alumnoId ?>">
                            <input type="hidden" name="cliente_id"  value="<?= (int)($alumno['rep_cliente_id'] ?? 0) ?>">
                            <input type="hidden" name="grupo_id"    value="<?= (int)$grupoActualId ?>">
                            <input type="hidden" id="fpg_tipo"      name="tipo"     value="<?= htmlspecialchars($primerRubro['tipo_sugerido']) ?>">
                            <input type="hidden" id="fpg_rubro_id"  name="rubro_id" value="<?= (int)$primerRubro['rub_id'] ?>">
                            <?php
                            // Beca activa del alumno para JS (primer descuento activo)
                            $becaActiva = $becasAlumno[0] ?? null;
                            $becaJson   = $becaActiva ? json_encode(['tipo' => $becaActiva['fbe_tipo'], 'valor' => (float)$becaActiva['fbe_valor'], 'nombre' => $becaActiva['fbe_nombre']]) : 'null';
                            ?>
                            <input type="hidden" id="fpg_beca_json" value="<?= htmlspecialchars($becaJson) ?>">

                            <div class="form-group mb-2">
                                <label class="small">Concepto</label>
                                <input type="text" id="fpg_concepto" name="concepto"
                                       class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($primerRubro['rub_nombre']) ?>"
                                       placeholder="Descripción del pago" maxlength="200">
                            </div>

                            <div class="form-group mb-2">
                                <label class="small">Mes Correspondiente</label>
                                <input type="month" name="mes_correspondiente" class="form-control form-control-sm"
                                       value="<?= date('Y-m') ?>">
                            </div>

                            <!-- 1. Monto del Rubro (readonly, desde sede) -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">
                                    Monto ($)
                                    <span class="text-muted font-weight-normal" style="font-size:.75rem;">— valor de la sede</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-dollar-sign text-secondary"></i></span></div>
                                    <input type="number" id="fpg_monto" name="monto"
                                           class="form-control form-control-sm font-weight-bold"
                                           step="0.01" min="0" readonly
                                           style="background:#f8f9fa; color:#495057;" value="0.00">
                                </div>
                            </div>

                            <!-- 2. Beca (readonly, calculada automáticamente) -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">
                                    Beca ($)
                                    <?php if ($becaActiva): ?>
                                    <span class="badge badge-info ml-1" style="font-size:.7rem;"
                                          title="<?= htmlspecialchars($becaActiva['fbe_nombre']) ?>">
                                        <i class="fas fa-tag mr-1"></i><?= $becaActiva['fbe_valor'] ?><?= $becaActiva['fbe_tipo'] === 'PORCENTAJE' ? '%' : '$' ?> — <?= htmlspecialchars($becaActiva['fbe_nombre']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted font-weight-normal" style="font-size:.75rem;">— sin beca asignada</span>
                                    <?php endif; ?>
                                </label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text bg-info text-white"><i class="fas fa-tag"></i></span></div>
                                    <input type="number" id="fpg_beca_descuento" name="beca_descuento"
                                           class="form-control form-control-sm text-info font-weight-bold"
                                           step="0.01" min="0" readonly
                                           style="background:#f8f9fa;" value="0.00">
                                </div>
                            </div>

                            <!-- 3. Descuento adicional (EDITABLE por el usuario) -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">
                                    Descuento ($)
                                    <span class="text-muted font-weight-normal" style="font-size:.75rem;">— descuento adicional</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text bg-warning text-white"><i class="fas fa-percent"></i></span></div>
                                    <input type="number" id="fpg_descuento" name="descuento"
                                           class="form-control form-control-sm"
                                           step="0.01" min="0" value="0.00"
                                           placeholder="0.00">
                                </div>
                            </div>

                            <!-- 4. Total a Pagar (EDITABLE — permite pago parcial) -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-success">
                                    Total a Pagar ($) <span class="text-danger">*</span>
                                    <span class="text-muted font-weight-normal" style="font-size:.75rem;">— editable para pago parcial</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text bg-success text-white"><i class="fas fa-money-bill-wave"></i></span></div>
                                    <input type="number" id="fpg_total_pagar" name="total_pagado"
                                           class="form-control form-control-sm font-weight-bold"
                                           style="font-size:1.05rem; color:#155724;"
                                           step="0.01" min="0.01" required value="0.00">
                                </div>
                            </div>

                            <!-- 4. Saldo Pendiente (readonly, siempre visible) -->
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold" id="lbl_saldo_pendiente">
                                    <i class="fas fa-balance-scale mr-1"></i>Saldo Pendiente ($)
                                </label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend" id="icon_saldo"><span class="input-group-text bg-light"><i class="fas fa-check text-success"></i></span></div>
                                    <input type="text" id="fpg_saldo_pendiente"
                                           class="form-control form-control-sm font-weight-bold"
                                           readonly value="0.00">
                                </div>
                            </div>

                            <!-- Método de Pago + Referencia -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="small">Método de Pago <span class="text-danger">*</span></label>
                                        <select id="fpg_metodo_pago" name="metodo_pago" class="form-control form-control-sm" required>
                                            <option value="EFECTIVO">Efectivo</option>
                                            <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                            <option value="DEPOSITO">Depósito Bancario</option>
                                            <option value="TARJETA">Tarjeta de Débito/Crédito</option>
                                            <option value="CHEQUE">Cheque</option>
                                            <option value="PAYPHONE">PayPhone</option>
                                            <option value="OTRO">Otro Canal de Pago</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="small">Referencia / Nro. Comprobante</label>
                                        <input type="text" name="referencia" class="form-control form-control-sm"
                                               placeholder="Nro. transacción...">
                                    </div>
                                </div>
                            </div>

                            <!-- Imagen de comprobante (visible solo para métodos bancarios) -->
                            <div id="wrap_imagen_pago" class="form-group mb-2" style="display:none;">
                                <label class="small font-weight-bold">
                                    <i class="fas fa-camera mr-1 text-primary"></i>Imagen del Comprobante
                                    <span class="text-muted" style="font-weight:400;">(JPG, PNG, PDF — máx 5MB)</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="fpg_imagen_pago" name="imagen_pago"
                                           accept="image/jpeg,image/png,image/webp,application/pdf">
                                    <label class="custom-file-label" for="fpg_imagen_pago">Seleccionar archivo...</label>
                                </div>
                                <div id="preview_imagen" class="mt-1" style="display:none;">
                                    <img id="img_preview" src="" alt="Vista previa"
                                         class="img-thumbnail js-ver-imagen-upload"
                                         style="max-height:80px; cursor:pointer;"
                                         title="Clic para ampliar">
                                    <small class="text-muted ml-1"><i class="fas fa-search-plus"></i> Clic para ampliar</small>
                                </div>
                            </div>

                            <!-- hidden: recargo mora = 0 por defecto -->
                            <input type="hidden" name="recargo_mora" value="0">

                            <!-- 5. Estado (auto-calculado, visible) -->
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Estado del Pago</label>
                                <select id="fpg_estado_nuevo" name="estado" class="form-control form-control-sm font-weight-bold">
                                    <option value="PAGADO" selected>✔ Pagado (Cancelado)</option>
                                    <option value="PENDIENTE">⏳ Pendiente</option>
                                </select>
                                <small id="estado_hint" class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>El pago cubre el total del rubro
                                </small>
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
                                        <th class="align-middle">Fecha</th>
                                        <th class="align-middle">Concepto</th>
                                        <th class="align-middle">Mes</th>
                                        <th class="text-right align-middle">Total</th>
                                        <th class="text-center align-middle">Estado</th>
                                        <th class="text-center align-middle" width="190">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $p): ?>
                                    <tr id="fila-pago-<?= $p['fpg_pago_id'] ?>">
                                        <td class="small align-middle">
                                            <?= !empty($p['fpg_fecha']) ? date('d/m/Y', strtotime($p['fpg_fecha'])) : '—' ?>
                                        </td>
                                        <td class="small align-middle">
                                            <?= htmlspecialchars($p['fpg_concepto'] ?? $p['fpg_tipo'] ?? '') ?>
                                            <?php if (!empty($p['grupo_nombre'])): ?>
                                            <br><span class="text-muted"><?= htmlspecialchars($p['grupo_nombre']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small align-middle"><?= $p['fpg_mes_correspondiente'] ?? '—' ?></td>
                                        <td class="text-right small align-middle">
                                            <strong>$<?= number_format($p['fpg_total'] ?? 0, 2) ?></strong>
                                            <?php if (($p['fpg_beca_descuento'] ?? 0) > 0 || ($p['fpg_descuento'] ?? 0) > 0): ?>
                                            <br><small class="text-info">
                                                <i class="fas fa-tag"></i>
                                                -$<?= number_format(($p['fpg_beca_descuento'] ?? 0) + ($p['fpg_descuento'] ?? 0), 2) ?>
                                            </small>
                                            <?php endif; ?>
                                            <?php if (($p['fpg_saldo'] ?? 0) > 0): ?>
                                            <br><small class="text-warning font-weight-bold">
                                                <i class="fas fa-exclamation-circle"></i>
                                                Saldo: $<?= number_format($p['fpg_saldo'], 2) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php $est = $p['fpg_estado'] ?? 'PENDIENTE'; ?>
                                            <span class="badge badge-<?= $estadoClass[$est] ?? 'secondary' ?> badge-estado"
                                                  id="estado-<?= $p['fpg_pago_id'] ?>">
                                                <?= $est ?>
                                            </span>
                                        </td>
                                        <?php $tieneFact = !empty($p['fpg_factura_id']); ?>
                                        <?php $tieneComp = !empty($p['fcm_comprobante_id']); ?>
                                        <td class="text-center align-middle" style="white-space:nowrap;">
                                            <div class="btn-group btn-group-sm">
                                                <?php if (in_array($est, ['PENDIENTE','VENCIDO'])): ?>
                                                <button type="button" class="btn btn-success js-cobrar"
                                                        data-id="<?= $p['fpg_pago_id'] ?>"
                                                        title="Marcar como cobrado">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if ($est !== 'ANULADO'): ?>
                                                <button type="button"
                                                        class="btn <?= $tieneFact ? 'btn-outline-secondary' : 'btn-info' ?> js-editar-pago"
                                                        data-id="<?= $p['fpg_pago_id'] ?>"
                                                        data-pago="<?= htmlspecialchars(json_encode([
                                                            'fpg_pago_id'            => $p['fpg_pago_id'],
                                                            'fpg_tipo'               => $p['fpg_tipo'] ?? '',
                                                            'fpg_concepto'           => $p['fpg_concepto'] ?? '',
                                                            'fpg_mes_correspondiente'=> $p['fpg_mes_correspondiente'] ?? '',
                                                            'fpg_monto'              => $p['fpg_monto'] ?? 0,
                                                            'fpg_descuento'          => $p['fpg_descuento'] ?? 0,
                                                            'fpg_beca_descuento'     => $p['fpg_beca_descuento'] ?? 0,
                                                            'fpg_total'              => $p['fpg_total'] ?? 0,
                                                            'fpg_metodo_pago'        => $p['fpg_metodo_pago'] ?? 'EFECTIVO',
                                                            'fpg_referencia'         => $p['fpg_referencia'] ?? '',
                                                            'fpg_estado'             => $p['fpg_estado'] ?? '',
                                                            'fpg_notas'              => $p['fpg_notas'] ?? '',
                                                        ], JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP), ENT_QUOTES) ?>"
                                                        title="<?= $tieneFact ? 'Pago facturado — no se puede modificar' : 'Editar pago' ?>"
                                                        <?= $tieneFact ? 'disabled' : '' ?>>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn <?= $tieneFact ? 'btn-outline-secondary' : 'btn-warning' ?> js-anular"
                                                        data-id="<?= $p['fpg_pago_id'] ?>"
                                                        title="<?= $tieneFact ? 'Pago facturado — no se puede anular' : 'Anular pago' ?>"
                                                        <?= $tieneFact ? 'disabled' : '' ?>>
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if ($est === 'PAGADO'): ?>
                                                <?php if ($tieneComp): ?>
                                                <a href="<?= url('futbol', 'comprobante', 'imprimir') ?>&id=<?= (int)$p['fcm_comprobante_id'] ?>"
                                                   class="btn btn-outline-secondary"
                                                   title="Ver comprobante <?= htmlspecialchars($p['comprobante_numero'] ?? '') ?>">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-outline-primary js-enviar-comp"
                                                        data-id="<?= (int)$p['fcm_comprobante_id'] ?>"
                                                        data-numero="<?= htmlspecialchars($p['comprobante_numero'] ?? '') ?>"
                                                        title="Enviar comprobante por email<?= $p['fcm_enviado_email'] ? ' (ya enviado)' : '' ?>">
                                                    <i class="fas fa-envelope<?= $p['fcm_enviado_email'] ? '-open' : '' ?>"></i>
                                                </button>
                                                <?php else: ?>
                                                <button type="button"
                                                        class="btn btn-outline-secondary js-generar-comp"
                                                        data-id="<?= $p['fpg_pago_id'] ?>"
                                                        title="Generar comprobante de pago">
                                                    <i class="fas fa-receipt"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if (($p['fpg_saldo'] ?? 0) > 0 && $est !== 'ANULADO'): ?>
                                                <button type="button"
                                                        class="btn btn-warning js-registrar-abono"
                                                        data-pago-id="<?= $p['fpg_pago_id'] ?>"
                                                        data-saldo="<?= number_format($p['fpg_saldo'], 2, '.', '') ?>"
                                                        data-concepto="<?= htmlspecialchars($p['fpg_concepto'] ?? $p['fpg_tipo'] ?? '') ?>"
                                                        title="Registrar abono — Saldo pendiente: $<?= number_format($p['fpg_saldo'], 2) ?>">
                                                    <i class="fas fa-plus-circle"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if (!empty($imagenesPagos[$p['fpg_pago_id']])): ?>
                                                <button type="button"
                                                        class="btn btn-outline-info js-ver-comprobante-img"
                                                        data-arc-id="<?= (int)$imagenesPagos[$p['fpg_pago_id']] ?>"
                                                        title="Ver imagen del comprobante">
                                                    <i class="fas fa-image"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($tieneFact): ?>
                                            <small class="text-muted d-block" style="font-size:10px;margin-top:3px;">
                                                <i class="fas fa-file-invoice mr-1"></i>Facturado
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php foreach ($abonosPorPago[$p['fpg_pago_id']] ?? [] as $ab): ?>
                                    <tr class="table-light" style="border-left:3px solid #ffc107;">
                                        <td class="small text-muted" style="padding-left:24px!important;">
                                            <i class="fas fa-level-up-alt fa-flip-horizontal text-warning mr-1"></i>
                                            <?= !empty($ab['fab_fecha']) ? date('d/m/Y', strtotime($ab['fab_fecha'])) : '—' ?>
                                        </td>
                                        <td class="small text-muted">
                                            <em>Abono<?= !empty($ab['fab_metodo_pago']) ? ' — ' . htmlspecialchars($ab['fab_metodo_pago']) : '' ?></em>
                                            <?php if (!empty($ab['fab_referencia'])): ?>
                                            <span class="ml-1">(<?= htmlspecialchars($ab['fab_referencia']) ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted">—</td>
                                        <td class="text-right small text-success font-weight-bold">
                                            +$<?= number_format($ab['fab_monto'], 2) ?>
                                        </td>
                                        <td class="text-center"><span class="badge badge-success" style="font-size:.68rem;">ABONO</span></td>
                                        <td class="text-center">
                                            <?php if (!empty($ab['fcm_comprobante_id'])): ?>
                                            <a href="<?= url('futbol', 'comprobante', 'imprimir') ?>&id=<?= (int)$ab['fcm_comprobante_id'] ?>"
                                               class="btn btn-xs btn-outline-secondary" title="Ver recibo del abono" style="font-size:.7rem;padding:1px 5px;">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <?php else: ?>
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-secondary js-generar-comp"
                                                    data-id="<?= $p['fpg_pago_id'] ?>"
                                                    data-abono-id="<?= (int)$ab['fab_abono_id'] ?>"
                                                    title="Generar recibo de este abono"
                                                    style="font-size:.7rem;padding:1px 5px;">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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

<!-- Modal Nueva Inactividad -->
<div class="modal fade" id="modalNuevaInactividad" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formNuevaInactividad">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="accion"     value="crear">
                <input type="hidden" name="alumno_id"  value="<?= $alumnoId ?>">
                <div class="modal-header py-2 bg-secondary text-white">
                    <h5 class="modal-title"><i class="fas fa-pause-circle mr-2"></i>Registrar Inactividad / Licencia</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-control form-control-sm" required>
                            <option value="VACACIONES">Vacaciones</option>
                            <option value="VIAJE">Viaje</option>
                            <option value="ENFERMEDAD">Enfermedad</option>
                            <option value="ECONOMICO">Motivo económico</option>
                            <option value="OTRO" selected>Otro motivo</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Fecha Desde <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_desde" id="inac_fecha_desde"
                                       class="form-control form-control-sm" required
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">
                                    Fecha Hasta
                                    <span class="text-muted" style="font-weight:400;">(vacío = indefinida)</span>
                                </label>
                                <input type="date" name="fecha_hasta" id="inac_fecha_hasta"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Motivo / Detalle</label>
                        <textarea name="motivo" class="form-control form-control-sm" rows="2"
                                  placeholder="Descripción opcional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-secondary btn-sm" id="btnGuardarInactividad">
                        <i class="fas fa-save mr-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Imagen Comprobante -->
<div class="modal fade" id="modalImagenComprobante" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#2c3e50;color:white;">
                <h5 class="modal-title"><i class="fas fa-image mr-2"></i>Comprobante de Pago</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center p-2" id="contenedorImagenComp">
                <div id="loadingImagenComp" class="py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Cargando imagen...</p>
                </div>
                <img id="imgComprobanteModal" src="" alt="Comprobante de pago"
                     class="img-fluid rounded" style="display:none; max-height:70vh;">
                <div id="pdfComprobanteModal" style="display:none;">
                    <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                    <p class="text-muted">Este comprobante es un archivo PDF.</p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <a id="btnDescargarComprobante" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download mr-1"></i>Descargar
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Pago -->
<div class="modal fade" id="modalEditarPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditarPago" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="ep_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Pago</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- hidden: recargo mora no se edita en este flujo -->
                    <input type="hidden" name="recargo_mora" value="0">

                    <div class="row mb-2">
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Concepto</label>
                                <input type="text" id="ep_concepto_display" class="form-control form-control-sm" readonly
                                       style="background:#f8f9fa;">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Mes Correspondiente</label>
                                <input type="month" name="mes_correspondiente" id="ep_mes"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">

                    <!-- 1. Monto (readonly) -->
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">
                            Monto ($)
                            <span class="text-muted font-weight-normal" style="font-size:.75rem;">— valor del rubro</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-dollar-sign text-secondary"></i></span></div>
                            <input type="number" name="monto" id="ep_monto"
                                   class="form-control form-control-sm font-weight-bold"
                                   style="background:#f8f9fa; color:#495057;" step="0.01" min="0" readonly>
                        </div>
                    </div>

                    <!-- 2. Beca (readonly) -->
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">
                            Beca ($)
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text bg-info text-white"><i class="fas fa-tag"></i></span></div>
                            <input type="number" name="beca_descuento" id="ep_beca_descuento"
                                   class="form-control form-control-sm text-info font-weight-bold"
                                   style="background:#f8f9fa;" step="0.01" min="0" readonly value="0.00">
                        </div>
                    </div>

                    <!-- 3. Descuento adicional (EDITABLE) -->
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">
                            Descuento ($)
                            <span class="text-muted font-weight-normal" style="font-size:.75rem;">— descuento adicional</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text bg-warning text-white"><i class="fas fa-percent"></i></span></div>
                            <input type="number" name="descuento" id="ep_descuento"
                                   class="form-control form-control-sm"
                                   step="0.01" min="0" value="0.00" placeholder="0.00">
                        </div>
                    </div>

                    <!-- 4. Total a Pagar (editable) -->
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-success">
                            Total a Pagar ($) <span class="text-danger">*</span>
                            <span class="text-muted font-weight-normal" style="font-size:.75rem;">— editable para pago parcial</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text bg-success text-white"><i class="fas fa-money-bill-wave"></i></span></div>
                            <input type="number" name="total_pagado" id="ep_total_pagar"
                                   class="form-control form-control-sm font-weight-bold"
                                   style="font-size:1.05rem; color:#155724;"
                                   step="0.01" min="0.01" required value="0.00">
                        </div>
                    </div>

                    <!-- 4. Saldo Pendiente (readonly, siempre visible) -->
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold" id="ep_lbl_saldo">
                            <i class="fas fa-balance-scale mr-1"></i>Saldo Pendiente ($)
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend" id="ep_icon_saldo"><span class="input-group-text bg-success"><i class="fas fa-check text-white"></i></span></div>
                            <input type="text" id="ep_saldo_pendiente"
                                   class="form-control form-control-sm font-weight-bold"
                                   readonly value="0.00">
                        </div>
                    </div>

                    <hr class="my-2">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Método de Pago</label>
                                <select name="metodo_pago" id="ep_metodo_pago" class="form-control form-control-sm">
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                    <option value="DEPOSITO">Depósito Bancario</option>
                                    <option value="TARJETA">Tarjeta de Débito/Crédito</option>
                                    <option value="CHEQUE">Cheque</option>
                                    <option value="PAYPHONE">PayPhone</option>
                                    <option value="OTRO">Otro Canal de Pago</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Referencia</label>
                                <input type="text" name="referencia" id="ep_referencia"
                                       class="form-control form-control-sm" placeholder="Nro. transacción...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Estado del Pago</label>
                                <select name="estado" id="ep_estado" class="form-control form-control-sm font-weight-bold">
                                    <option value="PAGADO">✔ Pagado (Cancelado)</option>
                                    <option value="PENDIENTE">⏳ Pendiente</option>
                                    <option value="VENCIDO">⚠ Vencido</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Imagen de comprobante (visible solo para métodos bancarios) -->
                    <div id="ep_wrap_imagen_pago" class="form-group mt-2 mb-0" style="display:none;">
                        <label class="small font-weight-bold">
                            <i class="fas fa-camera mr-1 text-primary"></i>Imagen del Comprobante
                            <span class="text-muted" style="font-weight:400;">(JPG, PNG, PDF — máx 5MB)</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="ep_imagen_pago" name="imagen_pago"
                                   accept="image/jpeg,image/png,image/webp,application/pdf">
                            <label class="custom-file-label" for="ep_imagen_pago">Seleccionar archivo...</label>
                        </div>
                        <div id="ep_preview_imagen" class="mt-1" style="display:none;">
                            <img id="ep_img_preview" src="" alt="Vista previa"
                                 class="img-thumbnail js-ver-imagen-upload"
                                 style="max-height:70px; cursor:pointer;"
                                 title="Clic para ampliar">
                            <small class="text-muted ml-1"><i class="fas fa-search-plus"></i> Clic para ampliar</small>
                        </div>
                    </div>

                    <div class="form-group mb-0 mt-2">
                        <label class="small font-weight-bold">Notas</label>
                        <textarea name="notas" id="ep_notas" class="form-control form-control-sm" rows="2"
                                  placeholder="Observaciones opcionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info" id="btnGuardarEdicion">
                        <i class="fas fa-save mr-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Registrar Abono -->
<div class="modal fade" id="modalRegistrarAbono" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form id="formRegistrarAbono" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="pago_id"    id="ab_pago_id">
                <div class="modal-header py-2" style="background:#fd7e14;color:white;">
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Registrar Abono</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <!-- Info del concepto + saldo actual -->
                    <div class="alert alert-warning py-2 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small font-weight-bold" id="ab_concepto_label">—</span>
                            <span class="small">Saldo actual: <strong id="ab_saldo_actual_label" class="text-warning">$0.00</strong></span>
                        </div>
                    </div>

                    <!-- Monto del abono -->
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">
                            Monto del Abono ($) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text bg-warning text-white"><i class="fas fa-money-bill-wave"></i></span></div>
                            <input type="number" id="ab_monto" name="monto"
                                   class="form-control form-control-sm font-weight-bold"
                                   style="font-size:1.05rem;color:#7d4e00;"
                                   step="0.01" min="0.01" required value="">
                        </div>
                    </div>

                    <!-- Nuevo saldo calculado (readonly) -->
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold" id="ab_lbl_nuevo_saldo">
                            <i class="fas fa-balance-scale mr-1"></i>Nuevo Saldo Pendiente ($)
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend" id="ab_icon_nuevo_saldo">
                                <span class="input-group-text bg-secondary"><i class="fas fa-hourglass-half text-white"></i></span>
                            </div>
                            <input type="text" id="ab_nuevo_saldo"
                                   class="form-control form-control-sm font-weight-bold"
                                   readonly value="0.00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Método de Pago <span class="text-danger">*</span></label>
                                <select id="ab_metodo_pago" name="metodo_pago" class="form-control form-control-sm" required>
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                    <option value="DEPOSITO">Depósito Bancario</option>
                                    <option value="TARJETA">Tarjeta de Débito/Crédito</option>
                                    <option value="CHEQUE">Cheque</option>
                                    <option value="PAYPHONE">PayPhone</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Referencia / Comprobante</label>
                                <input type="text" name="referencia" id="ab_referencia"
                                       class="form-control form-control-sm" placeholder="Nro. transacción...">
                            </div>
                        </div>
                    </div>

                    <!-- Imagen comprobante del abono -->
                    <div id="ab_wrap_imagen" class="form-group mb-2" style="display:none;">
                        <label class="small font-weight-bold">
                            <i class="fas fa-camera mr-1 text-primary"></i>Imagen del Comprobante
                            <span class="text-muted" style="font-weight:400;">(JPG, PNG, PDF — máx 5MB)</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="ab_imagen_pago" name="imagen_pago"
                                   accept="image/jpeg,image/png,image/webp,application/pdf">
                            <label class="custom-file-label" for="ab_imagen_pago">Seleccionar archivo...</label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Notas</label>
                        <textarea name="notas" id="ab_notas" class="form-control form-control-sm" rows="2"
                                  placeholder="Observaciones opcionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm text-white" id="btnGuardarAbono">
                        <i class="fas fa-save mr-1"></i>Guardar Abono
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function () {
    var CSRF             = '<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>';
    var URL_CREAR        = '<?= url('futbol', 'pago', 'crear') ?>';
    var URL_COBRAR       = '<?= url('futbol', 'pago', 'cobrar') ?>';
    var URL_ANULAR       = '<?= url('futbol', 'pago', 'anular') ?>';
    var URL_EDITAR       = '<?= url('futbol', 'pago', 'editar') ?>';
    var URL_VER_COMP     = '<?= url('futbol', 'pago', 'verComprobante') ?>';
    var URL_ABONO        = '<?= url('futbol', 'pago', 'abono') ?>';
    var URL_INACTIVIDAD  = '<?= url('futbol', 'pago', 'inactividad') ?>';
    var URL_COMP_CREAR   = '<?= url('futbol', 'comprobante', 'crear') ?>';
    var URL_COMP_ENVIAR  = '<?= url('futbol', 'comprobante', 'enviar') ?>';
    var URL_COMP_VER     = '<?= url('futbol', 'comprobante', 'imprimir') ?>';
    var URL_COMP_HTML    = '<?= url('futbol', 'comprobante', 'reciboHtml') ?>';

    // --- Datos de beca del alumno ---
    var becaAlumno = (function() {
        try { return JSON.parse(document.getElementById('fpg_beca_json').value || 'null'); } catch(e) { return null; }
    })();

    // --- Cálculo de totales en el formulario de nuevo pago ---
    // -----------------------------------------------------------------------
    // Lógica del formulario Nuevo Pago
    // -----------------------------------------------------------------------
    var totalCalculado = 0; // monto - descuento/beca (calculado automáticamente)

    /** Calcula el descuento/beca para un monto dado */
    function calcularDescuentoBeca(monto) {
        if (!becaAlumno) return 0;
        return becaAlumno.tipo === 'PORCENTAJE'
            ? Math.round(monto * becaAlumno.valor / 100 * 100) / 100
            : becaAlumno.valor;
    }

    /**
     * Recalcula totalCalculado usando monto, beca Y descuento editable.
     * Pre-rellena "Total a Pagar" con el nuevo totalCalculado.
     */
    function actualizarDesdeRubro(monto) {
        var beca = calcularDescuentoBeca(monto);
        document.getElementById('fpg_beca_descuento').value = beca.toFixed(2);
        recalcularTotal();
    }

    /**
     * Recalcula totalCalculado = monto - beca - descuento y
     * actualiza "Total a Pagar" (sin pisar un valor ya editado por el usuario).
     * Llamado cuando cambia el descuento editable o se selecciona un nuevo rubro.
     */
    function recalcularTotal() {
        var monto     = parseFloat(document.getElementById('fpg_monto').value)          || 0;
        var beca      = parseFloat(document.getElementById('fpg_beca_descuento').value) || 0;
        var descuento = parseFloat(document.getElementById('fpg_descuento').value)       || 0;
        totalCalculado = Math.max(0, monto - beca - descuento);
        document.getElementById('fpg_total_pagar').value = totalCalculado.toFixed(2);
        actualizarSaldoEstado();
    }

    // Recalcular cuando el usuario cambia el descuento
    document.getElementById('fpg_descuento').addEventListener('input', recalcularTotal);

    /**
     * Recalcula saldo y estado cuando el usuario modifica "Total a Pagar"
     */
    function actualizarSaldoEstado() {
        var totalPagar = parseFloat(document.getElementById('fpg_total_pagar').value) || 0;
        var saldo = Math.max(0, totalCalculado - totalPagar);
        var haySaldo = saldo > 0.005;

        // Saldo Pendiente
        document.getElementById('fpg_saldo_pendiente').value = saldo.toFixed(2);

        // Color dinámico según saldo
        var iconSaldo  = document.getElementById('icon_saldo').querySelector('span');
        var inpSaldo   = document.getElementById('fpg_saldo_pendiente');
        var lblSaldo   = document.getElementById('lbl_saldo_pendiente');
        if (haySaldo) {
            iconSaldo.className  = 'input-group-text bg-warning';
            iconSaldo.innerHTML  = '<i class="fas fa-exclamation-triangle text-white"></i>';
            inpSaldo.style.color = '#856404';
            lblSaldo.className   = 'small font-weight-bold text-warning';
        } else {
            iconSaldo.className  = 'input-group-text bg-success';
            iconSaldo.innerHTML  = '<i class="fas fa-check text-white"></i>';
            inpSaldo.style.color = '#155724';
            lblSaldo.className   = 'small font-weight-bold text-success';
        }

        // Estado + hint
        var selectEstado = document.getElementById('fpg_estado_nuevo');
        var hint         = document.getElementById('estado_hint');
        if (haySaldo) {
            selectEstado.value   = 'PENDIENTE';
            hint.className       = 'text-warning';
            hint.innerHTML       = '<i class="fas fa-clock mr-1"></i>Pago parcial — quedará saldo pendiente de $' + saldo.toFixed(2);
        } else {
            selectEstado.value   = 'PAGADO';
            hint.className       = 'text-success';
            hint.innerHTML       = '<i class="fas fa-check-circle mr-1"></i>El pago cubre el total del rubro';
        }
    }

    // El campo "Total a Pagar" es editable → recalcular saldo/estado al cambiarlo
    document.getElementById('fpg_total_pagar').addEventListener('input', actualizarSaldoEstado);

    // --- Selector de rubro / tipo de pago ---
    var btns = document.querySelectorAll('.js-tipo-pago');

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            btns.forEach(function (b) { desactivarTipo(b); });
            activarTipo(btn);
            document.getElementById('fpg_tipo').value     = btn.dataset.tipo     || 'OTRO';
            document.getElementById('fpg_rubro_id').value = btn.dataset.rubroId  || '0';
            document.getElementById('fpg_concepto').value = btn.dataset.concepto || '';

            var monto = parseFloat(btn.dataset.monto || '0');
            document.getElementById('fpg_monto').value    = monto > 0 ? monto.toFixed(2) : '0.00';
            document.getElementById('fpg_descuento').value = '0.00'; // reset descuento al cambiar rubro
            actualizarDesdeRubro(monto);
        });
    });

    // Activar primer botón al cargar
    if (btns.length > 0) {
        activarTipo(btns[0]);
        var montoInicial = parseFloat(btns[0].dataset.monto || '0');
        document.getElementById('fpg_monto').value    = montoInicial > 0 ? montoInicial.toFixed(2) : '0.00';
        document.getElementById('fpg_descuento').value = '0.00';
        actualizarDesdeRubro(montoInicial);
    }

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

    // --- Mostrar/ocultar campo imagen según método de pago ---
    var METODOS_BANCARIOS = ['TRANSFERENCIA', 'DEPOSITO', 'TARJETA', 'CHEQUE', 'PAYPHONE', 'OTRO'];
    var selMetodo = document.getElementById('fpg_metodo_pago');
    function toggleImagenPago() {
        var bancario = METODOS_BANCARIOS.indexOf(selMetodo.value) !== -1;
        document.getElementById('wrap_imagen_pago').style.display = bancario ? '' : 'none';
    }
    selMetodo.addEventListener('change', toggleImagenPago);
    toggleImagenPago();

    // Preview de imagen seleccionada
    document.getElementById('fpg_imagen_pago').addEventListener('change', function() {
        var file = this.files[0];
        var label = this.nextElementSibling;
        label.textContent = file ? file.name : 'Seleccionar archivo...';
        var prev = document.getElementById('preview_imagen');
        if (file && file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('img_preview').src = ev.target.result;
                prev.style.display = '';
            };
            reader.readAsDataURL(file);
        } else {
            prev.style.display = 'none';
        }
    });

    // --- Thumbnail del formulario → abrir modal con imagen en tamaño completo ---
    document.getElementById('img_preview').addEventListener('click', function() {
        var src = this.src;
        if (!src) return;
        document.getElementById('loadingImagenComp').style.display = 'none';
        document.getElementById('pdfComprobanteModal').style.display = 'none';
        var img = document.getElementById('imgComprobanteModal');
        img.src = src;
        img.style.display = '';
        document.getElementById('btnDescargarComprobante').href = src;
        $('#modalImagenComprobante').modal('show');
    });

    // --- Botón "Ver imagen" del historial → cargar desde servidor ---
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-ver-comprobante-img');
        if (!btn) return;
        var arcId = btn.dataset.arcId;
        var url   = URL_VER_COMP + '&arc_id=' + arcId;

        // Resetear modal
        document.getElementById('imgComprobanteModal').style.display = 'none';
        document.getElementById('imgComprobanteModal').src = '';
        document.getElementById('pdfComprobanteModal').style.display = 'none';
        document.getElementById('loadingImagenComp').style.display = '';
        document.getElementById('btnDescargarComprobante').href = url;
        $('#modalImagenComprobante').modal('show');

        // Intentar cargar como imagen; si es PDF mostrar ícono
        var img = document.getElementById('imgComprobanteModal');
        img.onload = function() {
            document.getElementById('loadingImagenComp').style.display = 'none';
            img.style.display = '';
        };
        img.onerror = function() {
            document.getElementById('loadingImagenComp').style.display = 'none';
            document.getElementById('pdfComprobanteModal').style.display = '';
        };
        img.src = url;
    });


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

    // -----------------------------------------------------------------------
    // Lógica del modal Editar Pago
    // -----------------------------------------------------------------------
    var epTotalCalculado = 0; // monto - desc/beca del pago que se edita

    function epRecalcularTotal() {
        var monto     = parseFloat(document.getElementById('ep_monto').value)          || 0;
        var beca      = parseFloat(document.getElementById('ep_beca_descuento').value) || 0;
        var descuento = parseFloat(document.getElementById('ep_descuento').value)       || 0;
        epTotalCalculado = Math.max(0, monto - beca - descuento);
        document.getElementById('ep_total_pagar').value = epTotalCalculado.toFixed(2);
        epActualizarSaldoEstado();
    }

    document.getElementById('ep_descuento').addEventListener('input', epRecalcularTotal);

    // Mostrar/ocultar imagen en modal Editar según método de pago
    function epToggleImagenPago() {
        var val = document.getElementById('ep_metodo_pago').value;
        var show = METODOS_BANCARIOS.indexOf(val) !== -1;
        document.getElementById('ep_wrap_imagen_pago').style.display = show ? '' : 'none';
    }
    document.getElementById('ep_metodo_pago').addEventListener('change', epToggleImagenPago);

    // Preview de imagen en modal Editar
    document.getElementById('ep_imagen_pago').addEventListener('change', function() {
        var file = this.files[0];
        this.nextElementSibling.textContent = file ? file.name : 'Seleccionar archivo...';
        var prev = document.getElementById('ep_preview_imagen');
        if (file && file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('ep_img_preview').src = ev.target.result;
                prev.style.display = '';
            };
            reader.readAsDataURL(file);
        } else {
            prev.style.display = 'none';
        }
    });

    // Clic en preview → abrir modal imagen
    document.getElementById('ep_img_preview').addEventListener('click', function() {
        var src = this.src;
        if (!src) return;
        document.getElementById('loadingImagenComp').style.display = 'none';
        document.getElementById('pdfComprobanteModal').style.display = 'none';
        var img = document.getElementById('imgComprobanteModal');
        img.src = src;
        img.style.display = '';
        document.getElementById('btnDescargarComprobante').href = src;
        $('#modalImagenComprobante').modal('show');
    });

    function epActualizarSaldoEstado() {
        var totalPagar = parseFloat(document.getElementById('ep_total_pagar').value) || 0;
        var saldo      = Math.max(0, epTotalCalculado - totalPagar);
        var haySaldo   = saldo > 0.005;

        document.getElementById('ep_saldo_pendiente').value = saldo.toFixed(2);

        var iconEl = document.getElementById('ep_icon_saldo').querySelector('span');
        var inpEl  = document.getElementById('ep_saldo_pendiente');
        var lblEl  = document.getElementById('ep_lbl_saldo');
        if (haySaldo) {
            iconEl.className  = 'input-group-text bg-warning';
            iconEl.innerHTML  = '<i class="fas fa-exclamation-triangle text-white"></i>';
            inpEl.style.color = '#856404';
            lblEl.className   = 'small font-weight-bold text-warning';
            document.getElementById('ep_estado').value = 'PENDIENTE';
        } else {
            iconEl.className  = 'input-group-text bg-success';
            iconEl.innerHTML  = '<i class="fas fa-check text-white"></i>';
            inpEl.style.color = '#155724';
            lblEl.className   = 'small font-weight-bold text-success';
            document.getElementById('ep_estado').value = 'PAGADO';
        }
    }

    document.getElementById('ep_total_pagar').addEventListener('input', epActualizarSaldoEstado);

    // --- Editar pago: abrir modal ---
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-editar-pago');
        if (!btn || btn.disabled) return;
        var p = JSON.parse(btn.dataset.pago);

        var monto     = parseFloat(p.fpg_monto          || 0);
        var beca      = parseFloat(p.fpg_beca_descuento || 0);
        var descuento = parseFloat(p.fpg_descuento      || 0);
        epTotalCalculado = Math.max(0, monto - beca - descuento);

        document.getElementById('ep_id').value               = p.fpg_pago_id;
        document.getElementById('ep_concepto_display').value = p.fpg_concepto || p.fpg_tipo || '';
        document.getElementById('ep_mes').value              = p.fpg_mes_correspondiente || '';
        document.getElementById('ep_monto').value            = monto.toFixed(2);
        document.getElementById('ep_beca_descuento').value   = beca.toFixed(2);
        document.getElementById('ep_descuento').value        = descuento.toFixed(2);
        document.getElementById('ep_total_pagar').value      = parseFloat(p.fpg_total || epTotalCalculado).toFixed(2);
        document.getElementById('ep_metodo_pago').value      = p.fpg_metodo_pago || 'EFECTIVO';
        document.getElementById('ep_referencia').value       = p.fpg_referencia  || '';
        document.getElementById('ep_estado').value           = p.fpg_estado      || 'PAGADO';
        document.getElementById('ep_notas').value            = p.fpg_notas       || '';
        // Reset imagen
        document.getElementById('ep_imagen_pago').value = '';
        document.getElementById('ep_imagen_pago').nextElementSibling.textContent = 'Seleccionar archivo...';
        document.getElementById('ep_preview_imagen').style.display = 'none';
        epToggleImagenPago();
        epActualizarSaldoEstado();
        $('#modalEditarPago').modal('show');
    });

    // --- Editar pago: guardar ---
    document.getElementById('formEditarPago').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnGuardarEdicion');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...';
        var fd = new FormData(this);
        fetch(URL_EDITAR, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    $('#modalEditarPago').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Pago actualizado', timer: 1400, showConfirmButton: false })
                            .then(function () { location.reload(); });
                    } else { location.reload(); }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                    else alert('Error: ' + res.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar Cambios';
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar Cambios';
            });
    });

    // --- Generar comprobante ---
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-generar-comp');
        if (!btn) return;
        var pagoId  = btn.dataset.id;
        var abonoId = btn.dataset.abonoId || null;
        var titulo  = abonoId ? 'Generar recibo de abono' : 'Generar recibo de pago';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: titulo,
                text: '¿Generar recibo para este registro?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                confirmButtonText: 'Sí, generar',
                cancelButtonText: 'Cancelar'
            }).then(function (r) { if (r.isConfirmed) generarComprobante(pagoId, btn, abonoId); });
        } else {
            if (confirm('¿Generar recibo?')) generarComprobante(pagoId, btn, abonoId);
        }
    });

    function generarComprobante(pagoId, btn, abonoId) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        var fd = new FormData();
        fd.append('csrf_token', CSRF);
        fd.append('pago_id', pagoId);
        fd.append('tipo', 'RECIBO');
        if (abonoId) fd.append('abono_id', abonoId);
        fetch(URL_COMP_CREAR, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: res.ya_existia ? 'Recibo ya emitido' : '¡Recibo generado!',
                            text: res.message,
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-receipt mr-1"></i> Ver Recibo',
                            cancelButtonText: 'Cerrar'
                        }).then(function (r) {
                            if (r.isConfirmed && res.comprobante_id) {
                                window.location.href = URL_COMP_VER + '&id=' + res.comprobante_id;
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        if (res.comprobante_id && confirm('Recibo generado. ¿Ver recibo?')) {
                            window.location.href = URL_COMP_VER + '&id=' + res.comprobante_id;
                        } else { location.reload(); }
                    }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-receipt"></i>';
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-receipt"></i>';
            });
    }

    // --- Enviar comprobante por email ---
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-enviar-comp');
        if (!btn) return;
        var compId  = btn.dataset.id;
        var numero  = btn.dataset.numero || '';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Enviar comprobante',
                html: 'Se enviará el comprobante <strong>' + numero + '</strong> por email al representante.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: '<i class="fas fa-envelope mr-1"></i> Enviar',
                cancelButtonText: 'Cancelar'
            }).then(function (r) { if (r.isConfirmed) enviarComprobante(compId, btn); });
        } else {
            if (confirm('¿Enviar comprobante por email?')) enviarComprobante(compId, btn);
        }
    });

    function enviarComprobante(compId, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // 1) Obtener HTML del recibo desde el servidor
        fetch(URL_COMP_HTML + '&id=' + compId)
            .then(function (r) {
                if (!r.ok) throw new Error('No se pudo obtener el recibo');
                return r.text();
            })
            .then(function (html) {
                // 2) Renderizar en contenedor oculto para html2pdf
                var wrap = document.createElement('div');
                wrap.style.cssText = 'position:fixed;left:-9999px;top:0;width:680px;';
                wrap.innerHTML = html;
                document.body.appendChild(wrap);

                var opt = {
                    margin: [5, 5, 5, 5],
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                // 3) Generar PDF blob
                return html2pdf().set(opt).from(wrap).outputPdf('blob').then(function (pdfBlob) {
                    document.body.removeChild(wrap);
                    return pdfBlob;
                });
            })
            .then(function (pdfBlob) {
                // 4) Enviar email con PDF adjunto
                var fd = new FormData();
                fd.append('csrf_token', CSRF);
                fd.append('id', compId);
                fd.append('pdf_file', pdfBlob, 'Recibo_' + compId + '.pdf');
                return fetch(URL_COMP_ENVIAR, { method: 'POST', body: fd });
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: '¡Enviado!', text: res.message, timer: 2000, showConfirmButton: false })
                            .then(function () { location.reload(); });
                    } else { location.reload(); }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                    else alert('Error: ' + res.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-envelope"></i>';
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-envelope"></i>';
            });
    }

    // -----------------------------------------------------------------------
    // Lógica de Inactividades
    // -----------------------------------------------------------------------

    // Abrir modal nueva inactividad
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.js-nueva-inactividad')) return;
        document.getElementById('inac_fecha_desde').value = new Date().toISOString().slice(0, 10);
        document.getElementById('inac_fecha_hasta').value = '';
        $('#modalNuevaInactividad').modal('show');
    });

    // Guardar inactividad
    document.getElementById('formNuevaInactividad').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('btnGuardarInactividad');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...';
        var fd = new FormData(this);
        fetch(URL_INACTIVIDAD, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    $('#modalNuevaInactividad').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false })
                            .then(function() { location.reload(); });
                    } else { location.reload(); }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                    else alert(res.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar';
                }
            })
            .catch(function() {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar';
            });
    });

    // Finalizar inactividad
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-finalizar-inactividad');
        if (!btn) return;
        var id = btn.dataset.id;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Reactivar alumno?',
                text: 'Se marcará la inactividad como finalizada hoy.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: '<i class="fas fa-play-circle mr-1"></i>Sí, reactivar',
                cancelButtonText: 'Cancelar'
            }).then(function(r) { if (r.isConfirmed) finalizarInactividad(id); });
        } else {
            if (confirm('¿Reactivar alumno?')) finalizarInactividad(id);
        }
    });

    function finalizarInactividad(id) {
        var fd = new FormData();
        fd.append('csrf_token', CSRF);
        fd.append('accion', 'finalizar');
        fd.append('id', id);
        fetch(URL_INACTIVIDAD, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false })
                            .then(function() { location.reload(); });
                    } else { location.reload(); }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                }
            });
    }

    // Eliminar inactividad
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-eliminar-inactividad');
        if (!btn) return;
        var id = btn.dataset.id;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar este registro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(r) { if (r.isConfirmed) eliminarInactividad(id); });
        } else {
            if (confirm('¿Eliminar?')) eliminarInactividad(id);
        }
    });

    function eliminarInactividad(id) {
        var fd = new FormData();
        fd.append('csrf_token', CSRF);
        fd.append('accion', 'eliminar');
        fd.append('id', id);
        fetch(URL_INACTIVIDAD, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false })
                            .then(function() { location.reload(); });
                    } else { location.reload(); }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                }
            });
    }

    // -----------------------------------------------------------------------
    // Lógica del modal Registrar Abono
    // -----------------------------------------------------------------------
    var abSaldoActual = 0;

    // Mostrar/ocultar imagen según método
    var METODOS_BANCARIOS_AB = ['TRANSFERENCIA', 'DEPOSITO', 'TARJETA', 'CHEQUE', 'PAYPHONE', 'OTRO'];
    document.getElementById('ab_metodo_pago').addEventListener('change', function() {
        document.getElementById('ab_wrap_imagen').style.display =
            METODOS_BANCARIOS_AB.indexOf(this.value) !== -1 ? '' : 'none';
    });

    // Custom-file label
    document.getElementById('ab_imagen_pago').addEventListener('change', function() {
        this.nextElementSibling.textContent = this.files[0] ? this.files[0].name : 'Seleccionar archivo...';
    });

    // Calcular nuevo saldo al escribir el monto
    document.getElementById('ab_monto').addEventListener('input', function() {
        var monto   = parseFloat(this.value) || 0;
        var nuevo   = Math.max(0, abSaldoActual - monto);
        var excede  = monto > abSaldoActual + 0.005;
        document.getElementById('ab_nuevo_saldo').value = nuevo.toFixed(2);

        var iconEl = document.getElementById('ab_icon_nuevo_saldo').querySelector('span');
        var inpEl  = document.getElementById('ab_nuevo_saldo');
        var lblEl  = document.getElementById('ab_lbl_nuevo_saldo');
        if (excede) {
            iconEl.className  = 'input-group-text bg-danger';
            iconEl.innerHTML  = '<i class="fas fa-times text-white"></i>';
            inpEl.style.color = '#721c24';
            lblEl.className   = 'small font-weight-bold text-danger';
        } else if (nuevo < 0.005) {
            iconEl.className  = 'input-group-text bg-success';
            iconEl.innerHTML  = '<i class="fas fa-check text-white"></i>';
            inpEl.style.color = '#155724';
            lblEl.className   = 'small font-weight-bold text-success';
        } else {
            iconEl.className  = 'input-group-text bg-warning';
            iconEl.innerHTML  = '<i class="fas fa-exclamation-triangle text-white"></i>';
            inpEl.style.color = '#856404';
            lblEl.className   = 'small font-weight-bold text-warning';
        }
    });

    // Abrir modal al clic en botón "+" Abono
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-registrar-abono');
        if (!btn) return;
        abSaldoActual = parseFloat(btn.dataset.saldo) || 0;

        document.getElementById('ab_pago_id').value          = btn.dataset.pagoId;
        document.getElementById('ab_concepto_label').textContent = btn.dataset.concepto || 'Pago #' + btn.dataset.pagoId;
        document.getElementById('ab_saldo_actual_label').textContent = '$' + abSaldoActual.toFixed(2);
        document.getElementById('ab_monto').value             = abSaldoActual.toFixed(2);
        document.getElementById('ab_monto').max               = abSaldoActual.toFixed(2);
        document.getElementById('ab_nuevo_saldo').value       = '0.00';
        document.getElementById('ab_referencia').value        = '';
        document.getElementById('ab_notas').value             = '';
        document.getElementById('ab_metodo_pago').value       = 'EFECTIVO';
        document.getElementById('ab_wrap_imagen').style.display = 'none';
        document.getElementById('ab_imagen_pago').value       = '';
        document.getElementById('ab_imagen_pago').nextElementSibling.textContent = 'Seleccionar archivo...';

        // Trigger cálculo inicial
        document.getElementById('ab_monto').dispatchEvent(new Event('input'));
        $('#modalRegistrarAbono').modal('show');
    });

    // Enviar abono
    document.getElementById('formRegistrarAbono').addEventListener('submit', function(e) {
        e.preventDefault();
        var monto = parseFloat(document.getElementById('ab_monto').value) || 0;
        if (monto <= 0 || monto > abSaldoActual + 0.005) {
            if (typeof Swal !== 'undefined') Swal.fire('Error', 'Ingrese un monto válido (máx $' + abSaldoActual.toFixed(2) + ')', 'error');
            return;
        }
        var btn = document.getElementById('btnGuardarAbono');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...';

        var fd = new FormData(this);
        fetch(URL_ABONO, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    $('#modalRegistrarAbono').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Abono registrado!',
                            text: res.message,
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-receipt mr-1"></i> Generar Recibo',
                            cancelButtonText: 'Cerrar'
                        }).then(function(r) {
                            if (r.isConfirmed && res.abono_id) {
                                var pagoId = document.getElementById('ab_pago_id').value;
                                var fakBtn = { disabled: false, innerHTML: '' };
                                generarComprobante(pagoId, fakBtn, res.abono_id);
                            } else { location.reload(); }
                        });
                    } else { location.reload(); }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', res.message, 'error');
                    else alert('Error: ' + res.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar Abono';
                }
            })
            .catch(function() {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar Abono';
            });
    });

    // DataTable historial
    if (typeof $ !== 'undefined' && $('#tablaHistorial').length && $('#tablaHistorial tbody tr').length > 1) {
        $('#tablaHistorial').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 10,
            ordering: false,
            responsive: true,
            columnDefs: [{ orderable: false, targets: '_all' }]
        });
    }
}());
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.2/html2pdf.bundle.min.js" nonce="<?= cspNonce() ?>"></script>
<?php $scripts = ob_get_clean(); ?>
