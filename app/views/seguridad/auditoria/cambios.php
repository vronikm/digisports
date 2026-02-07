<?php
/**
 * Vista: Logs de Cambios
 */
?>
<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-file-alt"></i> Logs de Cambios</h2>
    <form class="form-inline mb-3">
        <input type="text" name="entidad" class="form-control mr-2" placeholder="Entidad">
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
                <th>Tabla</th>
                <th>Operación</th>
                <th>Antes</th>
                <th>Después</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= !empty($log['aud_fecha_operacion']) ? date('d/m/Y H:i:s', strtotime($log['aud_fecha_operacion'])) : '' ?></td>
                <td><?= htmlspecialchars(($log['usu_nombres'] ?? '') . ' ' . ($log['usu_apellidos'] ?? '')) ?></td>
                <td><code><?= htmlspecialchars($log['aud_tabla'] ?? '') ?></code>
                    <?php if (!empty($log['aud_registro_id'])): ?>
                        <br><small>ID: <?= $log['aud_registro_id'] ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $op = $log['aud_operacion'] ?? '';
                    switch ($op) {
                        case 'INSERT': $badge = 'success'; break;
                        case 'UPDATE': $badge = 'warning'; break;
                        case 'DELETE': $badge = 'danger'; break;
                        default: $badge = 'secondary'; break;
                    }
                    ?>
                    <span class="badge badge-<?= $badge ?>"><?= htmlspecialchars($op) ?></span>
                </td>
                <td><pre class="mb-0" style="max-height:100px;overflow:auto;font-size:11px"><?= htmlspecialchars($log['aud_valores_anteriores'] ?? '-') ?></pre></td>
                <td><pre class="mb-0" style="max-height:100px;overflow:auto;font-size:11px"><?= htmlspecialchars($log['aud_valores_nuevos'] ?? '-') ?></pre></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($logs)): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'info',
                title: 'Sin registros de cambios',
                text: 'No hay registros de cambios para los filtros seleccionados.',
                confirmButtonText: 'Entendido'
            });
        });
        </script>
    <?php endif; ?>
</div>
