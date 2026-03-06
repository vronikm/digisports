<?php
/**
 * DigiSports Fútbol - Gestión de Entrenadores
 */
$entrenadores = $entrenadores ?? [];
$sedes        = $sedes ?? [];
$sedeActiva   = $sede_activa ?? null;
$moduloColor  = $modulo_actual['color'] ?? '#22C55E';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-futbol mr-2" style="color:<?= $moduloColor ?>"></i>Entrenadores</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" id="btnNuevoEntrenador" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Nuevo Entrenador</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($entrenadores)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-futbol fa-3x mb-3 opacity-50"></i><p>No hay entrenadores registrados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Entrenador</th><?php if (!$sedeActiva): ?><th>Sede</th><?php endif; ?><th>Rol</th><th>Especialidad</th><th>Teléfono</th><th>Email</th><th class="text-center">Grupos</th><th class="text-center">Estado</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entrenadores as $i => $ent): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($ent['fen_nombres'] . ' ' . $ent['fen_apellidos']) ?></strong></td>
                                <?php if (!$sedeActiva): ?><td><?= htmlspecialchars($ent['sede_nombre'] ?? '—') ?></td><?php endif; ?>
                                <td>
                                    <?php
                                    $rolBadge = [
                                        'DIRECTOR_TECNICO'  => 'danger',
                                        'ENTRENADOR'        => 'primary',
                                        'ASISTENTE'         => 'info',
                                        'PREPARADOR_FISICO' => 'warning',
                                        'PORTEROS'          => 'secondary'
                                    ][$ent['fen_rol'] ?? ''] ?? 'light';
                                    ?>
                                    <span class="badge badge-<?= $rolBadge ?>"><?= htmlspecialchars($ent['fen_rol'] ?? '—') ?></span>
                                </td>
                                <td><?= htmlspecialchars($ent['fen_especialidad'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($ent['fen_telefono'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($ent['fen_email'] ?? '—') ?></td>
                                <td class="text-center"><span class="badge badge-info"><?= (int)($ent['total_grupos'] ?? 0) ?></span></td>
                                <td class="text-center"><?= $ent['fen_activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>' ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary js-editar-entrenador" title="Editar"
                                            data-entrenador="<?= htmlspecialchars(json_encode($ent, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger js-eliminar-entrenador" title="Desactivar"
                                            data-id="<?= $ent['fen_entrenador_id'] ?>"
                                            data-nombre="<?= htmlspecialchars($ent['fen_nombres'], ENT_QUOTES) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
<div class="modal fade" id="modalEntrenador" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEntrenador"
                data-url-crear="<?= url('futbol', 'entrenador', 'crear') ?>"
                data-url-editar="<?= url('futbol', 'entrenador', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="ent_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-futbol mr-2"></i>Nuevo Entrenador</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Nombres <span class="text-danger">*</span></label><input type="text" name="nombres" id="ent_nombres" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Apellidos <span class="text-danger">*</span></label><input type="text" name="apellidos" id="ent_apellidos" class="form-control" required></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rol <span class="text-danger">*</span></label>
                                <select name="rol" id="ent_rol" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    <option value="DIRECTOR_TECNICO">Director Técnico</option>
                                    <option value="ENTRENADOR">Entrenador</option>
                                    <option value="ASISTENTE">Asistente</option>
                                    <option value="PREPARADOR_FISICO">Preparador Físico</option>
                                    <option value="PORTEROS">Porteros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sede</label>
                                <select name="sede_id" id="ent_sede" class="form-control">
                                    <option value="">— Sin sede —</option>
                                    <?php foreach ($sedes as $s): ?>
                                    <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Identificación</label><input type="text" name="identificacion" id="ent_identif" class="form-control" maxlength="20"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Especialidad</label><input type="text" name="especialidad" id="ent_especialidad" class="form-control" placeholder="Fútbol infantil, formativo..."></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Teléfono</label><input type="text" name="telefono" id="ent_telefono" class="form-control"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" name="email" id="ent_email" class="form-control"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-8"><div class="form-group"><label>Certificaciones</label><textarea name="certificaciones" id="ent_certificaciones" class="form-control" rows="2"></textarea></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Color</label><input type="color" name="color" id="ent_color" class="form-control" value="#22C55E"></div></div>
                    </div>
                    <div class="form-group"><label>Notas</label><textarea name="notas" id="ent_notas" class="form-control" rows="2"></textarea></div>
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
    var csrfToken    = '<?= addslashes($csrf_token ?? '') ?>';
    var urlEliminar  = '<?= url('futbol', 'entrenador', 'eliminar') ?>';

    function abrirModalNuevo() {
        $('#formEntrenador')[0].reset();
        $('#ent_id').val('');
        $('#modalTitulo').html('<i class="fas fa-futbol mr-2"></i>Nuevo Entrenador');
        $('#formEntrenador').data('mode', 'crear');
        $('#modalEntrenador').modal('show');
    }

    function abrirModalEditar(ent) {
        $('#ent_id').val(ent.fen_entrenador_id);
        $('#ent_nombres').val(ent.fen_nombres || '');
        $('#ent_apellidos').val(ent.fen_apellidos || '');
        $('#ent_rol').val(ent.fen_rol || '');
        $('#ent_sede').val(ent.fen_sede_id || '');
        $('#ent_identif').val(ent.fen_identificacion || '');
        $('#ent_especialidad').val(ent.fen_especialidad || '');
        $('#ent_telefono').val(ent.fen_telefono || '');
        $('#ent_email').val(ent.fen_email || '');
        $('#ent_certificaciones').val(ent.fen_certificaciones || '');
        $('#ent_color').val(ent.fen_color || '#22C55E');
        $('#ent_notas').val(ent.fen_notas || '');
        $('#modalTitulo').html('<i class="fas fa-edit mr-2"></i>Editar Entrenador');
        $('#formEntrenador').data('mode', 'editar');
        $('#modalEntrenador').modal('show');
    }

    $('#btnNuevoEntrenador').on('click', abrirModalNuevo);

    $(document).on('click', '.js-editar-entrenador', function() {
        var ent = JSON.parse($(this).attr('data-entrenador'));
        abrirModalEditar(ent);
    });

    $(document).on('click', '.js-eliminar-entrenador', function() {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        Swal.fire({
            title: '¿Desactivar entrenador?',
            html: 'Se desactivará a <strong>' + $('<div>').text(nombre).html() + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post(urlEliminar, { id: id, csrf_token: csrfToken }, function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json').fail(function() {
                Toast.fire({ icon: 'error', title: 'Error de conexión' });
            });
        });
    });

    $('#formEntrenador').on('submit', function(e) {
        e.preventDefault();
        var mode = $(this).data('mode') || 'crear';
        var url  = mode === 'editar' ? $(this).data('url-editar') : $(this).data('url-crear');
        var $btn = $(this).find('[type=submit]').prop('disabled', true);
        $.post(url, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalEntrenador').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de comunicación' });
        }).always(function() { $btn.prop('disabled', false); });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
