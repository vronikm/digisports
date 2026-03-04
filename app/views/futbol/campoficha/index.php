<?php
/**
 * DigiSports Fútbol - Campos de Ficha Personalizados
 * @vars $campos, $csrf_token, $modulo_actual
 */
$campos       = $campos ?? [];
$tipos_campo  = ['TEXT','NUMBER','SELECT','DATE','BOOLEAN','TEXTAREA'];
$moduloColor  = $modulo_actual['color'] ?? '#22C55E';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-sliders-h mr-2" style="color:<?= $moduloColor ?>"></i>Campos de Ficha</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><button class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;" onclick="abrirModal()"><i class="fas fa-plus mr-1"></i>Nuevo Campo</button></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>Configure los campos adicionales que aparecerán en la ficha de cada jugador. Estos campos son personalizables por empresa.</div>

        <div class="card shadow-sm">
            <div class="card-header py-2">
                <span class="badge badge-secondary"><?= count($campos) ?> campo(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($campos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-sliders-h fa-3x mb-3 opacity-50"></i>
                    <p>No hay campos personalizados</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaCampos">
                        <thead class="thead-light">
                            <tr>
                                <th width="60">Orden</th>
                                <th>Nombre (slug)</th>
                                <th>Etiqueta</th>
                                <th>Tipo</th>
                                <th>Opciones</th>
                                <th class="text-center">Requerido</th>
                                <th class="text-center">Activo</th>
                                <th width="130" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCampos">
                            <?php foreach ($campos as $c): ?>
                            <tr class="<?= !($c['fcf_activo'] ?? 1) ? 'table-secondary' : '' ?>" data-id="<?= $c['fcf_campo_id'] ?>">
                                <td class="text-center">
                                    <span class="badge badge-light"><?= (int)($c['fcf_orden'] ?? 0) ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($c['fcf_clave'] ?? '') ?></code></td>
                                <td><strong><?= htmlspecialchars($c['fcf_etiqueta'] ?? '') ?></strong></td>
                                <td><span class="badge badge-light"><?= htmlspecialchars($c['fcf_tipo'] ?? 'TEXT') ?></span></td>
                                <td>
                                    <?php
                                        $opciones = $c['fcf_opciones'] ?? '';
                                        if (!empty($opciones)) {
                                            $decoded = json_decode($opciones, true);
                                            $listOps = is_array($decoded) ? $decoded : explode(',', $opciones);
                                            foreach ($listOps as $op) {
                                                echo '<span class="badge badge-outline-secondary mr-1">' . htmlspecialchars(trim($op)) . '</span>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">—</span>';
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?= ($c['fcf_requerido'] ?? 0) ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-minus text-muted"></i>' ?>
                                </td>
                                <td class="text-center">
                                    <?= ($c['fcf_activo'] ?? 1) ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editarCampo(<?= json_encode($c) ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger" onclick="eliminarCampo(<?= $c['fcf_campo_id'] ?>, '<?= htmlspecialchars($c['fcf_etiqueta'] ?? $c['fcf_clave'] ?? '') ?>')" title="Desactivar"><i class="fas fa-trash"></i></button>
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
<div class="modal fade" id="modalCampo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCampo" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="cf_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-sliders-h mr-2"></i>Nuevo Campo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalCampo()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre (slug) <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="cf_nombre" class="form-control" required pattern="[a-z0-9_]+" title="Solo letras minúsculas, números y _">
                                <small class="text-muted">Identificador único, ej: posicion_juego</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Etiqueta <span class="text-danger">*</span></label>
                                <input type="text" name="etiqueta" id="cf_etiqueta" class="form-control" required>
                                <small class="text-muted">Nombre visible, ej: Posición de Juego</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select name="tipo" id="cf_tipo" class="form-control" onchange="toggleTipo()">
                                    <?php foreach ($tipos_campo as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Orden</label>
                                <input type="number" name="orden" id="cf_orden" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group pt-4">
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="cf_requerido" name="requerido" value="1">
                                    <label class="custom-control-label" for="cf_requerido">Requerido</label>
                                </div>
                                <div class="custom-control custom-checkbox mt-1">
                                    <input type="checkbox" class="custom-control-input" id="cf_activo" name="activo" value="1" checked>
                                    <label class="custom-control-label" for="cf_activo">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="divOpciones" style="display:none;">
                        <label>Opciones (separadas por coma)</label>
                        <textarea name="opciones" id="cf_opciones" class="form-control" rows="2" placeholder="Portero, Defensa, Mediocampista, Delantero"></textarea>
                        <small class="text-muted">Solo aplica cuando el tipo es SELECT.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="cerrarModalCampo()">Cancelar</button>
                    <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
var urlCrear  = '<?= url('futbol', 'campoficha', 'crear') ?>';
var urlEditar = '<?= url('futbol', 'campoficha', 'editar') ?>';

// Submit via AJAX con confirmación previa - VERSIÓN MEJORADA
var isSubmittingCampo = false;

// Usar DOMContentLoaded + MutationObserver para asegurar que el formulario exista
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEBUG] DOMContentLoaded ejecutado');
    
    function setupFormListener() {
        var form = document.getElementById('formCampo');
        console.log('[DEBUG] Buscando formulario #formCampo:', form);
        
        if (!form) {
            console.warn('[WARN] Formulario #formCampo no encontrado');
            return false;
        }
        
        // Remover listener anterior si existe
        form.removeEventListener('submit', handleFormSubmit);
        
        // Agregar nuevo listener
        form.addEventListener('submit', handleFormSubmit);
        console.log('[SUCCESS] Listener del formulario agregado correctamente');
        return true;
    }
    
    function handleFormSubmit(e) {
        console.log('[DEBUG] Evento submit capturado');
        e.preventDefault();
        e.stopPropagation();
        
        // Prevenir doble-click
        if (isSubmittingCampo) {
            console.log('[WARN] Ya hay un envío en curso, se ignoró');
            return false;
        }
        
        var cfId = document.getElementById('cf_id').value;
        var isEditing = cfId && cfId.length > 0;
        var titulo = isEditing ? '¿Guardar cambios?' : '¿Crear nuevo campo?';
        var texto = isEditing ? '¿Deseas confirmar la actualización de este campo?' : '¿Deseas crear este campo?';
        
        console.log('[DEBUG] Mostrando confirmación SweetAlert2:', { titulo, isEditing });
        
        // Mostrar confirmación con SweetAlert2
        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#22C55E',
            cancelButtonColor: '#6c757d',
            confirmButtonText: isEditing ? 'Sí, guardar' : 'Sí, crear',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(function(result) {
            console.log('[DEBUG] Resultado SweetAlert:', result.isConfirmed);
            
            if (result.isConfirmed) {
                isSubmittingCampo = true;
                var formData = new FormData(document.getElementById('formCampo'));
                var url = document.getElementById('formCampo').getAttribute('action');
                var params = new URLSearchParams(formData);
                
                console.log('[DEBUG] Enviando AJAX a:', url);
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: params.toString(),
                    contentType: 'application/x-www-form-urlencoded',
                    dataType: 'json',
                    success: function(res) {
                        console.log('[DEBUG] Respuesta AJAX:', res);
                        
                        if (res.success) {
                            cerrarModalCampo();
                            
                            // Toast de éxito
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: isEditing ? 'Campo actualizado correctamente' : 'Campo creado correctamente',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: function(toast) {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                }
                            });
                            
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            // Toast de error
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: res.message || 'No se pudo guardar el campo',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true,
                                didOpen: function(toast) {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                }
                            });
                        }
                        isSubmittingCampo = false;
                    },
                    error: function(xhr, status, error) {
                        console.log('[ERROR] AJAX Error:', { status, error });
                        
                        // Toast de error de conexión
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Error de conexión: ' + (xhr.statusText || 'Error desconocido'),
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true,
                            didOpen: function(toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                        isSubmittingCampo = false;
                    }
                });
            }
        });
    }
    
    // Intentar setup inmediatamente
    if (!setupFormListener()) {
        // Si falla, usar MutationObserver para esperar por el elemento
        console.log('[INFO] Esperando por elemento #formCampo...');
        var observer = new MutationObserver(function() {
            if (setupFormListener()) {
                observer.disconnect();
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});

function toggleTipo() {
    var tipo = document.getElementById('cf_tipo').value;
    document.getElementById('divOpciones').style.display = (tipo === 'SELECT') ? '' : 'none';
}

function abrirModal() {
    var modal = document.getElementById('modalCampo');
    if (!modal) {
        Swal.fire('Error', 'Modal no encontrado', 'error');
        return;
    }
    document.getElementById('formCampo').reset();
    document.getElementById('cf_id').value = '';
    document.getElementById('cf_activo').checked = true;
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-sliders-h mr-2"></i>Nuevo Campo';
    document.getElementById('formCampo').action = urlCrear;
    toggleTipo();
    document.body.classList.remove('hold-transition');
    
    var modalShown = false;
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
            jQuery('#modalCampo').modal('show');
            modalShown = true;
            setTimeout(function() {
                if (!modal.classList.contains('show')) {
                    abrirModalManual(modal);
                }
            }, 300);
            return;
        }
    } catch(e) {
        console.warn('Bootstrap modal falló:', e);
        modalShown = false;
    }
    
    abrirModalManual(modal);
}

