<div class="container-fluid">
  <h2><?= htmlspecialchars($pageTitle) ?></h2>
  <form method="get" class="row g-2 mb-3">
    <div class="col-md-2">
      <input type="text" name="usuario_id" class="form-control" placeholder="ID Usuario" value="<?= htmlspecialchars($filtros['usuario_id'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input type="text" name="tenant_id" class="form-control" placeholder="ID Tenant" value="<?= htmlspecialchars($filtros['tenant_id'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input type="text" name="accion" class="form-control" placeholder="Acción" value="<?= htmlspecialchars($filtros['accion'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input type="text" name="entidad" class="form-control" placeholder="Entidad" value="<?= htmlspecialchars($filtros['entidad'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
  </form>
  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
      <thead class="table-dark">
        <tr>
          <th>Fecha</th>
          <th>Usuario</th>
          <th>Tenant</th>
          <th>Acción</th>
          <th>Entidad</th>
          <th>ID</th>
          <th>IP</th>
          <th>Resultado</th>
          <th>Mensaje</th>
          <th>Antes</th>
          <th>Después</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
          <td><?= htmlspecialchars($log['fecha']) ?></td>
          <td><?= htmlspecialchars($log['nombres'] . ' ' . $log['apellidos']) ?> (ID: <?= htmlspecialchars($log['usuario_id']) ?>)</td>
          <td><?= htmlspecialchars($log['nombre_comercial']) ?> (ID: <?= htmlspecialchars($log['tenant_id']) ?>)</td>
          <td><?= htmlspecialchars($log['accion']) ?></td>
          <td><?= htmlspecialchars($log['entidad']) ?></td>
          <td><?= htmlspecialchars($log['entidad_id']) ?></td>
          <td><?= htmlspecialchars($log['ip']) ?></td>
          <td><?= htmlspecialchars($log['resultado']) ?></td>
          <td><?= htmlspecialchars($log['mensaje']) ?></td>
          <td><pre style="max-width:300px;max-height:120px;overflow:auto;white-space:pre-wrap;"><?= htmlspecialchars($log['datos_antes']) ?></pre></td>
          <td><pre style="max-width:300px;max-height:120px;overflow:auto;white-space:pre-wrap;"><?= htmlspecialchars($log['datos_despues']) ?></pre></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>