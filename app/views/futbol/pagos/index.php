<?php
/**
 * Vista de Pagos - Módulo Fútbol
 * @vars $pagos, $alumnos, $grupos, $periodos, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$moduloColor = '#22C55E';
$moduloIcon = 'fas fa-futbol';

// Calcular resumen
$totalCobrado = 0;
$totalPendiente = 0;
$totalMora = 0;
if (!empty($pagos)) {
    foreach ($pagos as $p) {
        if (($p['fpg_estado'] ?? '') === 'PAGADO') {
            $totalCobrado += floatval($p['fpg_total'] ?? 0);
        }
        if (in_array($p['fpg_estado'] ?? '', ['PENDIENTE', 'PARCIAL', 'VENCIDO'])) {
            $totalPendiente += floatval($p['fpg_total'] ?? 0);
        }
        $totalMora += floatval($p['fpg_recargo_mora'] ?? 0);
    }
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcon ?>" style="color: <?= $moduloColor ?>"></i>
                    Gestión de Pagos
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Pagos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$<?= number_format($totalCobrado, 2) ?></h3>
                        <p>Total Cobrado</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>$<?= number_format($totalPendiente, 2) ?></h3>
                        <p>Pendiente</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$<?= number_format($totalMora, 2) ?></h3>
                        <p>En Mora</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>

        <!-- Filtro y botón -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-control" id="filtroSede" onchange="filtrarPorSede(this.value)">
                    <option value="">Todas las sedes</option>
                    <?php if (!empty($sedes)): ?>
                        <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['sed_sede_id'] ?>" <?= (isset($sede_activa) && $sede_activa == $sede['sed_sede_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['sed_nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-9 text-right">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalPago" onclick="limpiarFormulario()">
                    <i class="fas fa-plus"></i> Nuevo Pago
                </button>
            </div>
        </div>

        <!-- Tabla de Pagos -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Listado de Pagos</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($pagos)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tablaPagos">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>Alumno</th>
                                    <th>Grupo</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Mes</th>
                                    <th>Estado</th>
                                    <th>Método Pago</th>
                                    <th>Fecha Pago</th>
                                    <th>Recargo</th>
                                    <th width="160">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $i => $pago): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars(($pago['alu_nombres'] ?? '') . ' ' . ($pago['alu_apellidos'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($pago['grupo_nombre'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $tipoIcons = [
                                                'MENSUALIDAD' => 'fas fa-calendar-alt',
                                                'INSCRIPCION' => 'fas fa-user-plus',
                                                'UNIFORME' => 'fas fa-tshirt',
                                                'MATERIAL' => 'fas fa-futbol',
                                                'TORNEO' => 'fas fa-trophy',
                                                'OTRO' => 'fas fa-ellipsis-h'
                                            ];
                                            $tipo = $pago['fpg_tipo'] ?? 'OTRO';
                                            $icon = $tipoIcons[$tipo] ?? 'fas fa-tag';
                                            ?>
                                            <i class="<?= $icon ?>"></i> <?= $tipo ?>
                                        </td>
                                        <td>$<?= number_format($pago['fpg_monto'] ?? 0, 2) ?></td>
                                        <td><?= $pago['fpg_mes_correspondiente'] ?? '' ?></td>
                                        <td>
                                            <?php
                                            $estadoClass = [
                                                'PENDIENTE' => 'warning',
                                                'PAGADO' => 'success',
                                                'PARCIAL' => 'info',
                                                'VENCIDO' => 'danger',
                                                'ANULADO' => 'secondary'
                                            ];
                                            $estado = $pago['fpg_estado'] ?? 'PENDIENTE';
                                            $clase = $estadoClass[$estado] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $clase ?>"><?= $estado ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($pago['fpg_metodo_pago'] ?? '') ?></td>
                                        <td><?= isset($pago['fpg_fecha']) ? date('d/m/Y', strtotime($pago['fpg_fecha'])) : '' ?></td>
                                        <td>
                                            <?php if (($pago['fpg_recargo_mora'] ?? 0) > 0): ?>
                                                <span class="text-danger font-weight-bold">$<?= number_format($pago['fpg_recargo_mora'], 2) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">$0.00</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info" title="Editar"
                                                    onclick='editarPago(<?= json_encode($pago) ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-success" title="Comprobante"
                                                    onclick="generarComprobante(<?= $pago['fpg_pago_id'] ?? 0 ?>)">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </button>
                                                <?php if (($pago['fpg_estado'] ?? '') !== 'ANULADO'): ?>
                                                    <button type="button" class="btn btn-warning" title="Anular"
                                                        onclick="anularPago(<?= $pago['fpg_pago_id'] ?? 0 ?>)">
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
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-money-bill-wave fa-3x opacity-50 text-muted mb-3"></i>
                        <p class="text-muted">No hay pagos registrados.</p>
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalPago" onclick="limpiarFormulario()">
                            <i class="fas fa-plus"></i> Registrar primer pago
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Pago -->
<div class="modal fade" id="modalPago" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: <?= $moduloColor ?>; color: #fff;">
                <h5 class="modal-title" id="modalPagoTitle">
                    <i class="<?= $moduloIcon ?>"></i> Nuevo Pago
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPago" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="hidden" name="id" id="fpg_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fpg_alumno_id">Alumno <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="fpg_alumno_id" name="alumno_id" required style="width: 100%;">
                                    <option value="">Seleccionar alumno...</option>
                                    <?php if (!empty($alumnos)): ?>
                                        <?php foreach ($alumnos as $alumno): ?>
                                            <option value="<?= $alumno['alu_alumno_id'] ?>"><?= htmlspecialchars(($alumno['alu_nombres'] ?? '') . ' ' . ($alumno['alu_apellidos'] ?? '')) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fpg_grupo_id">Grupo <span class="text-danger">*</span></label>
                                <select class="form-control" id="fpg_grupo_id" name="grupo_id" required>
                                    <option value="">Seleccionar grupo</option>
                                    <?php if (!empty($grupos)): ?>
                                        <?php foreach ($grupos as $grupo): ?>
                                            <option value="<?= $grupo['fgr_grupo_id'] ?>"><?= htmlspecialchars($grupo['fgr_nombre']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fpg_tipo">Tipo <span class="text-danger">*</span></label>
                                <select class="form-control" id="fpg_tipo" name="tipo" required>
                                    <option value="MENSUALIDAD">Mensualidad</option>
                                    <option value="INSCRIPCION">Inscripción</option>
                                    <option value="UNIFORME">Uniforme</option>
                                    <option value="MATERIAL">Material</option>
                                    <option value="TORNEO">Torneo</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fpg_monto">Monto ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="fpg_monto" name="monto" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fpg_mes_correspondiente">Mes Correspondiente</label>
                                <input type="month" class="form-control" id="fpg_mes_correspondiente" name="mes_correspondiente">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fpg_descuento">Descuento ($)</label>
                                <input type="number" class="form-control" id="fpg_descuento" name="descuento" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fpg_recargo_mora">Recargo mora ($)</label>
                                <input type="number" class="form-control" id="fpg_recargo_mora" name="recargo_mora" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fpg_metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-control" id="fpg_metodo_pago" name="metodo_pago" required>
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="TRANSFERENCIA">Transferencia</option>
                                    <option value="TARJETA">Tarjeta</option>
                                    <option value="DEPOSITO">Depósito</option>
                                    <option value="CHEQUE">Cheque</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fpg_referencia">Referencia / Nro. Transacción</label>
                                <input type="text" class="form-control" id="fpg_referencia" name="referencia" placeholder="Nro. de comprobante">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="fpg_notas">Notas</label>
                        <textarea class="form-control" id="fpg_notas" name="notas" rows="2" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    if ($('#tablaPagos tbody tr').length > 0 && !$('#tablaPagos tbody .text-center').length) {
        $('#tablaPagos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[8, 'desc']],
            responsive: true
        });
    }

    // Select2
    $('#fpg_alumno_id').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccionar alumno...',
        allowClear: true,
        dropdownParent: $('#modalPago')
    });

    // Submit
    $('#formPago').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var id = $('#fpg_id').val();
        var urlAction = id
            ? '<?= url("futbol", "pago", "editar") ?>'
            : '<?= url("futbol", "pago", "crear") ?>';

        $.post(urlAction, formData, function(response) {
            if (response.success) {
                Swal.fire('¡Éxito!', response.message || 'Pago registrado correctamente.', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'No se pudo guardar el pago.', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        });
    });
});

function limpiarFormulario() {
    $('#formPago')[0].reset();
    $('#fpg_id').val('');
    $('#fpg_alumno_id').val(null).trigger('change');
    $('#fpg_tipo').val('MENSUALIDAD');
    $('#fpg_metodo_pago').val('EFECTIVO');
    $('#fpg_descuento').val(0);
    $('#fpg_recargo_mora').val(0);
    $('#modalPagoTitle').html('<i class="<?= $moduloIcon ?>"></i> Nuevo Pago');
}

function editarPago(obj) {
    limpiarFormulario();
    $('#fpg_id').val(obj.fpg_pago_id);
    $('#fpg_alumno_id').val(obj.fpg_alumno_id || '').trigger('change');
    $('#fpg_grupo_id').val(obj.fpg_grupo_id || '');
    $('#fpg_tipo').val(obj.fpg_tipo || 'MENSUALIDAD');
    $('#fpg_monto').val(obj.fpg_monto || '');
    $('#fpg_mes_correspondiente').val(obj.fpg_mes_correspondiente || '');
    $('#fpg_descuento').val(obj.fpg_descuento || 0);
    $('#fpg_recargo_mora').val(obj.fpg_recargo_mora || 0);
    $('#fpg_metodo_pago').val(obj.fpg_metodo_pago || 'EFECTIVO');
    $('#fpg_referencia').val(obj.fpg_referencia || '');
    $('#fpg_notas').val(obj.fpg_notas || '');
    $('#modalPagoTitle').html('<i class="<?= $moduloIcon ?>"></i> Editar Pago');
    $('#modalPago').modal('show');
}

function anularPago(id) {
    Swal.fire({
        title: '¿Anular pago?',
        text: 'El pago será marcado como ANULADO.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url("futbol", "pago", "anular") ?>&id=' + id;
        }
    });
}

function generarComprobante(id) {
    Swal.fire({
        title: '¿Generar comprobante?',
        text: 'Se generará un recibo de pago.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar'
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post('<?= url("futbol", "comprobante", "crear") ?>', {
                csrf_token: '<?= $csrf_token ?? "" ?>',
                pago_id: id,
                tipo: 'RECIBO'
            }, function(res) {
                if (res.success) {
                    Swal.fire('¡Generado!', res.message, 'success');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión.', 'error');
            });
        }
    });
}

function filtrarPorSede(sedeId) {
    var url = '<?= url("futbol", "pago", "index") ?>';
    if (sedeId) url += '&sede=' + sedeId;
    window.location.href = url;
}
</script>
<?php $scripts = ob_get_clean(); ?>
