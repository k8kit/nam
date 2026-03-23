<?php
// Supplies Management Page
$cat_result = $conn->query("SELECT * FROM supply_categories ORDER BY sort_order ASC");
$categories = $cat_result ? $cat_result->fetch_all(MYSQLI_ASSOC) : [];

$supplies_result = $conn->query("
    SELECT s.*, sc.category_name
    FROM supplies s
    LEFT JOIN supply_categories sc ON s.category_id = sc.id
    ORDER BY sc.sort_order ASC, s.sort_order ASC
");
$supplies = $supplies_result ? $supplies_result->fetch_all(MYSQLI_ASSOC) : [];
displayAlert();

// ── Pagination ──────────────────────────────────────────────────────────────
$per_page    = 10;
$total_items = count($supplies);
$total_pages = max(1, ceil($total_items / $per_page));
$cur_page    = max(1, min($total_pages, intval($_GET['supplies_page'] ?? 1)));
$offset      = ($cur_page - 1) * $per_page;
$page_items  = array_slice($supplies, $offset, $per_page);
?>

<script>
var UPLOADS_URL = '<?php echo UPLOADS_URL; ?>';
</script>

<style>
.adm-pagination { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; padding:1rem 0 .25rem; }
.adm-pag-info { font-size:.83rem; color:var(--text-light); font-weight:600; }
.adm-pag-info strong { color:var(--text-dark); }
.adm-pag-btns { display:flex; gap:.35rem; align-items:center; flex-wrap:wrap; }
.adm-pag-btn { display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px; padding:0 .55rem; border-radius:8px; border:1.5px solid var(--border-color); background:#fff; color:var(--text-dark); font-size:.82rem; font-weight:700; font-family:inherit; cursor:pointer; transition:all .2s; text-decoration:none; }
.adm-pag-btn:hover { border-color:var(--primary-color); color:var(--primary-color); background:rgba(21,101,192,.06); }
.adm-pag-btn.active { background:var(--primary-color); border-color:var(--primary-color); color:#fff; }
.adm-pag-btn.disabled { opacity:.35; cursor:not-allowed; pointer-events:none; }
.adm-pag-btn i { font-size:.75rem; }

.confirm-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:1rem; }
.confirm-modal-overlay.active { display:flex; animation:cfFadeIn .2s ease; }
@keyframes cfFadeIn { from{opacity:0} to{opacity:1} }
.confirm-modal-box { background:#fff; border-radius:16px; max-width:420px; width:100%; box-shadow:0 24px 70px rgba(0,0,0,.25); overflow:hidden; animation:cfSlideUp .28s cubic-bezier(.22,.68,0,1.1); }
@keyframes cfSlideUp { from{transform:translateY(20px) scale(.97);opacity:0} to{transform:translateY(0) scale(1);opacity:1} }
.confirm-modal-header { background:linear-gradient(135deg,#DC3545,#C82333); padding:1.5rem 1.8rem 1.2rem; display:flex; align-items:center; gap:1rem; }
.confirm-modal-icon { width:48px; height:48px; background:rgba(255,255,255,.18); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.3rem; color:#fff; flex-shrink:0; }
.confirm-modal-header h3 { color:#fff; margin:0; font-size:1.1rem; font-weight:800; }
.confirm-modal-header p  { color:rgba(255,255,255,.8); margin:3px 0 0; font-size:.83rem; }
.confirm-modal-body { padding:1.6rem 1.8rem; }
.confirm-modal-body > p { color:#374151; font-size:.97rem; line-height:1.7; margin:0; }
.confirm-modal-footer { padding:.9rem 1.8rem 1.3rem; display:flex; gap:.65rem; justify-content:flex-end; border-top:1px solid #e2e8f0; background:#FAFBFF; }
.confirm-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.58rem 1.3rem; border-radius:8px; font-size:.88rem; font-weight:700; cursor:pointer; border:none; transition:all .2s; font-family:inherit; }
.confirm-btn-cancel { background:#F1F5F9; color:#4A5568; } .confirm-btn-cancel:hover { background:#E2E8F0; }
.confirm-btn-delete { background:linear-gradient(135deg,#DC3545,#C82333); color:#fff; } .confirm-btn-delete:hover { background:linear-gradient(135deg,#C82333,#A71D2A); transform:translateY(-1px); box-shadow:0 6px 18px rgba(220,53,69,.35); }

.sup-tabs { display:flex; gap:.45rem; flex-wrap:wrap; margin-bottom:1.2rem; }
.sup-tab { display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .9rem; border-radius:50px; font-size:.8rem; font-weight:700; border:1.5px solid var(--border-color); background:#fff; color:var(--text-light); cursor:pointer; transition:all .2s; font-family:inherit; }
.sup-tab.active, .sup-tab:hover { border-color:var(--primary-color); background:var(--primary-color); color:#fff; }
.sup-tab .cbadge { background:rgba(255,255,255,.22); color:#fff; border-radius:50px; padding:0 .38rem; font-size:.68rem; font-weight:800; }
.sup-tab:not(.active) .cbadge { background:var(--light-bg); color:var(--text-light); }

.sup-search-wrap { display:flex; align-items:center; gap:.5rem; background:#fff; border:1.5px solid var(--border-color); border-radius:8px; padding:.4rem .9rem; max-width:240px; transition:border-color .2s,box-shadow .2s; }
.sup-search-wrap:focus-within { border-color:var(--primary-color); box-shadow:0 0 0 3px rgba(21,101,192,.1); }
.sup-search-wrap i { color:#9CA3AF; font-size:.85rem; }
.sup-search-wrap input { border:none; outline:none; font-size:.84rem; color:#374151; width:100%; font-family:inherit; background:transparent; }
#supNoResults { display:none; }
#supImgPreview img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid var(--primary-color); display:block; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h2 style="margin:0;">Manage Supplies
            <span style="font-size:.8rem;font-weight:600;color:var(--text-light);margin-left:.5rem;">(<?php echo $total_items; ?> total)</span>
        </h2>
        <p style="margin:.2rem 0 0;font-size:.83rem;color:var(--text-light);"><?php echo $total_items; ?> total supplies across <?php echo count($categories); ?> categories</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
        <div class="sup-search-wrap"><i class="fas fa-search"></i><input type="text" id="supSearchInput" placeholder="Search supplies…" oninput="filterSupplies(this.value)"></div>
        <button class="btn-add" style="background:#6A1B9A;" onclick="openAddCategoryModal()"><i class="fas fa-tag"></i> Add Category</button>
        <button class="btn-add" onclick="openAddSupplyModal()"><i class="fas fa-plus"></i> Add Supply</button>
    </div>
</div>

<!-- Tab bar -->
<div class="sup-tabs" id="supTabs">
    <button class="sup-tab active" data-cat="all" onclick="switchTab(this,'all')"><i class="fas fa-layer-group"></i> All <span class="cbadge"><?php echo $total_items; ?></span></button>
    <?php foreach ($categories as $cat):
        $cnt = array_reduce($supplies, function($c,$s) use ($cat){ return $c+($s['category_id']==$cat['id']?1:0); }, 0);
    ?>
    <button class="sup-tab" data-cat="<?php echo $cat['id']; ?>" onclick="switchTab(this,'<?php echo $cat['id']; ?>')">
        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($cat['category_name']); ?> <span class="cbadge"><?php echo $cnt; ?></span>
    </button>
    <?php endforeach; ?>
    <button class="sup-tab" data-cat="categories" onclick="switchTab(this,'categories')" style="margin-left:auto;"><i class="fas fa-folder"></i> Categories</button>
</div>

<!-- Supplies Table -->
<div id="suppliesView">
    <div class="admin-card">
        <?php if (!empty($supplies)): ?>
        <table class="admin-table" id="suppliesTable">
            <thead><tr><th>Order</th><th>Supply</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($page_items as $sup): ?>
                <tr data-cat="<?php echo $sup['category_id']; ?>" data-search="<?php echo strtolower(htmlspecialchars($sup['supply_name'].' '.$sup['description'].' '.$sup['category_name'])); ?>">
                    <td style="font-size:.85rem;text-align:center;"><?php echo $sup['sort_order']; ?></td>
                    <td><span style="font-weight:700;font-size:.9rem;"><?php echo htmlspecialchars($sup['supply_name']); ?></span></td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:.35rem;background:#1565C020;color:#1565C0;border:1px solid #1565C040;border-radius:50px;padding:.2rem .65rem;font-size:.76rem;font-weight:800;white-space:nowrap;">
                            <i class="fas fa-tag" style="font-size:.65rem;"></i> <?php echo htmlspecialchars($sup['category_name'] ?? 'Uncategorized'); ?>
                        </span>
                    </td>
                    <td><span class="badge" style="background-color:<?php echo $sup['is_active']?'#28A745':'#6C757D'; ?>;"><?php echo $sup['is_active']?'Active':'Inactive'; ?></span></td>
                    <td><?php echo formatDate($sup['created_at']); ?></td>
                    <td>
                        <div class="admin-actions">
                            <button class="btn-edit" onclick="editSupply(<?php echo $sup['id']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" onclick="openSupplyDeleteConfirm(<?php echo $sup['id']; ?>,'<?php echo addslashes(htmlspecialchars($sup['supply_name'])); ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr id="supNoResults"><td colspan="6" style="text-align:center;color:var(--text-light);padding:2.5rem;"><i class="fas fa-search" style="margin-right:.4rem;"></i> No supplies match your search.</td></tr>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="adm-pagination">
            <div class="adm-pag-info">
                Showing <strong><?php echo $offset+1; ?>–<?php echo min($offset+$per_page,$total_items); ?></strong>
                of <strong><?php echo $total_items; ?></strong> supplies
            </div>
            <div class="adm-pag-btns">
                <a href="?page=supplies&supplies_page=<?php echo max(1,$cur_page-1); ?>" class="adm-pag-btn <?php echo $cur_page<=1?'disabled':''; ?>"><i class="fas fa-chevron-left"></i></a>
                <?php
                $range=2; $start=max(1,$cur_page-$range); $end=min($total_pages,$cur_page+$range);
                if($start>1) echo '<a href="?page=supplies&supplies_page=1" class="adm-pag-btn">1</a>';
                if($start>2) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';
                for($p=$start;$p<=$end;$p++) echo '<a href="?page=supplies&supplies_page='.$p.'" class="adm-pag-btn'.($p===$cur_page?' active':'').'">'.$p.'</a>';
                if($end<$total_pages-1) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';
                if($end<$total_pages) echo '<a href="?page=supplies&supplies_page='.$total_pages.'" class="adm-pag-btn">'.$total_pages.'</a>';
                ?>
                <a href="?page=supplies&supplies_page=<?php echo min($total_pages,$cur_page+1); ?>" class="adm-pag-btn <?php echo $cur_page>=$total_pages?'disabled':''; ?>"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div style="text-align:center;color:var(--text-light);padding:3rem;">
            <i class="fas fa-boxes" style="font-size:3rem;margin-bottom:1rem;display:block;opacity:.4;"></i>
            No supplies yet. <a href="#" onclick="openAddSupplyModal();return false;">Add one now</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Categories View -->
<div id="categoriesView" style="display:none;">
    <div class="admin-card">
        <?php if (!empty($categories)): ?>
        <table class="admin-table">
            <thead><tr><th>Category</th><th>Description</th><th>Supplies</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($categories as $cat):
                    $cnt=array_reduce($supplies,function($c,$s) use ($cat){return $c+($s['category_id']==$cat['id']?1:0);},0);
                ?>
                <tr>
                    <td><div style="display:flex;align-items:center;gap:.65rem;"><div style="width:36px;height:36px;border-radius:8px;flex-shrink:0;background:#1565C0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem;"><i class="fas fa-tag"></i></div><span style="font-weight:700;font-size:.9rem;"><?php echo htmlspecialchars($cat['category_name']); ?></span></div></td>
                    <td style="font-size:.83rem;color:var(--text-light);max-width:240px;"><?php echo !empty($cat['description'])?htmlspecialchars(substr($cat['description'],0,80)).'…':'<span style="opacity:.4;">—</span>'; ?></td>
                    <td style="text-align:center;"><span class="badge" style="background-color:var(--primary-color);"><?php echo $cnt; ?></span></td>
                    <td style="font-size:.85rem;text-align:center;"><?php echo $cat['sort_order']; ?></td>
                    <td><span class="badge" style="background-color:<?php echo $cat['is_active']?'#28A745':'#6C757D'; ?>;"><?php echo $cat['is_active']?'Active':'Inactive'; ?></span></td>
                    <td><div class="admin-actions"><button class="btn-edit" onclick="editCategory(<?php echo $cat['id']; ?>)"><i class="fas fa-edit"></i></button><button class="btn-delete" onclick="openCatDeleteConfirm(<?php echo $cat['id']; ?>,'<?php echo addslashes(htmlspecialchars($cat['category_name'])); ?>')"><i class="fas fa-trash"></i></button></div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align:center;color:var(--text-light);padding:3rem;"><i class="fas fa-folder-open" style="font-size:3rem;margin-bottom:1rem;display:block;opacity:.4;"></i>No categories yet. <a href="#" onclick="openAddCategoryModal();return false;">Add one now</a></div>
        <?php endif; ?>
    </div>
</div>

<!-- Supply Modal -->
<div class="modal-overlay" id="supplyModal">
    <div class="modal-content" style="max-width:520px;max-height:90vh;overflow-y:auto;">
        <div class="modal-header"><h2 id="supplyModalTitle">Add New Supply</h2><button class="modal-close" onclick="closeSupplyModal()">&times;</button></div>
        <form id="supplyForm" enctype="multipart/form-data" onsubmit="submitSupplyForm(event)">
            <input type="hidden" id="supplyId" name="supply_id" value="">
            <div class="form-group"><label for="supCategory">Category <span style="color:#DC3545;">*</span></label><select id="supCategory" name="category_id" class="form-control" required><option value="">— Select category —</option><?php foreach($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="supName">Supply Name <span style="color:#DC3545;">*</span></label><input type="text" id="supName" name="supply_name" class="form-control" required></div>
            <div class="form-group"><label for="supOrder">Sort Order</label><input type="number" id="supOrder" name="sort_order" class="form-control" value="0" min="0"></div>
            <div class="form-group"><label for="supDescription">Description</label><textarea id="supDescription" name="description" class="form-control" rows="3"></textarea></div>
            <div class="form-group"><label for="supImage">Image</label><input type="file" id="supImage" name="supply_image" class="form-control" accept="image/*" onchange="previewSupplyImg(this)"><small style="color:var(--text-light);">JPG, PNG, WEBP — max 5MB</small><div id="supImgPreview" style="margin-top:.6rem;"></div></div>
            <div class="form-group"><label><input type="checkbox" id="supActive" name="is_active" value="1" checked> Active (visible on website)</label></div>
            <div class="modal-footer"><button type="button" class="btn-secondary-main" style="background:#6C757D;color:#fff;border:none;" onclick="closeSupplyModal()">Cancel</button><button type="submit" class="btn-add" id="supplySubmitBtn">Save Supply</button></div>
        </form>
    </div>
</div>

<!-- Category Modal -->
<div class="modal-overlay" id="categoryModal">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header"><h2 id="categoryModalTitle">Add Category</h2><button class="modal-close" onclick="closeCategoryModal()">&times;</button></div>
        <form id="categoryForm" onsubmit="submitCategoryForm(event)">
            <input type="hidden" id="categoryId" name="category_id" value="">
            <div class="form-group"><label for="catName">Category Name <span style="color:#DC3545;">*</span></label><input type="text" id="catName" name="category_name" class="form-control" required></div>
            <div class="form-group"><label for="catDesc">Description</label><textarea id="catDesc" name="description" class="form-control" rows="2"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group"><label for="catOrder">Sort Order</label><input type="number" id="catOrder" name="sort_order" class="form-control" value="0" min="0"></div>
                <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:.5rem;"><label><input type="checkbox" id="catActive" name="is_active" value="1" checked> Active</label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-secondary-main" style="background:#6C757D;color:#fff;border:none;" onclick="closeCategoryModal()">Cancel</button><button type="submit" class="btn-add">Save Category</button></div>
        </form>
    </div>
</div>

<!-- Delete Confirms -->
<div class="confirm-modal-overlay" id="supplyDeleteConfirm">
    <div class="confirm-modal-box"><div class="confirm-modal-header"><div class="confirm-modal-icon"><i class="fas fa-trash-alt"></i></div><div><h3>Delete Supply</h3><p>This action cannot be undone</p></div></div><div class="confirm-modal-body"><p>Are you sure you want to delete <strong id="delSupName">this supply</strong>?</p></div><div class="confirm-modal-footer"><button class="confirm-btn confirm-btn-cancel" onclick="closeSupplyDeleteConfirm()"><i class="fas fa-times"></i> Cancel</button><button class="confirm-btn confirm-btn-delete" onclick="executeSupplyDelete()"><i class="fas fa-trash-alt"></i> Yes, Delete</button></div></div>
</div>
<div class="confirm-modal-overlay" id="catDeleteConfirm">
    <div class="confirm-modal-box"><div class="confirm-modal-header"><div class="confirm-modal-icon"><i class="fas fa-trash-alt"></i></div><div><h3>Delete Category</h3><p>All supplies will also be deleted</p></div></div><div class="confirm-modal-body"><p>Delete category <strong id="delCatName">this category</strong>?</p><div style="margin-top:.8rem;background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:.65rem 1rem;font-size:.84rem;color:#92400E;font-weight:600;display:flex;gap:.5rem;align-items:center;"><i class="fas fa-exclamation-triangle" style="color:#D97706;"></i>All supplies under this category will be permanently removed.</div></div><div class="confirm-modal-footer"><button class="confirm-btn confirm-btn-cancel" onclick="closeCatDeleteConfirm()"><i class="fas fa-times"></i> Cancel</button><button class="confirm-btn confirm-btn-delete" onclick="executeCatDelete()"><i class="fas fa-trash-alt"></i> Yes, Delete</button></div></div>
</div>

<script>
function switchTab(btn, cat) {
    document.querySelectorAll('.sup-tab').forEach(function(t){t.classList.remove('active');});
    btn.classList.add('active');
    var supView=document.getElementById('suppliesView'),catView=document.getElementById('categoriesView');
    if(cat==='categories'){supView.style.display='none';catView.style.display='';return;}
    catView.style.display='none';supView.style.display='';
    var rows=document.querySelectorAll('#suppliesTable tbody tr:not(#supNoResults)'),visible=0;
    rows.forEach(function(r){var show=(cat==='all')||(r.getAttribute('data-cat')===String(cat));r.style.display=show?'':'none';if(show)visible++;});
    document.getElementById('supNoResults').style.display=visible===0?'':'none';
}
function filterSupplies(q){q=q.toLowerCase().trim();var rows=document.querySelectorAll('#suppliesTable tbody tr:not(#supNoResults)'),visible=0;rows.forEach(function(r){var matches=q===''||(r.getAttribute('data-search')||'').includes(q);r.style.display=matches?'':'none';if(matches)visible++;});document.getElementById('supNoResults').style.display=visible===0?'':'none';}

function openAddSupplyModal(){document.getElementById('supplyModalTitle').innerText='Add New Supply';document.getElementById('supplyForm').reset();document.getElementById('supplyId').value='';document.getElementById('supImgPreview').innerHTML='';document.getElementById('supplySubmitBtn').disabled=false;document.getElementById('supplySubmitBtn').innerHTML='Save Supply';document.getElementById('supplyModal').classList.add('active');}
function closeSupplyModal(){document.getElementById('supplyModal').classList.remove('active');}
function previewSupplyImg(input){var preview=document.getElementById('supImgPreview');preview.innerHTML='';if(input.files&&input.files[0]){var reader=new FileReader();reader.onload=function(e){var img=document.createElement('img');img.src=e.target.result;preview.appendChild(img);};reader.readAsDataURL(input.files[0]);}}
function editSupply(id){fetch('../backend/get_supply.php?id='+id).then(function(r){return r.json();}).then(function(data){if(!data.success){alert('Could not load supply.');return;}var s=data.data;document.getElementById('supplyModalTitle').innerText='Edit Supply';document.getElementById('supplyId').value=s.id;document.getElementById('supCategory').value=s.category_id;document.getElementById('supName').value=s.supply_name;document.getElementById('supDescription').value=s.description||'';document.getElementById('supOrder').value=s.sort_order;document.getElementById('supActive').checked=s.is_active==1;document.getElementById('supplySubmitBtn').disabled=false;document.getElementById('supplySubmitBtn').innerHTML='Save Supply';var preview=document.getElementById('supImgPreview');preview.innerHTML='';if(s.image_path){var img=document.createElement('img');img.src=UPLOADS_URL+s.image_path;img.alt=s.supply_name;img.onerror=function(){preview.innerHTML='';};preview.appendChild(img);}document.getElementById('supplyModal').classList.add('active');});}
function submitSupplyForm(e){e.preventDefault();var btn=document.getElementById('supplySubmitBtn');btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving…';fetch('../backend/save_supply.php',{method:'POST',body:new FormData(document.getElementById('supplyForm'))}).then(function(r){return r.json();}).then(function(data){if(data.success){window.location.href='dashboard.php?page=supplies';}else{alert('Error: '+data.message);btn.disabled=false;btn.innerHTML='Save Supply';}}).catch(function(){alert('Network error.');btn.disabled=false;btn.innerHTML='Save Supply';});}

function openAddCategoryModal(){document.getElementById('categoryModalTitle').innerText='Add Category';document.getElementById('categoryForm').reset();document.getElementById('categoryId').value='';document.getElementById('categoryModal').classList.add('active');}
function closeCategoryModal(){document.getElementById('categoryModal').classList.remove('active');}
function editCategory(id){fetch('../backend/get_category.php?id='+id).then(function(r){return r.json();}).then(function(data){if(!data.success){alert('Could not load category.');return;}var c=data.data;document.getElementById('categoryModalTitle').innerText='Edit Category';document.getElementById('categoryId').value=c.id;document.getElementById('catName').value=c.category_name;document.getElementById('catDesc').value=c.description||'';document.getElementById('catOrder').value=c.sort_order;document.getElementById('catActive').checked=c.is_active==1;document.getElementById('categoryModal').classList.add('active');});}
function submitCategoryForm(e){e.preventDefault();fetch('../backend/save_category.php',{method:'POST',body:new FormData(document.getElementById('categoryForm'))}).then(function(r){return r.json();}).then(function(data){if(data.success){window.location.href='dashboard.php?page=supplies';}else{alert('Error: '+data.message);}});}

var _delSupId=null,_delCatId=null;
function openSupplyDeleteConfirm(id,name){_delSupId=id;document.getElementById('delSupName').textContent='"'+name+'"';document.getElementById('supplyDeleteConfirm').classList.add('active');}
function closeSupplyDeleteConfirm(){document.getElementById('supplyDeleteConfirm').classList.remove('active');_delSupId=null;}
function executeSupplyDelete(){if(_delSupId)window.location.href='../backend/delete_supply.php?id='+_delSupId;}
function openCatDeleteConfirm(id,name){_delCatId=id;document.getElementById('delCatName').textContent='"'+name+'"';document.getElementById('catDeleteConfirm').classList.add('active');}
function closeCatDeleteConfirm(){document.getElementById('catDeleteConfirm').classList.remove('active');_delCatId=null;}
function executeCatDelete(){if(_delCatId)window.location.href='../backend/delete_category.php?id='+_delCatId;}

['supplyModal','categoryModal','supplyDeleteConfirm','catDeleteConfirm'].forEach(function(id){document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');});});
document.addEventListener('keydown',function(e){if(e.key==='Escape')['supplyModal','categoryModal','supplyDeleteConfirm','catDeleteConfirm'].forEach(function(id){document.getElementById(id).classList.remove('active');});});
</script>