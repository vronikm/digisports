<?php
/**
 * DigiSports Arena — Vista: Control de Entradas (Index)
 * Listado diario con KPIs, filtros y acciones rápidas
 */
$entradas       = $entradas ?? [];
$resumen        = $resumen ?? [];
$totalRegistros = $totalRegistros ?? 0;
$pagina         = $pagina ?? 1;
$totalPaginas   = $totalPaginas ?? 1;
$buscar         = $buscar ?? '';
$estado         = $estado ?? '';
$fecha          = $fecha ?? date('Y-m-d');

$tipoColors  = ['GENERAL'=>'primary','VIP'=>'warning','CORTESIA'=>'info','ABONADO'=>'success'];
$estadoColors = ['VENDIDA'=>'success','USADA'=>'secondary','ANULADA'=>'danger','VENCIDA'=>'dark'];
$estadoIcons  = ['VENDIDA'=>'fa-ticket-alt','USADA'=>'fa-door-open','ANULADA'=>'fa-ban','VENCIDA'=>'fa-clock'];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-ticket-alt mr-2 text-primary"></i> Control de Entradas</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= url('instalaciones', 'entrada', 'vender') ?>" class="btn btn-success mr-1">
                    <i class="fas fa-plus-circle mr-1"></i> Vender Entrada
                </a>
                <a href="<?= url('instalaciones', 'entrada', 'escanear') ?>" class="btn btn-info mr-1">
                    <i class="fas fa-qrcode mr-1"></i> Escanear
                </a>
                <a href="<?= url('instalaciones', 'entrada', 'tarifas') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-tags mr-1"></i> Tarifas
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI Cards -->
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h3>$<?= number_format($resumen['total_recaudado'] ?? 0, 2) ?></h3>
                        <p>Recaudado Hoy</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-primary">
                    <div class="inner">
                        <h3><?= (int)($resumen['total_entradas'] ?? 0) ?></h3>
                        <p>Entradas del Día</p>
                    </div>
                    <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-info">
                    <div class="inner">
                        <h3><?= (int)($resumen['vendidas'] ?? 0) ?></h3>
                        <p>Activas (sin usar)</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="small-box bg-gradient-secondary">
                    <div class="inner">
                        <h3><?= (int)($resumen['usadas'] ?? 0) ?></h3>
                        <p>Ingresaron</p>
                    </div>
                    <div class="icon"><i class="fas fa-door-open"></i></div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card card-outline card-primary">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
                <div class="card-tools">
                    <button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body py-2">
                <form method="POST" action="<?= url('instalaciones', 'entrada', 'index') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small mb-1">Fecha</label>
                        <input type="date" name="fecha" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="VENDIDA" <?= $estado === 'VENDIDA' ? 'selected' : '' ?>>Vendida</option>
                            <option value="USADA" <?= $estado === 'USADA' ? 'selected' : '' ?>>Usada</option>
                            <option value="ANULADA" <?= $estado === 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Cliente o código..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-3 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm mr-1 flex-fill">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="<?= url('instalaciones', 'entrada', 'index') ?>" class="btn btn-outline-secondary btn-sm" title="Hoy">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Entradas (<?= $totalRegistros ?>)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($entradas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay entradas para esta fecha</p>
                        <a href="<?= url('instalaciones', 'entrada', 'vender') ?>" class="btn btn-success">
                            <i class="fas fa-plus-circle mr-1"></i> Vender Primera Entrada
                        </a>
                    </div>
                <?php else: ?>
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width:50px">#</th>
                                <th>Código</th>
                                <th>Instalación</th>
                                <th>Cliente</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Hora</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width:120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entradas as $e): ?>
                            <?php
                                $tc = $tipoColors[$e['ent_tipo']] ?? 'secondary';
                                $ec = $estadoColors[$e['ent_estado']] ?? 'secondary';
                                $ei = $estadoIcons[$e['ent_estado']] ?? 'fa-circle';
                            ?>
                            <tr>
                                <td class="text-center text-muted"><?= $e['ent_entrada_id'] ?></td>
                                <td>
                                    <strong class="text-primary"><?= htmlspecialchars($e['ent_codigo']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($e['instalacion_nombre']) ?></td>
                                <td>
                                    <?= $e['cliente_nombre'] ? htmlspecialchars($e['cliente_nombre']) : '<em class="text-muted">Sin cliente</em>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $tc ?>"><?= $e['ent_tipo'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?= $e['ent_hora_entrada'] ? date('H:i', strtotime($e['ent_hora_entrada'])) : '—' ?>
                                </td>
                                <td class="text-right">
                                    <?php if ($e['ent_tipo'] === 'CORTESIA'): ?>
                                        <span class="text-muted">Cortesía</span>
                                    <?php else: ?>
                                        <strong>$<?= number_format($e['ent_total'], 2) ?></strong>
                                        <?php if ((float)$e['ent_monto_monedero'] > 0): ?>
                                            <br><small class="text-warning"><i class="fas fa-wallet"></i> $<?= number_format($e['ent_monto_monedero'], 2) ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $ec ?>">
                                        <i class="fas <?= $ei ?> mr-1"></i> <?= $e['ent_estado'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('instalaciones', 'entrada', 'ticket') ?>&id=<?= $e['ent_entrada_id'] ?>"
                                       class="btn btn-xs btn-info" title="Ver ticket">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                    <?php if ($e['ent_estado'] === 'VENDIDA'): ?>
                                        <button class="btn btn-xs btn-success btn-ingreso"
                                                data-id="<?= $e['ent_entrada_id'] ?>"
                                                data-codigo="<?= htmlspecialchars($e['ent_codigo']) ?>"
                                                title="Registrar ingreso">
                                            <i class="fas fa-door-open"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger btn-anular-entrada"
                                                data-id="<?= $e['ent_entrada_id'] ?>"
                                                data-codigo="<?= htmlspecialchars($e['ent_codigo']) ?>"
                                                title="Anular">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('instalaciones', 'entrada', 'index') ?>&pagina=<?= $i ?>&fecha=<?= $fecha ?>&estado=<?= $estado ?>&buscar=<?= urlencode($buscar) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
                <small class="text-muted float-left mt-1">Página <?= $pagina ?> de <?= $totalPaginas ?></small>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Registrar ingreso
document.querySelectorAll('.btn-ingreso').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var codigo = this.dataset.codigo;
        Swal.fire({
            title: '¿Registrar ingreso?',
            html: 'Entrada <strong>' + codigo + '</strong> pasará a <span class="badge badge-secondary">USADA</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="fas fa-door-open mr-1"></i> Registrar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var form = new FormData();
                form.append('entrada_id', id);
                fetch('<?= url('instalaciones', 'entrada', 'registrarIngreso') ?>', {
                    method: 'POST', body: form
                }).then(function(r){ return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire('¡Ingreso registrado!', data.message, 'success').then(function(){ location.reload(); });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    });
});

// Anular entrada
document.querySelectorAll('.btn-anular-entrada').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var codigo = this.dataset.codigo;
        Swal.fire({
            title: '¿Anular entrada?',
            html: 'Se anulará la entrada <strong>' + codigo + '</strong>',
            icon: 'warning',
            input: 'text',
            inputLabel: 'Motivo (opcional)',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-ban mr-1"></i> Anular',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var form = new FormData();
                form.append('entrada_id', id);
                form.append('motivo', result.value || '');
                fetch('<?= url('instalaciones', 'entrada', 'anular') ?>', {
                    method: 'POST', body: form
                }).then(function(r){ return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire('Anulada', data.message, 'success').then(function(){ location.reload(); });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    });
});
</script>
