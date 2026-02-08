<?php
// Vista para usuarios bloqueados
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = 'Usuarios Bloqueados';
$headerSubtitle = 'Cuentas de usuario bloqueadas por intentos fallidos';
$headerIcon     = 'fas fa-user-lock';
$headerButtons  = [
    ['url' => url('seguridad', 'usuario', 'index'), 'label' => 'Volver a Usuarios', 'icon' => 'fas fa-arrow-left', 'solid' => false],
];
include __DIR__ . '/../partials/header.php';
?>

<!-- Listado de usuarios bloqueados -->
    </div>
</section>