<?php
/**
 * Vista Gestión de Pagos - Lista de Alumnos
 * @var array $alumnos
 * @var array $totales
 * @var array $categorias
 * @var array $grupos
 * @var string $q
 * @var string $categoria_id
 * @var string $grupo_id
 * @var string $estado_pago
 * @var string $csrf_token
 * @var array  $modulo_actual
 */
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$totales     = $totales ?? [];
$alumnos     = $alumnos ?? [];

$totalCobrado   = $totales['PAGADO']    ?? 0;
$totalPendiente = ($totales['PENDIENTE'] ?? 0) + ($totales['VENCIDO'] ?? 0);
$totalMora      = $totales['VENCIDO']   ?? 0;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-futbol mr-2" style="color:<?= $moduloColor ?>"></i>Gestión de Pagos
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Pagos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Resumen financiero -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$<?= number_format($totalCobrado, 2) ?></h3>
                        <p>Total Cobrado</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>$<?= number_format($totalPendiente, 2) ?></h3>
                        <p>Pendiente de Cobro</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$<?= number_format($totalMora, 2) ?></h3>
                        <p>En Mora (Vencido)</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Buscar Alumno</h3>
            </div>
            <div class="card-body py-2">
                <form id="formFiltroPagos" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small">Buscar</label>
                        <input type="text" id="filtroQ" class="form-control form-control-sm"
                               placeholder="Nombres, apellidos, cédula..."
                               value="<?= htmlspecialchars($q ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small">Categoría</label>
                        <select id="filtroCategoriaId" class="form-control form-control-sm">
                            <option value="">— Todas —</option>
                            <?php foreach ($categorias ?? [] as $cat): ?>
                            <option value="<?= $cat['fct_categoria_id'] ?>"
                                <?= ($categoria_id ?? '') == $cat['fct_categoria_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['fct_nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Grupo</label>
                        <select id="filtroGrupoId" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <?php foreach ($grupos ?? [] as $g): ?>
                            <option value="<?= $g['fgr_grupo_id'] ?>"
                                <?= ($grupo_id ?? '') == $g['fgr_grupo_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['fgr_nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Estado de Pago</label>
                        <select id="filtroEstadoPago" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <option value="AL_DIA" <?= ($estado_pago ?? '') === 'AL_DIA' ? 'selected' : '' ?>>Al día</option>
                            <option value="MORA"   <?= ($estado_pago ?? '') === 'MORA'   ? 'selected' : '' ?>>En mora</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>
                        <a href="<?= url('futbol', 'pago', 'index') ?>" class="btn btn-sm btn-outline-secondary ml-1">
                            <i class="fas fa-times mr-1"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de alumnos -->
        <div class="card shadow-sm">
            <div class="card-header py-2" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Alumnos
                    <span class="badge badge-secondary ml-2"><?= count($alumnos) ?></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($alumnos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                    <p>No se encontraron alumnos con los filtros aplicados.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaAlumnosPagos">
                        <thead class="thead-light">
                            <tr>
                                <th width="110">Cédula</th>
                                <th>Nombres y Apellidos</th>
                                <th>Categoría / Grupo</th>
                                <th class="text-center" width="110">Estado Pago</th>
                                <th class="text-center" width="90">Beca/Desc.</th>
                                <th class="text-center" width="140">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $a): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($a['alu_identificacion'])): ?>
                                    <code class="small"><?= htmlspecialchars($a['alu_identificacion']) ?></code>
                                    <?php else: ?>
                                    <span class="text-muted small">Sin cédula</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($a['alu_nombres'] . ' ' . $a['alu_apellidos']) ?></strong>
                                    <?php if (($a['alu_estado'] ?? '') !== 'ACTIVO'): ?>
                                    <br><span class="badge badge-secondary badge-sm"><?= $a['alu_estado'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($a['categoria_nombre'])): ?>
                                    <span class="badge badge-sm" style="background:<?= htmlspecialchars($a['categoria_color'] ?? '#6c757d') ?>;color:white;">
                                        <?= htmlspecialchars($a['categoria_nombre']) ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (!empty($a['grupo_nombre'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($a['grupo_nombre']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($a['tiene_mora']): ?>
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i>En Mora</span>
                                    <?php else: ?>
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Al Día</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($a['tiene_descuento']): ?>
                                    <span class="badge badge-info" title="Tiene beca o descuento registrado">
                                        <i class="fas fa-tag mr-1"></i>Sí
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('futbol', 'pago', 'alumno') ?>&id=<?= $a['alu_alumno_id'] ?>"
                                       class="btn btn-sm btn-success"
                                       title="Registrar / Ver pagos">
                                        <i class="fas fa-dollar-sign mr-1"></i>Registrar Pago
                                    </a>
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

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function () {
    // Filtro: POST para preservar el parámetro ?r= del router encriptado
    document.getElementById('formFiltroPagos').addEventListener('submit', function (e) {
        e.preventDefault();
        var fields = [
            { n: 'q',            v: document.getElementById('filtroQ').value },
            { n: 'categoria_id', v: document.getElementById('filtroCategoriaId').value },
            { n: 'grupo_id',     v: document.getElementById('filtroGrupoId').value },
            { n: 'estado_pago',  v: document.getElementById('filtroEstadoPago').value },
        ];
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url('futbol', 'pago', 'index') ?>';
        fields.forEach(function (p) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = p.n; inp.value = p.v;
            form.appendChild(inp);
        });
        document.body.appendChild(form);
        form.submit();
    });

    if ($('#tablaAlumnosPagos tbody tr').length > 1) {
        $('#tablaAlumnosPagos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[1, 'asc']],
            responsive: true,
            columnDefs: [{ orderable: false, targets: [4, 5] }]
        });
    }
});
</script>
<?php $scripts = ob_get_clean(); ?>
