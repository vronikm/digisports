<?php
/**
 * Vista: Mostrar error genérico
 * @var string $error_message
 */
?>

<div class="error-icon">
    <i class="fas fa-exclamation-triangle"></i>
</div>

<h1 class="error-title">¡Oops! Algo salió mal</h1>

<p class="error-message">
    <?php echo htmlspecialchars($error_message ?? 'Ha ocurrido un error inesperado.') ?>
</p>

<a href="<?php echo Config::baseUrl() ?>" class="btn-home">
    <i class="fas fa-home me-2"></i>
    Volver al Inicio
</a>
