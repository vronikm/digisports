<?php
/**
 * Vista: Logs de Acceso
 */
?>
<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-history"></i> Logs de Acceso</h2>
    <form class="form-inline mb-3">
        <input type="text" name="usuario" class="form-control mr-2" placeholder="Usuario">
        <input type="date" name="fecha_desde" class="form-control mr-2">
        <input type="date" name="fecha_hasta" class="form-control mr-2">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>IP</th>
                <th>Tipo</th>
                <th>Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= !empty($log['acc_fecha']) ? date('d/m/Y H:i:s', strtotime($log['acc_fecha'])) : '' ?></td>
                <td><?= htmlspecialchars(($log['usu_nombres'] ?? '') . ' ' . ($log['usu_apellidos'] ?? '')) ?>
                    <br><small class="text-muted">@<?= htmlspecialchars($log['usu_username'] ?? '') ?></small>
                </td>
                <td><code><?= htmlspecialchars($log['acc_ip'] ?? '') ?></code></td>
                <td>
                    <?php
                    $tipo = $log['acc_tipo'] ?? '';
                    switch ($tipo) {
                        case 'LOGIN': $badge = 'success'; break;
                        case 'LOGOUT': $badge = 'info'; break;
                        case 'LOGIN_FAILED': $badge = 'danger'; break;
                        default: $badge = 'secondary'; break;
                    }
                    ?>
                    <span class="badge badge-<?= $badge ?>"><?= htmlspecialchars($tipo) ?></span>
                </td>
                <td>
                    <?php if (($log['acc_exito'] ?? 'S') === 'S'): ?>
                        <span class="badge badge-success">Éxito</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Fallido</span>
                    <?php endif; ?>
                    <?php if (!empty($log['acc_mensaje'])): ?>
                        <br><small><?= htmlspecialchars($log['acc_mensaje']) ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($logs)): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'info',
                title: 'Sin registros de acceso',
                text: 'No hay registros de acceso para los filtros seleccionados.',
                confirmButtonText: 'Entendido'
            });
        });
        </script>
    <?php endif; ?>
</div>
