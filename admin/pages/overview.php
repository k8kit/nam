<?php
// Admin Overview Page
// Pull fresh counts for all modules
$unread_msgs   = 0;
$unread_result = $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read = 0");
if ($unread_result) $unread_msgs = $unread_result->fetch_assoc()['c'];

$replied_msgs   = 0;
$replied_result = $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_replied = 1");
if ($replied_result) $replied_msgs = $replied_result->fetch_assoc()['c'];

$active_services   = 0;
$as_result = $conn->query("SELECT COUNT(*) as c FROM services WHERE is_active = 1");
if ($as_result) $active_services = $as_result->fetch_assoc()['c'];

$active_clients   = 0;
$ac_result = $conn->query("SELECT COUNT(*) as c FROM clients WHERE is_active = 1");
if ($ac_result) $active_clients = $ac_result->fetch_assoc()['c'];

$active_supplies   = 0;
$asupp_result = $conn->query("SELECT COUNT(*) as c FROM supplies WHERE is_active = 1");
if ($asupp_result) $active_supplies = $asupp_result->fetch_assoc()['c'];

$active_updates   = 0;
$au_result = $conn->query("SELECT COUNT(*) as c FROM updates WHERE is_active = 1");
if ($au_result) $active_updates = $au_result->fetch_assoc()['c'];

$supply_categories_count = 0;
$sc_result = $conn->query("SELECT COUNT(*) as c FROM supply_categories WHERE is_active = 1");
if ($sc_result) $supply_categories_count = $sc_result->fetch_assoc()['c'];

// Recent messages (last 5)
$recent_msgs_result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$recent_msgs = $recent_msgs_result ? $recent_msgs_result->fetch_all(MYSQLI_ASSOC) : [];
?>

<style>
.ov-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.2rem;
    margin-bottom: 2rem;
}
.ov-stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.4rem 1.5rem;
    border: 1.5px solid var(--border-color);
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
    display: flex; align-items: center; justify-content: space-between;
    transition: box-shadow .2s, border-color .2s;
}
.ov-stat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.09); border-color: rgba(21,101,192,.2); }
.ov-stat-label { font-size: .78rem; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: .06em; margin-bottom: .3rem; }
.ov-stat-value { font-family: 'Barlow Condensed', sans-serif; font-size: 2.2rem; font-weight: 800; line-height: 1; }
.ov-stat-sub   { font-size: .72rem; color: var(--text-light); margin-top: .25rem; }
.ov-stat-icon  { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }

.ov-section-title { font-size: 1rem; font-weight: 800; color: var(--text-dark); margin: 0 0 1.2rem; letter-spacing: .02em; display: flex; align-items: center; gap: .5rem; }
.ov-section-title i { color: var(--primary-color); font-size: .95rem; }

.ov-quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .75rem;
    margin-bottom: 2rem;
}
.ov-quick-btn {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .5rem; padding: 1.2rem 1rem;
    background: #fff; border: 1.5px solid var(--border-color); border-radius: 12px;
    text-decoration: none; color: var(--text-dark);
    font-size: .82rem; font-weight: 700; letter-spacing: .03em; text-align: center;
    transition: all .22s; cursor: pointer;
}
.ov-quick-btn:hover { border-color: var(--primary-color); color: var(--primary-color); background: rgba(21,101,192,.05); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(21,101,192,.12); text-decoration: none; }
.ov-quick-btn i { font-size: 1.3rem; }

