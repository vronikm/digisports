<?php
/**
 * Vista de Egresos - Módulo Fútbol
 * @vars $egresos, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$moduloColor = '#22C55E';
$moduloIcon = 'fas fa-futbol';

// Calcular total egresos del mes
$totalEgresosMes = 0;
$egresosPorCategoria = [];
if (!empty($egresos)) {
    $mesActual = date('Y-m');
    foreach ($egresos as $eg) {
        if (isset($eg['feg_fecha']) && substr($eg['feg_fecha'], 0, 7) === $mesActual) {
            $totalEgresosMes += floatval($eg['feg_monto'] ?? 0);
        }
        $cat = $eg['feg_categoria'] ?? 'OTROS';
        if (!isset($egresosPorCategoria[$cat])) $egresosPorCategoria[$cat] = 0;
        $egresosPorCategoria[$cat] += floatval($eg['feg_monto'] ?? 0);
    }
}

// Colores por categoría
$categoriasConfig = [
    'UNIFORMES' => ['color' => '#3B82F6', 'icon' => 'fas fa-tshirt'],
    'BALONES' => ['color' => '#22C55E', 'icon' => 'fas fa-futbol'],
    'ARBITRAJE' => ['color' => '#EF4444', 'icon' => 'fas fa-whistle'],
    'TRANSPORTE' => ['color' => '#F59E0B', 'icon' => 'fas fa-bus'],
    'CANCHAS' => ['color' => '#10B981', 'icon' => 'fas fa-map-marked-alt'],
    'ENTRENADORES' => ['color' => '#8B5CF6', 'icon' => 'fas fa-chalkboard-teacher'],
    'MATERIAL' => ['color' => '#06B6D4', 'icon' => 'fas fa-boxes'],
    'PREMIACION' => ['color' => '#F97316', 'icon' => 'fas fa-trophy'],
    'INSCRIPCION_TORNEO' => ['color' => '#EC4899', 'icon' => 'fas fa-clipboard-list'],
    'ALIMENTACION' => ['color' => '#84CC16', 'icon' => 'fas fa-utensils'],
    'OTROS' => ['color' => '#6B7280', 'icon' => 'fas fa-ellipsis-h']
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcon ?>" style="color: <?= $moduloColor ?>"></i>
                    Gestión de Egresos
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Egresos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Resumen -->
        <div class="row">
            <div class="col-lg-4 col-12">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$<?= number_format($totalEgresosMes, 2) ?></h3>
                        <p>Total Egresos del Mes</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-down"></i></div>
                </div>
            </div>
            <div class="col-lg-8 col-12">
                <div class="card">
                    <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                        <h3 class="card-title"><i class="fas fa-chart-pie"></i> Egresos por Categoría</h3>
                    </div>
                    <div class="card-body" style="height: 180px;">
                        <canvas id="chartEgresosCat"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtro y botón -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-control" id="filtroSede" onchange="filtrarPorSede(this.value)">
                    <option value="">Todas las sedes</option>
                    <?php if (!empty($sedes)): ?>
                        <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['sed_sede_id'] ?>" <?= (isset($sede_activa) && $sede_activa == $sede['sed_sede_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['sed_nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-9 text-right">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalEgreso" onclick="limpiarFormulario()">
                    <i class="fas fa-plus"></i> Nuevo Egreso
                </button>
            </div>
        </div>

        <!-- Tabla de Egresos -->
        <div class="card">
            <div class="card-header" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-receipt"></i> Listado de Egresos</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($egresos)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tablaEgresos">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>Descripción</th>
                                    <th>Categoría</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Comprobante</th>
                                    <th width="100">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($egresos as $i => $egreso): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($egreso['feg_concepto'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $cat = $egreso['feg_categoria'] ?? 'OTROS';
                                            $catConf = $categoriasConfig[$cat] ?? $categoriasConfig['OTROS'];
                                            ?>
                                            <span class="badge" style="background-color: <?= $catConf['color'] ?>; color: #fff;">
                                                <i class="<?= $catConf['icon'] ?>"></i> <?= str_replace('_', ' ', $cat) ?>
                                            </span>
                                        </td>
                                        <td class="font-weight-bold text-danger">$<?= number_format($egreso['feg_monto'] ?? 0, 2) ?></td>
                                        <td><?= isset($egreso['feg_fecha']) ? date('d/m/Y', strtotime($egreso['feg_fecha'])) : '' ?></td>
                                        <td><?= htmlspecialchars($egreso['feg_proveedor'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($egreso['feg_factura_ref'] ?? '') ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info" title="Editar"
                                                    onclick='editarEgreso(<?= json_encode($egreso) ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" title="Eliminar"
                                                    onclick="eliminarEgreso(<?= $egreso['feg_egreso_id'] ?? 0 ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x opacity-50 text-muted mb-3"></i>
                        <p class="text-muted">No hay egresos registrados.</p>
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalEgreso" onclick="limpiarFormulario()">
                            <i class="fas fa-plus"></i> Registrar primer egreso
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Egreso -->
<div class="modal fade" id="modalEgreso" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: <?= $moduloColor ?>; color: #fff;">
                <h5 class="modal-title" id="modalEgresoTitle">
                    <i class="<?= $moduloIcon ?>"></i> Nuevo Egreso
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEgreso" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="hidden" name="id" id="feg_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="feg_descripcion">Descripción <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="feg_descripcion" name="concepto" required placeholder="Descripción del gasto">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="feg_categoria">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control" id="feg_categoria" name="categoria" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="UNIFORMES">Uniformes</option>
                                    <option value="BALONES">Balones</option>
                                    <option value="ARBITRAJE">Arbitraje</option>
                                    <option value="TRANSPORTE">Transporte</option>
                                    <option value="CANCHAS">Canchas</option>
                                    <option value="ENTRENADORES">Entrenadores</option>
                                    <option value="MATERIAL">Material Deportivo</option>
                                    <option value="PREMIACION">Premiación</option>
                                    <option value="INSCRIPCION_TORNEO">Inscripción Torneo</option>
                                    <option value="ALIMENTACION">Alimentación</option>
                                    <option value="OTROS">Otros</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="feg_monto">Monto ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="feg_monto" name="monto" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="feg_fecha">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="feg_fecha" name="fecha" required value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="feg_proveedor">Proveedor</label>
                                <input type="text" class="form-control" id="feg_proveedor" name="proveedor" placeholder="Nombre del proveedor">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="feg_comprobante_num">Nro. Comprobante</label>
                                <input type="text" class="form-control" id="feg_comprobante_num" name="factura_ref" placeholder="Número de factura/recibo">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="feg_notas">Notas</label>
                                <textarea class="form-control" id="feg_notas" name="notas" rows="2" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // DataTable
    if ($('#tablaEgresos tbody tr').length > 0 && !$('#tablaEgresos tbody .text-center').length) {
        $('#tablaEgresos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[4, 'desc']],
            responsive: true
        });
    }

    // Chart de egresos por categoría
    var ctxCat = document.getElementById('chartEgresosCat');
    if (ctxCat) {
        var dataCat = <?= json_encode(array_values($egresosPorCategoria)) ?>;
        var labelsCat = <?= json_encode(array_map(function($k) { return str_replace('_', ' ', $k); }, array_keys($egresosPorCategoria))) ?>;
        var colorsCat = <?= json_encode(array_map(function($k) use ($categoriasConfig) { return ($categoriasConfig[$k] ?? $categoriasConfig['OTROS'])['color']; }, array_keys($egresosPorCategoria))) ?>;

        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: labelsCat,
                datasets: [{
                    data: dataCat,
                    backgroundColor: colorsCat,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }

    // Submit
    $('#formEgreso').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var id = $('#feg_id').val();
        var urlAction = id
            ? '<?= url("futbol", "egreso", "editar") ?>'
            : '<?= url("futbol", "egreso", "crear") ?>';

        $.post(urlAction, formData, function(response) {
            if (response.success) {
                Swal.fire('¡Éxito!', response.message || 'Egreso guardado correctamente.', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'No se pudo guardar el egreso.', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        });
    });
});

function limpiarFormulario() {
    $('#formEgreso')[0].reset();
    $('#feg_id').val('');
    $('#feg_fecha').val('<?= date("Y-m-d") ?>');
    $('#feg_categoria').val('');
    $('#modalEgresoTitle').html('<i class="<?= $moduloIcon ?>"></i> Nuevo Egreso');
}

function editarEgreso(obj) {
    limpiarFormulario();
    $('#feg_id').val(obj.feg_egreso_id);
    $('#feg_descripcion').val(obj.feg_concepto || '');
    $('#feg_categoria').val(obj.feg_categoria || '');
    $('#feg_monto').val(obj.feg_monto || '');
    $('#feg_fecha').val(obj.feg_fecha || '');
    $('#feg_proveedor').val(obj.feg_proveedor || '');
    $('#feg_comprobante_num').val(obj.feg_factura_ref || '');
    $('#feg_notas').val(obj.feg_notas || '');
    $('#modalEgresoTitle').html('<i class="<?= $moduloIcon ?>"></i> Editar Egreso');
    $('#modalEgreso').modal('show');
}

function eliminarEgreso(id) {
    Swal.fire({
        title: '¿Anular egreso?',
        text: 'El egreso será marcado como anulado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url("futbol", "egreso", "anular") ?>&id=' + id;
        }
    });
}

function filtrarPorSede(sedeId) {
    $.post('<?= url("futbol", "sede", "seleccionar") ?>', { sede_id: sedeId, csrf_token: '<?= $csrf_token ?? "" ?>' }, function() { location.reload(); }, 'json');
}
</script>
<?php $scripts = ob_get_clean(); ?>
