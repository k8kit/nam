<?php
ob_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$response = ['success' => false, 'message' => ''];

try {
    $ids      = $_POST['ids']      ?? [];
    $labels   = $_POST['labels']   ?? [];
    $values   = $_POST['values']   ?? [];
    $suffixes = $_POST['suffixes'] ?? [];
    $orders   = $_POST['orders']   ?? [];
    $actives  = $_POST['actives']  ?? [];   // checkboxes: only present when checked

    if (empty($ids)) throw new Exception('No stats data received.');

    $stmt = $conn->prepare("
        UPDATE site_stats
        SET label = ?, value = ?, suffix = ?, sort_order = ?, is_active = ?
        WHERE id = ?
    ");

    foreach ($ids as $i => $id) {
        $id        = intval($id);
        $label     = sanitize($labels[$i]   ?? '');
        $value     = intval($values[$i]     ?? 0);
        $suffix    = trim($suffixes[$i]     ?? '');   /* trim only — sanitize strips + */
        $order     = intval($orders[$i]     ?? $i);
        $is_active = isset($actives[$id]) ? 1 : 0;

        if (empty($label)) continue;

        $stmt->bind_param('siisii', $label, $value, $suffix, $order, $is_active, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
    }

    $stmt->close();
    $response['success'] = true;
    $response['message'] = 'Stats saved.';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);