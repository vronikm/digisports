<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($title ?? 'DigiSports') ?> - DigiSports</title>
    
    <!-- Bootstrap 5.x -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6.x -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2F39F2;
            --primary-dark: #2C285B;
            --secondary: #64748b;
            --success: #08DC64;
            --danger: #dc2626;
            --warning: #ea580c;
            --light: #ECE1D4;
            --dark: #2C285B;
            --digitech-blue: #2F39F2;
            --digitech-green: #08DC64;
            --digitech-navy: #2C285B;
            --digitech-beige: #ECE1D4;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--digitech-blue) 0%, var(--digitech-navy) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--digitech-blue) 0%, var(--digitech-navy) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .auth-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .auth-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        .auth-logo {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--digitech-green);
        }
        
        .auth-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control,
        .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--digitech-blue);
            box-shadow: 0 0 0 3px rgba(47, 57, 242, 0.1);
        }
        
        .form-control::placeholder {
            color: #cbd5e1;
        }
        
        .btn-auth {
            width: 100%;
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-primary-auth {
            background: linear-gradient(135deg, var(--digitech-blue) 0%, var(--digitech-navy) 100%);
            color: white;
        }
        
        .btn-primary-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 57, 242, 0.3);
            color: white;
        }
        
        .form-check {
            margin-bottom: 10px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            accent-color: var(--digitech-blue);
        }
        
        .form-check-label {
            font-size: 14px;
            color: var(--secondary);
            cursor: pointer;
        }
        
        .auth-footer {
            padding: 20px 30px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 14px;
        }
        
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .invalid-feedback {
            display: block;
            font-size: 12px;
            color: var(--danger);
            margin-top: 5px;
        }
        
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: var(--danger);
        }
        
        .input-group-text {
            background: white;
            border: 1px solid #e2e8f0;
            border-right: none;
            color: var(--secondary);
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group .form-control:focus {
            border-left: 1px solid var(--primary);
        }
        
        .password-toggle {
            cursor: pointer;
            background: none;
            border: none;
            color: var(--secondary);
            padding: 0;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .btn-auth.loading {
            position: relative;
            color: transparent;
        }
        
        .btn-auth.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 576px) {
            .auth-container {
                padding: 10px;
            }
            
            .auth-header {
                padding: 30px 20px;
            }
            
            .auth-header h1 {
                font-size: 24px;
            }
            
            .auth-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-futbol"></i>
                </div>
                <h1>DigiSports</h1>
                <p><?php echo htmlspecialchars($title ?? '') ?></p>
            </div>
            
            <div class="auth-body">
                <?php 
                // Mostrar mensajes flash
                $flashMessage = getFlashMessage();
                if ($flashMessage): 
                ?>
                    <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type'] ?>" role="alert">
                        <i class="fas fa-<?php echo $flashMessage['type'] === 'error' ? 'exclamation-circle' : ($flashMessage['type'] === 'success' ? 'check-circle' : 'info-circle') ?> me-2"></i>
                        <?php echo htmlspecialchars($flashMessage['message']) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($warning)): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-clock me-2"></i>
                        <?php echo htmlspecialchars($warning) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Incluir vista específica -->
                <?php echo isset($content) ? $content : '' ?>
            </div>
            
            <div class="auth-footer">
                <?php if (strpos($title ?? '', 'Iniciar') !== false): ?>
                    ¿No tienes cuenta? <a href="<?php echo url('core', 'auth', 'register') ?>">Registrarse</a>
                <?php endif; ?>
                
                <?php if (strpos($title ?? '', 'Registr') !== false): ?>
                    ¿Ya tienes cuenta? <a href="<?php echo url('core', 'auth', 'login') ?>">Inicia sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prevenir doble submit
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const btn = form.querySelector('[type="submit"]');
                    if (btn && !btn.disabled) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            });
            
            // Toggle de contraseña
            document.querySelectorAll('.password-toggle').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const input = this.closest('.input-group').querySelector('input');
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    } else {
                        input.type = 'password';
                        this.innerHTML = '<i class="fas fa-eye"></i>';
                    }
                });
            });
            
            // Auto-ocultar alertas
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (alert.querySelector('.alert-success')) {
                        alert.style.transition = 'opacity 0.3s ease';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }
                });
            }, 5000);
        });
    </script>
</body>
</html>