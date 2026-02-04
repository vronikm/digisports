<?php
// Vista para resetear contraseña
?>
<h2>Resetear Contraseña</h2>
<form><!-- Formulario de reseteo --></form>

<?php
// Vista de confirmación para resetear contraseña
?>
<h2>¿Confirmar reseteo de contraseña?</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario_id ?? '') ?>">
    <button type="submit" class="btn btn-warning">Resetear</button>
    <a href="<?= url('seguridad', 'usuario', 'index') ?>" class="btn btn-secondary">Cancelar</a>
</form>