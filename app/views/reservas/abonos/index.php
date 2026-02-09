<?php
/**
 * DigiSports Arena — Vista: Listado de Monederos / Abonos
 * Muestra KPIs globales + listado con búsqueda y paginación
 */

$abonos        = $abonos ?? [];
$resumen       = $resumen ?? ['total_monederos'=>0,'activos'=>0,'saldo_total'=>0,'recargas_total'=>0,'consumos_total'=>0];
$buscar        = $buscar ?? '';
$estado        = $estado ?? '';
$pagina        = $pagina ?? 1;
$totalPaginas  = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;
$csrfToken     = $csrf_token ?? '';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-wallet mr-2 text-primary"></i>
                    Monedero / Abonos
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('reservas', 'abon', 'crear') ?>" class="btn btn-primary">
                        <i class="fas fa-plus-circle mr-1"></i> Nuevo Monedero
                    </a>
                    <a href="<?= url('reservas', 'abon', 'historial') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-history mr-1"></i> Historial
                    </a>
                    <a href="<?= url('reservas', 'abon', 'paquetes') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-gift mr-1"></i> Paquetes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI Resumen -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="info-box bg-gradient-primary">
                    <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Monederos Activos</span>
                        <span class="info-box-number"><?= (int)$resumen['activos'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Saldo Total</span>
                        <span class="info-box-number">$<?= number_format((float)$resumen['saldo_total'], 2) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Recargas</span>
                        <span class="info-box-number">$<?= number_format((float)$resumen['recargas_total'], 2) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Consumos</span>
                        <span class="info-box-number">$<?= number_format((float)$resumen['consumos_total'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="POST" action="<?= url('reservas', 'abon', 'index') ?>" class="row align-items-end" id="formFiltros">
                    <div class="col-md-5">
                        <label class="small text-muted">Buscar</label>
                        <input type="text" name="buscar" class="form-control" placeholder="Nombre, email, identificación..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="ACTIVO" <?= $estado === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                            <option value="VENCIDO" <?= $estado === 'VENCIDO' ? 'selected' : '' ?>>Vencido</option>
                            <option value="AGOTADO" <?= $estado === 'AGOTADO' ? 'selected' : '' ?>>Agotado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Buscar</button>
                        <a href="<?= url('reservas', 'abon', 'index') ?>" class="btn btn-secondary ml-1"><i class="fas fa-undo mr-1"></i> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    <?= $totalRegistros ?> monedero<?= $totalRegistros !== 1 ? 's' : '' ?> encontrado<?= $totalRegistros !== 1 ? 's' : '' ?>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($abonos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Saldo Disponible</th>
                                <th>Utilizado</th>
                                <th>Total Cargado</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($abonos as $a): ?>
                            <?php
                                $estadoMap = ['ACTIVO'=>'badge-success','VENCIDO'=>'badge-danger','AGOTADO'=>'badge-warning'];
                                $estadoClass = $estadoMap[$a['estado'] ?? ''] ?? 'badge-secondary';
                                $vencido = !empty($a['fecha_vencimiento']) && $a['fecha_vencimiento'] < date('Y-m-d');
                            ?>
                            <tr>
                                <td><?= $a['abono_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($a['cliente_nombre']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($a['cliente_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success" style="font-size: 1.1em;">
                                        $<?= number_format((float)$a['saldo_disponible'], 2) ?>
                                    </span>
                                </td>
                                <td>$<?= number_format((float)($a['monto_utilizado'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float)($a['monto_total'] ?? 0), 2) ?></td>
                                <td>
                                    <?php if (!empty($a['fecha_vencimiento'])): ?>
                                        <span class="<?= $vencido ? 'text-danger' : '' ?>">
                                            <?= date('d/m/Y', strtotime($a['fecha_vencimiento'])) ?>
                                        </span>
                                        <?php if ($vencido): ?>
                                            <i class="fas fa-exclamation-triangle text-danger ml-1" title="Vencido"></i>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin vencimiento</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $estadoClass ?>"><?= $a['estado'] ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('reservas', 'abon', 'ver', ['id' => $a['abono_id']]) ?>" 
                                           class="btn btn-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($a['estado'] === 'ACTIVO'): ?>
                                        <button type="button" class="btn btn-success btn-recargar" title="Recargar"
                                                data-id="<?= $a['abono_id'] ?>"
                                                data-nombre="<?= htmlspecialchars($a['cliente_nombre']) ?>"
                                                data-saldo="<?= $a['saldo_disponible'] ?>">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($totalPaginas > 1): ?>
                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('reservas', 'abon', 'index', ['pagina' => $i, 'buscar' => $buscar, 'estado' => $estado]) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-wallet fa-4x mb-3" style="opacity: .2"></i>
                    <p class="mb-2">No hay monederos registrados</p>
                    <a href="<?= url('reservas', 'abon', 'crear') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle mr-1"></i> Crear Primer Monedero
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- Modal Recarga Rápida -->
<div class="modal fade" id="modalRecarga" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formRecarga" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="abono_id" id="recarga_abono_id">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Recargar Monedero</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Cliente: <strong id="recarga_cliente_nombre"></strong><br>
                        Saldo actual: <strong class="text-success" id="recarga_saldo_actual"></strong>
                    </p>
                    <div class="form-group">
                        <label>Monto a recargar ($)</label>
                        <input type="number" name="monto" class="form-control form-control-lg text-center" 
                               min="1" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Forma de pago</label>
                        <select name="forma_pago" class="form-control">
                            <option value="EFECTIVO">💵 Efectivo</option>
                            <option value="TARJETA">💳 Tarjeta</option>
                            <option value="TRANSFERENCIA">🏦 Transferencia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nota (opcional)</label>
                        <input type="text" name="nota" class="form-control" placeholder="Referencia, comentario...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Confirmar Recarga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Abrir modal de recarga
    document.querySelectorAll('.btn-recargar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('recarga_abono_id').value = this.dataset.id;
            document.getElementById('recarga_cliente_nombre').textContent = this.dataset.nombre;
            document.getElementById('recarga_saldo_actual').textContent = '$' + parseFloat(this.dataset.saldo).toFixed(2);
            $('#modalRecarga').modal('show');
        });
    });

    // Enviar recarga por AJAX
    document.getElementById('formRecarga').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);

        fetch('<?= url('reservas', 'abon', 'recargar') ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success' || data.success) {
                Swal.fire('¡Recarga exitosa!', data.message || 'Saldo actualizado', 'success')
                    .then(function() { location.reload(); });
            } else {
                Swal.fire('Error', data.message || 'Error al recargar', 'error');
            }
        })
        .catch(function() {
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    });
});
</script>
