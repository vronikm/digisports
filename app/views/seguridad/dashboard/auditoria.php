<?php
/**
 * Vista: Auditoría de Seguridad
 * Muestra el log de acciones de seguridad/auditoría del sistema
 */
$logs = $logs ?? [];
$filtros = $filtros ?? [];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-shield-alt text-warning"></i> Auditoría de Seguridad</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard', 'index') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item active">Auditoría</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filtros -->
        <div class="card card-outline card-warning collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="route" value="seguridad/dashboard/auditoria">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Acción</label>
                                <select name="accion" class="form-control form-control-sm">
                                    <option value="">— Todas —</option>
                                    <?php foreach (['CREATED','UPDATED','DELETED','CANCELLED','LOGIN','LOGOUT'] as $a): ?>
                                    <option value="<?= $a ?>" <?= ($filtros['accion'] ?? '') === $a ? 'selected' : '' ?>><?= $a ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Entidad</label>
                                <input type="text" name="entidad" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filtros['entidad'] ?? '') ?>"
                                       placeholder="Ej: reservas, canchas...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" name="fecha_desde" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-warning btn-sm btn-block">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de resultados -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Registro de Auditoría
                    <span class="badge badge-info ml-2"><?= count($logs) ?> registros</span>
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Tenant</th>
                            <th>Acción</th>
                            <th>Entidad</th>
                            <th>Registro</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2" style="opacity:.3"></i>
                                <p class="mb-0">No se encontraron registros de auditoría</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><small class="text-muted"><?= $log['aud_auditoria_id'] ?? '' ?></small></td>
                            <td>
                                <small><?= isset($log['aud_fecha_operacion']) ? date('d/m/Y H:i:s', strtotime($log['aud_fecha_operacion'])) : '-' ?></small>
                            </td>
                            <td>
                                <?php if (!empty($log['usu_nombres'])): ?>
                                    <i class="fas fa-user text-primary mr-1"></i>
                                    <?= htmlspecialchars($log['usu_nombres'] . ' ' . ($log['usu_apellidos'] ?? '')) ?>
                                <?php else: ?>
                                    <span class="text-muted">Sistema</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars($log['ten_nombre_comercial'] ?? '-') ?></small></td>
                            <td>
                                <?php
                                $badgeMap = [
                                    'CREATED' => 'success', 'UPDATED' => 'info', 'DELETED' => 'danger',
                                    'CANCELLED' => 'warning', 'LOGIN' => 'primary', 'LOGOUT' => 'secondary'
                                ];
                                $op = $log['aud_operacion'] ?? 'OTHER';
                                $badge = $badgeMap[$op] ?? 'dark';
                                ?>
                                <span class="badge badge-<?= $badge ?>"><?= htmlspecialchars($op) ?></span>
                            </td>
                            <td><code><?= htmlspecialchars($log['aud_tabla'] ?? '-') ?></code></td>
                            <td><small><?= $log['aud_registro_id'] ?? '-' ?></small></td>
                            <td>
                                <?php if (!empty($log['aud_datos_anteriores']) || !empty($log['aud_datos_nuevos'])): ?>
                                <button class="btn btn-xs btn-outline-info" 
                                        onclick="toggleDetail(this)"
                                        data-antes="<?= htmlspecialchars($log['aud_datos_anteriores'] ?? '{}') ?>"
                                        data-despues="<?= htmlspecialchars($log['aud_datos_nuevos'] ?? '{}') ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php else: ?>
                                <small class="text-muted">—</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- Modal de detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-code"></i> Detalle del Cambio</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-danger"><i class="fas fa-arrow-left"></i> Datos Anteriores</h6>
                        <pre id="datosAntes" class="bg-light p-2 rounded" style="max-height:300px;overflow:auto;font-size:12px"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success"><i class="fas fa-arrow-right"></i> Datos Nuevos</h6>
                        <pre id="datosDespues" class="bg-light p-2 rounded" style="max-height:300px;overflow:auto;font-size:12px"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDetail(btn) {
    var antes = btn.dataset.antes || '{}';
    var despues = btn.dataset.despues || '{}';
    try { antes = JSON.stringify(JSON.parse(antes), null, 2); } catch(e) {}
    try { despues = JSON.stringify(JSON.parse(despues), null, 2); } catch(e) {}
    document.getElementById('datosAntes').textContent = antes;
    document.getElementById('datosDespues').textContent = despues;
    $('#modalDetalle').modal('show');
}
</script>
