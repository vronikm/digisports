<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($title ?? 'Bienvenido') ?> | DigiSports</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --success: #16a34a;
            --accent: #f59e0b;
        }
        
        * {
            font-family: 'Source Sans Pro', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
        }
        
        .welcome-header {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .hero-section {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.9);
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        .btn-login {
            background: white;
            color: var(--primary);
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-login:hover {
            background: var(--accent);
            color: white;
            transform: scale(1.05);
        }
        
        .btn-outline-light {
            border-radius: 50px;
            padding: 12px 35px;
            font-weight: 600;
        }
        
        .stats-section {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 60px 0;
        }
        
        .stat-item {
            text-align: center;
            color: white;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .modules-section {
            padding: 80px 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.1) 100%);
        }
        
        .footer-section {
            background: rgba(0,0,0,0.2);
            padding: 30px 0;
            color: rgba(255,255,255,0.8);
        }
        
        .brand-logo {
            font-size: 2rem;
            font-weight: 700;
            color: white;
        }
        
        .brand-logo i {
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="welcome-header py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="brand-logo">
                    <i class="fas fa-futbol"></i>
                    DigiSports
                </div>
                <div>
                    <a href="<?php echo url('core', 'auth', 'login') ?>" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Contenido -->
    <?php echo $content ?? '' ?>
    
    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        &copy; <?php echo date('Y') ?> DigiSports. Todos los derechos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        Versión 1.0.0 | 
                        <a href="#" class="text-white text-decoration-none">Términos</a> | 
                        <a href="#" class="text-white text-decoration-none">Privacidad</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
