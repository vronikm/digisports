<?php
/**
 * Vista: Página de Bienvenida / Landing
 * Muestra información del sistema y opción de login
 */
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="hero-title mb-4">
                    Sistema Integral de Gestión Deportiva
                </h1>
                <p class="hero-subtitle mb-4">
                    Administra tu centro deportivo de forma eficiente. 
                    Reservas, instalaciones, facturación y mucho más en una sola plataforma.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?php echo url('core', 'auth', 'login') ?>" class="btn btn-login btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Ingresar al Sistema
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>
                        Conocer más
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="position-relative">
                    <i class="fas fa-futbol" style="font-size: 15rem; color: rgba(255,255,255,0.2);"></i>
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <i class="fas fa-building" style="font-size: 4rem; color: white; margin: 10px;"></i>
                        <i class="fas fa-calendar-check" style="font-size: 4rem; color: white; margin: 10px;"></i>
                        <i class="fas fa-chart-line" style="font-size: 4rem; color: white; margin: 10px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="stat-item">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Centros Deportivos</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="stat-item">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Reservas Mensuales</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Usuarios Activos</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">Disponibilidad</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="modules-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-white mb-3" style="font-size: 2.5rem; font-weight: 700;">
                Módulos del Sistema
            </h2>
            <p class="text-white-50">
                Todo lo que necesitas para gestionar tu centro deportivo
            </p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($modulos)): ?>
                <?php foreach ($modulos as $modulo): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon" style="background: linear-gradient(135deg, <?= htmlspecialchars($modulo['color']) ?> 0%, <?= htmlspecialchars($modulo['color']) ?>99 100%);">
                                <i class="<?= htmlspecialchars($modulo['icono']) ?>"></i>
                            </div>
                            <h5 class="fw-bold mb-3"><?= htmlspecialchars($modulo['nombre']) ?></h5>
                            <p class="text-muted small mb-0">
                                <?= htmlspecialchars($modulo['descripcion'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback estático si no hay módulos en BD -->
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Instalaciones</h5>
                        <p class="text-muted small mb-0">
                            Gestiona canchas de fútbol, tenis, pádel, piscinas y más con tarifas flexibles.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Reservas</h5>
                        <p class="text-muted small mb-0">
                            Sistema de reservas por bloques horarios con confirmación automática.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Facturación</h5>
                        <p class="text-muted small mb-0">
                            Comprobantes electrónicos SRI, múltiples formas de pago.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Seguridad</h5>
                        <p class="text-muted small mb-0">
                            2FA, encriptación AES-256, auditoría completa y protección avanzada.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- CTA -->
        <div class="text-center mt-5">
            <a href="<?php echo url('core', 'auth', 'login') ?>" class="btn btn-login btn-lg px-5">
                <i class="fas fa-rocket me-2"></i>
                Comenzar Ahora
            </a>
        </div>
    </div>
</section>
