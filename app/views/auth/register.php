<?php
/**
 * Vista: Registro de Nuevo Tenant
 * @var string $csrf_token
 */
?>

<form method="POST" action="<?php echo url('core', 'auth', 'crearTenant') ?>" id="formRegister">
    <div class="text-center mb-4">
        <div style="font-size: 48px; color: var(--primary); margin-bottom: 15px;">
            <i class="fas fa-user-plus"></i>
        </div>
        <h2 style="font-size: 20px; color: var(--dark); margin-bottom: 5px;">
            Crear Nueva Cuenta
        </h2>
        <p style="color: var(--secondary); font-size: 13px;">
            Accede a DigiSports en 2 minutos
        </p>
    </div>
    
    <!-- Datos de la Empresa -->
    <h5 style="color: var(--dark); font-size: 14px; font-weight: 600; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
        Datos de la Empresa
    </h5>
    
    <div class="form-group">
        <label for="empresa" class="form-label">Nombre de la Empresa</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-building"></i>
            </span>
            <input 
                type="text" 
                class="form-control"
                id="empresa"
                name="empresa"
                placeholder="Ej: Centro Deportivo El Futuro"
                required
                autofocus
            >
        </div>
    </div>
    
    <div class="form-group">
        <label for="ruc" class="form-label">RUC</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-id-card"></i>
            </span>
            <input 
                type="text" 
                class="form-control"
                id="ruc"
                name="ruc"
                placeholder="Ej: 1234567890001"
                pattern="[0-9\-]{10,13}"
                required
            >
        </div>
        <small class="text-muted d-block mt-2">
            RUC de 13 dígitos sin guiones (se valida automáticamente)
        </small>
    </div>
    
    <div class="form-group">
        <label for="email" class="form-label">Email Corporativo</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-envelope"></i>
            </span>
            <input 
                type="email" 
                class="form-control"
                id="email"
                name="email"
                placeholder="empresa@ejemplo.com"
                required
            >
        </div>
    </div>
    
    <div class="form-group">
        <label for="telefono" class="form-label">Teléfono</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-phone"></i>
            </span>
            <input 
                type="tel" 
                class="form-control"
                id="telefono"
                name="telefono"
                placeholder="Ej: +593 2 1234567"
            >
        </div>
    </div>
    
    <!-- Datos del Administrador -->
    <h5 style="color: var(--dark); font-size: 14px; font-weight: 600; margin-bottom: 15px; padding-top: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
        Datos del Administrador
    </h5>
    
    <div class="form-group">
        <label for="nombres" class="form-label">Nombres</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-user"></i>
            </span>
            <input 
                type="text" 
                class="form-control"
                id="nombres"
                name="nombres"
                placeholder="Juan"
                required
            >
        </div>
    </div>
    
    <div class="form-group">
        <label for="apellidos" class="form-label">Apellidos</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-user"></i>
            </span>
            <input 
                type="text" 
                class="form-control"
                id="apellidos"
                name="apellidos"
                placeholder="Pérez López"
                required
            >
        </div>
    </div>
    
    <div class="form-group">
        <label for="username" class="form-label">Usuario (para login)</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-at"></i>
            </span>
            <input 
                type="text" 
                class="form-control"
                id="username"
                name="username"
                placeholder="juan_perez"
                pattern="[a-z0-9_]{4,}"
                required
            >
        </div>
        <small class="text-muted d-block mt-2">
            Mínimo 4 caracteres, solo letras minúsculas, números y guiones bajos
        </small>
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
                placeholder="Contraseña segura"
                required
            >
            <button class="btn btn-outline-secondary password-toggle" type="button">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    
    <div class="form-group">
        <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input 
                type="password" 
                class="form-control"
                id="password_confirm"
                name="password_confirm"
                placeholder="Confirma tu contraseña"
                required
            >
            <button class="btn btn-outline-secondary password-toggle" type="button">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    
    <!-- Términos -->
    <div class="form-check mt-3 mb-3">
        <input 
            class="form-check-input" 
            type="checkbox" 
            id="terms"
            name="terms"
            required
        >
        <label class="form-check-label" for="terms" style="font-size: 12px;">
            Acepto los <a href="#" style="color: var(--primary);">términos y condiciones</a> de DigiSports
        </label>
    </div>
    
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '') ?>">
    
    <!-- Botón de envío -->
    <button type="submit" class="btn btn-auth btn-primary-auth mt-2">
        <i class="fas fa-user-check me-2"></i>
        Crear Cuenta Gratuita
    </button>
    
    <!-- Nota importante -->
    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; margin-top: 15px; font-size: 12px; color: #166534;">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Plan Gratuito:</strong> 30 días de acceso completo sin costo
    </div>
</form>

<script>
    document.getElementById('formRegister').addEventListener('submit', function(e) {
        const empresa = document.getElementById('empresa').value.trim();
        const ruc = document.getElementById('ruc').value.trim();
        const email = document.getElementById('email').value.trim();
        const nombres = document.getElementById('nombres').value.trim();
        const apellidos = document.getElementById('apellidos').value.trim();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        const terms = document.getElementById('terms').checked;
        
        // Validaciones
        if (!empresa || empresa.length < 3) {
            e.preventDefault();
            alert('El nombre de la empresa debe tener al menos 3 caracteres');
            return;
        }
        
        // Validar RUC (formato básico)
        if (!ruc || !/^[0-9]{13}$/.test(ruc.replace(/\-/g, ''))) {
            e.preventDefault();
            alert('El RUC debe tener 13 dígitos');
            return;
        }
        
        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Por favor ingresa un email válido');
            return;
        }
        
        // Validar nombres y apellidos
        if (!nombres || nombres.length < 2) {
            e.preventDefault();
            alert('Los nombres son requeridos');
            return;
        }
        
        if (!apellidos || apellidos.length < 2) {
            e.preventDefault();
            alert('Los apellidos son requeridos');
            return;
        }
        
        // Validar usuario
        if (!username || !/^[a-z0-9_]{4,}$/.test(username)) {
            e.preventDefault();
            alert('El usuario debe tener mínimo 4 caracteres (solo letras minúsculas, números y _)');
            return;
        }
        
        // Validar contraseña
        if (!password || password.length < 8) {
            e.preventDefault();
            alert('La contraseña debe tener mínimo 8 caracteres');
            return;
        }
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return;
        }
        
        // Validar términos
        if (!terms) {
            e.preventDefault();
            alert('Debes aceptar los términos y condiciones');
            return;
        }
    });
</script>
