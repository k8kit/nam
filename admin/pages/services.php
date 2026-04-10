<?php
$all_services = getAllServices($conn, false);
displayAlert();

foreach ($all_services as &$service) {
    $sid = $service['id'];
    $img_result = $conn->query("SELECT * FROM service_images WHERE service_id = $sid ORDER BY sort_order ASC");
    $service['images'] = $img_result ? $img_result->fetch_all(MYSQLI_ASSOC) : [];
}
unset($service);

$admin_uploads_url = rtrim(str_replace('/admin', '', rtrim(BASE_URL, '/')), '/') . '/uploads/';
$total_items       = count($all_services);

$per_page    = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$cur_page    = max(1, min($total_pages, intval($_GET['services_page'] ?? 1)));
$offset      = ($cur_page - 1) * $per_page;
$page_items  = array_slice($all_services, $offset, $per_page);
?>

<script>window.UPLOADS_URL = '<?php echo $admin_uploads_url; ?>';</script>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:.75rem;">
    <h2 style="margin:0;">
        List of Services
        <span style="font-size:.8rem;font-weight:600;color:var(--text-light);margin-left:.5rem;">
            (<?php echo $total_items; ?> total)
        </span>
    </h2>
    <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
        <div class="msg-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="serviceSearchInput" placeholder="Search services…" oninput="applyServiceFilters(this.value)">
        </div>
        <button class="btn-add" onclick="openAddServiceModal()">
            <i class="fas fa-plus"></i> Add New Service
        </button>
    </div>
</div>

<div class="admin-card">
    <?php if (!empty($all_services)): ?>
        <table class="admin-table" id="servicesTable">
            <thead>
                <tr>
                    <th>Order</th><th>Service Name</th><th>Status</th><th>Created</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($page_items as $service): ?>
                <tr data-search="<?php echo strtolower(htmlspecialchars($service['service_name'] . ' ' . strip_tags($service['description'] ?? ''))); ?>">
                    <td><?php echo $service['sort_order']; ?></td>
                    <td><?php echo sanitize($service['service_name']); ?></td>
                    <td>
                        <span class="badge" style="background-color:<?php echo $service['is_active'] ? '#28A745' : '#6C757D'; ?>;">
                            <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo formatDate($service['created_at']); ?></td>
                    <td>
                        <div class="admin-actions">
                            <button class="btn-edit" onclick="editService(<?php echo $service['id']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" onclick="openSvcDeleteConfirm(<?php echo $service['id']; ?>, '<?php echo addslashes(sanitize($service['service_name'])); ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <tr id="serviceNoResults" style="display:none;">
                    <td colspan="5" style="text-align:center; color:var(--text-light); padding:2rem;">
                        <i class="fas fa-search" style="margin-right:.4rem;"></i> No services match your search.
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="adm-pagination">
            <div class="adm-pag-info">
                Showing <strong><?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_items); ?></strong>
                of <strong><?php echo $total_items; ?></strong> services
            </div>
            <div class="adm-pag-btns">
                <a href="?page=services&services_page=<?php echo max(1, $cur_page - 1); ?>"
                   class="adm-pag-btn <?php echo $cur_page <= 1 ? 'disabled' : ''; ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>

                <?php
                $range = 2;
                $start = max(1, $cur_page - $range);
                $end   = min($total_pages, $cur_page + $range);

                if ($start > 1) echo '<a href="?page=services&services_page=1" class="adm-pag-btn">1</a>';
                if ($start > 2) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';

                for ($p = $start; $p <= $end; $p++):
                ?>
                    <a href="?page=services&services_page=<?php echo $p; ?>"
                       class="adm-pag-btn <?php echo $p === $cur_page ? 'active' : ''; ?>">
                        <?php echo $p; ?>
                    </a>
                <?php endfor;

                if ($end < $total_pages - 1) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';
                if ($end < $total_pages)     echo '<a href="?page=services&services_page=' . $total_pages . '" class="adm-pag-btn">' . $total_pages . '</a>';
                ?>

                <a href="?page=services&services_page=<?php echo min($total_pages, $cur_page + 1); ?>"
                   class="adm-pag-btn <?php echo $cur_page >= $total_pages ? 'disabled' : ''; ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <p style="text-align:center; color:var(--text-light); padding:2rem;">
            <i class="fas fa-inbox"></i> No services yet. <a href="#" onclick="openAddServiceModal(); return false;">Add one now</a>
        </p>
    <?php endif; ?>
</div>

<!-- Add / Edit Service Modal -->
<div class="modal-overlay" id="serviceModal">
    <div class="modal-content" style="max-width:580px;max-height:90vh;overflow-y:auto;">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Service</h2>
            <button class="modal-close" onclick="closeServiceModal()">&times;</button>
        </div>
        <form id="serviceForm" enctype="multipart/form-data">
            <input type="hidden" id="serviceId" name="service_id" value="">

            <div class="form-group">
                <label for="serviceName">Service Name</label>
                <input type="text" id="serviceName" name="service_name" class="form-control" placeholder="Enter service name">
            </div>
            <div class="form-group">
                <label for="serviceDescription">Description</label>
                <textarea id="serviceDescription" name="description" class="form-control" rows="4" placeholder="Describe this service"></textarea>
            </div>

            <!-- Existing images (shown in edit mode) -->
            <div id="existingImagesSection" style="display:none; margin-bottom:1.2rem;">
                <label style="font-weight:600;color:var(--text-dark);display:block;margin-bottom:.5rem;">
                    Current Images <small style="font-weight:400;color:var(--text-light);">(click × to remove)</small>
                </label>
                <div class="existing-imgs-wrap" id="existingImagesGrid"></div>
            </div>

            <div class="form-group">
                <label for="serviceImages">
                    Service Images <small style="color:var(--text-light);font-weight:normal;">(select multiple)</small>
                </label>
                <input type="file" id="serviceImages" name="service_images[]" class="form-control" accept="image/*" multiple onchange="previewNewImages(this)">
                <small style="color:var(--text-light);">Formats: JPG, PNG, GIF, WEBP. Max 5 MB each.</small>
            </div>
            <div id="newImagesPreview" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:1rem;"></div>

            <div class="form-group">
                <label for="serviceOrder">Display Order</label>
                <input type="number" id="serviceOrder" name="sort_order" class="form-control" value="0" min="0">
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="serviceActive" name="is_active" value="1" checked> Active</label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary-main" style="background-color:#6C757D;color:white;border:none;" onclick="closeServiceModal()">Cancel</button>
                <button type="button" id="svcSaveBtn" class="btn-add" onclick="submitServiceForm(event)">Save Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="confirm-modal-overlay" id="svcDeleteConfirmModal">
    <div class="confirm-modal-box">
        <div class="confirm-modal-header">
            <div class="confirm-modal-icon"><i class="fas fa-trash-alt"></i></div>
            <div><h3>Delete Service</h3><p>This action cannot be undone</p></div>
        </div>
        <div class="confirm-modal-body">
            <p>Are you sure you want to delete <strong id="deleteSvcName">this service</strong> and all its images?</p>
            <div class="confirm-warning"><i class="fas fa-exclamation-triangle"></i> All associated images will be permanently removed.</div>
        </div>
        <div class="confirm-modal-footer">
            <button class="confirm-btn confirm-btn-cancel" onclick="closeSvcDeleteConfirm()"><i class="fas fa-times"></i> Cancel</button>
            <button class="confirm-btn confirm-btn-delete" onclick="executeSvcDelete()"><i class="fas fa-trash-alt"></i> Yes, Delete</button>
        </div>
    </div>
</div>