function editarCampo(c) {
    var modal = document.getElementById('modalCampo');
    if (!modal) {
        Swal.fire('Error', 'Modal no encontrado', 'error');
        return;
    }
    document.getElementById('cf_id').value       = c.fcf_campo_id;
    document.getElementById('cf_nombre').value    = c.fcf_clave || '';
    document.getElementById('cf_etiqueta').value  = c.fcf_etiqueta || '';
    document.getElementById('cf_tipo').value      = c.fcf_tipo || 'TEXT';
    document.getElementById('cf_orden').value     = c.fcf_orden || 0;
    document.getElementById('cf_requerido').checked = !!parseInt(c.fcf_requerido);
    document.getElementById('cf_activo').checked    = !!parseInt(c.fcf_activo);

    var ops = c.fcf_opciones || '';
    if (ops) {
        try {
            var parsed = JSON.parse(ops);
            ops = Array.isArray(parsed) ? parsed.join(', ') : ops;
        } catch(e) { }
    }
    document.getElementById('cf_opciones').value = ops;

    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Campo';
    document.getElementById('formCampo').action = urlEditar;
    toggleTipo();
    document.body.classList.remove('hold-transition');
    
    var modalShown = false;
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
            jQuery('#modalCampo').modal('show');
            modalShown = true;
            setTimeout(function() {
                if (!modal.classList.contains('show')) {
                    abrirModalManual(modal);
                }
            }, 300);
            return;
        }
    } catch(e) {
        console.warn('Bootstrap modal falló:', e);
        modalShown = false;
    }
    
    abrirModalManual(modal);
}

