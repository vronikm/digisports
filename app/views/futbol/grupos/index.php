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
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" id="btnNuevoGrupo" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Nuevo Grupo</button></div></div>
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
                    <table class="table table-hover mb-0" id="tablaGrupos">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Grupo</th><th>Categoría</th><th>Entrenador</th><th>Cancha</th><?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?><th class="text-center">Cupo</th><th class="text-center">Estado</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupos as $i => $g): ?>
                            <tr data-id="<?= $g['fgr_grupo_id'] ?>">
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
                                        <button class="btn btn-outline-primary js-editar-grupo"
                                            data-grupo="<?= htmlspecialchars(json_encode($g, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>"
                                            title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger js-eliminar-grupo"
                                            data-id="<?= $g['fgr_grupo_id'] ?>"
                                            data-nombre="<?= htmlspecialchars($g['fgr_nombre'], ENT_QUOTES) ?>"
                                            title="Cerrar"><i class="fas fa-times"></i></button>
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
            <form id="formGrupo" method="POST"
                data-url-crear="<?= url('futbol', 'grupo', 'crear') ?>"
                data-url-editar="<?= url('futbol', 'grupo', 'editar') ?>">
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
<script nonce="<?= cspNonce() ?>">
$(function() {
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    var csrfToken = '<?= addslashes($csrf_token ?? '') ?>';

    // Nuevo grupo
    $('#btnNuevoGrupo').on('click', function() {
        $('#formGrupo')[0].reset();
        $('#gr_id').val('');
        $('#gr_color').val('#22C55E');
        $('#gr_estado').val('ABIERTO');
        $('#modalTitulo').html('<i class="fas fa-users mr-2"></i>Nuevo Grupo');
        $('#formGrupo').data('mode', 'crear');
        $('#modalGrupo').modal('show');
    });

    // Editar grupo
    $(document).on('click', '.js-editar-grupo', function() {
        var g = JSON.parse($(this).attr('data-grupo'));
        $('#gr_id').val(g.fgr_grupo_id);
        $('#gr_nombre').val(g.fgr_nombre || '');
        $('#gr_sede').val(g.fgr_sede_id || '');
        $('#gr_color').val(g.fgr_color || '#22C55E');
        $('#gr_categoria').val(g.fgr_categoria_id || '');
        $('#gr_entrenador').val(g.fgr_entrenador_id || '');
        $('#gr_cancha').val(g.fgr_cancha_id || '');
        $('#gr_periodo').val(g.fgr_periodo_id || '');
        $('#gr_cupo').val(g.fgr_cupo_maximo || 20);
        $('#gr_precio').val(g.fgr_precio || '');
        $('#gr_estado').val(g.fgr_estado || 'ABIERTO');
        $('#gr_desc').val(g.fgr_descripcion || '');
        $('#modalTitulo').html('<i class="fas fa-edit mr-2"></i>Editar Grupo');
        $('#formGrupo').data('mode', 'editar');
        $('#modalGrupo').modal('show');
    });

    // Submit crear/editar
    $('#formGrupo').on('submit', function(e) {
        e.preventDefault();
        var mode = $(this).data('mode') || 'crear';
        var action = $(this).attr(mode === 'editar' ? 'data-url-editar' : 'data-url-crear');
        var $btn = $(this).find('[type=submit]').prop('disabled', true);
        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalGrupo').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de comunicación' });
        }).always(function() { $btn.prop('disabled', false); });
    });

    // Cerrar grupo
    $(document).on('click', '.js-eliminar-grupo', function() {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var $row = $(this).closest('tr');
        Swal.fire({
            title: '¿Cerrar grupo?',
            html: '<strong>' + nombre + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post('<?= url('futbol', 'grupo', 'eliminar') ?>', { id: id, csrf_token: csrfToken }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    $row.fadeOut(400, function() { location.reload(); });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de comunicación' });
            });
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
