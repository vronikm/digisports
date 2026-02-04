<?php
// Vista para renovar tenant
?>
<h2>Renovar Tenant</h2>
<form><!-- Formulario de renovación --></form>

<?php
// Vista de confirmación para renovar tenant
?>
<h2>¿Confirmar renovación de Tenant?</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($tenant_id ?? '') ?>">
    <label>Meses a renovar:</label>
    <input type="number" name="meses" class="form-control" value="12" min="1" max="36">
    <br>
    <button type="submit" class="btn btn-success">Renovar</button>
    <a href="<?= url('seguridad', 'tenant', 'index') ?>" class="btn btn-secondary">Cancelar</a>
</form>