function abrirModalManual(modal) {
    if (!modal) return;
    modal.style.display = 'block';
    modal.classList.add('show');
    
    var backdrop = document.querySelector('.modal-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
    
    if (!document.body.classList.contains('modal-open')) {
        document.body.style.overflow = 'hidden';
        document.body.classList.add('modal-open');
    }
}

function cerrarModalCampo() {
    var modal = document.getElementById('modalCampo');
    if (!modal) return;
    
    try {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.modal) {
            jQuery('#modalCampo').modal('hide');
            return;
        }
    } catch(e) {
        console.warn('Bootstrap modal falló al cerrar:', e);
    }
    
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    
    var backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

function eliminarCampo(id, nombre) {
    Swal.fire({
        title: '¿Desactivar campo?',
        html: 'Se desactivará el campo <strong>' + nombre + '</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = '<?= url('futbol', 'campoficha', 'eliminar') ?>&id=' + id;
    });
}

// Drag-to-reorder (si Sortable.js está disponible)
$(function() {
    if (typeof Sortable !== 'undefined' && document.getElementById('tbodyCampos')) {
        new Sortable(document.getElementById('tbodyCampos'), {
            animation: 150,
            handle: 'tr',
            onEnd: function() {
                var orden = [];
                $('#tbodyCampos tr').each(function(i) {
                    orden.push({ id: $(this).data('id'), orden: i + 1 });
                });
                $.post('<?= url('futbol', 'campoficha', 'reordenar') ?>', {
                    csrf_token: '<?= $csrf_token ?? '' ?>',
                    orden: JSON.stringify(orden)
                }, function(res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Orden actualizado', timer: 1500, showConfirmButton: false });
                    }
                }, 'json');
            }
        });
    }
});
</script>
<?php $scripts = ob_get_clean(); ?>
