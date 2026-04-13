<?php
ob_start();

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $service_id   = (isset($_POST['service_id']) && $_POST['service_id'] !== '') ? intval($_POST['service_id']) : null;
    $service_name = trim($_POST['service_name'] ?? '');
    $description  = trim($_POST['description']  ?? '');
    $sort_order   = intval($_POST['sort_order']  ?? 0);
    $is_active    = isset($_POST['is_active'])   ? 1 : 0;
    $client_id    = (isset($_POST['client_id']) && $_POST['client_id'] !== '') ? intval($_POST['client_id']) : null;

    if ($service_name === '') throw new Exception('Service name is required.');
    if ($description === '')  throw new Exception('Service description is required.');

    // Collect uploaded images
    $uploaded_paths = [];
    if (!empty($_FILES['service_images']['name'][0])) {
        $count = count($_FILES['service_images']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['service_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $file = [
                'name'     => $_FILES['service_images']['name'][$i],
                'type'     => $_FILES['service_images']['type'][$i],
                'tmp_name' => $_FILES['service_images']['tmp_name'][$i],
                'error'    => $_FILES['service_images']['error'][$i],
                'size'     => $_FILES['service_images']['size'][$i],
            ];
            $upload = uploadFile($file, UPLOADS_PATH . 'services/');
            if (!$upload['success']) throw new Exception($upload['error']);
            $uploaded_paths[] = 'services/' . $upload['filename'];
        }
    }

    if ($service_id) {
        // UPDATE — bind_param: s=service_name, s=description, i=sort_order, i=is_active, i=client_id, i=service_id
        $stmt = $conn->prepare(
            "UPDATE services
             SET service_name = ?, description = ?, sort_order = ?, is_active = ?, client_id = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->bind_param('ssiiii', $service_name, $description, $sort_order, $is_active, $client_id, $service_id);
        $stmt->execute();
        $stmt->close();

        if (!empty($uploaded_paths)) {
            $nr = $conn->query("SELECT COALESCE(MAX(sort_order),-1)+1 AS nxt FROM service_images WHERE service_id={$service_id}");
            $next_order = $nr ? (int)$nr->fetch_assoc()['nxt'] : 0;
            $ins = $conn->prepare("INSERT INTO service_images (service_id, image_path, sort_order) VALUES (?,?,?)");
            foreach ($uploaded_paths as $path) {
                $ins->bind_param('isi', $service_id, $path, $next_order);
                $ins->execute();
                $next_order++;
            }
            $ins->close();
        }

        $img_note = !empty($uploaded_paths) ? ' '.count($uploaded_paths).' new image(s) added.' : '';
        $response = ['success' => true, 'message' => "Service \"{$service_name}\" updated successfully.{$img_note}"];

    } else {
        // INSERT — client_id may be null; use 's' type (null-safe in PHP 8, works in 7.4+ too)
        $stmt = $conn->prepare(
            "INSERT INTO services (service_name, description, sort_order, is_active, client_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssiis', $service_name, $description, $sort_order, $is_active, $client_id);
        $stmt->execute();
        $new_id = $conn->insert_id;
        $stmt->close();

        if (!empty($uploaded_paths)) {
            $ins = $conn->prepare("INSERT INTO service_images (service_id, image_path, sort_order) VALUES (?,?,?)");
            foreach ($uploaded_paths as $i => $path) {
                $ins->bind_param('isi', $new_id, $path, $i);
                $ins->execute();
            }
            $ins->close();
        }

        $img_note = !empty($uploaded_paths) ? ' '.count($uploaded_paths).' image(s) uploaded.' : '';
        $response = ['success' => true, 'message' => "Service \"{$service_name}\" added successfully.{$img_note}"];
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);