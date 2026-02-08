<?php
/**
 * Vista: Alertas de Seguridad
 */
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = 'Alertas de Seguridad';
$headerSubtitle = 'Eventos críticos e intentos sospechosos';
$headerIcon     = 'fas fa-bell';
$headerButtons  = [];
include __DIR__ . '/../partials/header.php';
?>
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
                <th>Mensaje</th>
                <th>Usuario</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alertas as $alerta): ?>
            <tr>
                <td><?= !empty($alerta['acc_fecha']) ? date('d/m/Y H:i:s', strtotime($alerta['acc_fecha'])) : '' ?></td>
                <td><span class="badge badge-danger"><?= htmlspecialchars($alerta['acc_tipo'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($alerta['acc_mensaje'] ?? 'Intento fallido') ?></td>
                <td><?= htmlspecialchars(($alerta['usu_nombres'] ?? '') . ' ' . ($alerta['usu_apellidos'] ?? '')) ?>
                    <br><small class="text-muted">@<?= htmlspecialchars($alerta['usu_username'] ?? 'desconocido') ?></small>
                </td>
                <td><code><?= htmlspecialchars($alerta['acc_ip'] ?? '') ?></code></td>
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
</section>
