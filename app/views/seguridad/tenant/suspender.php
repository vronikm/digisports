<?php
// Vista de confirmación para suspender tenant
?>
<h2>¿Confirmar suspensión de Tenant?</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($tenant_id ?? '') ?>">
    <label>Motivo:</label>
    <input type="text" name="motivo" class="form-control" value="Suspendido por administrador">
    <br>
    <button type="submit" class="btn btn-danger">Suspender</button>
    <a href="<?= url('seguridad', 'tenant', 'index') ?>" class="btn btn-secondary">Cancelar</a>
</form>