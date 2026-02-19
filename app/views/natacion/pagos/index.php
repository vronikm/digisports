<?php
/**
 * DigiSports Natación - Gestión de Pagos
 */
$pagos       = $pagos ?? [];
$totales     = $totales ?? [];
$sedeActiva  = $sede_activa ?? null;
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
$totalPagado = 0; $totalPendiente = 0;
foreach ($totales as $t) {
    if ($t['npg_estado'] === 'PAGADO') $totalPagado = (float)$t['monto'];
    if ($t['npg_estado'] === 'PENDIENTE') $totalPendiente = (float)$t['monto'];
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-dollar-sign mr-2" style="color:<?= $moduloColor ?>"></i>Pagos</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Pago</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Resumen -->
        <div class="row mb-3">
            <div class="col-md-4"><div class="small-box bg-success"><div class="inner"><h4>$<?= number_format($totalPagado, 2) ?></h4><p>Total Cobrado</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
            <div class="col-md-4"><div class="small-box bg-warning"><div class="inner"><h4>$<?= number_format($totalPendiente, 2) ?></h4><p>Pendiente</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div>
            <div class="col-md-4"><div class="small-box bg-info"><div class="inner"><h4><?= count($pagos) ?></h4><p>Total Registros</p></div><div class="icon"><i class="fas fa-file-invoice-dollar"></i></div></div></div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($pagos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-dollar-sign fa-3x mb-3 opacity-50"></i><p>No hay pagos registrados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Alumno</th><th>Grupo</th><?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?><th>Representante</th><th class="text-right">Monto</th><th>Método</th><th>Fecha</th><th class="text-center">Estado</th><th width="120" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $i => $p): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars(($p['alu_nombres'] ?? '') . ' ' . ($p['alu_apellidos'] ?? '')) ?></strong></td>
                                <td><?= htmlspecialchars($p['grupo_nombre'] ?? '—') ?></td>
                                <?php if (!$sedeActiva): ?><td><?= htmlspecialchars($p['sede_nombre'] ?? '—') ?></td><?php endif; ?>
                                <td><?= htmlspecialchars(($p['representante_nombre'] ?? '') . ' ' . ($p['representante_apellido'] ?? '')) ?: '—' ?></td>
                                <td class="text-right font-weight-bold">$<?= number_format((float)$p['npg_monto'], 2) ?></td>
                                <td><span class="badge badge-light"><?= htmlspecialchars($p['npg_metodo_pago'] ?? '—') ?></span></td>
                                <td><?= date('d/m/Y', strtotime($p['npg_fecha_pago'])) ?></td>
                                <td class="text-center">
                                    <?php $eb = ['PAGADO'=>'success','PENDIENTE'=>'warning','ANULADO'=>'danger'][$p['npg_estado']] ?? 'secondary'; ?>
                                    <span class="badge badge-<?= $eb ?>"><?= $p['npg_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarPago(<?= json_encode($p) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <?php if ($p['npg_estado'] !== 'ANULADO'): ?>
                                        <button class="btn btn-outline-danger" onclick="anularPago(<?= $p['npg_pago_id'] ?>)" title="Anular"><i class="fas fa-ban"></i></button>
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
</section>

<!-- Modal -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPago" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="pag_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-dollar-sign mr-2"></i>Nuevo Pago</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Buscar Inscripción <span class="text-danger">*</span></label>
                        <input type="text" id="buscarInscPago" class="form-control" placeholder="Nombre del alumno...">
                        <input type="hidden" name="inscripcion_id" id="pag_inscripcion" required>
                        <div id="inscSelInfo" class="mt-1"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Monto <span class="text-danger">*</span></label><input type="number" name="monto" id="pag_monto" class="form-control" step="0.01" min="0.01" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Método</label><select name="metodo_pago" id="pag_metodo" class="form-control"><option value="EFECTIVO">Efectivo</option><option value="TRANSFERENCIA">Transferencia</option><option value="TARJETA">Tarjeta</option><option value="DEPOSITO">Depósito</option></select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Estado</label><select name="estado" id="pag_estado" class="form-control"><option value="PAGADO">Pagado</option><option value="PENDIENTE">Pendiente</option></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Referencia</label><input type="text" name="referencia" id="pag_referencia" class="form-control" placeholder="Nro. comprobante"></div></div>
                    </div>
                    <div class="form-group"><label>Notas</label><textarea name="notas" id="pag_notas" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear = '<?= url('natacion', 'pago', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'pago', 'editar') ?>';
function abrirModal() {
    document.getElementById('formPago').reset(); document.getElementById('pag_id').value = '';
    document.getElementById('pag_inscripcion').value = '';
    document.getElementById('inscSelInfo').innerHTML = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-dollar-sign mr-2"></i>Nuevo Pago';
    document.getElementById('formPago').action = urlCrear; $('#modalPago').modal('show');
}
function editarPago(p) {
    document.getElementById('pag_id').value = p.npg_pago_id;
    document.getElementById('pag_inscripcion').value = p.npg_inscripcion_id || '';
    document.getElementById('pag_monto').value = p.npg_monto || '';
    document.getElementById('pag_metodo').value = p.npg_metodo_pago || 'EFECTIVO';
    document.getElementById('pag_estado').value = p.npg_estado || 'PENDIENTE';
    document.getElementById('pag_referencia').value = p.npg_referencia || '';
    document.getElementById('pag_notas').value = p.npg_notas || '';
    document.getElementById('inscSelInfo').innerHTML = '<span class="badge badge-info">' + (p.alu_nombres||'') + ' ' + (p.alu_apellidos||'') + ' - ' + (p.grupo_nombre||'') + '</span>';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Pago';
    document.getElementById('formPago').action = urlEditar; $('#modalPago').modal('show');
}
function anularPago(id) {
    Swal.fire({ title: '¿Anular pago?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, anular', cancelButtonText: 'No'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'pago', 'anular') ?>&id=' + id; });
}
// Buscar inscripciones
var timerInsc;
$('#buscarInscPago').on('input', function() {
    clearTimeout(timerInsc);
    var q = $(this).val(); if (q.length < 2) return;
    timerInsc = setTimeout(function() {
        $.getJSON('<?= url('natacion', 'pago', 'buscarInscripciones') ?>&q=' + encodeURIComponent(q), function(res) {
            if (res.success && res.data.length) {
                var html = '<div class="list-group">';
                res.data.forEach(function(r) { html += '<a href="#" class="list-group-item list-group-item-action py-1 selInsc" data-id="' + r.nis_inscripcion_id + '">' + r.alu_nombres + ' ' + r.alu_apellidos + ' — ' + r.grupo + '</a>'; });
                html += '</div>';
                $('#inscSelInfo').html(html);
            }
        });
    }, 300);
});
$(document).on('click', '.selInsc', function(e) {
    e.preventDefault();
    $('#pag_inscripcion').val($(this).data('id'));
    $('#inscSelInfo').html('<span class="badge badge-info">' + $(this).text() + '</span>');
});
</script>
<?php $scripts = ob_get_clean(); ?>