.ov-recent-table { width: 100%; border-collapse: collapse; }
.ov-recent-table th { padding: .65rem 1rem; text-align: left; font-size: .75rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--text-light); border-bottom: 2px solid var(--border-color); background: #FAFBFF; }
.ov-recent-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--border-color); font-size: .88rem; }
.ov-recent-table tbody tr:hover { background: #F8FAFC; }
.ov-badge { display: inline-flex; align-items: center; gap: .28rem; padding: .2rem .6rem; border-radius: 50px; font-size: .7rem; font-weight: 800; white-space: nowrap; }
.ov-badge.unread  { background: #FEF9C3; color: #854D0E; border: 1px solid #FDE68A; }
.ov-badge.read    { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }
.ov-badge.replied { background: #EFF6FF; color: #1D4ED8; border: 1px solid #93C5FD; }

.ov-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
@media (max-width: 768px) { .ov-two-col { grid-template-columns: 1fr; } .ov-stat-grid { grid-template-columns: repeat(2,1fr); } }
</style>

<!-- ── Stat Cards ── -->
<div class="ov-stat-grid">
    <div class="ov-stat-card">
        <div>
            <div class="ov-stat-label">Clients</div>
            <div class="ov-stat-value" style="color:var(--primary-color);"><?php echo $stats['total_clients']; ?></div>
            <div class="ov-stat-sub"><?php echo $active_clients; ?> active</div>
        </div>
        <div class="ov-stat-icon" style="background:rgba(21,101,192,.1);color:var(--primary-color);"><i class="fas fa-users"></i></div>
    </div>
    <div class="ov-stat-card">
        <div>
            <div class="ov-stat-label">Services</div>
            <div class="ov-stat-value" style="color:#007BFF;"><?php echo $stats['total_services']; ?></div>
            <div class="ov-stat-sub"><?php echo $active_services; ?> active</div>
        </div>
        <div class="ov-stat-icon" style="background:rgba(0,123,255,.1);color:#007BFF;"><i class="fas fa-cogs"></i></div>
    </div>
    <div class="ov-stat-card">
        <div>
            <div class="ov-stat-label">Supplies</div>
            <div class="ov-stat-value" style="color:#E65100;"><?php echo $stats['total_supplies']; ?></div>
            <div class="ov-stat-sub"><?php echo $supply_categories_count; ?> categories · <?php echo $active_supplies; ?> active</div>
        </div>
        <div class="ov-stat-icon" style="background:rgba(230,81,0,.1);color:#E65100;"><i class="fas fa-boxes"></i></div>
    </div>
    <div class="ov-stat-card">
        <div>
            <div class="ov-stat-label">Updates / Posts</div>
            <div class="ov-stat-value" style="color:#6A1B9A;"><?php echo $stats['total_updates']; ?></div>
            <div class="ov-stat-sub"><?php echo $active_updates; ?> published</div>
        </div>
        <div class="ov-stat-icon" style="background:rgba(106,27,154,.1);color:#6A1B9A;"><i class="fas fa-newspaper"></i></div>
    </div>
    <div class="ov-stat-card">
        <div>
            <div class="ov-stat-label">Messages</div>
            <div class="ov-stat-value" style="color:#28A745;"><?php echo $stats['total_messages']; ?></div>
            <div class="ov-stat-sub"><?php echo $replied_msgs; ?> replied</div>
        </div>
        <div class="ov-stat-icon" style="background:rgba(40,167,69,.1);color:#28A745;"><i class="fas fa-envelope"></i></div>
    </div>
    <div class="ov-stat-card" style="<?php echo $stats['unread_messages']>0?'border-color:#FFC107;':'' ?>">
        <div>
            <div class="ov-stat-label">Unread</div>
            <div class="ov-stat-value" style="color:#FFC107;"><?php echo $stats['unread_messages']; ?></div>
            <div class="ov-stat-sub">need attention</div>
        </div>
        <div class="ov-stat-icon" style="background:rgba(255,193,7,.15);color:#FFC107;"><i class="fas fa-bell"></i></div>
    </div>
</div>

<!-- ── Two columns: Quick Actions + Recent Messages ── -->
<div class="ov-two-col">
    <!-- Quick Actions -->
    <div class="admin-card" style="margin-bottom:0;">
        <div class="ov-section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
        <div class="ov-quick-grid">
            <a href="dashboard.php?page=clients" class="ov-quick-btn"><i class="fas fa-users" style="color:var(--primary-color);"></i>Manage Clients</a>
            <a href="dashboard.php?page=services" class="ov-quick-btn"><i class="fas fa-cogs" style="color:#007BFF;"></i>Manage Services</a>
            <a href="dashboard.php?page=supplies" class="ov-quick-btn"><i class="fas fa-boxes" style="color:#E65100;"></i>Manage Supplies</a>
            <a href="dashboard.php?page=updates" class="ov-quick-btn"><i class="fas fa-newspaper" style="color:#6A1B9A;"></i>Manage Posts</a>
            <a href="dashboard.php?page=stats" class="ov-quick-btn"><i class="fas fa-chart-bar" style="color:#00ACC1;"></i>Edit Stats</a>
            <a href="dashboard.php?page=messages" class="ov-quick-btn" style="<?php echo $stats['unread_messages']>0?'border-color:#FFC107;color:#856404;':'' ?>">
                <i class="fas fa-envelope" style="color:#FFC107;"></i>
                Messages<?php echo $stats['unread_messages']>0?' ('.$stats['unread_messages'].')':''; ?>
            </a>
        </div>

        <!-- Module Summary -->
        <div class="ov-section-title" style="margin-top:1.5rem;"><i class="fas fa-layer-group"></i> Content Summary</div>
        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
            <?php
            $modules = [
                ['label'=>'Clients',         'total'=>$stats['total_clients'],   'active'=>$active_clients,      'page'=>'clients',  'icon'=>'fas fa-users',    'color'=>'var(--primary-color)'],
                ['label'=>'Services',         'total'=>$stats['total_services'],  'active'=>$active_services,     'page'=>'services', 'icon'=>'fas fa-cogs',     'color'=>'#007BFF'],
                ['label'=>'Supply Categories','total'=>$supply_categories_count,  'active'=>$supply_categories_count,'page'=>'supplies','icon'=>'fas fa-tag',    'color'=>'#6A1B9A'],
                ['label'=>'Supplies',         'total'=>$stats['total_supplies'],  'active'=>$active_supplies,     'page'=>'supplies', 'icon'=>'fas fa-boxes',    'color'=>'#E65100'],
                ['label'=>'Posts / Updates',  'total'=>$stats['total_updates'],   'active'=>$active_updates,      'page'=>'updates',  'icon'=>'fas fa-newspaper','color'=>'#6A1B9A'],
            ];
            foreach ($modules as $m):
            ?>
            <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:.6rem 0;"><i class="<?php echo $m['icon']; ?>" style="color:<?php echo $m['color']; ?>;margin-right:.5rem;width:16px;text-align:center;"></i><?php echo $m['label']; ?></td>
                <td style="text-align:center;padding:.6rem .5rem;font-weight:700;"><?php echo $m['total']; ?></td>
                <td style="text-align:right;padding:.6rem 0;">
                    <span style="background:rgba(40,167,69,.12);color:#28A745;border-radius:50px;padding:.15rem .55rem;font-size:.72rem;font-weight:800;"><?php echo $m['active']; ?> active</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Recent Messages -->
    <div class="admin-card" style="margin-bottom:0;">
        <div class="ov-section-title" style="justify-content:space-between;">
            <span><i class="fas fa-envelope"></i> Recent Messages</span>
            <a href="dashboard.php?page=messages" style="font-size:.78rem;font-weight:700;color:var(--primary-color);text-decoration:none;">View all →</a>
        </div>
        <?php if (!empty($recent_msgs)): ?>
        <table class="ov-recent-table">
            <thead><tr><th>From</th><th>Service</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($recent_msgs as $msg):
                    if (!empty($msg['is_replied']))    { $sk='replied'; $si='fa-reply';  $sl='Replied'; }
                    elseif (!empty($msg['is_read']))   { $sk='read';    $si='fa-check';  $sl='Read'; }
                    else                               { $sk='unread';  $si='fa-circle'; $sl='Unread'; }
                ?>
                <tr>
                    <td style="font-weight:600;"><?php echo sanitize($msg['full_name']); ?><br><span style="font-size:.75rem;color:var(--text-light);font-weight:400;"><?php echo sanitize($msg['email']); ?></span></td>
                    <td style="font-size:.8rem;color:var(--text-light);"><?php echo sanitize($msg['service_needed'] ?? '—'); ?></td>
                    <td style="font-size:.78rem;color:var(--text-light);white-space:nowrap;"><?php echo date('M j', strtotime($msg['created_at'])); ?></td>
                    <td><span class="ov-badge <?php echo $sk; ?>"><i class="fas <?php echo $si; ?>"></i> <?php echo $sl; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($stats['total_messages'] > 5): ?>
        <div style="text-align:center;padding-top:.75rem;font-size:.8rem;color:var(--text-light);">
            <?php echo $stats['total_messages'] - 5; ?> more messages →
            <a href="dashboard.php?page=messages" style="color:var(--primary-color);font-weight:700;text-decoration:none;">View all</a>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <p style="text-align:center;color:var(--text-light);padding:2rem 1rem;"><i class="fas fa-inbox"></i> No messages yet.</p>
        <?php endif; ?>
    </div>
</div>