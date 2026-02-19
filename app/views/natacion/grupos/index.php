<?php
/**
 * DigiSports Natación - Gestión de Grupos
 */
$grupos      = $grupos ?? [];
$niveles     = $niveles ?? [];
$piscinas    = $piscinas ?? [];
$instructores = $instructores ?? [];
$periodos    = $periodos ?? [];
$sedes       = $sedes ?? [];
$sedeActiva  = $sede_activa ?? null;
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-users mr-2" style="color:<?= $moduloColor ?>"></i>Grupos</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Grupo</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <span class="badge badge-secondary"><?= count($grupos) ?> grupo(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($grupos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-users fa-3x mb-3 opacity-50"></i><p>No hay grupos registrados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Grupo</th><th>Nivel</th><th>Instructor</th><th>Piscina</th><?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?><th class="text-center">Cupo</th><th class="text-center">Estado</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupos as $i => $g): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($g['ngr_color'])): ?><span class="badge mr-1" style="background:<?= htmlspecialchars($g['ngr_color']) ?>">&nbsp;</span><?php endif; ?>
                                    <strong><?= htmlspecialchars($g['ngr_nombre']) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($g['nivel_nombre'])): ?>
                                    <span class="badge" style="background:<?= htmlspecialchars($g['nivel_color'] ?? '#6c757d') ?>;color:white;"><?= htmlspecialchars($g['nivel_nombre']) ?></span>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(($g['instructor_nombre'] ?? '') . ' ' . ($g['instructor_apellido'] ?? '')) ?: '—' ?></td>
                                <td><?= htmlspecialchars($g['piscina_nombre'] ?? '—') ?></td>
                                <?php if (!$sedeActiva): ?><td><?= htmlspecialchars($g['sed_nombre'] ?? '—') ?></td><?php endif; ?>
                                <td class="text-center">
                                    <?php $pct = $g['ngr_cupo_maximo'] > 0 ? round($g['ngr_cupo_actual']/$g['ngr_cupo_maximo']*100) : 0; ?>
                                    <span class="badge badge-<?= $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') ?>">
                                        <?= $g['ngr_cupo_actual'] ?>/<?= $g['ngr_cupo_maximo'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php $eb = ['ABIERTO'=>'success','EN_CURSO'=>'info','CERRADO'=>'secondary','CANCELADO'=>'danger'][$g['ngr_estado']] ?? 'light'; ?>
                                    <span class="badge badge-<?= $eb ?>"><?= $g['ngr_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarGrupo(<?= json_encode($g) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger" onclick="eliminarGrupo(<?= $g['ngr_grupo_id'] ?>,'<?= htmlspecialchars($g['ngr_nombre']) ?>')" title="Cerrar"><i class="fas fa-times"></i></button>
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
<div class="modal fade" id="modalGrupo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formGrupo" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="gr_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-users mr-2"></i>Nuevo Grupo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8"><div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="gr_nombre" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Color</label><input type="color" name="color" id="gr_color" class="form-control" value="#0EA5E9"></div></div>
                    </div>
                    <div class="form-group">
                        <label>Sede</label>
                        <select name="sede_id" id="gr_sede" class="form-control">
                            <option value="">— Sin sede —</option>
                            <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Nivel</label><select name="nivel_id" id="gr_nivel" class="form-control"><option value="">—</option><?php foreach($niveles as $n): ?><option value="<?= $n['nnv_nivel_id'] ?>"><?= htmlspecialchars($n['nnv_nombre']) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Instructor</label><select name="instructor_id" id="gr_instructor" class="form-control"><option value="">—</option><?php foreach($instructores as $ins): ?><option value="<?= $ins['nin_instructor_id'] ?>"><?= htmlspecialchars($ins['nin_nombres'].' '.$ins['nin_apellidos']) ?></option><?php endforeach; ?></select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Piscina</label><select name="piscina_id" id="gr_piscina" class="form-control"><option value="">—</option><?php foreach($piscinas as $p): ?><option value="<?= $p['npi_piscina_id'] ?>"><?= htmlspecialchars($p['npi_nombre']) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Período</label><select name="periodo_id" id="gr_periodo" class="form-control"><option value="">—</option><?php foreach($periodos as $per): ?><option value="<?= $per['npe_periodo_id'] ?>"><?= htmlspecialchars($per['npe_nombre']) ?></option><?php endforeach; ?></select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Cupo Máximo</label><input type="number" name="cupo_maximo" id="gr_cupo" class="form-control" min="1" value="15"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Precio</label><input type="number" name="precio" id="gr_precio" class="form-control" step="0.01" min="0"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Estado</label><select name="estado" id="gr_estado" class="form-control"><option value="ABIERTO">Abierto</option><option value="EN_CURSO">En Curso</option><option value="CERRADO">Cerrado</option><option value="CANCELADO">Cancelado</option></select></div></div>
                    </div>
                    <div class="form-group"><label>Descripción</label><textarea name="descripcion" id="gr_desc" class="form-control" rows="2"></textarea></div>
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
var urlCrear = '<?= url('natacion', 'grupo', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'grupo', 'editar') ?>';
function abrirModal() {
    document.getElementById('formGrupo').reset(); document.getElementById('gr_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-users mr-2"></i>Nuevo Grupo';
    document.getElementById('formGrupo').action = urlCrear; $('#modalGrupo').modal('show');
}
function editarGrupo(g) {
    document.getElementById('gr_id').value = g.ngr_grupo_id;
    document.getElementById('gr_nombre').value = g.ngr_nombre || '';
    document.getElementById('gr_sede').value = g.ngr_sede_id || '';
    document.getElementById('gr_color').value = g.ngr_color || '#0EA5E9';
    document.getElementById('gr_nivel').value = g.ngr_nivel_id || '';
    document.getElementById('gr_instructor').value = g.ngr_instructor_id || '';
    document.getElementById('gr_piscina').value = g.ngr_piscina_id || '';
    document.getElementById('gr_periodo').value = g.ngr_periodo_id || '';
    document.getElementById('gr_cupo').value = g.ngr_cupo_maximo || 15;
    document.getElementById('gr_precio').value = g.ngr_precio || '';
    document.getElementById('gr_estado').value = g.ngr_estado || 'ABIERTO';
    document.getElementById('gr_desc').value = g.ngr_descripcion || '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Grupo';
    document.getElementById('formGrupo').action = urlEditar; $('#modalGrupo').modal('show');
}
function eliminarGrupo(id, nombre) {
    Swal.fire({ title: '¿Cerrar grupo?', html: '<strong>' + nombre + '</strong>', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, cerrar', cancelButtonText: 'Cancelar'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'grupo', 'eliminar') ?>&id=' + id; });
}
</script>
<?php $scripts = ob_get_clean(); ?>
