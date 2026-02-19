<?php
/**
 * Vista de Control de Mora - Módulo Fútbol
 * @vars $morosos, $resumen, $csrf_token, $modulo_actual
 */
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$morosos     = $morosos ?? [];
$resumen     = $resumen ?? ['total_en_mora' => 0, 'monto_total_adeudado' => 0, 'total_alumnos_morosos' => 0];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-futbol" style="color: <?= $moduloColor ?>"></i>
                    Control de Mora
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Mora</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">

        <!-- Resumen -->
        <div class="row">
            <div class="col-lg-4 col-sm-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= (int)($resumen['total_en_mora'] ?? 0) ?></h3>
                        <p>Pagos en Mora</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>$<?= number_format((float)($resumen['monto_total_adeudado'] ?? 0), 2) ?></h3>
                        <p>Monto Total Adeudado</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= (int)($resumen['total_alumnos_morosos'] ?? 0) ?></h3>
                        <p>Alumnos Morosos</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>

        <!-- Tabla Morosos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-top: 3px solid #EF4444">
                <h3 class="card-title"><i class="fas fa-exclamation-circle mr-2 text-danger"></i>Pagos Pendientes / Vencidos</h3>
                <div>
                    <button class="btn btn-sm btn-outline-info" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt mr-1"></i> Actualizar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($morosos)): ?>
                <div class="table-responsive">
                    <table id="tblMorosos" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Alumno</th>
                                <th>Representante</th>
                                <th>Grupo</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                                <th>Días Mora</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($morosos as $i => $m): ?>
                            <?php
                                $dias = (int)($m['dias_mora_calc'] ?? 0);
                                $diasClass = $dias > 30 ? 'danger' : ($dias > 15 ? 'warning' : 'info');
                                $alumnoNombre = trim(($m['alu_nombres'] ?? '') . ' ' . ($m['alu_apellidos'] ?? ''));
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($alumnoNombre) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($m['representante_nombre'] ?? '—') ?>
                                    <?php if (!empty($m['representante_telefono'])): ?>
                                        <br><small class="text-muted"><i class="fas fa-phone"></i> <?= htmlspecialchars($m['representante_telefono']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($m['grupo_nombre'] ?? '—') ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($m['fpg_tipo'] ?? '—') ?></span></td>
                                <td class="font-weight-bold text-danger">$<?= number_format((float)($m['fpg_total'] ?? 0), 2) ?></td>
                                <td><?= isset($m['fpg_fecha']) ? date('d/m/Y', strtotime($m['fpg_fecha'])) : '—' ?></td>
                                <td><span class="badge badge-<?= $diasClass ?>"><?= $dias ?> días</span></td>
                                <td>
                                    <span class="badge badge-<?= ($m['fpg_estado'] ?? '') === 'VENCIDO' ? 'danger' : 'warning' ?>">
                                        <?= htmlspecialchars($m['fpg_estado'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-warning" title="Enviar Notificación"
                                            onclick="enviarNotificacion(<?= (int)($m['fpg_pago_id'] ?? 0) ?>)">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                        <button class="btn btn-info" title="Ver Historial"
                                            onclick="verHistorial(<?= (int)($m['alu_alumno_id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($alumnoNombre)) ?>')">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <button class="btn btn-danger" title="Suspender"
                                            onclick="suspenderAlumno(<?= (int)($m['alu_alumno_id'] ?? 0) ?>, <?= (int)($m['fpg_grupo_id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($alumnoNombre)) ?>')">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No hay pagos en mora. ¡Todo al día!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Notificación -->
<div class="modal fade" id="modalNotificacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-bell mr-2"></i>Enviar Notificación de Mora</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formNotificacion">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="pago_id" id="notif_pago_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Canal de Notificación</label>
                        <select class="form-control" name="canal" id="notif_canal">
                            <option value="SISTEMA">Sistema (interno)</option>
                            <option value="EMAIL">Email</option>
                            <option value="WHATSAPP">WhatsApp</option>
                            <option value="SMS">SMS</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane mr-1"></i>Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                <h5 class="modal-title"><i class="fas fa-history mr-2"></i>Historial: <span id="hist_nombre"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="historialContenido" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i><p class="text-muted mt-2">Cargando...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    if ($('#tblMorosos tbody tr').length > 0) {
        $('#tblMorosos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            responsive: true,
            order: [[7, 'desc']]
        });
    }

    $('#formNotificacion').on('submit', function(e) {
        e.preventDefault();
        $.post('<?= url("futbol", "mora", "enviarNotificacion") ?>', $(this).serialize(), function(r) {
            if (r.success) {
                Swal.fire('¡Enviada!', r.message, 'success');
                $('#modalNotificacion').modal('hide');
            } else {
                Swal.fire('Error', r.message || 'No se pudo enviar.', 'error');
            }
        }, 'json').fail(function() { Swal.fire('Error', 'Error de conexión.', 'error'); });
    });
});

