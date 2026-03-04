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
                <form method="POST" action="<?= url('natacion', 'inscripcion', 'index') ?>" class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small">Grupo</label>
                        <select name="grupo" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['ngr_grupo_id'] ?>" <?= ($grupoFiltro ?? '') == $g['ngr_grupo_id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['ngr_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <option value="ACTIVA" <?= ($estadoFiltro ?? '') === 'ACTIVA' ? 'selected' : '' ?>>Activa</option>
                            <option value="CANCELADA" <?= ($estadoFiltro ?? '') === 'CANCELADA' ? 'selected' : '' ?>>Cancelada</option>
                            <option value="COMPLETADA" <?= ($estadoFiltro ?? '') === 'COMPLETADA' ? 'selected' : '' ?>>Completada</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-right">
                        <button class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i>Filtrar</button>
                        <a href="<?= url('natacion', 'inscripcion', 'index') ?>" class="btn btn-sm btn-outline-secondary ml-1">Limpiar</a>
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
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModal()">&times;</button>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="cerrarModal()">Cancelar</button>
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
    var modal = document.getElementById('modalInscripcion');
    if (!modal) {
        Swal.fire('Error', 'El formulario de inscripción no se encontró.', 'error');
        return;
    }
    
    // Limpiar formulario
    document.getElementById('formInscripcion').reset();
    document.getElementById('insc_id').value = '';
    document.getElementById('insc_alumno_id').value = '';
    document.getElementById('alumnoSelInfo').innerHTML = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-clipboard-list mr-2"></i>Nueva Inscripción';
    document.getElementById('formInscripcion').action = urlCrear;
    
    // Remover hold-transition que bloquea animaciones
    document.body.classList.remove('hold-transition');
    
    // Mover modal al body si está dentro de content-wrapper
    if (modal.closest('.content-wrapper')) {
        document.body.appendChild(modal);
    }
    
    // Intentar con Bootstrap/jQuery
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
            jQuery('#modalInscripcion').modal('show');
            setTimeout(function() {
                if (modal.style.display !== 'block' && !modal.classList.contains('show')) {
                    abrirModalManual(modal);
                }
            }, 300);
            return;
        }
    } catch(e) {
        console.warn('Bootstrap modal falló, usando fallback:', e);
    }
    
    // Fallback manual
    abrirModalManual(modal);
}

function abrirModalManual(modal) {
    if (!modal) modal = document.getElementById('modalInscripcion');
    if (!modal) return;
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.setAttribute('aria-modal', 'true');
    modal.removeAttribute('aria-hidden');
    modal.style.paddingRight = '17px';
    // Crear backdrop si no existe
    if (!document.getElementById('modalBackdropFallback')) {
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdropFallback';
        backdrop.onclick = function() { cerrarModal(); };
        document.body.appendChild(backdrop);
    }
    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    var modal = document.getElementById('modalInscripcion');
    if (modal) {
        try {
            if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
                jQuery('#modalInscripcion').modal('hide');
            } else {
                modal.style.display = 'none';
                modal.classList.remove('show');
            }
        } catch(e) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }
    var backdrop = document.getElementById('modalBackdropFallback');
    if (backdrop) backdrop.remove();
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
}

function editarInscripcion(ins) {
    var modal = document.getElementById('modalInscripcion');
    if (!modal) {
        Swal.fire('Error', 'El formulario de inscripción no se encontró.', 'error');
        return;
    }
    
    document.getElementById('insc_id').value = ins.nis_inscripcion_id;
    document.getElementById('insc_alumno_id').value = ins.nis_alumno_id;
    document.getElementById('insc_grupo').value = ins.nis_grupo_id || '';
    document.getElementById('insc_notas').value = ins.nis_notas || '';
    document.getElementById('alumnoSelInfo').innerHTML = '<span class="badge badge-info">' + (ins.alu_nombres || '') + ' ' + (ins.alu_apellidos || '') + '</span>';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Inscripción';
    document.getElementById('formInscripcion').action = urlEditar;
    
    // Remover hold-transition
    document.body.classList.remove('hold-transition');
    
    // Mover modal si está dentro de content-wrapper
    if (modal.closest('.content-wrapper')) {
        document.body.appendChild(modal);
    }
    
    // Intentar con Bootstrap/jQuery
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
            jQuery('#modalInscripcion').modal('show');
            setTimeout(function() {
                if (modal.style.display !== 'block' && !modal.classList.contains('show')) {
                    abrirModalManual(modal);
                }
            }, 300);
            return;
        }
    } catch(e) {
        console.warn('Bootstrap modal falló, usando fallback:', e);
    }
    
    // Fallback manual
    abrirModalManual(modal);
}

function cancelarInscripcion(id) {
    Swal.fire({ title: '¿Cancelar inscripción?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
    }).then(function(r) { if (r.isConfirmed) window.location.href = '<?= url('natacion', 'inscripcion', 'cancelar') ?>&id=' + id; });
}

// Búsqueda AJAX de alumno
var timerAlumno;
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function() {
        jQuery('#buscarAlumnoInsc').on('input', function() {
            clearTimeout(timerAlumno);
            var q = jQuery(this).val();
            if (q.length < 2) return;
            timerAlumno = setTimeout(function() {
                jQuery.getJSON('<?= url('natacion', 'alumno', 'buscarRepresentante') ?>&q=' + encodeURIComponent(q), function(res) {
                    // Endpoint de búsqueda — adaptar si se crea uno específico
                });
            }, 300);
        });
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
