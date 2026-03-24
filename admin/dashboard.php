<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$page  = isset($_GET['page']) ? sanitize($_GET['page']) : 'overview';
$stats = [
    'total_clients'   => countRecords($conn, 'clients'),
    'total_services'  => countRecords($conn, 'services'),
    'total_messages'  => countRecords($conn, 'contact_messages'),
    'total_supplies'  => countRecords($conn, 'supplies'),
    'total_updates'   => countRecords($conn, 'updates'),
    'unread_messages' => 0,
];

$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['unread_messages'] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NAM Builders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout">

    <!-- ── Sidebar ── -->
    <div class="admin-sidebar" id="adminSidebar">

        <div style="padding:1.4rem 1.5rem; border-bottom:1px solid rgba(255,255,255,0.15); display:flex; align-items:center; gap:.75rem; flex-shrink:0; background:var(--primary-color);">
            <img src="../css/assets/logo.png"
                 alt="NAM Builders"
                 style="height:36px;width:auto;object-fit:contain;flex-shrink:0;"
                 onerror="this.style.display='none'">
            <div>
                <div style="font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:.95rem;color:#fff;line-height:1.2;">
                    NAM Builders and Supply Corp
                </div>
                <div style="font-size:.68rem;color:rgba(255,255,255,.7);font-weight:600;letter-spacing:.06em;text-transform:uppercase;">
                    Admin Panel
                </div>
            </div>
        </div>

        <nav style="padding:.75rem 0;flex:1;display:flex;flex-direction:column;">
            <div style="flex:1;">
                <a href="dashboard.php" class="admin-nav-link <?php echo $page === 'overview'  ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line" style="width:16px;text-align:center;"></i> Overview
                </a>
                <a href="dashboard.php?page=clients" class="admin-nav-link <?php echo $page === 'clients'  ? 'active' : ''; ?>">
                    <i class="fas fa-users" style="width:16px;text-align:center;"></i> Clients
                </a>
                <a href="dashboard.php?page=services" class="admin-nav-link <?php echo $page === 'services' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs" style="width:16px;text-align:center;"></i> Services
                </a>
                <a href="dashboard.php?page=supplies" class="admin-nav-link <?php echo $page === 'supplies' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes" style="width:16px;text-align:center;"></i> Supplies
                    <?php if ($stats['total_supplies'] > 0): ?>
                        <span style="background:rgba(255,255,255,.18);color:rgba(255,255,255,.85);border-radius:50px;padding:0 .45rem;font-size:.68rem;font-weight:800;margin-left:auto;flex-shrink:0;">
                            <?php echo $stats['total_supplies']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="dashboard.php?page=updates" class="admin-nav-link <?php echo $page === 'updates'  ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper" style="width:16px;text-align:center;"></i> Updates
                    <?php if ($stats['total_updates'] > 0): ?>
                        <span style="background:rgba(255,255,255,.18);color:rgba(255,255,255,.85);border-radius:50px;padding:0 .45rem;font-size:.68rem;font-weight:800;margin-left:auto;flex-shrink:0;">
                            <?php echo $stats['total_updates']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="dashboard.php?page=stats" class="admin-nav-link <?php echo $page === 'stats'    ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar" style="width:16px;text-align:center;"></i> Stats
                </a>
                <a href="dashboard.php?page=messages" class="admin-nav-link <?php echo $page === 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope" style="width:16px;text-align:center;"></i> Messages
                    <?php if ($stats['unread_messages'] > 0): ?>
                        <span style="background:#FFC107;color:#333;border-radius:50%;width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;font-size:.68rem;margin-left:auto;font-weight:800;flex-shrink:0;">
                            <?php echo $stats['unread_messages']; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <div style="padding-bottom:.5rem;">
                <hr style="margin:.5rem 0 .5rem .8rem;border-color:rgba(255,255,255,0.2);">
                <a href="../backend/logout.php" class="admin-nav-link logout-link">
                    <i class="fas fa-sign-out-alt" style="width:16px;text-align:center;"></i> Logout
                </a>
            </div>
        </nav>

    </div><!-- /.admin-sidebar -->

    <!-- ── Main body ── -->
    <div class="admin-body">

        <div class="admin-header">
            <div style="display:flex;align-items:center;gap:.75rem;min-width:0;">
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Open menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h3>
                    <?php
                    switch ($page) {
                        case 'clients':  echo 'Manage Clients';       break;
                        case 'services': echo 'Manage Services';      break;
                        case 'supplies': echo 'Manage Supplies';      break;
                        case 'updates':  echo 'Updates &amp; Posts';  break;
                        case 'stats':    echo 'Stats Section';        break;
                        case 'messages': echo 'Contact Messages';     break;
                        default:         echo 'Dashboard Overview';
                    }
                    ?>
                </h3>
            </div>

            <div class="admin-top-right">
                <span style="font-size:.88rem;color:var(--text-light);">
                    Welcome, <strong style="color:var(--text-dark);"><?php echo sanitize($_SESSION['admin_username']); ?></strong>
                </span>
                <?php if ($stats['unread_messages'] > 0): ?>
                    <a href="dashboard.php?page=messages"
                       style="background:#FFC107;color:#333;border-radius:50px;padding:.25rem .7rem;font-size:.78rem;font-weight:800;text-decoration:none;display:flex;align-items:center;gap:.35rem;">
                        <i class="fas fa-envelope"></i> <?php echo $stats['unread_messages']; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div><!-- /.admin-header -->

        <div class="admin-main">
            <div class="container-lg" style="max-width:1200px;">
                <?php
                switch ($page) {
                    case 'clients':  require 'pages/clients.php';  break;
                    case 'services': require 'pages/services.php'; break;
                    case 'supplies': require 'pages/supplies.php'; break;
                    case 'updates':  require 'pages/updates.php';  break;
                    case 'stats':    require 'pages/stats.php';    break;
                    case 'messages': require 'pages/messages.php'; break;
                    default:         require 'pages/overview.php';
                }
                ?>
            </div>
        </div><!-- /.admin-main -->

    </div><!-- /.admin-body -->
</div><!-- /.admin-layout -->

<!-- Toast container -->
<div id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin.js"></script>

</body>
</html>