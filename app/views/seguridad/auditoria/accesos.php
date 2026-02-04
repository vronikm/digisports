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
                <th>Acción</th>
                <th>Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['fecha'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['usuario'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['ip'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['accion'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['resultado'] ?? '') ?></td>
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
