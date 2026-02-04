<?php
/**
 * Vista: Alertas de Seguridad
 */
?>
<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-bell"></i> Alertas de Seguridad</h2>
    <form class="form-inline mb-3">
        <input type="text" name="tipo" class="form-control mr-2" placeholder="Tipo de alerta">
        <input type="date" name="fecha_desde" class="form-control mr-2">
        <input type="date" name="fecha_hasta" class="form-control mr-2">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Usuario</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alertas as $alerta): ?>
            <tr>
                <td><?= htmlspecialchars($alerta['fecha'] ?? '') ?></td>
                <td><?= htmlspecialchars($alerta['tipo'] ?? '') ?></td>
                <td><?= htmlspecialchars($alerta['descripcion'] ?? '') ?></td>
                <td><?= htmlspecialchars($alerta['usuario'] ?? '') ?></td>
                <td><?= htmlspecialchars($alerta['accion'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($alertas)): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'info',
                title: 'Sin alertas de seguridad',
                text: 'No hay alertas de seguridad para los filtros seleccionados.',
                confirmButtonText: 'Entendido'
            });
        });
        </script>
    <?php endif; ?>
</div>
