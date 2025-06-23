<?php
/**
 * API xóa hội nghị
 */
// CORS and API headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Conference.php';

// Kiểm tra xác thực
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'organizer')) {
    echo json_encode([
        'status' => false,
        'message' => 'Permission denied',
        'code' => 403
    ]);
    exit;
}

// Support both POST and DELETE methods
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get conference ID
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // For DELETE: Parse from URL or request body
        $parts = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
        $conferenceId = intval(end($parts));
        
        // If no ID in URL, try to get from request body
        if ($conferenceId === 0) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            $conferenceId = intval($json_data['id'] ?? 0);
        }
    } else {
        // For POST: Get from form data or JSON
        $json_data = json_decode(file_get_contents('php://input'), true);
        $request_data = !empty($json_data) ? $json_data : $_POST;
        $conferenceId = intval($request_data['id'] ?? 0);
    }
    
    // Validate conference ID
    if ($conferenceId <= 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid conference ID',
            'code' => 400
        ]);
        exit;
    }

    $conferenceModel = new Conference();

    // Check if conference exists
    $conference = $conferenceModel->getConferenceById($conferenceId);
    if (!$conference) {
        echo json_encode([
            'status' => false,
            'message' => 'Conference not found',
            'code' => 404
        ]);
        exit;
    }

    // Check permissions: organizers can only delete their own conferences
    if ($_SESSION['user_role'] === 'organizer' && $conference['created_by'] != $_SESSION['user_id']) {
        echo json_encode([
            'status' => false,
            'message' => 'You do not have permission to delete this conference',
            'code' => 403
        ]);
        exit;
    }

    // Delete the conference
    $result = $conferenceModel->deleteConference($conferenceId);

    if ($result) {
        echo json_encode([
            'status' => true,
            'message' => 'Conference deleted successfully',
            'data' => ['id' => $conferenceId],
            'code' => 200
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to delete conference',
            'code' => 500
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Method not allowed',
        'code' => 405
    ]);
}
?>