<?php
/**
 * DigiSports Seguridad — Header Premium Reutilizable
 * 
 * Variables esperadas:
 *   $headerTitle    (string)  — Título principal (ej: "Módulos del Sistema")
 *   $headerSubtitle (string)  — Subtítulo (ej: "Gestión de subsistemas y aplicaciones")
 *   $headerIcon     (string)  — Clase FontAwesome (ej: "fas fa-puzzle-piece")
 *   $headerButtons  (array)   — Botones de acción [['url'=>..., 'label'=>..., 'icon'=>..., 'solid'=>bool], ...]
 *   $moduloColor    (string)  — Color del módulo (heredado del layout)
 */

$headerTitle    = $headerTitle    ?? ($pageTitle ?? 'Seguridad');
$headerSubtitle = $headerSubtitle ?? '';
$headerIcon     = $headerIcon     ?? ($moduloIcono ?? 'fas fa-shield-alt');
$headerButtons  = $headerButtons  ?? [];
$_hColor        = $moduloColor    ?? '#F59E0B';
?>

<style>
/* ═══ Header Premium — Estilos Reutilizables ═══ */
.dg-header {
    background: linear-gradient(135deg, <?= $_hColor ?> 0%, <?= $_hColor ?>dd 50%, <?= $_hColor ?>bb 100%);
    border-radius: 18px; padding: 1.75rem 2rem; margin-bottom: 1.5rem;
    color: white; position: relative; overflow: hidden;
    box-shadow: 0 4px 20px <?= $_hColor ?>30;
}
.dg-header::before {
    content: ''; position: absolute; top: -50%; right: -6%;
    width: 280px; height: 280px; background: rgba(255,255,255,0.06); border-radius: 50%;
    pointer-events: none;
}
.dg-header::after {
    content: ''; position: absolute; bottom: -60%; left: -4%;
    width: 200px; height: 200px; background: rgba(255,255,255,0.04); border-radius: 50%;
    pointer-events: none;
}
.dg-header .hdr-icon-circle {
    width: 52px; height: 52px; border-radius: 14px;
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.15);
}
.dg-header .hdr-text { position: relative; z-index: 1; }
.dg-header .hdr-text h1 {
    font-size: 1.35rem; font-weight: 700; margin: 0; line-height: 1.3;
    letter-spacing: -0.01em;
}
.dg-header .header-sub {
    opacity: 0.82; font-size: 0.82rem; margin-top: 3px;
    font-weight: 400;
}
.dg-header .hdr-btns {
    position: relative; z-index: 1;
    display: flex; flex-wrap: wrap; gap: 8px;
    justify-content: flex-end;
}
.dg-header .btn-h {
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
    color: white; border-radius: 10px; padding: 0.5rem 1.15rem;
    font-weight: 500; font-size: 0.8rem; transition: all 0.25s;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none; white-space: nowrap;
    backdrop-filter: blur(4px);
}
.dg-header .btn-h:hover {
    background: rgba(255,255,255,0.3); color: white;
    text-decoration: none; transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.dg-header .btn-h-solid {
    background: white; color: <?= $_hColor ?>; border: none; font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.dg-header .btn-h-solid:hover {
    background: #f8fafc; color: <?= $_hColor ?>;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

@media (max-width: 768px) {
    .dg-header { padding: 1.25rem 1.25rem; border-radius: 14px; }
    .dg-header .hdr-text h1 { font-size: 1.15rem; }
    .dg-header .hdr-icon-circle { width: 42px; height: 42px; font-size: 1.1rem; border-radius: 11px; }
    .dg-header .hdr-btns { justify-content: flex-start; }
    .dg-header .btn-h { padding: 0.4rem 0.9rem; font-size: 0.75rem; }
}
</style>

<div class="dg-header">
    <div class="row align-items-center">
        <div class="col-lg-6 col-md-6">
            <div class="d-flex align-items-center" style="position: relative; z-index: 1;">
                <div class="hdr-icon-circle mr-3">
                    <i class="<?= htmlspecialchars($headerIcon) ?>"></i>
                </div>
                <div class="hdr-text">
                    <h1><?= htmlspecialchars($headerTitle) ?></h1>
                    <?php if ($headerSubtitle): ?>
                    <div class="header-sub"><?= htmlspecialchars($headerSubtitle) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (!empty($headerButtons)): ?>
        <div class="col-lg-6 col-md-6 mt-3 mt-md-0">
            <div class="hdr-btns">
                <?php foreach ($headerButtons as $btn): ?>
                <a href="<?= $btn['url'] ?? '#' ?>" class="btn-h <?= !empty($btn['solid']) ? 'btn-h-solid' : '' ?>">
                    <?php if (!empty($btn['icon'])): ?><i class="<?= $btn['icon'] ?>"></i><?php endif; ?>
                    <?= htmlspecialchars($btn['label'] ?? '') ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
