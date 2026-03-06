<?php
/**
 * DigiSports Fútbol - Gestión de Sedes
 */
$sedes       = $sedes ?? [];
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$sedeActiva  = $sede_activa ?? null;
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-building mr-2" style="color:<?= $moduloColor ?>"></i>Sedes</h1></div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($sedeActiva): ?>
                    <button class="btn btn-outline-warning btn-sm mr-2" id="btnLimpiarFiltro"><i class="fas fa-times mr-1"></i>Quitar filtro sede</button>
                    <?php endif; ?>
                    <button class="btn btn-sm" id="btnNuevaSede" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Nueva Sede</button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Resumen financiero mensual -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Resumen Financiero por Sede</h3>
                        <div class="card-tools">
                            <select id="resMes" class="form-control form-control-sm d-inline-block" style="width:auto;">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>><?= ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][$m] ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="resYear" class="form-control form-control-sm d-inline-block" style="width:auto;">
                                <?php for ($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                            <button class="btn btn-sm btn-primary ml-1" id="btnCargarResumen"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="resumenFinanciero">
                            <div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de sedes -->
        <?php if (empty($sedes)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-building fa-3x mb-3 opacity-50"></i>
            <p>No hay sedes registradas</p>
            <button class="btn btn-sm" id="btnNuevaSedaEmpty" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-plus mr-1"></i>Crear primera sede</button>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($sedes as $s): ?>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 <?= $sedeActiva == $s['sed_sede_id'] ? 'border-primary' : '' ?>" style="<?= $sedeActiva == $s['sed_sede_id'] ? 'border-width:2px;' : '' ?>">
                    <div class="card-header py-2 d-flex align-items-center" style="background:<?= $moduloColor ?>15;">
                        <h5 class="mb-0 flex-grow-1">
                            <i class="fas fa-building mr-1" style="color:<?= $moduloColor ?>"></i>
                            <?= htmlspecialchars($s['sed_nombre']) ?>
                            <?php if (($s['sed_es_principal'] ?? '') === 'S'): ?>
                            <span class="badge badge-warning ml-1" title="Sede Principal"><i class="fas fa-star"></i></span>
                            <?php endif; ?>
                        </h5>
                        <?php if ($s['sed_estado'] === 'A'): ?>
                        <span class="badge badge-success">Activa</span>
                        <?php else: ?>
                        <span class="badge badge-secondary">Inactiva</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body py-2">
                        <?php if (!empty($s['sed_direccion'])): ?>
                        <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($s['sed_direccion']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($s['sed_ciudad'])): ?>
                        <p class="text-muted small mb-2"><i class="fas fa-city mr-1"></i><?= htmlspecialchars($s['sed_ciudad']) ?></p>
                        <?php endif; ?>

                        <div class="row text-center mt-2">
                            <div class="col-3">
                                <div class="font-weight-bold" style="color:<?= $moduloColor ?>"><?= (int)($s['total_alumnos'] ?? 0) ?></div>
                                <small class="text-muted">Jugadores</small>
                            </div>
                            <div class="col-3">
                                <div class="font-weight-bold text-info"><?= (int)($s['total_canchas'] ?? 0) ?></div>
                                <small class="text-muted">Canchas</small>
                            </div>
                            <div class="col-3">
                                <div class="font-weight-bold text-success"><?= (int)($s['total_entrenadores'] ?? 0) ?></div>
                                <small class="text-muted">Entren.</small>
                            </div>
                            <div class="col-3">
                                <div class="font-weight-bold text-warning"><?= (int)($s['total_grupos'] ?? 0) ?></div>
                                <small class="text-muted">Grupos</small>
                            </div>
                        </div>

                        <hr class="my-2">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="text-success font-weight-bold">$<?= number_format((float)($s['ingresos_mes'] ?? 0), 2) ?></div>
                                <small class="text-muted">Ingresos Mes</small>
                            </div>
                            <div class="col-6">
                                <div class="text-danger font-weight-bold">$<?= number_format((float)($s['egresos_mes'] ?? 0), 2) ?></div>
                                <small class="text-muted">Egresos Mes</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer py-2 text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary js-seleccionar-sede" title="Filtrar por esta sede"
                                data-id="<?= $s['sed_sede_id'] ?>">
                                <i class="fas fa-filter mr-1"></i>Seleccionar
                            </button>
                            <button class="btn btn-outline-secondary js-editar-sede" title="Editar"
                                data-sede="<?= htmlspecialchars(json_encode($s, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if (($s['sed_es_principal'] ?? '') !== 'S'): ?>
                            <button class="btn btn-outline-danger js-desactivar-sede" title="Desactivar"
                                data-id="<?= $s['sed_sede_id'] ?>"
                                data-nombre="<?= htmlspecialchars($s['sed_nombre'], ENT_QUOTES) ?>">
                                <i class="fas fa-power-off"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Sede -->
<div class="modal fade" id="modalSede" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formSede" method="POST"
                data-url-crear="<?= url('futbol', 'sede', 'crear') ?>"
                data-url-editar="<?= url('futbol', 'sede', 'editar') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="sed_id">
                <div class="modal-header" style="background:<?= $moduloColor ?>;color:white;">
                    <h5 class="modal-title" id="modalTitulo"><i class="fas fa-building mr-2"></i>Nueva Sede</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group"><label>Nombre <span class="text-danger">*</span></label><input type="text" name="nombre" id="sed_nombre" class="form-control" required></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Código</label><input type="text" name="codigo" id="sed_codigo" class="form-control" maxlength="20" placeholder="Ej: SC01"></div>
                        </div>
                    </div>
                    <div class="form-group"><label>Dirección</label><input type="text" name="direccion" id="sed_direccion" class="form-control"></div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Ciudad</label><input type="text" name="ciudad" id="sed_ciudad" class="form-control"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Teléfono</label><input type="text" name="telefono" id="sed_telefono" class="form-control"></div></div>
                    </div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" id="sed_email" class="form-control"></div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="sed_principal" name="es_principal" value="1">
                        <label class="custom-control-label" for="sed_principal">Sede Principal</label>
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
<script nonce="<?= cspNonce() ?>">
$(function() {
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    var csrfToken = '<?= addslashes($csrf_token ?? '') ?>';
    var urlSeleccionar = '<?= url('futbol', 'sede', 'seleccionar') ?>';
    var urlEliminar    = '<?= url('futbol', 'sede', 'eliminar') ?>';
    var urlResumen     = '<?= url('futbol', 'sede', 'resumenFinanciero') ?>';

    // ── Abrir modal nuevo ──
    function abrirModalNueva() {
        $('#formSede')[0].reset();
        $('#sed_id').val('');
        $('#modalTitulo').html('<i class="fas fa-building mr-2"></i>Nueva Sede');
        $('#formSede').data('mode', 'crear');
        $('#modalSede').modal('show');
    }

    $('#btnNuevaSede, #btnNuevaSedaEmpty').on('click', abrirModalNueva);

    // ── Editar sede ──
    $(document).on('click', '.js-editar-sede', function() {
        var s = JSON.parse($(this).attr('data-sede'));
        $('#formSede')[0].reset();
        $('#sed_id').val(s.sed_sede_id);
        $('#sed_nombre').val(s.sed_nombre || '');
        $('#sed_codigo').val(s.sed_codigo || '');
        $('#sed_direccion').val(s.sed_direccion || '');
        $('#sed_ciudad').val(s.sed_ciudad || '');
        $('#sed_telefono').val(s.sed_telefono || '');
        $('#sed_email').val(s.sed_email || '');
        $('#sed_principal').prop('checked', s.sed_es_principal === 'S');
        $('#modalTitulo').html('<i class="fas fa-edit mr-2"></i>Editar Sede');
        $('#formSede').data('mode', 'editar');
        $('#modalSede').modal('show');
    });

    // ── Submit crear/editar (AJAX) ──
    $('#formSede').on('submit', function(e) {
        e.preventDefault();
        var mode   = $(this).data('mode') || 'crear';
        var action = $(this).attr(mode === 'editar' ? 'data-url-editar' : 'data-url-crear');
        var $btn   = $(this).find('[type=submit]').prop('disabled', true);
        $.post(action, $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalSede').modal('hide');
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                Toast.fire({ icon: 'error', title: res.message });
            }
        }, 'json').fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de comunicación' });
        }).always(function() { $btn.prop('disabled', false); });
    });

    // ── Seleccionar sede activa ──
    $(document).on('click', '.js-seleccionar-sede', function() {
        var id = $(this).data('id');
        $.post(urlSeleccionar, { id: id }, function() { location.reload(); }, 'json');
    });

    // ── Quitar filtro sede ──
    $('#btnLimpiarFiltro').on('click', function() {
        $.post(urlSeleccionar, { id: 0 }, function() { location.reload(); }, 'json');
    });

    // ── Desactivar sede ──
    $(document).on('click', '.js-desactivar-sede', function() {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        var $row   = $(this).closest('.col-lg-4, .col-md-6');
        Swal.fire({
            title: '¿Desactivar sede?',
            html: 'Se desactivará <strong>' + nombre + '</strong>',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            $.post(urlEliminar, { id: id, csrf_token: csrfToken }, function(res) {
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

    // ── Cargar resumen financiero ──
    function cargarResumen() {
        var mes  = $('#resMes').val();
        var year = $('#resYear').val();
        $('#resumenFinanciero').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando...</div>');
        $.getJSON(urlResumen + '&mes=' + mes + '&year=' + year, function(res) {
            if (!res.success || !res.data || !res.data.length) {
                $('#resumenFinanciero').html('<div class="text-center py-3 text-muted">Sin datos para el período seleccionado</div>');
                return;
            }
            var html = '<table class="table table-sm mb-0"><thead class="thead-light"><tr><th>Sede</th><th class="text-right text-success">Ingresos</th><th class="text-right text-danger">Egresos</th><th class="text-right">Utilidad</th></tr></thead><tbody>';
            var totI = 0, totE = 0;
            res.data.forEach(function(r) {
                var ing  = parseFloat(r.total_ingresos) || 0;
                var egr  = parseFloat(r.total_egresos) || 0;
                var util = parseFloat(r.utilidad) || 0;
                totI += ing; totE += egr;
                html += '<tr><td><strong>' + r.sed_nombre + '</strong></td>'
                      + '<td class="text-right text-success">$' + ing.toFixed(2) + '</td>'
                      + '<td class="text-right text-danger">$' + egr.toFixed(2) + '</td>'
                      + '<td class="text-right ' + (util >= 0 ? 'text-success' : 'text-danger') + ' font-weight-bold">$' + util.toFixed(2) + '</td></tr>';
            });
            html += '</tbody><tfoot class="thead-light"><tr><th>TOTAL</th>'
                  + '<th class="text-right text-success">$' + totI.toFixed(2) + '</th>'
                  + '<th class="text-right text-danger">$' + totE.toFixed(2) + '</th>'
                  + '<th class="text-right font-weight-bold ' + ((totI-totE) >= 0 ? 'text-success' : 'text-danger') + '">$' + (totI-totE).toFixed(2) + '</th>'
                  + '</tr></tfoot></table>';
            $('#resumenFinanciero').html(html);
        });
    }

    $('#btnCargarResumen').on('click', cargarResumen);
    cargarResumen();
});
</script>
<?php $scripts = ob_get_clean(); ?>
