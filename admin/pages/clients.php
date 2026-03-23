<?php
// Clients Management Page
$clients = getAllClients($conn, false);
displayAlert();

// ── Pagination ──────────────────────────────────────────────────────────────
$per_page    = 10;
$total_items = count($clients);
$total_pages = max(1, ceil($total_items / $per_page));
$cur_page    = max(1, min($total_pages, intval($_GET['clients_page'] ?? 1)));
$offset      = ($cur_page - 1) * $per_page;
$page_items  = array_slice($clients, $offset, $per_page);
?>

<style>
/* ── Pagination ── */
.adm-pagination {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem;
    padding: 1rem 0 .25rem;
}
.adm-pag-info {
    font-size: .83rem; color: var(--text-light); font-weight: 600;
}
.adm-pag-info strong { color: var(--text-dark); }
.adm-pag-btns { display: flex; gap: .35rem; align-items: center; flex-wrap: wrap; }
.adm-pag-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 34px; height: 34px; padding: 0 .55rem;
    border-radius: 8px; border: 1.5px solid var(--border-color);
    background: #fff; color: var(--text-dark);
    font-size: .82rem; font-weight: 700; font-family: inherit; cursor: pointer;
    transition: all .2s; text-decoration: none;
}
.adm-pag-btn:hover { border-color: var(--primary-color); color: var(--primary-color); background: rgba(21,101,192,.06); }
.adm-pag-btn.active { background: var(--primary-color); border-color: var(--primary-color); color: #fff; }
.adm-pag-btn:disabled, .adm-pag-btn.disabled { opacity: .35; cursor: not-allowed; pointer-events: none; }
.adm-pag-btn i { font-size: .75rem; }

/* ── Confirm Modal ── */
.confirm-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
    z-index: 9999; align-items: center; justify-content: center; padding: 1rem;
}
.confirm-modal-overlay.active { display: flex; animation: cfFadeIn .2s ease; }
@keyframes cfFadeIn { from{opacity:0} to{opacity:1} }
.confirm-modal-box {
    background:#fff; border-radius:16px; max-width:420px; width:100%;
    box-shadow:0 24px 70px rgba(0,0,0,.25); overflow:hidden;
    animation:cfSlideUp .28s cubic-bezier(.22,.68,0,1.1);
}
@keyframes cfSlideUp {
    from { transform:translateY(20px) scale(.97); opacity:0; }
    to   { transform:translateY(0) scale(1); opacity:1; }
}
.confirm-modal-header { background:linear-gradient(135deg,#DC3545,#C82333); padding:1.5rem 1.8rem 1.2rem; display:flex; align-items:center; gap:1rem; }
.confirm-modal-icon { width:48px; height:48px; background:rgba(255,255,255,.18); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.3rem; color:#fff; flex-shrink:0; }
.confirm-modal-header h3 { color:#fff; margin:0; font-size:1.1rem; font-weight:800; }
.confirm-modal-header p  { color:rgba(255,255,255,.8); margin:3px 0 0; font-size:.83rem; }
.confirm-modal-body { padding:1.6rem 1.8rem; }
.confirm-modal-body > p { color:#374151; font-size:.97rem; line-height:1.7; margin:0; }
.confirm-modal-footer { padding:.9rem 1.8rem 1.3rem; display:flex; gap:.65rem; justify-content:flex-end; border-top:1px solid #e2e8f0; background:#FAFBFF; }
.confirm-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.58rem 1.3rem; border-radius:8px; font-size:.88rem; font-weight:700; cursor:pointer; border:none; transition:all .2s; font-family:inherit; }
.confirm-btn-cancel { background:#F1F5F9; color:#4A5568; }
.confirm-btn-cancel:hover { background:#E2E8F0; }
.confirm-btn-delete { background:linear-gradient(135deg,#DC3545,#C82333); color:#fff; }
.confirm-btn-delete:hover { background:linear-gradient(135deg,#C82333,#A71D2A); transform:translateY(-1px); box-shadow:0 6px 18px rgba(220,53,69,.35); }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>List of Clients
        <span style="font-size:.8rem;font-weight:600;color:var(--text-light);margin-left:.5rem;">(<?php echo $total_items; ?> total)</span>
    </h2>
    <button class="btn-add" onclick="openAddClientModal()">
        <i class="fas fa-plus"></i> Add New Client
    </button>
</div>

<div class="admin-card">
    <?php if (!empty($clients)): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Client Name</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($page_items as $client): ?>
                    <tr>
                        <td><?php echo $client['sort_order']; ?></td>
                        <td><?php echo sanitize($client['client_name']); ?></td>
                        <td>
                            <span class="badge" style="background-color: <?php echo $client['is_active'] ? '#28A745' : '#6C757D'; ?>;">
                                <?php echo $client['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($client['created_at']); ?></td>
                        <td>
                            <div class="admin-actions">
                                <button class="btn-edit" onclick="editClient(<?php echo $client['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete" onclick="openClientDeleteConfirm(<?php echo $client['id']; ?>, '<?php echo addslashes(sanitize($client['client_name'])); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
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
                if ($end < $total_pages) echo '<a href="?page=clients&clients_page=' . $total_pages . '" class="adm-pag-btn">' . $total_pages . '</a>';
                ?>
                <a href="?page=clients&clients_page=<?php echo min($total_pages, $cur_page + 1); ?>"
                   class="adm-pag-btn <?php echo $cur_page >= $total_pages ? 'disabled' : ''; ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <p style="text-align: center; color: var(--text-light); padding: 2rem;">
            <i class="fas fa-inbox"></i> No clients yet. <a href="#" onclick="openAddClientModal(); return false;">Add one now</a>
        </p>
    <?php endif; ?>
</div>

<!-- Add/Edit Client Modal -->
<div class="modal-overlay" id="clientModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Client</h2>
            <button class="modal-close" onclick="closeClientModal()">&times;</button>
        </div>
        <form id="clientForm" enctype="multipart/form-data" onsubmit="submitClientForm(event)">
            <input type="hidden" id="clientId" name="client_id" value="">
            <div class="form-group">
                <label for="clientName">Client Name</label>
                <input type="text" id="clientName" name="client_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="clientImage">Client Logo/Image</label>
                <input type="file" id="clientImage" name="client_image" class="form-control" accept="image/*">
                <small style="color: var(--text-light);">Formats: JPG, PNG, GIF. Max size: 5MB</small>
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
                <label>
                    <input type="checkbox" id="clientActive" name="is_active" value="1" checked>
                    Active
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-main" style="background-color: #6C757D; color: white; border: none;" onclick="closeClientModal()">Cancel</button>
                <button type="submit" class="btn-add">Save Client</button>
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

<script>
var _clientDeleteId = null;

function openClientDeleteConfirm(id, name) {
    _clientDeleteId = id;
    document.getElementById('deleteClientName').textContent = '"' + name + '"';
    document.getElementById('clientDeleteConfirmModal').classList.add('active');
}
function closeClientDeleteConfirm() {
    document.getElementById('clientDeleteConfirmModal').classList.remove('active');
    _clientDeleteId = null;
}
function executeClientDelete() {
    if (_clientDeleteId) window.location.href = '../backend/delete_client.php?id=' + _clientDeleteId;
}
document.getElementById('clientDeleteConfirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeClientDeleteConfirm();
});

function openAddClientModal() {
    document.getElementById('modalTitle').innerText = 'Add New Client';
    document.getElementById('clientForm').reset();
    document.getElementById('clientId').value = '';
    document.getElementById('clientModal').classList.add('active');
}
function closeClientModal() { document.getElementById('clientModal').classList.remove('active'); }

function editClient(id) {
    fetch('../backend/get_client.php?id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var client = data.data;
                document.getElementById('modalTitle').innerText    = 'Edit Client';
                document.getElementById('clientId').value          = client.id;
                document.getElementById('clientName').value        = client.client_name;
                document.getElementById('clientDescription').value = client.description;
                document.getElementById('clientOrder').value       = client.sort_order;
                document.getElementById('clientActive').checked    = client.is_active == 1;
                document.getElementById('clientModal').classList.add('active');
            }
        });
}

function submitClientForm(event) {
    event.preventDefault();
    var formData = new FormData(document.getElementById('clientForm'));
    fetch('../backend/save_client.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { window.location.href = 'dashboard.php?page=clients'; }
            else { alert('Error: ' + data.message); }
        });
}

document.getElementById('clientModal').addEventListener('click', function(e) {
    if (e.target === this) closeClientModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeClientModal(); closeClientDeleteConfirm(); }
});
</script>