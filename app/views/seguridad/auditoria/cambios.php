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
                <th>Entidad</th>
                <th>Acción</th>
                <th>Antes</th>
                <th>Después</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['fecha'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['usuario'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['entidad'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['accion'] ?? '') ?></td>
                <td><pre><?= htmlspecialchars($log['antes'] ?? '') ?></pre></td>
                <td><pre><?= htmlspecialchars($log['despues'] ?? '') ?></pre></td>
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
