<?php
/**
 * DigiSports Natación - Gestión de Inscripciones
 */
$inscripciones = $inscripciones ?? [];
$grupos        = $grupos ?? [];
$moduloColor   = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-clipboard-list mr-2" style="color:<?= $moduloColor ?>"></i>Inscripciones</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nueva Inscripción</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('natacion', 'inscripcion', 'index') ?>" class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small">Grupo</label>
                        <select name="grupo_id" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['ngr_grupo_id'] ?>" <?= ($grupo_id ?? '') == $g['ngr_grupo_id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['ngr_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <option value="ACTIVA" <?= ($estado ?? '') === 'ACTIVA' ? 'selected' : '' ?>>Activa</option>
                            <option value="CANCELADA" <?= ($estado ?? '') === 'CANCELADA' ? 'selected' : '' ?>>Cancelada</option>
                            <option value="COMPLETADA" <?= ($estado ?? '') === 'COMPLETADA' ? 'selected' : '' ?>>Completada</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-right">
                        <button class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i>Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header py-2"><span class="badge badge-secondary"><?= count($inscripciones) ?> inscripción(es)</span></div>
            <div class="card-body p-0">
                <?php if (empty($inscripciones)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-clipboard-list fa-3x mb-3 opacity-50"></i><p>No hay inscripciones</p></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th width="40">#</th><th>Alumno</th><th>Grupo</th><th>Fecha</th><th class="text-center">Estado</th><th width="130" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $i => $ins): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars(($ins['alu_nombres'] ?? '') . ' ' . ($ins['alu_apellidos'] ?? '')) ?></strong></td>
                                <td><?= htmlspecialchars($ins['grupo_nombre'] ?? '—') ?></td>
                                <td><?= date('d/m/Y', strtotime($ins['nis_fecha_inscripcion'])) ?></td>
                                <td class="text-center">
                                    <?php $bc = ['ACTIVA'=>'success','CANCELADA'=>'danger','SUSPENDIDA'=>'warning','COMPLETADA'=>'info'][$ins['nis_estado']] ?? 'secondary'; ?>
                                    <span class="badge badge-<?= $bc ?>"><?= $ins['nis_estado'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarInscripcion(<?= json_encode($ins) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <?php if ($ins['nis_estado'] === 'ACTIVA'): ?>
                                        <button class="btn btn-outline-danger" onclick="cancelarInscripcion(<?= $ins['nis_inscripcion_id'] ?>)" title="Cancelar"><i class="fas fa-ban"></i></button>
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
<div class="modal fade" id="modalInscripcion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formInscripcion" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="insc_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-clipboard-list mr-2"></i>Nueva Inscripción</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Buscar Alumno <span class="text-danger">*</span></label>
                        <input type="text" id="buscarAlumnoInsc" class="form-control" placeholder="Nombre del alumno...">
                        <input type="hidden" name="alumno_id" id="insc_alumno_id" required>
                        <div id="alumnoSelInfo" class="mt-1"></div>
                    </div>
                    <div class="form-group">
                        <label>Grupo <span class="text-danger">*</span></label>
                        <select name="grupo_id" id="insc_grupo" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['ngr_grupo_id'] ?>"><?= htmlspecialchars($g['ngr_nombre']) ?> (<?= $g['ngr_cupo_actual'] ?>/<?= $g['ngr_cupo_maximo'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Notas</label><textarea name="notas" id="insc_notas" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Inscribir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear = '<?= url('natacion', 'inscripcion', 'crear') ?>';
var urlEditar = '<?= url('natacion', 'inscripcion', 'editar') ?>';

function abrirModal() {
    document.getElementById('formInscripcion').reset();
    document.getElementById('insc_id').value = '';
    document.getElementById('insc_alumno_id').value = '';
    document.getElementById('alumnoSelInfo').innerHTML = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-clipboard-list mr-2"></i>Nueva Inscripción';
    document.getElementById('formInscripcion').action = urlCrear;
    $('#modalInscripcion').modal('show');
}

function editarInscripcion(ins) {
    document.getElementById('insc_id').value = ins.nis_inscripcion_id;
    document.getElementById('insc_alumno_id').value = ins.nis_alumno_id;
    document.getElementById('insc_grupo').value = ins.nis_grupo_id || '';
    document.getElementById('insc_notas').value = ins.nis_notas || '';
    document.getElementById('alumnoSelInfo').innerHTML = '<span class="badge badge-info">' + (ins.alu_nombres || '') + ' ' + (ins.alu_apellidos || '') + '</span>';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Inscripción';
    document.getElementById('formInscripcion').action = urlEditar;
    $('#modalInscripcion').modal('show');
}

function cancelarInscripcion(id) {
    Swal.fire({ title: '¿Cancelar inscripción?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'inscripcion', 'cancelar') ?>&id=' + id; });
}

// Búsqueda AJAX de alumno
var timerAlumno;
$('#buscarAlumnoInsc').on('input', function() {
    clearTimeout(timerAlumno);
    var q = $(this).val();
    if (q.length < 2) return;
    timerAlumno = setTimeout(function() {
        $.getJSON('<?= url('natacion', 'alumno', 'buscarRepresentante') ?>&q=' + encodeURIComponent(q), function(res) {
            // Reutiliza endpoint de búsqueda — adaptar si se crea uno específico
        });
    }, 300);
});
</script>
<?php $scripts = ob_get_clean(); ?>
