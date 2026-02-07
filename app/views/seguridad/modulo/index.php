<?php
/**
 * DigiSports Seguridad - Lista de Módulos (Vista Premium)
 */
$modulos = $modulos ?? [];
$statsModulos = $statsModulos ?? ['total' => 0, 'activos' => 0, 'externos' => 0, 'con_tenants' => 0];
$moduloColor = $moduloColor ?? $modulo_actual['color'] ?? '#F59E0B';
$moduloIcono = $moduloIcono ?? $modulo_actual['icono'] ?? 'fas fa-shield-alt';
?>

<style>
/* ═══ Módulos del Sistema — Estilos Premium ═══ */
.mod-header {
    background: linear-gradient(135deg, <?= $moduloColor ?> 0%, <?= $moduloColor ?>cc 100%);
    border-radius: 16px; padding: 1.5rem 2rem; margin-bottom: 1.5rem;
    color: white; position: relative; overflow: hidden;
}
.mod-header::before {
    content: ''; position: absolute; top: -40%; right: -8%;
    width: 250px; height: 250px; background: rgba(255,255,255,0.07); border-radius: 50%;
}
.mod-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; position: relative; z-index: 1; }
.mod-header .header-sub { opacity: 0.85; font-size: 0.85rem; margin-top: 4px; position: relative; z-index: 1; }
.mod-header .btn-h { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 10px; padding: 0.45rem 1.1rem; font-weight: 500; font-size: 0.82rem; transition: all 0.3s; }
.mod-header .btn-h:hover { background: rgba(255,255,255,0.35); color: white; text-decoration: none; transform: translateY(-1px); }
.mod-header .btn-h-solid { background: white; color: <?= $moduloColor ?>; border: none; font-weight: 600; }
.mod-header .btn-h-solid:hover { background: #f1f5f9; color: <?= $moduloColor ?>; }

/* Mini KPIs */
.mini-kpi { background: white; border-radius: 12px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); text-align: center; transition: all 0.3s; }
.mini-kpi:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.mini-kpi .mk-val { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; }
.mini-kpi .mk-lbl { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: #64748b; margin-top: 4px; }

/* Module Cards */
.mod-card {
    border: none; border-radius: 16px; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
    position: relative;
}
.mod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}
.mod-card .card-accent { height: 4px; }
.mod-card .card-body { padding: 1.5rem 1.25rem 1.25rem; }
.mod-card .mod-icon-wrap {
    width: 64px; height: 64px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; margin: 0 auto 1rem;
    transition: transform 0.3s;
}
.mod-card:hover .mod-icon-wrap { transform: scale(1.1) rotate(-3deg); }
.mod-card .mod-title {
    font-size: 1rem; font-weight: 700; color: #1e293b;
    margin-bottom: 0.35rem;
}
.mod-card .mod-desc {
    font-size: 0.78rem; color: #94a3b8; line-height: 1.4;
    min-height: 2.8em;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.mod-card .mod-badges { display: flex; flex-wrap: wrap; gap: 4px; justify-content: center; margin: 0.75rem 0; }
.mod-badge-code {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 0.68rem; font-weight: 600; letter-spacing: 0.3px;
    font-family: 'SFMono-Regular', monospace;
}
.mod-badge-status {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 0.68rem; font-weight: 600;
}
.mod-badge-status.active { background: #dcfce7; color: #16a34a; }
.mod-badge-status.inactive { background: #f1f5f9; color: #94a3b8; }
.mod-badge-status.external { background: #fef3c7; color: #d97706; }
.mod-badge-status.licensed { background: #ede9fe; color: #7c3aed; }

/* Tenant counter */
.mod-tenants {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 0.5rem; background: #f8fafc; border-radius: 8px;
    margin: 0.5rem 0;
}
.mod-tenants .t-count { font-weight: 700; font-size: 0.9rem; color: #1e293b; }
.mod-tenants .t-label { font-size: 0.72rem; color: #64748b; }
.mod-tenants .t-bar { flex: 1; height: 4px; background: #e2e8f0; border-radius: 2px; max-width: 80px; overflow: hidden; }
.mod-tenants .t-bar-fill { height: 100%; border-radius: 2px; transition: width 0.6s ease; }

/* Action Buttons */
.mod-actions {
    display: flex; gap: 6px; padding-top: 0.75rem;
    border-top: 1px solid #f1f5f9; margin-top: 0.5rem;
}
.mod-actions .btn-act {
    flex: 1; padding: 0.4rem; border-radius: 8px; border: 1px solid #e2e8f0;
    background: white; color: #64748b; font-size: 0.75rem; font-weight: 500;
    transition: all 0.2s; cursor: pointer; text-align: center;
    text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 4px;
}
.mod-actions .btn-act:hover { border-color: #cbd5e1; color: #334155; background: #f8fafc; text-decoration: none; }
.mod-actions .btn-act.act-edit:hover { border-color: #93c5fd; color: #2563eb; background: #eff6ff; }
.mod-actions .btn-act.act-dup:hover { border-color: #a5b4fc; color: #6366f1; background: #eef2ff; }
.mod-actions .btn-act.act-del:hover { border-color: #fca5a5; color: #dc2626; background: #fef2f2; }

/* Order badge */
.mod-order {
    position: absolute; top: 12px; right: 12px;
    width: 26px; height: 26px; border-radius: 50%;
    background: #f1f5f9; color: #94a3b8;
    font-size: 0.7rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}

/* Filter bar */
.filter-bar {
    background: white; border-radius: 12px; padding: 0.75rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 1rem;
    display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
}
.filter-bar .filter-btn {
    padding: 0.35rem 0.85rem; border-radius: 8px; border: 1px solid #e2e8f0;
    background: white; color: #64748b; font-size: 0.78rem; font-weight: 500;
    cursor: pointer; transition: all 0.2s;
}
.filter-bar .filter-btn:hover, .filter-bar .filter-btn.active {
    border-color: <?= $moduloColor ?>; color: <?= $moduloColor ?>;
    background: <?= $moduloColor ?>08;
}
.filter-bar .filter-btn.active { font-weight: 600; }
.filter-bar .search-input {
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.35rem 0.75rem;
    font-size: 0.82rem; outline: none; transition: border-color 0.2s; flex: 1; min-width: 180px;
}
.filter-bar .search-input:focus { border-color: <?= $moduloColor ?>; box-shadow: 0 0 0 2px <?= $moduloColor ?>15; }

/* Empty state */
.empty-state { text-align: center; padding: 3rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; color: #e2e8f0; }

/* Animations */
@keyframes cardIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.mod-card { animation: cardIn 0.4s ease-out forwards; opacity: 0; }
.mod-col:nth-child(1) .mod-card { animation-delay: 0.03s; }
.mod-col:nth-child(2) .mod-card { animation-delay: 0.06s; }
.mod-col:nth-child(3) .mod-card { animation-delay: 0.09s; }
.mod-col:nth-child(4) .mod-card { animation-delay: 0.12s; }
.mod-col:nth-child(5) .mod-card { animation-delay: 0.15s; }
.mod-col:nth-child(6) .mod-card { animation-delay: 0.18s; }
.mod-col:nth-child(7) .mod-card { animation-delay: 0.21s; }
.mod-col:nth-child(8) .mod-card { animation-delay: 0.24s; }
.mod-col:nth-child(n+9) .mod-card { animation-delay: 0.27s; }

@media (max-width: 768px) {
    .mod-header { padding: 1rem 1.25rem; }
    .mod-header h1 { font-size: 1.2rem; }
    .mini-kpi .mk-val { font-size: 1.3rem; }
}
</style>

<section class="content pt-3">
<div class="container-fluid">

<!-- ═══ HEADER ═══ -->
<div class="mod-header">
    <div class="row align-items-center">
        <div class="col-lg-6 col-md-5">
            <h1><i class="<?= htmlspecialchars($moduloIcono) ?> mr-2"></i> Módulos del Sistema</h1>
            <div class="header-sub">
                <i class="fas fa-puzzle-piece mr-1"></i>
                Gestión de subsistemas y aplicaciones de la plataforma
            </div>
        </div>
        <div class="col-lg-6 col-md-7 text-md-right mt-3 mt-md-0" style="position: relative; z-index:1;">
            <a href="<?= url('seguridad', 'modulo', 'crear') ?>" class="btn btn-h btn-h-solid mr-2">
                <i class="fas fa-plus mr-1"></i> Nuevo Módulo
            </a>
            <a href="<?= url('seguridad', 'modulo', 'iconos') ?>" class="btn btn-h">
                <i class="fas fa-icons mr-1"></i> Iconos y Colores
            </a>
        </div>
    </div>
</div>

<!-- ═══ MINI KPIs ═══ -->
<div class="row mb-3">
    <div class="col-6 col-md-3 mb-2">
        <div class="mini-kpi">
            <div class="mk-val" style="color: <?= $moduloColor ?>"><?= $statsModulos['total'] ?></div>
            <div class="mk-lbl">Total Módulos</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="mini-kpi">
            <div class="mk-val" style="color: #22C55E"><?= $statsModulos['activos'] ?></div>
            <div class="mk-lbl">Activos</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="mini-kpi">
            <div class="mk-val" style="color: #F59E0B"><?= $statsModulos['externos'] ?></div>
            <div class="mk-lbl">Externos</div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="mini-kpi">
            <div class="mk-val" style="color: #3B82F6"><?= $statsModulos['con_tenants'] ?></div>
            <div class="mk-lbl">Con Tenants</div>
        </div>
    </div>
</div>

<!-- ═══ FILTER BAR ═══ -->
<div class="filter-bar">
    <i class="fas fa-filter" style="color: #94a3b8; font-size: 0.85rem;"></i>
    <button class="filter-btn active" data-filter="all">Todos</button>
    <button class="filter-btn" data-filter="active">Activos</button>
    <button class="filter-btn" data-filter="inactive">Inactivos</button>
    <button class="filter-btn" data-filter="external">Externos</button>
    <div class="ml-auto d-flex align-items-center gap-2">
        <input type="text" class="search-input" id="searchModulos" placeholder="Buscar módulo...">
    </div>
</div>

<!-- ═══ MODULE CARDS GRID ═══ -->
<?php
$maxTenants = 1;
foreach ($modulos as $m) {
    $t = (int)($m['mod_tenants_activos'] ?? 0);
    if ($t > $maxTenants) $maxTenants = $t;
}
?>
<div class="row" id="modulosGrid">
    <?php if (empty($modulos)): ?>
    <div class="col-12">
        <div class="empty-state">
            <i class="fas fa-puzzle-piece"></i>
            <h5>No hay módulos registrados</h5>
            <p>Crea el primer módulo para comenzar</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($modulos as $idx => $m):
        $color = htmlspecialchars($m['mod_color_fondo'] ?? '#94a3b8');
        $icono = !empty($m['mod_icono']) ? $m['mod_icono'] : 'fa-cube';
        // Asegurar prefijo fas si no tiene
        if (!preg_match('/^(fas|far|fab|fal|fad) /', $icono)) $icono = 'fas ' . $icono;
        $tenants = (int)($m['mod_tenants_activos'] ?? 0);
        $pctTenants = $maxTenants > 0 ? round(($tenants / $maxTenants) * 100) : 0;
        $isActive = (bool)$m['mod_activo'];
        $isExternal = (int)$m['mod_es_externo'] === 1;
    ?>
    <div class="col-xl-3 col-lg-4 col-md-6 mb-3 mod-col"
         data-name="<?= strtolower(htmlspecialchars($m['mod_nombre'])) ?>"
         data-code="<?= strtolower(htmlspecialchars($m['mod_codigo'])) ?>"
         data-active="<?= $isActive ? '1' : '0' ?>"
         data-external="<?= $isExternal ? '1' : '0' ?>">
        <div class="card mod-card h-100">
            <div class="card-accent" style="background: <?= $color ?>;"></div>
            <span class="mod-order">#<?= (int)$m['mod_orden'] ?></span>
            <div class="card-body text-center d-flex flex-column">
                <!-- Icono -->
                <div class="mod-icon-wrap" style="background: <?= $color ?>12; color: <?= $color ?>;">
                    <i class="<?= $icono ?>"></i>
                </div>

                <!-- Nombre y descripción -->
                <div class="mod-title"><?= htmlspecialchars($m['mod_nombre']) ?></div>
                <div class="mod-desc"><?= htmlspecialchars($m['mod_descripcion'] ?? 'Sin descripción') ?></div>

                <!-- Badges -->
                <div class="mod-badges">
                    <span class="mod-badge-code" style="background: <?= $color ?>10; color: <?= $color ?>;">
                        <i class="fas fa-code" style="font-size: 0.6rem;"></i>
                        <?= htmlspecialchars($m['mod_codigo']) ?>
                    </span>
                    <span class="mod-badge-status <?= $isActive ? 'active' : 'inactive' ?>">
                        <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                        <?= $isActive ? 'Activo' : 'Inactivo' ?>
                    </span>
                    <?php if ($isExternal): ?>
                    <span class="mod-badge-status external">
                        <i class="fas fa-external-link-alt" style="font-size: 0.55rem;"></i> Externo
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($m['mod_requiere_licencia'])): ?>
                    <span class="mod-badge-status licensed">
                        <i class="fas fa-key" style="font-size: 0.55rem;"></i> Licencia
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Tenants counter -->
                <div class="mod-tenants">
                    <i class="fas fa-building" style="color: <?= $color ?>; font-size: 0.8rem;"></i>
                    <span class="t-count"><?= $tenants ?></span>
                    <span class="t-label">tenant<?= $tenants !== 1 ? 's' : '' ?></span>
                    <div class="t-bar">
                        <div class="t-bar-fill" style="width: <?= $pctTenants ?>%; background: <?= $color ?>;"></div>
                    </div>
                </div>

                <!-- Spacer -->
                <div class="flex-grow-1"></div>

                <!-- Actions -->
                <div class="mod-actions">
                    <a href="<?= url('seguridad', 'modulo', 'editar', ['id' => $m['mod_id']]) ?>" class="btn-act act-edit" title="Editar">
                        <i class="fas fa-pen"></i> Editar
                    </a>
                    <a href="<?= url('seguridad', 'modulo', 'duplicar', ['id' => $m['mod_id']]) ?>" class="btn-act act-dup" title="Duplicar">
                        <i class="fas fa-copy"></i>
                    </a>
                    <a href="#" class="btn-act act-del" title="Eliminar"
                       data-id="<?= $m['mod_id'] ?>" data-name="<?= htmlspecialchars($m['mod_nombre']) ?>">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('modulosGrid');
    const cards = grid ? grid.querySelectorAll('.mod-col') : [];
    const searchInput = document.getElementById('searchModulos');
    const filterBtns = document.querySelectorAll('.filter-btn');

    // ─── Filtro por categoría ───
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });

    // ─── Búsqueda ───
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    function applyFilters() {
        const search = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const activeFilter = document.querySelector('.filter-btn.active');
        const filter = activeFilter ? activeFilter.dataset.filter : 'all';

        cards.forEach(col => {
            const name = col.dataset.name || '';
            const code = col.dataset.code || '';
            const isActive = col.dataset.active === '1';
            const isExternal = col.dataset.external === '1';

            let showByFilter = true;
            if (filter === 'active') showByFilter = isActive;
            else if (filter === 'inactive') showByFilter = !isActive;
            else if (filter === 'external') showByFilter = isExternal;

            const showBySearch = !search || name.includes(search) || code.includes(search);

            col.style.display = (showByFilter && showBySearch) ? '' : 'none';
        });
    }

    // ─── Eliminar con SweetAlert ───
    document.querySelectorAll('.act-del').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const name = this.dataset.name;
            const id = this.dataset.id;
            Swal.fire({
                title: '¿Eliminar módulo?',
                html: `Se eliminará <strong>${name}</strong> del sistema.<br><small class="text-muted">Esta acción no se puede deshacer.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'rounded-lg' }
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = '<?= url('seguridad', 'modulo', 'eliminar') ?>&id=' + id;
                }
            });
        });
    });
});
</script>