function enviarNotificacion(pagoId) {
    $('#notif_pago_id').val(pagoId);
    $('#modalNotificacion').modal('show');
}

function verHistorial(alumnoId, nombre) {
    $('#hist_nombre').text(nombre);
    $('#historialContenido').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="text-muted mt-2">Cargando...</p></div>');
    $('#modalHistorial').modal('show');
    $.getJSON('<?= url("futbol", "mora", "historial") ?>&alumno_id=' + alumnoId, function(r) {
        if (r.success && r.data) {
            var pagos = r.data.pagos || [];
            var html = '<div class="mb-3"><strong>Total adeudado:</strong> <span class="text-danger font-weight-bold">$' + parseFloat(r.data.total_adeudado||0).toFixed(2) + '</span></div>';
            if (pagos.length === 0) { html += '<p class="text-muted text-center">Sin registros.</p>'; }
            else {
                html += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Tipo</th><th>Monto</th><th>Fecha</th><th>Estado</th><th>Grupo</th><th>Días</th></tr></thead><tbody>';
                pagos.forEach(function(p) {
                    var d = parseInt(p.dias_mora_calc)||0;
                    var sc = p.fpg_estado==='PAGADO'?'success':(p.fpg_estado==='VENCIDO'?'danger':'warning');
                    html += '<tr><td>'+(p.fpg_tipo||'—')+'</td><td class="font-weight-bold">$'+parseFloat(p.fpg_total||0).toFixed(2)+'</td><td>'+(p.fpg_fecha||'—')+'</td><td><span class="badge badge-'+sc+'">'+(p.fpg_estado||'—')+'</span></td><td>'+(p.grupo_nombre||'—')+'</td><td>'+(d>0?'<span class="badge badge-danger">'+d+' días</span>':'—')+'</td></tr>';
                });
                html += '</tbody></table></div>';
            }
            $('#historialContenido').html(html);
        } else { $('#historialContenido').html('<p class="text-danger text-center">'+(r.message||'Error')+'</p>'); }
    }).fail(function() { $('#historialContenido').html('<p class="text-danger text-center">Error de conexión.</p>'); });
}

function suspenderAlumno(alumnoId, grupoId, nombre) {
    Swal.fire({
        title: '¿Suspender alumno?',
        html: 'Se suspenderán las inscripciones activas de <strong>' + nombre + '</strong> por mora.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d33', confirmButtonText: 'Sí, suspender', cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('<?= url("futbol", "mora", "suspender") ?>', {
                csrf_token: '<?= $csrf_token ?? "" ?>',
                alumno_id: alumnoId, grupo_id: grupoId
            }, function(r) {
                if (r.success) { Swal.fire('Suspendido', r.message, 'success').then(function() { location.reload(); }); }
                else { Swal.fire('Error', r.message || 'No se pudo suspender.', 'error'); }
            }, 'json').fail(function() { Swal.fire('Error', 'Error de conexión.', 'error'); });
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
