<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    jsonResponse(false, 'Invalid service ID');
}

$service = getServiceById($conn, $id);

if ($service) {
    // Get all images for this service
    $img_result = $conn->query("SELECT * FROM service_images WHERE service_id = $id ORDER BY sort_order ASC");
    $service['images'] = $img_result ? $img_result->fetch_all(MYSQLI_ASSOC) : [];

    // Get client watermark info if assigned
    if (!empty($service['client_id'])) {
        $cid = intval($service['client_id']);
        $cl_result = $conn->query("SELECT id, client_name, image_path FROM clients WHERE id = $cid LIMIT 1");
        $cl_row = $cl_result ? $cl_result->fetch_assoc() : null;
        $service['client_name']       = $cl_row['client_name']  ?? '';
        $service['client_image_path'] = $cl_row['image_path']   ?? '';
    } else {
        $service['client_name']       = '';
        $service['client_image_path'] = '';
    }

    jsonResponse(true, 'Service found', $service);
} else {
    jsonResponse(false, 'Service not found');
}