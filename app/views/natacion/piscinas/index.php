<?php
/**
 * DigiSports Natación - Gestión de Piscinas
 */
$piscinas    = $piscinas ?? [];
$sedes       = $sedes ?? [];
$sedeActiva  = $sede_activa ?? null;
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-swimming-pool mr-2" style="color:<?= $moduloColor ?>"></i>Piscinas</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nueva Piscina</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($piscinas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-swimming-pool fa-3x mb-3 opacity-50"></i><p>No hay piscinas registradas</p>
                    <button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Crear primera piscina</button>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Nombre</th><?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?><th>Tipo</th><th class="text-center">Largo</th><th class="text-center">Carriles</th><th class="text-center">Temp.</th><th class="text-center">Estado</th><th width="140" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($piscinas as $i => $p): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td><i class="fas fa-water mr-2" style="color:<?= $moduloColor ?>"></i><strong><?= htmlspecialchars($p['npi_nombre']) ?></strong>
                                    <?php if (!empty($p['npi_ubicacion'])): ?><br><small class="text-muted"><?= htmlspecialchars($p['npi_ubicacion']) ?></small><?php endif; ?>
                                </td>
                                <?php if (!$sedeActiva): ?><td><?= htmlspecialchars($p['sed_nombre'] ?? '—') ?></td><?php endif; ?>
                                <td><?= htmlspecialchars($p['npi_tipo'] ?? '—') ?></td>
                                <td class="text-center"><?= $p['npi_largo_metros'] ? $p['npi_largo_metros'] . 'm' : '—' ?></td>
                                <td class="text-center"><span class="badge badge-info"><?= (int)($p['total_carriles'] ?? $p['npi_num_carriles'] ?? 0) ?></span></td>
                                <td class="text-center"><?= $p['npi_temperatura'] ? $p['npi_temperatura'] . '°C' : '—' ?></td>
                                <td class="text-center">
                                    <?= $p['npi_activo'] ? '<span class="badge badge-success">Activa</span>' : '<span class="badge badge-secondary">Inactiva</span>' ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarPiscina(<?= json_encode($p) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger" onclick="eliminarPiscina(<?= $p['npi_piscina_id'] ?>,'<?= htmlspecialchars($p['npi_nombre']) ?>')" title="Desactivar"><i class="fas fa-trash"></i></button>
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
<div class="modal fade" id="modalPiscina" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPiscina" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="pis_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-swimming-pool mr-2"></i>Nueva Piscina</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="pis_nombre" class="form-control" required></div>
                    <div class="form-group">
                        <label>Sede</label>
                        <select name="sede_id" id="pis_sede" class="form-control">
                            <option value="">— Sin sede —</option>
                            <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Tipo</label><select name="tipo" id="pis_tipo" class="form-control"><option value="CUBIERTA">Cubierta</option><option value="DESCUBIERTA">Descubierta</option><option value="SEMIOLIMPICA">Semi-olímpica</option><option value="OLIMPICA">Olímpica</option><option value="DIDACTICA">Didáctica</option></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Largo (m)</label><input type="number" name="largo_metros" id="pis_largo" class="form-control" step="0.5" min="0"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Ancho (m)</label><input type="number" name="ancho_metros" id="pis_ancho" class="form-control" step="0.5" min="0"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Prof. Max (m)</label><input type="number" name="profundidad_max" id="pis_prof" class="form-control" step="0.1" min="0"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Temperatura (°C)</label><input type="number" name="temperatura" id="pis_temp" class="form-control" step="0.5" min="0"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Nro. Carriles</label><input type="number" name="num_carriles" id="pis_carriles" class="form-control" min="0" max="20" value="6"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Ubicación</label><input type="text" name="ubicacion" id="pis_ubicacion" class="form-control"></div></div>
                    </div>
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
var urlCrear = '<?= url('natacion', 'piscina', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'piscina', 'editar') ?>';
function abrirModal() {
    document.getElementById('formPiscina').reset(); document.getElementById('pis_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-swimming-pool mr-2"></i>Nueva Piscina';
    document.getElementById('formPiscina').action = urlCrear; $('#modalPiscina').modal('show');
}
function editarPiscina(p) {
    document.getElementById('pis_id').value = p.npi_piscina_id;
    document.getElementById('pis_nombre').value = p.npi_nombre || '';
    document.getElementById('pis_sede').value = p.npi_sede_id || '';
    document.getElementById('pis_tipo').value = p.npi_tipo || 'CUBIERTA';
    document.getElementById('pis_largo').value = p.npi_largo_metros || '';
    document.getElementById('pis_ancho').value = p.npi_ancho_metros || '';
    document.getElementById('pis_prof').value = p.npi_profundidad_max || '';
    document.getElementById('pis_temp').value = p.npi_temperatura || '';
    document.getElementById('pis_carriles').value = p.npi_num_carriles || '';
    document.getElementById('pis_ubicacion').value = p.npi_ubicacion || '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Piscina';
    document.getElementById('formPiscina').action = urlEditar; $('#modalPiscina').modal('show');
}
function eliminarPiscina(id, nombre) {
    Swal.fire({ title: '¿Desactivar piscina?', html: 'Se desactivará <strong>' + nombre + '</strong>', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'piscina', 'eliminar') ?>&id=' + id; });
}
</script>
<?php $scripts = ob_get_clean(); ?>
