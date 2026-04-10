<?php
$all_clients = getAllClients($conn, false);
$total_items = count($all_clients);
displayAlert();

$per_page    = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$cur_page    = max(1, min($total_pages, intval($_GET['clients_page'] ?? 1)));
$offset      = ($cur_page - 1) * $per_page;
$page_items  = array_slice($all_clients, $offset, $per_page);
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:.75rem;">
    <h2 style="margin:0;">
        List of Clients
        <span style="font-size:.8rem;font-weight:600;color:var(--text-light);margin-left:.5rem;">
            (<?php echo $total_items; ?> total)
        </span>
    </h2>
    <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
        <div class="msg-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="clientSearchInput" placeholder="Search clients…" oninput="applyClientFilters(this.value)">
        </div>
        <button class="btn-add" onclick="openAddClientModal()">
            <i class="fas fa-plus"></i> Add New Client
        </button>
    </div>
</div>

<div class="admin-card">
    <?php if (!empty($all_clients)): ?>
        <table class="admin-table" id="clientsTable">
            <thead>
                <tr>
                    <th>Order</th><th>Client Name</th><th>Status</th><th>Created</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($page_items as $client): ?>
                <tr data-search="<?php echo strtolower(htmlspecialchars($client['client_name'] . ' ' . ($client['description'] ?? ''))); ?>">
                    <td><?php echo $client['sort_order']; ?></td>
                    <td><?php echo sanitize($client['client_name']); ?></td>
                    <td>
                        <span class="badge" style="background-color:<?php echo $client['is_active'] ? '#28A745' : '#6C757D'; ?>;">
                            <?php echo $client['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo formatDate($client['created_at']); ?></td>
                    <td>
                        <div class="admin-actions">
                            <button class="btn-edit" onclick="editClient(<?php echo $client['id']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" onclick="openClientDeleteConfirm(<?php echo $client['id']; ?>, '<?php echo addslashes(sanitize($client['client_name'])); ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <tr id="clientNoResults" style="display:none;">
                    <td colspan="5" style="text-align:center; color:var(--text-light); padding:2rem;">
                        <i class="fas fa-search" style="margin-right:.4rem;"></i> No clients match your search.
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="adm-pagination">
            <div class="adm-pag-info">
                Showing <strong><?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_items); ?></strong>
                of <strong><?php echo $total_items; ?></strong> clients
            </div>
            <div class="adm-pag-btns">
                <a href="?page=clients&clients_page=<?php echo max(1, $cur_page - 1); ?>"
                   class="adm-pag-btn <?php echo $cur_page <= 1 ? 'disabled' : ''; ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>

                <?php
                $range = 2;
                $start = max(1, $cur_page - $range);
                $end   = min($total_pages, $cur_page + $range);

                if ($start > 1) echo '<a href="?page=clients&clients_page=1" class="adm-pag-btn">1</a>';
                if ($start > 2) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';

                for ($p = $start; $p <= $end; $p++):
                ?>
                    <a href="?page=clients&clients_page=<?php echo $p; ?>"
                       class="adm-pag-btn <?php echo $p === $cur_page ? 'active' : ''; ?>">
                        <?php echo $p; ?>
                    </a>
                <?php endfor;

                if ($end < $total_pages - 1) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';
                if ($end < $total_pages)     echo '<a href="?page=clients&clients_page=' . $total_pages . '" class="adm-pag-btn">' . $total_pages . '</a>';
                ?>

                <a href="?page=clients&clients_page=<?php echo min($total_pages, $cur_page + 1); ?>"
                   class="adm-pag-btn <?php echo $cur_page >= $total_pages ? 'disabled' : ''; ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <p style="text-align:center; color:var(--text-light); padding:2rem;">
            <i class="fas fa-inbox"></i> No clients yet. <a href="#" onclick="openAddClientModal(); return false;">Add one now</a>
        </p>
    <?php endif; ?>
</div>

<!-- Add / Edit Client Modal -->
<div class="modal-overlay" id="clientModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Client</h2>
            <button class="modal-close" onclick="closeClientModal()">&times;</button>
        </div>
        <form id="clientForm" enctype="multipart/form-data">
            <input type="hidden" id="clientId" name="client_id" value="">
            <div class="form-group">
                <label for="clientName">Client Name</label>
                <input type="text" id="clientName" name="client_name" class="form-control" placeholder="Enter client name">
            </div>
            <div class="form-group">
                <label for="clientImage">Client Logo / Image</label>
                <input type="file" id="clientImage" name="client_image" class="form-control" accept="image/*">
                <small style="color:var(--text-light);">Formats: JPG, PNG, GIF. Max size: 5 MB</small>
            </div>
            <div class="form-group">
                <label for="clientDescription">Description</label>
                <textarea id="clientDescription" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="clientOrder">Display Order</label>
                <input type="number" id="clientOrder" name="sort_order" class="form-control" value="0" min="0">
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="clientActive" name="is_active" value="1" checked> Active</label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-main" style="background-color:#6C757D;color:white;border:none;" onclick="closeClientModal()">Cancel</button>
                <button type="button" id="clientSaveBtn" class="btn-add" onclick="submitClientForm(event)">Save Client</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="confirm-modal-overlay" id="clientDeleteConfirmModal">
    <div class="confirm-modal-box">
        <div class="confirm-modal-header">
            <div class="confirm-modal-icon"><i class="fas fa-trash-alt"></i></div>
            <div><h3>Delete Client</h3><p>This action cannot be undone</p></div>
        </div>
        <div class="confirm-modal-body">
            <p>Are you sure you want to delete <strong id="deleteClientName">this client</strong>?</p>
        </div>
        <div class="confirm-modal-footer">
            <button class="confirm-btn confirm-btn-cancel" onclick="closeClientDeleteConfirm()"><i class="fas fa-times"></i> Cancel</button>
            <button class="confirm-btn confirm-btn-delete" onclick="executeClientDelete()"><i class="fas fa-trash-alt"></i> Yes, Delete</button>
        </div>
    </div>
</div>