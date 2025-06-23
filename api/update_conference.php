<?php
/**
 * API cập nhật thông tin hội nghị
 */
// CORS and API headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Conference.php';

// Kiểm tra đăng nhập và quyền
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'organizer')) {
    echo json_encode([
        'status' => false,
        'message' => 'Permission denied',
        'code' => 403
    ]);
    exit;
}

// Support both POST and PUT methods
if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT')) {
    // Get data from request
    $json_data = json_decode(file_get_contents('php://input'), true);
    $request_data = !empty($json_data) ? $json_data : $_POST;
    
    // Check if conference ID is provided
    if (!isset($request_data['id'])) {
        echo json_encode([
            'status' => false,
            'message' => 'Conference ID is required',
            'code' => 400
        ]);
        exit;
    }

    $conferenceId = intval($request_data['id']);
    $conferenceModel = new Conference();
    $conference = $conferenceModel->getConferenceById($conferenceId);

    // Kiểm tra hội nghị tồn tại
    if (!$conference) {
        echo json_encode([
            'status' => false,
            'message' => 'Conference not found',
            'code' => 404
        ]);
        exit;
    }

    // Kiểm tra quyền: admin có thể sửa tất cả, organizer chỉ sửa của mình
    if ($_SESSION['user_role'] === 'organizer' && $conference['created_by'] != $_SESSION['user_id']) {
        echo json_encode([
            'status' => false,
            'message' => 'You do not have permission to edit this conference',
            'code' => 403
        ]);
        exit;
    }

    // Validate required fields
    $required_fields = ['title', 'description', 'start_date', 'end_date'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($request_data[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode([
            'status' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missing_fields),
            'code' => 400
        ]);
        exit;
    }

    // Prepare data for the new schema
    $data = [
        'title' => sanitize($request_data['title']),
        'slug' => sanitize($request_data['slug'] ?? strtolower(str_replace(' ', '-', $request_data['title']))),
        'description' => sanitize($request_data['description']),
        'short_description' => sanitize($request_data['short_description'] ?? substr($request_data['description'], 0, 150) . '...'),
        'start_date' => $request_data['start_date'],
        'end_date' => $request_data['end_date'],
        'venue_id' => intval($request_data['venue_id'] ?? $conference['venue_id']),
        'category_id' => intval($request_data['category_id'] ?? $conference['category_id']),
        'price' => floatval($request_data['price'] ?? 0),
        'capacity' => intval($request_data['capacity'] ?? 100),
        'status' => $request_data['status'] ?? 'active',
        'is_featured' => isset($request_data['is_featured']) ? ($request_data['is_featured'] ? 1 : 0) : $conference['is_featured'],
        'registration_enabled' => isset($request_data['registration_enabled']) ? ($request_data['registration_enabled'] ? 1 : 0) : $conference['registration_enabled'],
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Optional fields
    if (isset($request_data['website'])) {
        $data['website'] = sanitize($request_data['website']);
    }
    
    if (isset($request_data['contact_email'])) {
        $data['contact_email'] = sanitize($request_data['contact_email']);
    }
    
    if (isset($request_data['contact_phone'])) {
        $data['contact_phone'] = sanitize($request_data['contact_phone']);
    }
    
    // Process featured image upload if available
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $uploadDir = '../uploads/conferences/';

        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . basename($_FILES['featured_image']['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath)) {
            $data['featured_image'] = 'uploads/conferences/' . $fileName;
        } else {
            // Log error but continue with other updates
            error_log("Failed to upload image for conference ID: $conferenceId");
        }
    } elseif (!empty($request_data['featured_image_url'])) {
        // If an image URL was provided in JSON data
        $data['featured_image'] = sanitize($request_data['featured_image_url']);
    }

    // Update conference information
    $result = $conferenceModel->updateConference($conferenceId, $data);

    if ($result) {
        // Get updated conference data
        $updatedConference = $conferenceModel->getConferenceById($conferenceId);
        
        echo json_encode([
            'status' => true,
            'message' => 'Conference updated successfully',
            'data' => $updatedConference,
            'code' => 200
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update conference',
            'code' => 500
        ]);
    }
} else {
    // Method not supported
    echo json_encode([
        'status' => false,
        'message' => 'Method not allowed',
        'code' => 405
    ]);
}
?>