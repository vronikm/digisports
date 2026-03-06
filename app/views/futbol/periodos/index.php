<?php
/**
 * DigiSports Fútbol - Períodos Académicos
 */
$periodos    = $periodos ?? [];
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-calendar-alt mr-2" style="color:<?= $moduloColor ?>"></i>Períodos Académicos</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" id="btnNuevoPeriodo" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Nuevo Período</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($periodos)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-calendar-alt fa-3x mb-3 opacity-50"></i><p>No hay períodos registrados</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Período</th><th>Inicio</th><th>Fin</th><th class="text-center">Grupos</th><th class="text-center">Inscripciones</th><th class="text-center">Estado</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periodos as $i => $p): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($p['fpe_nombre']) ?></strong>
                                    <?php if (!empty($p['fpe_notas'])): ?><br><small class="text-muted"><?= htmlspecialchars(substr($p['fpe_notas'], 0, 60)) ?></small><?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($p['fpe_fecha_inicio'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($p['fpe_fecha_fin'])) ?></td>
                                <td class="text-center"><span class="badge badge-info"><?= (int)$p['total_grupos'] ?></span></td>
                                <td class="text-center"><span class="badge badge-primary"><?= (int)$p['total_inscripciones'] ?></span></td>
                                <td class="text-center">
                                    <?php $eb = ['PLANIFICADO'=>'secondary','ACTIVO'=>'success','FINALIZADO'=>'dark','CANCELADO'=>'danger'][$p['fpe_estado']] ?? 'light'; ?>
                                    <span class="badge badge-<?= $eb ?>"><?= $p['fpe_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary js-editar-periodo" title="Editar"
                                            data-periodo="<?= htmlspecialchars(json_encode($p, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger js-finalizar-periodo" title="Finalizar"
                                            data-id="<?= $p['fpe_periodo_id'] ?>"
                                            data-nombre="<?= htmlspecialchars($p['fpe_nombre'], ENT_QUOTES) ?>">
                                            <i class="fas fa-flag-checkered"></i>
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
<div class="modal fade" id="modalPeriodo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPeriodo"
                data-url-crear="<?= url('futbol', 'periodo', 'crear') ?>"
                data-url-editar="<?= url('futbol', 'periodo', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="per_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-calendar-alt mr-2"></i>Nuevo Período</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="per_nombre" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Fecha Inicio <span class="text-danger">*</span></label><input type="date" name="fecha_inicio" id="per_inicio" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Fecha Fin <span class="text-danger">*</span></label><input type="date" name="fecha_fin" id="per_fin" class="form-control" required></div></div>
                    </div>
                    <div class="form-group"><label>Estado</label><select name="estado" id="per_estado" class="form-control"><option value="PLANIFICADO">Planificado</option><option value="ACTIVO">Activo</option><option value="FINALIZADO">Finalizado</option></select></div>
                    <div class="form-group"><label>Notas</label><textarea name="notas" id="per_notas" class="form-control" rows="2"></textarea></div>
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
    var csrfToken   = '<?= addslashes($csrf_token ?? '') ?>';
    var urlFinalizar = '<?= url('futbol', 'periodo', 'eliminar') ?>';

    function abrirModalNuevo() {
        $('#formPeriodo')[0].reset();
        $('#per_id').val('');
        $('#modalTitulo').html('<i class="fas fa-calendar-alt mr-2"></i>Nuevo Período');
        $('#formPeriodo').data('mode', 'crear');
        $('#modalPeriodo').modal('show');
    }

    function abrirModalEditar(p) {
        $('#per_id').val(p.fpe_periodo_id);
        $('#per_nombre').val(p.fpe_nombre || '');
        $('#per_inicio').val(p.fpe_fecha_inicio || '');
        $('#per_fin').val(p.fpe_fecha_fin || '');
        $('#per_estado').val(p.fpe_estado || 'PLANIFICADO');
        $('#per_notas').val(p.fpe_notas || '');
        $('#modalTitulo').html('<i class="fas fa-edit mr-2"></i>Editar Período');
        $('#formPeriodo').data('mode', 'editar');
        $('#modalPeriodo').modal('show');
    }

    $('#btnNuevoPeriodo').on('click', abrirModalNuevo);

    $(document).on('click', '.js-editar-periodo', function() {
        var p = JSON.parse($(this).attr('data-periodo'));
        abrirModalEditar(p);
    });

    $(document).on('click', '.js-finalizar-periodo', function() {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        Swal.fire({
            title: '¿Finalizar período?',
            html: '<strong>' + $('<div>').text(nombre).html() + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post(urlFinalizar, { id: id, csrf_token: csrfToken }, function(res) {
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

    $('#formPeriodo').on('submit', function(e) {
        e.preventDefault();
        var mode = $(this).data('mode') || 'crear';
        var url  = mode === 'editar' ? $(this).data('url-editar') : $(this).data('url-crear');
        var $btn = $(this).find('[type=submit]').prop('disabled', true);
        $.post(url, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalPeriodo').modal('hide');
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
