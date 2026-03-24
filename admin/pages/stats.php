<?php
// Stats Management Page
$stats_result = $conn->query("SELECT * FROM site_stats ORDER BY sort_order ASC");
$all_stats    = $stats_result ? $stats_result->fetch_all(MYSQLI_ASSOC) : [];
displayAlert();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem;">
    <div>
        <h2 style="margin:0;">Stats Section</h2>
        <p style="margin:.2rem 0 0; font-size:.83rem; color:var(--text-light);">
            Edit the numbers displayed on the homepage stats bar. Changes are reflected instantly after saving.
        </p>
    </div>
</div>

<!-- Live preview bar -->
<div class="stats-preview-bar" id="statsPreviewBar">
    <?php foreach ($all_stats as $st): if (!$st['is_active']) continue; ?>
        <div class="stats-preview-item" id="preview_<?php echo $st['id']; ?>">
            <span class="stats-preview-number">
                <?php echo $st['value'] . htmlspecialchars($st['suffix']); ?>
            </span>
            <span class="stats-preview-label"><?php echo htmlspecialchars($st['label']); ?></span>
        </div>
    <?php endforeach; ?>
</div>

<!-- Edit form -->
<form id="statsForm" onsubmit="saveStats(event)">
    <div class="stat-cards-grid" id="statCardsGrid">
        <?php foreach ($all_stats as $i => $st): ?>
            <div class="stat-edit-card" id="statCard_<?php echo $st['id']; ?>">
                <span class="card-num-badge"><?php echo str_pad($st['sort_order'], 2, '0', STR_PAD_LEFT); ?></span>
                <input type="hidden" name="ids[]" value="<?php echo $st['id']; ?>">

                <label class="form-label" style="margin-top:.4rem;">Label</label>
                <input type="text"
                       class="form-control"
                       name="labels[]"
                       value="<?php echo htmlspecialchars($st['label']); ?>"
                       maxlength="80"
                       required
                       oninput="statsLivePreview(<?php echo $st['id']; ?>, this.closest('.stat-edit-card'))">

                <div class="stat-input-row" style="margin-top:.7rem;">
                    <div>
                        <label class="form-label">Value</label>
                        <input type="number"
                               class="form-control"
                               name="values[]"
                               value="<?php echo $st['value']; ?>"
                               min="0" max="99999"
                               required
                               oninput="statsLivePreview(<?php echo $st['id']; ?>, this.closest('.stat-edit-card'))">
                    </div>
                    <div>
                        <label class="form-label">Suffix</label>
                        <input type="text"
                               class="form-control"
                               name="suffixes[]"
                               value="<?php echo htmlspecialchars($st['suffix']); ?>"
                               maxlength="5"
                               placeholder="e.g. +"
                               oninput="statsLivePreview(<?php echo $st['id']; ?>, this.closest('.stat-edit-card'))">
                    </div>
                </div>

                <div class="stat-input-row">
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input type="number"
                               class="form-control"
                               name="orders[]"
                               value="<?php echo $st['sort_order']; ?>"
                               min="0" max="99">
                    </div>
                </div>

                <div class="stat-toggle">
                    <span class="stat-active-dot <?php echo $st['is_active'] ? '' : 'stat-inactive-dot'; ?>"
                          id="dot_<?php echo $st['id']; ?>"></span>
                    <input type="checkbox"
                           id="active_<?php echo $st['id']; ?>"
                           name="actives[<?php echo $st['id']; ?>]"
                           value="1"
                           <?php echo $st['is_active'] ? 'checked' : ''; ?>
                           onchange="statsTogglePreview(<?php echo $st['id']; ?>, this.checked)">
                    <label for="active_<?php echo $st['id']; ?>">Show on website</label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
        <button type="submit" class="btn-add" id="statsSaveBtn">
            <i class="fas fa-save"></i> Save All Stats
        </button>
    </div>
</form>