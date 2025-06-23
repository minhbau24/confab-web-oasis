<?php
/**
 * API endpoint for home page data
 * Pure API - chỉ trả dữ liệu JSON cho JS render
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Initialize response
    $response = [
        'success' => true,
        'data' => [
            'stats' => [],
            'featuredConferences' => [],
            'testimonials' => [],
            'user' => null
        ]
    ];

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION['user_id']);
    
    if ($isLoggedIn) {
        $response['data']['user'] = [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }

    // Get basic stats (fallback to static data if DB not available)
    $response['data']['stats'] = [
        'conferences' => 25,
        'speakers' => 42,
        'attendees' => 1200,
        'locations' => 8
    ];

    // Featured conferences data
    $response['data']['featuredConferences'] = [
        [
            'id' => 1,
            'title' => 'Vietnam Tech Summit 2025',
            'description' => 'Sự kiện công nghệ hàng đầu Việt Nam quy tụ các công ty khởi nghiệp tiên phong',
            'date' => '2025-09-15',
            'endDate' => '2025-09-17',
            'location' => 'TP. Hồ Chí Minh',
            'category' => 'Công nghệ',
            'price' => 1999000,
            'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop',
            'attendees' => 2600,
            'capacity' => 3000
        ],
        [
            'id' => 2,
            'title' => 'Digital Marketing Conference 2025',
            'description' => 'Hội nghị Marketing số hàng đầu với các chuyên gia trong ngành',
            'date' => '2025-08-20',
            'endDate' => '2025-08-22',
            'location' => 'Hà Nội',
            'category' => 'Marketing',
            'price' => 899000,
            'image' => 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=800&h=400&fit=crop',
            'attendees' => 1800,
            'capacity' => 2000
        ],
        [
            'id' => 3,
            'title' => 'Startup Ecosystem Vietnam 2025',
            'description' => 'Kết nối hệ sinh thái khởi nghiệp và đầu tư mạo hiểm',
            'date' => '2025-10-05',
            'endDate' => '2025-10-07',
            'location' => 'Đà Nẵng',
            'category' => 'Kinh doanh',
            'price' => 799000,
            'image' => 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&h=400&fit=crop',
            'attendees' => 1500,
            'capacity' => 1800
        ]
    ];

    // Testimonials data
    $response['data']['testimonials'] = [
        [
            'id' => 1,
            'name' => 'Nguyễn Văn A',
            'company' => 'Công ty ABC',
            'content' => 'Hội nghị được tổ chức rất chuyên nghiệp và đầy đủ tiện nghi. Tôi đã học được rất nhiều kiến thức bổ ích.',
            'rating' => 5,
            'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face',
            'position' => 'CEO'
        ],
        [
            'id' => 2,
            'name' => 'Trần Thị B',
            'company' => 'StartupXYZ',
            'content' => 'Tôi đã có được nhiều kết nối quý giá và học hỏi được nhiều kinh nghiệm từ các diễn giả.',
            'rating' => 5,
            'avatar' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face',
            'position' => 'Founder'
        ],
        [
            'id' => 3,
            'name' => 'Lê Văn C',
            'company' => 'TechCorp',
            'content' => 'Chất lượng diễn giả và nội dung hội nghị vượt xa mong đợi của tôi. Sẽ tham gia lại năm sau.',
            'rating' => 5,
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face',
            'position' => 'CTO'
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $errorResponse = [
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'data' => null
    ];
    
    http_response_code(500);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
}
?>
