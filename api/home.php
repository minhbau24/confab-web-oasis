<?php
/**
 * API for index.php - Home page
 */
// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

require_once '../includes/config.php';
require_once '../classes/Conference.php';
require_once '../classes/User.php';

// Xác định trạng thái yêu cầu
header('Content-Type: application/json');

// Initialize response object
$response = [
    'success' => true,
    'data' => [
        'stats' => [],
        'featuredConferences' => [],
        'testimonials' => [],
        'user' => null
    ]
];

try {
    // Kiểm tra trạng thái đăng nhập
    $isLoggedIn = isset($_SESSION['user_id']);
    
    // Get user info if logged in
    if ($isLoggedIn) {
        $user = new User();
        $userData = $user->getUserById($_SESSION['user_id']);
        
        if ($userData) {
            $response['data']['user'] = [
                'id' => $userData['id'],
                'name' => $userData['firstName'] . ' ' . $userData['lastName'],
                'email' => $userData['email'],
                'role' => $userData['role']
            ];
        }
    }    $conference = new Conference();
    $db = Database::getInstance();
    
    // Get featured conferences (3 upcoming)
    $featuredConferences = $conference->getAllConferences(3);
    $response['data']['featuredConferences'] = $featuredConferences;
    
    // Get system stats
    $stats = [];
    
    // Total conferences
    $countQuery = "SELECT COUNT(*) as count FROM conferences";
    $result = $db->fetch($countQuery);
    $stats['conferences'] = $result ? $result['count'] : 0;
    
    // Total speakers
    $query = "SELECT COUNT(*) as count FROM speakers";
    $result = $db->fetch($query);
    $stats['speakers'] = $result ? $result['count'] : 0;
    
    // Total attendees
    $query = "SELECT SUM(attendees) as count FROM conferences";
    $result = $db->fetch($query);
    $stats['attendees'] = $result && isset($result['count']) ? $result['count'] : 0;
    
    // Unique locations
    $query = "SELECT COUNT(DISTINCT location) as count FROM conferences";
    $result = $db->fetch($query);
    $stats['locations'] = $result ? $result['count'] : 0;
    
    $response['data']['stats'] = $stats;
      // Get testimonials
    $query = "SELECT * FROM testimonials WHERE is_featured = 1 LIMIT 3";
    $testimonials = $db->fetchAll($query);
    $response['data']['testimonials'] = $testimonials;
    $response['data']['testimonials'] = $testimonials;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>
