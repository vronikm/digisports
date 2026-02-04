<?php
/**
 * DigiSports - Dashboard Simple (sin vistas complejas)
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /digiSports/public/auth/login');
    exit;
}

define('BASE_PATH', dirname(dirname(__FILE__)));

require_once BASE_PATH . '/config/database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DigiSports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 20px;
        }
        .sidebar {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 10px;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line"></i> DigiSports
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['nombres']; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/digiSports/public/auth/logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="sidebar">
                    <h5><i class="fas fa-bars"></i> Menú</h5>
                    <a href="#"><i class="fas fa-home"></i> Dashboard</a>
                    <a href="#"><i class="fas fa-chart-bar"></i> Reportes</a>
                    <a href="#"><i class="fas fa-building"></i> Instalaciones</a>
                    <a href="#"><i class="fas fa-calendar"></i> Reservas</a>
                    <a href="#"><i class="fas fa-receipt"></i> Facturas</a>
                    <a href="#"><i class="fas fa-users"></i> Clientes</a>
                    <a href="#"><i class="fas fa-cog"></i> Configuración</a>
                </div>
            </div>

            <!-- Contenido -->
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-12">
                        <h2><i class="fas fa-tachometer-alt"></i> Dashboard Principal</h2>
                        <hr>
                    </div>
                </div>

                <!-- Tarjetas de Estadísticas -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="stat-label">Total de Usuarios</div>
                            <div class="stat-value" id="total-usuarios">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="stat-label">Total de Clientes</div>
                            <div class="stat-value" id="total-clientes">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="stat-label">Reservas del Mes</div>
                            <div class="stat-value" id="reservas-mes">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <div class="stat-label">Ingresos del Mes</div>
                            <div class="stat-value" id="ingresos-mes">$-</div>
                        </div>
                    </div>
                </div>

                <!-- Gráficas -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-chart-line"></i> Ingresos por Mes
                            </div>
                            <div class="card-body">
                                <canvas id="chartIngresos"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-chart-pie"></i> Distribución de Clientes
                            </div>
                            <div class="card-body">
                                <canvas id="chartClientes"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Tenant -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-info-circle"></i> Información del Tenant
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tenant ID:</strong> <?php echo $_SESSION['tenant_id']; ?></p>
                                        <p><strong>Usuario:</strong> <?php echo $_SESSION['username']; ?></p>
                                        <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Rol:</strong> <?php echo $_SESSION['role']; ?></p>
                                        <p><strong>Nivel de Acceso:</strong> <?php echo $_SESSION['nivel_acceso']; ?></p>
                                        <p><strong>Módulos Disponibles:</strong> <?php echo implode(', ', $_SESSION['modules']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASO 5 - Sistema de Reportes -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-chart-bar"></i> PASO 5 - Sistema de Reportes
                            </div>
                            <div class="card-body">
                                <p><strong>Estado:</strong> <span class="badge bg-success">✓ Activo</span></p>
                                <p><strong>Descripción:</strong> Sistema completo de reportes y análisis de datos con KPIs, gráficas interactivas y exportación a CSV.</p>
                                <div class="mt-3">
                                    <h6>Funcionalidades Disponibles:</h6>
                                    <ul>
                                        <li>📊 Dashboard de KPIs en tiempo real</li>
                                        <li>📈 Gráficas interactivas con Chart.js</li>
                                        <li>📋 Reportes de ingresos, facturas y clientes</li>
                                        <li>📥 Exportación de datos a CSV</li>
                                        <li>🔔 Alertas automáticas de anomalías</li>
                                        <li>📅 Filtros por período y rango de fechas</li>
                                    </ul>
                                </div>
                                <a href="?page=reportes" class="btn btn-success mt-3">
                                    <i class="fas fa-arrow-right"></i> Ir a Reportes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cargar datos
        async function loadDashboardData() {
            try {
                // Simular datos para demo
                document.getElementById('total-usuarios').textContent = '5';
                document.getElementById('total-clientes').textContent = '12';
                document.getElementById('reservas-mes').textContent = '8';
                document.getElementById('ingresos-mes').textContent = '$2,450.00';

                // Gráfica de ingresos
                const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');
                new Chart(ctxIngresos, {
                    type: 'line',
                    data: {
                        labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: [1200, 1900, 3800, 3908, 4800, 2450],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: true }
                        }
                    }
                });

                // Gráfica de clientes
                const ctxClientes = document.getElementById('chartClientes').getContext('2d');
                new Chart(ctxClientes, {
                    type: 'doughnut',
                    data: {
                        labels: ['Activos', 'Inactivos', 'Nuevos'],
                        datasets: [{
                            data: [65, 20, 15],
                            backgroundColor: ['#667eea', '#f093fb', '#4facfe']
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            } catch (error) {
                console.error('Error cargando datos:', error);
            }
        }

        // Ejecutar al cargar
        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>
</body>
</html>
