<?php
/**
 * DigiSports Fútbol - Gestión de Grupos
 */
$grupos       = $grupos ?? [];
$categorias   = $categorias ?? [];
$canchas      = $canchas ?? [];
$entrenadores = $entrenadores ?? [];
$periodos     = $periodos ?? [];
$sedes        = $sedes ?? [];
$sedeActiva   = $sede_activa ?? null;
$moduloColor  = $modulo_actual['color'] ?? '#22C55E';
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
                            <tr><th width="40">#</th><th>Grupo</th><th>Categoría</th><th>Entrenador</th><th>Cancha</th><?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?><th class="text-center">Cupo</th><th class="text-center">Estado</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupos as $i => $g): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($g['fgr_color'])): ?><span class="badge mr-1" style="background:<?= htmlspecialchars($g['fgr_color']) ?>">&nbsp;</span><?php endif; ?>
                                    <strong><?= htmlspecialchars($g['fgr_nombre']) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($g['categoria'])): ?>
                                    <span class="badge" style="background:<?= htmlspecialchars($g['categoria_color'] ?? '#6c757d') ?>;color:white;"><?= htmlspecialchars($g['categoria']) ?></span>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($g['entrenador'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($g['cancha'] ?? '—') ?></td>
                                <?php if (!$sedeActiva): ?><td><?= htmlspecialchars($g['sede_nombre'] ?? '—') ?></td><?php endif; ?>
                                <td class="text-center">
                                    <?php $cupoActual = (int)($g['fgr_cupo_actual'] ?? 0); $pct = $g['fgr_cupo_maximo'] > 0 ? round($cupoActual/$g['fgr_cupo_maximo']*100) : 0; ?>
                                    <span class="badge badge-<?= $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') ?>">
                                        <?= $cupoActual ?>/<?= $g['fgr_cupo_maximo'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php $eb = ['ABIERTO'=>'success','EN_CURSO'=>'info','CERRADO'=>'secondary','CANCELADO'=>'danger'][$g['fgr_estado']] ?? 'light'; ?>
                                    <span class="badge badge-<?= $eb ?>"><?= $g['fgr_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarGrupo(<?= json_encode($g) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger" onclick="eliminarGrupo(<?= $g['fgr_grupo_id'] ?>,'<?= htmlspecialchars($g['fgr_nombre']) ?>')" title="Cerrar"><i class="fas fa-times"></i></button>
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
                        <div class="col-md-4"><div class="form-group"><label>Color</label><input type="color" name="color" id="gr_color" class="form-control" value="#22C55E"></div></div>
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
                        <div class="col-md-6"><div class="form-group"><label>Categoría</label><select name="categoria_id" id="gr_categoria" class="form-control"><option value="">—</option><?php foreach($categorias as $c): ?><option value="<?= $c['fct_categoria_id'] ?>"><?= htmlspecialchars($c['fct_nombre']) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Entrenador</label><select name="entrenador_id" id="gr_entrenador" class="form-control"><option value="">—</option><?php foreach($entrenadores as $ent): ?><option value="<?= $ent['fen_entrenador_id'] ?>"><?= htmlspecialchars($ent['nombre']) ?></option><?php endforeach; ?></select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Cancha</label><select name="cancha_id" id="gr_cancha" class="form-control"><option value="">—</option><?php foreach($canchas as $ca): ?><option value="<?= $ca['can_cancha_id'] ?>"><?= htmlspecialchars($ca['can_nombre']) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Período</label><select name="periodo_id" id="gr_periodo" class="form-control"><option value="">—</option><?php foreach($periodos as $per): ?><option value="<?= $per['fpe_periodo_id'] ?>"><?= htmlspecialchars($per['fpe_nombre']) ?></option><?php endforeach; ?></select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Cupo Máximo</label><input type="number" name="cupo_maximo" id="gr_cupo" class="form-control" min="1" value="20"></div></div>
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
var urlCrear = '<?= url('futbol', 'grupo', 'crear') ?>';
var urlEditar = '<?= url('futbol', 'grupo', 'editar') ?>';
function abrirModal() {
    document.getElementById('formGrupo').reset(); document.getElementById('gr_id').value = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-users mr-2"></i>Nuevo Grupo';
    document.getElementById('formGrupo').action = urlCrear; $('#modalGrupo').modal('show');
}
function editarGrupo(g) {
    document.getElementById('gr_id').value = g.fgr_grupo_id;
    document.getElementById('gr_nombre').value = g.fgr_nombre || '';
    document.getElementById('gr_sede').value = g.fgr_sede_id || '';
    document.getElementById('gr_color').value = g.fgr_color || '#22C55E';
    document.getElementById('gr_categoria').value = g.fgr_categoria_id || '';
    document.getElementById('gr_entrenador').value = g.fgr_entrenador_id || '';
    document.getElementById('gr_cancha').value = g.fgr_cancha_id || '';
    document.getElementById('gr_periodo').value = g.fgr_periodo_id || '';
    document.getElementById('gr_cupo').value = g.fgr_cupo_maximo || 20;
    document.getElementById('gr_precio').value = g.fgr_precio || '';
    document.getElementById('gr_estado').value = g.fgr_estado || 'ABIERTO';
    document.getElementById('gr_desc').value = g.fgr_descripcion || '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Grupo';
    document.getElementById('formGrupo').action = urlEditar; $('#modalGrupo').modal('show');
}
function eliminarGrupo(id, nombre) {
    Swal.fire({ title: '¿Cerrar grupo?', html: '<strong>' + nombre + '</strong>', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, cerrar', cancelButtonText: 'Cancelar'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('futbol', 'grupo', 'eliminar') ?>&id=' + id; });
}
</script>
<?php $scripts = ob_get_clean(); ?>
