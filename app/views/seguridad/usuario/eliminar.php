<?php
// Vista de confirmación para eliminar usuario
?>
<h2>¿Confirmar eliminación de usuario?</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario_id ?? '') ?>">
    <button type="submit" class="btn btn-danger">Eliminar</button>
    <a href="<?= url('seguridad', 'usuario', 'index') ?>" class="btn btn-secondary">Cancelar</a>
</form>