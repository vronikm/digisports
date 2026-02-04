<?php
/**
 * Vista: Recuperación de Contraseña - Paso 1
 * @var string $csrf_token
 */
?>

<form method="POST" action="<?php echo url('core', 'auth', 'enviarRecuperacion') ?>" id="formRecuperar">
    <div class="text-center mb-4">
        <div style="font-size: 48px; color: var(--warning); margin-bottom: 15px;">
            <i class="fas fa-key"></i>
        </div>
        <h2 style="font-size: 20px; color: var(--dark); margin-bottom: 5px;">
            Recuperar Contraseña
        </h2>
        <p style="color: var(--secondary); font-size: 13px;">
            Ingresa tu email registrado y te enviaremos las instrucciones para restablecer tu contraseña
        </p>
    </div>
    
    <div class="form-group">
        <label for="email" class="form-label">Correo Electrónico</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-envelope"></i>
            </span>
            <input 
                type="email" 
                class="form-control"
                id="email"
                name="email"
                placeholder="usuario@ejemplo.com"
                required
                autofocus
                autocomplete="email"
            >
        </div>
        <small class="text-muted d-block mt-2">
            Te enviaremos un enlace seguro a este email para cambiar tu contraseña
        </small>
    </div>
    
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '') ?>">
    
    <!-- Botón de envío -->
    <button type="submit" class="btn btn-auth btn-primary-auth mt-4">
        <i class="fas fa-paper-plane me-2"></i>
        Enviar Instrucciones
    </button>
    
    <!-- Enlace volver a login -->
    <div class="mt-3 text-center">
        <a href="<?php echo url('core', 'auth', 'login') ?>" style="color: var(--secondary); text-decoration: none; font-size: 13px;">
            <i class="fas fa-arrow-left me-1"></i>
            Volver a Iniciar Sesión
        </a>
    </div>
</form>

<script>
    document.getElementById('formRecuperar').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        
        if (!email) {
            e.preventDefault();
            alert('Por favor ingresa tu email');
            return;
        }
        
        // Validación básica de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Por favor ingresa un email válido');
            return;
        }
    });
</script>
