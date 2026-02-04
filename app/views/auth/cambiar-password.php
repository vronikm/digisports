<?php
/**
 * Vista: Cambiar Contraseña (Usuario Autenticado)
 * @var string $csrf_token
 */
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Cambiar Contraseña
                    </h4>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo url('core', 'auth', 'cambiarPassword') ?>" id="formCambiarPassword">
                        
                        <!-- Contraseña Actual -->
                        <div class="form-group mb-3">
                            <label for="password_actual" class="form-label">Contraseña Actual</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control"
                                    id="password_actual"
                                    name="password_actual"
                                    placeholder="Ingresa tu contraseña actual"
                                    required
                                    autofocus
                                    autocomplete="current-password"
                                >
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Contraseña Nueva -->
                        <div class="form-group mb-3">
                            <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control"
                                    id="password_nueva"
                                    name="password_nueva"
                                    placeholder="Ingresa una nueva contraseña"
                                    required
                                >
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Mínimo 8 caracteres, con mayúsculas, minúsculas, números y caracteres especiales
                            </small>
                        </div>
                        
                        <!-- Confirmar Contraseña Nueva -->
                        <div class="form-group mb-3">
                            <label for="password_confirm" class="form-label">Confirmar Nueva Contraseña</label>
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
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Requisitos de Contraseña -->
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
                        
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '') ?>">
                        
                        <!-- Botones -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-save me-2"></i>
                                Guardar Cambios
                            </button>
                            <a href="<?php echo url('core', 'dashboard') ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password_nueva');
    const confirmInput = document.getElementById('password_confirm');
    const submitBtn = document.querySelector('button[type="submit"]');
    
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
        
        validateForm();
    });
    
    confirmInput.addEventListener('input', validateForm);
    
    // Validar formulario
    function validateForm() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        const passwordActual = document.getElementById('password_actual').value;
        
        const allValid = Object.values(requirements).every(regex => regex.test(password));
        const match = password === confirm && password.length > 0;
        const hasActual = passwordActual.length > 0;
        
        submitBtn.disabled = !(allValid && match && hasActual);
    }
    
    // Submit del formulario
    document.getElementById('formCambiarPassword').addEventListener('submit', function(e) {
        const passwordActual = document.getElementById('password_actual').value;
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (!passwordActual) {
            e.preventDefault();
            alert('Debes ingresar tu contraseña actual');
            return;
        }
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Las nuevas contraseñas no coinciden');
            return;
        }
        
        const allValid = Object.values(requirements).every(regex => regex.test(password));
        if (!allValid) {
            e.preventDefault();
            alert('La nueva contraseña no cumple con todos los requisitos');
            return;
        }
        
        if (passwordActual === password) {
            e.preventDefault();
            alert('La nueva contraseña no puede ser igual a la actual');
            return;
        }
    });
</script>
