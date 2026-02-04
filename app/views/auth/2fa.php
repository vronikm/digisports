<?php
/**
 * Vista: Verificación de Código 2FA
 * @var string $csrf_token
 */
?>

<form method="POST" action="<?php echo url('core', 'auth', 'validate2fa') ?>" id="form2fa">
    <div class="text-center mb-4">
        <div style="font-size: 48px; color: var(--primary); margin-bottom: 15px;">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h2 style="font-size: 20px; color: var(--dark); margin-bottom: 5px;">
            Verificación en Dos Pasos
        </h2>
        <p style="color: var(--secondary); font-size: 13px;">
            Te hemos enviado un código a tu email registrado
        </p>
    </div>
    
    <div class="form-group">
        <label for="codigo" class="form-label">Código de Verificación</label>
        <input 
            type="text" 
            class="form-control text-center"
            id="codigo"
            name="codigo"
            placeholder="000000"
            maxlength="6"
            pattern="[0-9]{6}"
            required
            autofocus
            autocomplete="off"
            style="font-size: 32px; letter-spacing: 10px; font-weight: 700; font-family: 'Courier New', monospace;"
        >
        <small class="text-muted d-block mt-2">
            Ingresa los 6 dígitos del código enviado a tu email
        </small>
    </div>
    
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? '') ?>">
    
    <!-- Botón de envío -->
    <button type="submit" class="btn btn-auth btn-primary-auth mt-3">
        <i class="fas fa-check me-2"></i>
        Verificar Código
    </button>
    
    <!-- Enlace reenviar código -->
    <div class="mt-3 text-center">
        <p style="color: var(--secondary); font-size: 13px; margin-bottom: 10px;">
            ¿No recibiste el código?
        </p>
        <button type="button" id="resendBtn" class="btn btn-sm btn-link" style="color: var(--primary); text-decoration: none; padding: 0;">
            <i class="fas fa-redo me-1"></i>
            Reenviar código
        </button>
    </div>
</form>

<script>
    // Autoformatear código - solo números
    document.getElementById('codigo').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
    
    // Reenviar código
    document.getElementById('resendBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const btn = this;
        const originalText = btn.innerHTML;
        
        fetch('<?php echo url('core', 'auth', 'resend2fa') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'csrf_token=<?php echo htmlspecialchars($csrf_token ?? '') ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Código reenviado';
                btn.disabled = true;
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 3000);
            } else {
                alert('Error al reenviar: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al reenviar el código');
        });
    });
    
    // Validación al submit
    document.getElementById('form2fa').addEventListener('submit', function(e) {
        const codigo = document.getElementById('codigo').value.trim();
        
        if (!codigo || codigo.length !== 6) {
            e.preventDefault();
            alert('Por favor ingresa un código válido de 6 dígitos');
            return;
        }
    });
</script>
