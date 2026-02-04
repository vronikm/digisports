<?php
/**
 * Vista: Reset de Contraseña
 * @var string $csrf_token
 * @var string $token Token de recuperación
 */
?>

<form method="POST" action="<?php echo url('core', 'auth', 'procesarReset') ?>" id="formReset">
    <div class="text-center mb-4">
        <div style="font-size: 48px; color: var(--success); margin-bottom: 15px;">
            <i class="fas fa-lock-open"></i>
        </div>
        <h2 style="font-size: 20px; color: var(--dark); margin-bottom: 5px;">
            Restablecer Contraseña
        </h2>
        <p style="color: var(--secondary); font-size: 13px;">
            Crea una nueva contraseña segura
        </p>
    </div>
    
    <!-- Indicador de fortaleza -->
    <div id="passwordStrengthContainer" style="margin-bottom: 15px; display: none;">
        <div style="font-size: 12px; color: var(--secondary); margin-bottom: 5px;">
            Fortaleza de contraseña
        </div>
        <div style="background-color: #e2e8f0; border-radius: 6px; height: 6px; overflow: hidden;">
            <div id="passwordStrengthBar" style="width: 0%; height: 100%; background-color: var(--danger); border-radius: 6px; transition: all 0.3s ease;"></div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="password" class="form-label">Nueva Contraseña</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input 
                type="password" 
                class="form-control"
                id="password"
                name="password"
                placeholder="Ingrese nueva contraseña"
                required
                autofocus
            >
            <button class="btn btn-outline-secondary password-toggle" type="button">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small class="text-muted d-block mt-2">
            Mínimo 8 caracteres, con mayúsculas, minúsculas, números y caracteres especiales
        </small>
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
                placeholder="Confirma la nueva contraseña"
                required
            >
            <button class="btn btn-outline-secondary password-toggle" type="button">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    
    <!-- Requisitos de contraseña -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin: 15px 0; font-size: 12px;">
        <div style="color: var(--secondary); margin-bottom: 8px; font-weight: 600;">
            Requisitos:
        </div>
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <div id="req-length" style="color: var(--danger);">
                <i class="fas fa-times me-1"></i> Mínimo 8 caracteres
            </div>
            <div id="req-upper" style="color: var(--danger);">
                <i class="fas fa-times me-1"></i> Una letra mayúscula
            </div>
            <div id="req-lower" style="color: var(--danger);">
                <i class="fas fa-times me-1"></i> Una letra minúscula
            </div>
            <div id="req-number" style="color: var(--danger);">
                <i class="fas fa-times me-1"></i> Un número
            </div>
            <div id="req-special" style="color: var(--danger);">
                <i class="fas fa-times me-1"></i> Un carácter especial (!@#$%^&*)
            </div>
        </div>
    </div>
    
    <!-- Token CSRF y recuperación -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '') ?>">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? '') ?>">
    
    <!-- Botón de envío -->
    <button type="submit" class="btn btn-auth btn-primary-auth mt-3" id="submitBtn">
        <i class="fas fa-save me-2"></i>
        Guardar Nueva Contraseña
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
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Requisitos de contraseña
    const requirements = {
        length: /^.{8,}$/,
        upper: /[A-Z]/,
        lower: /[a-z]/,
        number: /[0-9]/,
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/
    };
    
    // Validación en tiempo real
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        // Mostrar/actualizar barra de fortaleza
        let strength = 0;
        let passedChecks = 0;
        
        for (const [key, regex] of Object.entries(requirements)) {
            const passed = regex.test(password);
            const element = document.getElementById('req-' + key);
            
            if (passed) {
                element.style.color = 'var(--success)';
                element.innerHTML = '<i class="fas fa-check me-1"></i> ' + element.textContent.substring(1);
                passedChecks++;
            } else {
                element.style.color = 'var(--danger)';
                element.innerHTML = '<i class="fas fa-times me-1"></i> ' + element.textContent.substring(1);
            }
        }
        
        strength = (passedChecks / 5) * 100;
        
        // Actualizar barra
        const strengthBar = document.getElementById('passwordStrengthBar');
        const container = document.getElementById('passwordStrengthContainer');
        
        if (password.length > 0) {
            container.style.display = 'block';
            strengthBar.style.width = strength + '%';
            
            if (strength < 33) {
                strengthBar.style.backgroundColor = 'var(--danger)';
            } else if (strength < 66) {
                strengthBar.style.backgroundColor = 'var(--warning)';
            } else {
                strengthBar.style.backgroundColor = 'var(--success)';
            }
        } else {
            container.style.display = 'none';
        }
        
        validateForm();
    });
    
    confirmInput.addEventListener('input', validateForm);
    
    // Validar formulario
    function validateForm() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        const allValid = Object.values(requirements).every(regex => regex.test(password));
        const match = password === confirm && password.length > 0;
        
        submitBtn.disabled = !(allValid && match);
    }
    
    // Submit del formulario
    document.getElementById('formReset').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return;
        }
        
        const allValid = Object.values(requirements).every(regex => regex.test(password));
        if (!allValid) {
            e.preventDefault();
            alert('La contraseña no cumple con todos los requisitos');
            return;
        }
    });
</script>
