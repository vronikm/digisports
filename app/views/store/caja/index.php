<?php
/**
 * DigiSports Store - Panel de Caja
 * Apertura/cierre de turnos, estado actual
 */
$cajas       = $cajas ?? [];
$turnoActual = $turnoActual ?? null;
$ultimosTurnos = $ultimosTurnos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cash-register mr-2" style="color:<?= $moduloColor ?>"></i>Gestión de Caja</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('store', 'caja', 'historial') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-history mr-1"></i> Historial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if ($turnoActual): ?>
        <!-- ═══ TURNO ABIERTO ═══ -->
        <div class="alert alert-success d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x mr-3"></i>
            <div>
                <strong>Turno Activo</strong> — Caja: <?= htmlspecialchars($turnoActual['caj_nombre'] ?? 'Principal') ?>
                | Abierto: <?= date('d/m/Y H:i', strtotime($turnoActual['tur_fecha_apertura'])) ?>
                | Monto Apertura: <strong>$<?= number_format($turnoActual['tur_monto_apertura'] ?? 0, 2) ?></strong>
            </div>
        </div>

        <div class="row">
            <!-- Acciones del turno -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Acciones</h3></div>
                    <div class="card-body">
                        <a href="<?= url('store', 'pos', 'index') ?>" class="btn btn-block mb-2" style="background:<?= $moduloColor ?>;color:white;">
                            <i class="fas fa-cash-register mr-2"></i> Ir al Punto de Venta
                        </a>
                        <button class="btn btn-outline-success btn-block mb-2" onclick="abrirMovimiento('ENTRADA')">
                            <i class="fas fa-arrow-down mr-2"></i> Entrada de Efectivo
                        </button>
                        <button class="btn btn-outline-warning btn-block mb-2" onclick="abrirMovimiento('SALIDA')">
                            <i class="fas fa-arrow-up mr-2"></i> Salida de Efectivo
                        </button>
                        <a href="<?= url('store', 'caja', 'arqueo', ['turno_id' => $turnoActual['tur_turno_id']]) ?>" class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-calculator mr-2"></i> Realizar Arqueo
                        </a>
                        <hr>
                        <button class="btn btn-danger btn-block" onclick="confirmarCierreTurno()">
                            <i class="fas fa-lock mr-2"></i> Cerrar Turno
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen del turno -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Resumen del Turno</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="h4 mb-0 text-success">$<?= number_format($turnoActual['tur_total_ventas'] ?? 0, 2) ?></div>
                                <small class="text-muted">Total Ventas</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="h4 mb-0 text-info"><?= intval($turnoActual['tur_num_ventas'] ?? 0) ?></div>
                                <small class="text-muted">Transacciones</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="h4 mb-0 text-primary">$<?= number_format($turnoActual['tur_total_efectivo'] ?? 0, 2) ?></div>
                                <small class="text-muted">Efectivo</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="h4 mb-0 text-warning">$<?= number_format($turnoActual['tur_total_tarjeta'] ?? 0, 2) ?></div>
                                <small class="text-muted">Tarjeta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ═══ SIN TURNO ABIERTO ═══ -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-cash-register fa-4x mb-3" style="color:<?= $moduloColor ?>; opacity:0.5;"></i>
                        <h4>No hay turno abierto</h4>
                        <p class="text-muted">Debe abrir un turno de caja para poder realizar ventas</p>
                        <form method="POST" action="<?= url('store', 'caja', 'abrirTurno') ?>" class="mt-4">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <div class="form-group">
                                <label>Seleccionar Caja</label>
                                <select name="caja_id" class="form-control mx-auto" style="max-width:300px;" required>
                                    <?php foreach ($cajas as $cj): ?>
                                    <option value="<?= $cj['caj_caja_id'] ?>"><?= htmlspecialchars($cj['caj_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Monto de Apertura ($)</label>
                                <input type="number" step="0.01" min="0" name="monto_apertura" class="form-control mx-auto" style="max-width:200px;" value="0.00" required>
                            </div>
                            <div class="form-group">
                                <label>Observación</label>
                                <textarea name="observacion" class="form-control mx-auto" style="max-width:400px;" rows="2" placeholder="Nota de apertura..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-lg mt-2" style="background:<?= $moduloColor ?>;color:white;">
                                <i class="fas fa-unlock mr-2"></i> Abrir Turno
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Últimos Turnos -->
        <?php if (!empty($ultimosTurnos)): ?>
        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Últimos Turnos</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Caja</th>
                                <th>Apertura</th>
                                <th>Cierre</th>
                                <th class="text-right">Ventas</th>
                                <th class="text-center"># Ventas</th>
                                <th class="text-right">Diferencia</th>
                                <th class="text-center">Estado</th>
                                <th width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimosTurnos as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['caj_nombre'] ?? '—') ?></td>
                                <td><small><?= date('d/m H:i', strtotime($t['tur_fecha_apertura'])) ?></small></td>
                                <td><small><?= !empty($t['tur_fecha_cierre']) ? date('d/m H:i', strtotime($t['tur_fecha_cierre'])) : '—' ?></small></td>
                                <td class="text-right"><strong>$<?= number_format($t['tur_total_ventas'] ?? 0, 2) ?></strong></td>
                                <td class="text-center"><?= intval($t['tur_num_ventas'] ?? 0) ?></td>
                                <td class="text-right">
                                    <?php
                                    $dif = floatval($t['tur_diferencia'] ?? 0);
                                    $difColor = $dif == 0 ? 'text-success' : ($dif > 0 ? 'text-info' : 'text-danger');
                                    ?>
                                    <span class="<?= $difColor ?>">$<?= number_format($dif, 2) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $t['tur_estado'] === 'ABIERTO' ? 'success' : 'secondary' ?>"><?= $t['tur_estado'] ?></span>
                                </td>
                                <td>
                                    <a href="<?= url('store', 'caja', 'verTurno', ['id' => $t['tur_turno_id']]) ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- Modal Movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('store', 'caja', 'movimiento') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="turno_id" value="<?= $turnoActual['tur_turno_id'] ?? 0 ?>">
                <input type="hidden" name="tipo" id="movTipo" value="ENTRADA">
                <div class="modal-header" id="movHeader">
                    <h5 class="modal-title" id="movTitulo">Movimiento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Monto ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="monto" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción <span class="text-danger">*</span></label>
                        <textarea name="descripcion" class="form-control" rows="2" required placeholder="Motivo del movimiento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" id="movBtnGuardar"><i class="fas fa-save mr-1"></i> Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
function abrirMovimiento(tipo) {
    document.getElementById('movTipo').value = tipo;
    var header = document.getElementById('movHeader');
    var titulo = document.getElementById('movTitulo');
    var btn = document.getElementById('movBtnGuardar');
    if (tipo === 'ENTRADA') {
        header.className = 'modal-header bg-success text-white';
        titulo.innerHTML = '<i class="fas fa-arrow-down mr-2"></i>Entrada de Efectivo';
        btn.className = 'btn btn-success';
    } else {
        header.className = 'modal-header bg-warning text-white';
        titulo.innerHTML = '<i class="fas fa-arrow-up mr-2"></i>Salida de Efectivo';
        btn.className = 'btn btn-warning';
    }
    $('#modalMovimiento').modal('show');
}

function confirmarCierreTurno() {
    Swal.fire({
        title: '¿Cerrar el turno?',
        html: '<div class="form-group text-left"><label>Monto real en caja ($)</label>' +
              '<input type="number" step="0.01" min="0" id="swalMontoCierre" class="swal2-input" placeholder="0.00" required></div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Cerrar Turno',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        preConfirm: function() {
            var monto = document.getElementById('swalMontoCierre').value;
            if (!monto || parseFloat(monto) < 0) {
                Swal.showValidationMessage('Ingrese el monto real en caja');
                return false;
            }
            return monto;
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('store', 'caja', 'cerrarTurno') ?>';
            form.innerHTML = '<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">' +
                             '<input type="hidden" name="turno_id" value="<?= $turnoActual['tur_turno_id'] ?? 0 ?>">' +
                             '<input type="hidden" name="monto_cierre" value="' + result.value + '">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
