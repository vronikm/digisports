<?php
/**
 * Vista: Formulario de Login
 * @var string $csrf_token
 * @var array|null $error
 */
?>

<form method="POST" action="<?php echo url('core', 'auth', 'authenticate') ?>" id="loginForm">
    <div class="form-group">
        <label for="username" class="form-label">Usuario o Email</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-user"></i>
            </span>
            <input 
                type="text" 
                class="form-control" 
                id="username"
                name="username" 
                placeholder="Ingrese usuario o email"
                required
                autofocus
                autocomplete="username"
            >
        </div>
    </div>
    
    <div class="form-group">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input 
                type="password" 
                class="form-control" 
                id="password"
                name="password" 
                placeholder="Ingrese contraseña"
                required
                autocomplete="current-password"
            >
            <button class="btn btn-outline-secondary password-toggle" type="button">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    
    <div class="form-check">
        <input 
            class="form-check-input" 
            type="checkbox" 
            id="remember"
            name="remember"
            value="1"
        >
        <label class="form-check-label" for="remember">
            Recuérdame por 30 días
        </label>
    </div>
    
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '') ?>">
    
    <!-- Botón de envío -->
    <button type="submit" class="btn btn-auth btn-primary-auth mt-3">
        <i class="fas fa-sign-in-alt me-2"></i>
        Iniciar Sesión
    </button>
    
    <!-- Enlace de recuperación -->
    <div class="mt-3 text-center">
        <a href="<?php echo url('core', 'auth', 'recuperar') ?>" style="color: var(--secondary); text-decoration: none; font-size: 13px;">
            ¿Olvidaste tu contraseña?
        </a>
    </div>
</form>

<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        if (!username) {
            e.preventDefault();
            alert('El usuario o email es requerido');
            return;
        }
        
        if (!password) {
            e.preventDefault();
            alert('La contraseña es requerida');
            return;
        }
    });
</script>