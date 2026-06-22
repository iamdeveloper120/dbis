<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
    .active-summary-line {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.92rem;
        color: #4b5563;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.6rem 0.85rem;
        margin-bottom: 0.85rem;
    }

    .active-summary-sep {
        color: #9ca3af;
    }

    .active-tree-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-bottom: 0.6rem;
    }

    .active-tree-shell {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        padding: 0.65rem 0.75rem;
    }

    .active-tree {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .tree-domain-node + .tree-domain-node {
        margin-top: 0.35rem;
    }

    .domain-details > summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 6px;
        padding: 0.4rem 0.5rem;
        font-weight: 600;
        color: #1f2937;
    }

    .domain-details > summary::-webkit-details-marker {
        display: none;
    }

    .domain-details > summary::before {
        content: '+';
        display: inline-block;
        width: 1rem;
        text-align: center;
        color: #2074BA;
        font-weight: 700;
    }

    .domain-details[open] > summary::before {
        content: '-';
    }

    .domain-details > summary:hover {
        background: #f8fafc;
    }

    .tree-domain-count {
        color: #6b7280;
        font-weight: 500;
    }

    .tree-goals {
        margin: 0.3rem 0 0.2rem 1.35rem;
        padding-left: 0.85rem;
        border-left: 1px dashed #d1d5db;
        list-style: none;
    }

    .tree-goal-node + .tree-goal-node {
        margin-top: 0.2rem;
    }

    .goal-details > summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        color: #374151;
        padding: 0.2rem 0;
    }

    .goal-details > summary::-webkit-details-marker {
        display: none;
    }

    .goal-details > summary::before {
        content: '-';
        color: #9ca3af;
        display: inline-block;
        width: 0.85rem;
        text-align: center;
    }

    .goal-details[open] > summary::before {
        content: '=';
        color: #2074BA;
    }

    .tree-goal-count {
        color: #6b7280;
        font-size: 0.88rem;
    }

    .tree-targets {
        margin: 0.15rem 0 0.2rem 1.25rem;
        padding-left: 0.8rem;
        border-left: 1px dotted #d1d5db;
        list-style: none;
    }

    .tree-target-item {
        color: #4b5563;
        font-size: 0.9rem;
        padding: 0.12rem 0;
    }

    .tree-target-item::before {
        content: '-';
        color: #c0c4cb;
        margin-right: 0.35rem;
    }

    .active-empty {
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        color: #6b7280;
        background: #fcfcfd;
    }

    .file-manager-content-scroll {
        padding-bottom: 150px !important;
        min-height: calc(100vh - 150px);
        box-sizing: border-box;
    }

    @media (max-width: 576px) {
        .active-tree-shell {
            padding: 0.55rem 0.55rem;
        }

        .domain-details > summary {
            padding: 0.35rem 0.35rem;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2 file-manager-content-scroll">
    <div class="active-summary-line">
        <span>Active Domains: <?= (int) ($activeProgramData['program_summary']['total_domains_active'] ?? 0) ?></span>
        <span class="active-summary-sep">|</span>
        <span>Active Goals: <?= (int) ($activeProgramData['program_summary']['total_goals_active'] ?? 0) ?></span>
        <span class="active-summary-sep">|</span>
        <span>Active Targets: <?= (int) ($activeProgramData['program_summary']['total_targets_active'] ?? 0) ?></span>
    </div>

    <?php if (empty($activeProgramData['domains'])): ?>
        <div class="active-empty">
            No Active Program Items. All introduced targets are mastered or no targets are introduced yet.
        </div>
    <?php else: ?>
        <div class="active-tree-toolbar">
            <button type="button" id="expand_all_domains" class="btn btn-sm btn-light">Expand All</button>
            <button type="button" id="collapse_all_domains" class="btn btn-sm btn-light">Collapse All</button>
        </div>

        <div class="active-tree-shell">
            <ul class="active-tree">
                <?php foreach ($activeProgramData['domains'] as $domain): ?>
                    <?php $domainGoals = $domain['goals'] ?? []; ?>
                    <li class="tree-domain-node">
                        <details class="domain-details">
                            <summary>
                                <span><?= esc($domain['domain_name']) ?></span>
                                <span class="tree-domain-count">(<?= (int) count($domainGoals) ?>)</span>
                            </summary>
                            <ul class="tree-goals">
                                <?php foreach ($domainGoals as $goal): ?>
                                    <?php $goalTargets = $goal['targets'] ?? []; ?>
                                    <li class="tree-goal-node">
                                        <details class="goal-details">
                                            <summary>
                                                <span><?= esc($goal['goal_name']) ?></span>
                                                <span class="tree-goal-count">(<?= (int) count($goalTargets) ?>)</span>
                                            </summary>
                                            <ul class="tree-targets">
                                                <?php foreach ($goalTargets as $target): ?>
                                                    <li class="tree-target-item"><?= esc($target['target_name'] ?? '') ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </details>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    function debounce(fn, wait) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function initOrRecalcSimpleBars() {
        document.querySelectorAll("[data-simplebar]").forEach(el => {
            if (!el.SimpleBar && window.SimpleBar) {
                new SimpleBar(el, {
                    autoHide: false
                });
            }
            if (el.SimpleBar) el.SimpleBar.recalculate();
        });
    }

    const recalcSimpleBars = debounce(initOrRecalcSimpleBars, 60);

    document.addEventListener('DOMContentLoaded', function() {
        const expandBtn = document.getElementById('expand_all_domains');
        const collapseBtn = document.getElementById('collapse_all_domains');
        const treeNodes = document.querySelectorAll('.domain-details, .goal-details');

        if (expandBtn) {
            expandBtn.addEventListener('click', function() {
                treeNodes.forEach(node => node.setAttribute('open', 'open'));
                setTimeout(recalcSimpleBars, 80);
            });
        }

        if (collapseBtn) {
            collapseBtn.addEventListener('click', function() {
                treeNodes.forEach(node => node.removeAttribute('open'));
                setTimeout(recalcSimpleBars, 80);
            });
        }

        // Recalculate after any details toggle (domain/goal open-close).
        document.addEventListener('toggle', function(event) {
            if (event.target && event.target.matches('.domain-details, .goal-details')) {
                recalcSimpleBars();
            }
        }, true);
    });

    document.addEventListener('shown.bs.tab', recalcSimpleBars);
    window.addEventListener('resize', recalcSimpleBars);
    window.addEventListener('load', function() {
        setTimeout(recalcSimpleBars, 300);
    });

    const scrollHost = document.querySelector('.file-manager-content-scroll');
    if (scrollHost && window.MutationObserver) {
        const mo = new MutationObserver(recalcSimpleBars);
        mo.observe(scrollHost, {
            childList: true,
            subtree: true
        });
    }
</script>
<?= $this->endSection() ?>
