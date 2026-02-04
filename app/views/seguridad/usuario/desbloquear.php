<?php
// Vista de confirmación para desbloquear usuario
?>
<h2>¿Confirmar desbloqueo de usuario?</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario_id ?? '') ?>">
    <button type="submit" class="btn btn-success">Desbloquear</button>
    <a href="<?= url('seguridad', 'usuario', 'bloqueados') ?>" class="btn btn-secondary">Cancelar</a>
</form>