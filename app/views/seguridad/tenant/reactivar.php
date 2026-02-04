<?php
// Vista para reactivar tenant
?>
<h2>Reactivar Tenant</h2>
<form><!-- Formulario de reactivación --></form>

<?php
// Vista de confirmación para reactivar tenant
?>
<h2>¿Confirmar reactivación de Tenant?</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($tenant_id ?? '') ?>">
    <button type="submit" class="btn btn-success">Reactivar</button>
    <a href="<?= url('seguridad', 'tenant', 'index') ?>" class="btn btn-secondary">Cancelar</a>
</form>