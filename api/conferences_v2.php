<?php
/**
 * Confab Web Oasis - Enhanced Conferences API
 * API nâng cao cho hệ thống quản lý hội nghị với schema hoàn chỉnh
 */

// Define this file as an API endpoint to prevent HTML redirects
define('API_ENDPOINT', true);

// Tắt hiển thị lỗi để tránh trả về HTML thay vì JSON
ini_set('display_errors', 0);
error_reporting(0);

try {
    require_once '../includes/config.php';
    require_once '../classes/Database.php';

    // Xử lý CORS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

    // Khởi tạo Database
    $database = new Database();
    $conn = $database->getConnection();

    // Xác định request method
    $method = $_SERVER['REQUEST_METHOD'];

    // Xử lý OPTIONS request cho CORS
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Helper functions
    function send_response($status, $data = null, $message = '', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'status' => $status,
            'data' => $data,
            'message' => $message,
            'timestamp' => date('c')
        ]);
        exit();
    }

    function validate_input($data, $required_fields = []) {
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "Field '$field' is required";
            }
        }
        
        return $errors;
    }

    function sanitize_input($data) {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Xử lý các request
    switch ($method) {
        case 'GET':
            handle_get_request($conn);
            break;
        case 'POST':
            handle_post_request($conn);
            break;
        case 'PUT':
            handle_put_request($conn);
            break;
        case 'DELETE':
            handle_delete_request($conn);
            break;
        default:
            send_response(false, null, 'Method not allowed', 405);
    }

    // GET request handler
    function handle_get_request($conn) {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';

        if ($id > 0) {
            // Lấy một hội nghị cụ thể
            get_conference_by_id($conn, $id);
        } elseif ($action === 'featured') {
            // Lấy hội nghị nổi bật
            get_featured_conferences($conn);
        } elseif ($action === 'trending') {
            // Lấy hội nghị trending
            get_trending_conferences($conn);
        } elseif ($action === 'upcoming') {
            // Lấy hội nghị sắp tới
            get_upcoming_conferences($conn);
        } elseif ($action === 'categories') {
            // Lấy danh sách categories
            get_categories($conn);
        } elseif ($action === 'venues') {
            // Lấy danh sách venues
            get_venues($conn);
        } elseif ($action === 'speakers') {
            // Lấy danh sách speakers
            get_speakers($conn);
        } elseif ($action === 'stats') {
            // Lấy thống kê
            get_conference_stats($conn);
        } else {
            // Lấy danh sách hội nghị với filter
            get_conferences_list($conn);
        }
    }

    // POST request handler
    function handle_post_request($conn) {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : 'create';

        switch ($action) {
            case 'create':
                create_conference($conn, $input);
                break;
            case 'register':
                register_for_conference($conn, $input);
                break;
            case 'feedback':
                submit_feedback($conn, $input);
                break;
            case 'search':
                search_conferences($conn, $input);
                break;
            default:
                send_response(false, null, 'Invalid action', 400);
        }
    }

    // PUT request handler
    function handle_put_request($conn) {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            update_conference($conn, $id, $input);
        } else {
            send_response(false, null, 'Conference ID is required', 400);
        }
    }

    // DELETE request handler
    function handle_delete_request($conn) {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            delete_conference($conn, $id);
        } else {
            send_response(false, null, 'Conference ID is required', 400);
        }
    }

    // Lấy một hội nghị cụ thể
    function get_conference_by_id($conn, $id) {
        try {
            $sql = "
                SELECT 
                    c.*, 
                    cat.name as category_name,
                    cat.color as category_color,
                    cat.icon as category_icon,
                    v.name as venue_name,
                    v.address as venue_address,
                    v.capacity as venue_capacity,
                    v.facilities as venue_facilities,
                    u.firstName as creator_first_name,
                    u.lastName as creator_last_name,
                    cs.avg_rating,
                    cs.total_registrations,
                    cs.confirmed_registrations,
                    cs.occupancy_rate
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN users u ON c.created_by = u.id
                LEFT JOIN conference_stats cs ON c.id = cs.id
                WHERE c.id = ? AND c.deleted_at IS NULL
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $conference = $result->fetch_assoc();
                
                // Decode JSON fields
                $json_fields = ['gallery', 'tags', 'agenda', 'contact_info', 'social_links', 'sponsor_info'];
                foreach ($json_fields as $field) {
                    if ($conference[$field]) {
                        $conference[$field] = json_decode($conference[$field], true);
                    }
                }

                // Lấy speakers
                $conference['speakers'] = get_conference_speakers($conn, $id);
                
                // Lấy schedule
                $conference['schedule'] = get_conference_schedule($conn, $id);

                send_response(true, $conference);
            } else {
                send_response(false, null, 'Conference not found', 404);
            }
        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy danh sách hội nghị với filter
    function get_conferences_list($conn) {
        try {
            // Lấy tham số filter
            $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
            $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
            $venue_id = isset($_GET['venue_id']) ? intval($_GET['venue_id']) : 0;
            $status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
            $type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
            $date_from = isset($_GET['date_from']) ? sanitize_input($_GET['date_from']) : '';
            $date_to = isset($_GET['date_to']) ? sanitize_input($_GET['date_to']) : '';
            $price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
            $price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 0;
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 12;
            $sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'start_date';
            $order = isset($_GET['order']) ? sanitize_input($_GET['order']) : 'ASC';

            // Validate sort and order
            $allowed_sort = ['start_date', 'title', 'price', 'created_at', 'current_attendees'];
            $allowed_order = ['ASC', 'DESC'];
            
            if (!in_array($sort, $allowed_sort)) $sort = 'start_date';
            if (!in_array(strtoupper($order), $allowed_order)) $order = 'ASC';

            // Build WHERE clause
            $where_conditions = ['c.deleted_at IS NULL'];
            $params = [];
            $param_types = '';

            if (!empty($search)) {
                $where_conditions[] = '(c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ?)';
                $search_param = '%' . $search . '%';
                $params = array_merge($params, [$search_param, $search_param, $search_param]);
                $param_types .= 'sss';
            }

            if ($category_id > 0) {
                $where_conditions[] = 'c.category_id = ?';
                $params[] = $category_id;
                $param_types .= 'i';
            }

            if ($venue_id > 0) {
                $where_conditions[] = 'c.venue_id = ?';
                $params[] = $venue_id;
                $param_types .= 'i';
            }

            if (!empty($status)) {
                $where_conditions[] = 'c.status = ?';
                $params[] = $status;
                $param_types .= 's';
            }

            if (!empty($type)) {
                $where_conditions[] = 'c.type = ?';
                $params[] = $type;
                $param_types .= 's';
            }

            if (!empty($date_from)) {
                $where_conditions[] = 'DATE(c.start_date) >= ?';
                $params[] = $date_from;
                $param_types .= 's';
            }

            if (!empty($date_to)) {
                $where_conditions[] = 'DATE(c.start_date) <= ?';
                $params[] = $date_to;
                $param_types .= 's';
            }

            if ($price_min > 0) {
                $where_conditions[] = 'c.price >= ?';
                $params[] = $price_min;
                $param_types .= 'd';
            }

            if ($price_max > 0) {
                $where_conditions[] = 'c.price <= ?';
                $params[] = $price_max;
                $param_types .= 'd';
            }

            $where_clause = implode(' AND ', $where_conditions);

            // Count total records
            $count_sql = "
                SELECT COUNT(*) as total
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN venues v ON c.venue_id = v.id
                WHERE $where_clause
            ";

            $count_stmt = $conn->prepare($count_sql);
            if (!empty($params)) {
                $count_stmt->bind_param($param_types, ...$params);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_records = $count_result->fetch_assoc()['total'];

            // Calculate pagination
            $total_pages = ceil($total_records / $limit);
            $offset = ($page - 1) * $limit;

            // Get conferences
            $sql = "
                SELECT 
                    c.id, c.title, c.slug, c.short_description, c.description,
                    c.start_date, c.end_date, c.type, c.format, c.location,
                    c.price, c.currency, c.early_bird_price, c.early_bird_until,
                    c.capacity, c.current_attendees, c.status, c.visibility,
                    c.featured, c.trending, c.image, c.level, c.language,
                    cat.name as category_name, cat.color as category_color, cat.icon as category_icon,
                    v.name as venue_name, v.city as venue_city,
                    cs.avg_rating, cs.total_feedback, cs.occupancy_rate, cs.booking_status
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN conference_stats cs ON c.id = cs.id
                WHERE $where_clause
                ORDER BY c.$sort $order
                LIMIT ? OFFSET ?
            ";

            $stmt = $conn->prepare($sql);
            $params[] = $limit;
            $params[] = $offset;
            $param_types .= 'ii';

            if (!empty($params)) {
                $stmt->bind_param($param_types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $conferences = [];
            while ($row = $result->fetch_assoc()) {
                // Decode JSON fields
                if ($row['tags']) {
                    $row['tags'] = json_decode($row['tags'], true);
                }
                $conferences[] = $row;
            }

            $response_data = [
                'conferences' => $conferences,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'total_records' => $total_records,
                    'per_page' => $limit,
                    'has_next' => $page < $total_pages,
                    'has_prev' => $page > 1
                ],
                'filters' => [
                    'search' => $search,
                    'category_id' => $category_id,
                    'venue_id' => $venue_id,
                    'status' => $status,
                    'type' => $type,
                    'date_from' => $date_from,
                    'date_to' => $date_to,
                    'price_min' => $price_min,
                    'price_max' => $price_max
                ]
            ];

            send_response(true, $response_data);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy hội nghị nổi bật
    function get_featured_conferences($conn) {
        try {
            $limit = isset($_GET['limit']) ? min(20, max(1, intval($_GET['limit']))) : 6;
            
            $sql = "
                SELECT 
                    c.id, c.title, c.slug, c.short_description,
                    c.start_date, c.end_date, c.location, c.price, c.currency,
                    c.image, c.current_attendees, c.capacity,
                    cat.name as category_name, cat.color as category_color,
                    cs.avg_rating, cs.occupancy_rate
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN conference_stats cs ON c.id = cs.id
                WHERE c.featured = 1 AND c.status = 'published' AND c.deleted_at IS NULL
                ORDER BY c.start_date ASC
                LIMIT ?
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $conferences = [];
            while ($row = $result->fetch_assoc()) {
                $conferences[] = $row;
            }

            send_response(true, $conferences);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy categories
    function get_categories($conn) {
        try {
            $sql = "
                SELECT 
                    c.*, 
                    COUNT(conf.id) as conference_count
                FROM categories c
                LEFT JOIN conferences conf ON c.id = conf.category_id AND conf.status = 'published' AND conf.deleted_at IS NULL
                WHERE c.status = 'active'
                GROUP BY c.id
                ORDER BY c.sort_order ASC, c.name ASC
            ";
            
            $result = $conn->query($sql);
            $categories = [];
            
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }

            send_response(true, $categories);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy venues
    function get_venues($conn) {
        try {
            $sql = "
                SELECT 
                    v.*, 
                    COUNT(c.id) as conference_count
                FROM venues v
                LEFT JOIN conferences c ON v.id = c.venue_id AND c.status = 'published' AND c.deleted_at IS NULL
                WHERE v.status = 'active'
                GROUP BY v.id
                ORDER BY v.name ASC
            ";
            
            $result = $conn->query($sql);
            $venues = [];
            
            while ($row = $result->fetch_assoc()) {
                // Decode JSON fields
                $json_fields = ['facilities', 'amenities', 'images'];
                foreach ($json_fields as $field) {
                    if ($row[$field]) {
                        $row[$field] = json_decode($row[$field], true);
                    }
                }
                $venues[] = $row;
            }

            send_response(true, $venues);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Helper function: Get conference speakers
    function get_conference_speakers($conn, $conference_id) {
        $sql = "
            SELECT 
                s.*, cs.role, cs.talk_title, cs.talk_description, cs.status as speaker_status
            FROM conference_speakers cs
            JOIN speakers s ON cs.speaker_id = s.id
            WHERE cs.conference_id = ? AND cs.status = 'confirmed'
            ORDER BY cs.role ASC, s.name ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $conference_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $speakers = [];
        while ($row = $result->fetch_assoc()) {
            // Decode JSON fields
            $json_fields = ['specialties', 'languages'];
            foreach ($json_fields as $field) {
                if ($row[$field]) {
                    $row[$field] = json_decode($row[$field], true);
                }
            }
            $speakers[] = $row;
        }

        return $speakers;
    }

    // Helper function: Get conference schedule
    function get_conference_schedule($conn, $conference_id) {
        $sql = "
            SELECT 
                ss.*, s.name as speaker_name, s.image as speaker_image
            FROM schedule_sessions ss
            LEFT JOIN speakers s ON ss.speaker_id = s.id
            WHERE ss.conference_id = ? AND ss.status = 'scheduled'
            ORDER BY ss.session_date ASC, ss.start_time ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $conference_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $schedule = [];
        while ($row = $result->fetch_assoc()) {
            // Decode JSON fields
            $json_fields = ['additional_speakers', 'materials', 'tags'];
            foreach ($json_fields as $field) {
                if ($row[$field]) {
                    $row[$field] = json_decode($row[$field], true);
                }
            }
            $schedule[] = $row;
        }

        return $schedule;
    }

    // Create conference
    function create_conference($conn, $data) {
        // TODO: Implement authentication check
        
        $required_fields = ['title', 'description', 'start_date', 'location', 'capacity'];
        $errors = validate_input($data, $required_fields);
        
        if (!empty($errors)) {
            send_response(false, null, implode(', ', $errors), 400);
        }

        // Sanitize input
        $data = sanitize_input($data);
        
        try {
            // Generate slug
            $slug = strtolower(str_replace(' ', '-', $data['title']));
            
            $sql = "
                INSERT INTO conferences (
                    title, slug, description, short_description, start_date, end_date,
                    location, capacity, price, currency, status, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'sssssssissi',
                $data['title'],
                $slug,
                $data['description'],
                $data['short_description'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['location'],
                $data['capacity'],
                $data['price'] ?? 0,
                $data['currency'] ?? 'VND',
                $data['status'] ?? 'draft',
                $_SESSION['user_id'] ?? 1
            );
            
            if ($stmt->execute()) {
                $conference_id = $conn->insert_id;
                send_response(true, ['id' => $conference_id], 'Conference created successfully');
            } else {
                send_response(false, null, 'Failed to create conference', 500);
            }

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Update conference
    function update_conference($conn, $id, $data) {
        // TODO: Implement authentication and authorization check
        
        try {
            $data = sanitize_input($data);
            
            $sql = "
                UPDATE conferences 
                SET title = ?, description = ?, short_description = ?, start_date = ?, 
                    end_date = ?, location = ?, capacity = ?, price = ?, currency = ?, 
                    status = ?, updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'ssssssisssi',
                $data['title'],
                $data['description'],
                $data['short_description'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['location'],
                $data['capacity'],
                $data['price'] ?? 0,
                $data['currency'] ?? 'VND',
                $data['status'] ?? 'draft',
                $id
            );
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    send_response(true, ['id' => $id], 'Conference updated successfully');
                } else {
                    send_response(false, null, 'Conference not found or no changes made', 404);
                }
            } else {
                send_response(false, null, 'Failed to update conference', 500);
            }

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Delete conference (soft delete)
    function delete_conference($conn, $id) {
        // TODO: Implement authentication and authorization check
        
        try {
            $sql = "UPDATE conferences SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    send_response(true, ['id' => $id], 'Conference deleted successfully');
                } else {
                    send_response(false, null, 'Conference not found', 404);
                }
            } else {
                send_response(false, null, 'Failed to delete conference', 500);
            }

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy hội nghị trending
    function get_trending_conferences($conn) {
        try {
            $limit = isset($_GET['limit']) ? min(20, max(1, intval($_GET['limit']))) : 6;
            
            $sql = "
                SELECT 
                    c.id, c.title, c.slug, c.short_description,
                    c.start_date, c.end_date, c.location, c.price, c.currency,
                    c.image, c.current_attendees, c.capacity,
                    cat.name as category_name, cat.color as category_color,
                    cs.avg_rating, cs.occupancy_rate, cs.total_registrations
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN conference_stats cs ON c.id = cs.id
                WHERE c.trending = 1 AND c.status = 'published' AND c.deleted_at IS NULL
                ORDER BY cs.total_registrations DESC, c.start_date ASC
                LIMIT ?
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $conferences = [];
            while ($row = $result->fetch_assoc()) {
                $conferences[] = $row;
            }

            send_response(true, $conferences);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy hội nghị sắp tới
    function get_upcoming_conferences($conn) {
        try {
            $limit = isset($_GET['limit']) ? min(20, max(1, intval($_GET['limit']))) : 6;
            
            $sql = "
                SELECT 
                    c.id, c.title, c.slug, c.short_description,
                    c.start_date, c.end_date, c.location, c.price, c.currency,
                    c.image, c.current_attendees, c.capacity,
                    cat.name as category_name, cat.color as category_color,
                    cs.avg_rating, cs.occupancy_rate
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN conference_stats cs ON c.id = cs.id
                WHERE c.status = 'published' AND c.start_date > NOW() AND c.deleted_at IS NULL
                ORDER BY c.start_date ASC
                LIMIT ?
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $conferences = [];
            while ($row = $result->fetch_assoc()) {
                $conferences[] = $row;
            }

            send_response(true, $conferences);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy speakers
    function get_speakers($conn) {
        try {
            $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
            $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 20;
            
            $where_clause = "s.status = 'active'";
            $params = [];
            $param_types = '';
            
            if (!empty($search)) {
                $where_clause .= " AND (s.name LIKE ? OR s.company LIKE ? OR s.bio LIKE ?)";
                $search_param = '%' . $search . '%';
                $params = [$search_param, $search_param, $search_param];
                $param_types = 'sss';
            }
            
            $sql = "
                SELECT 
                    s.id, s.name, s.slug, s.title, s.company, s.short_bio, s.image,
                    s.website, s.linkedin, s.twitter, s.specialties, s.languages,
                    s.rating, s.total_talks, s.status
                FROM speakers s
                WHERE $where_clause
                ORDER BY s.rating DESC, s.total_talks DESC, s.name ASC
                LIMIT ?
            ";
            
            $stmt = $conn->prepare($sql);
            $params[] = $limit;
            $param_types .= 'i';
            
            if (!empty($params)) {
                $stmt->bind_param($param_types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $speakers = [];
            while ($row = $result->fetch_assoc()) {
                // Decode JSON fields
                $json_fields = ['specialties', 'languages'];
                foreach ($json_fields as $field) {
                    if ($row[$field]) {
                        $row[$field] = json_decode($row[$field], true);
                    }
                }
                $speakers[] = $row;
            }

            send_response(true, $speakers);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Lấy thống kê
    function get_conference_stats($conn) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_conferences,
                    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_conferences,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_conferences,
                    COUNT(CASE WHEN start_date > NOW() THEN 1 END) as upcoming_conferences,
                    AVG(price) as avg_price,
                    SUM(current_attendees) as total_attendees
                FROM conferences 
                WHERE deleted_at IS NULL
            ";
            
            $result = $conn->query($sql);
            $stats = $result->fetch_assoc();

            // Thống kê đăng ký
            $reg_sql = "
                SELECT 
                    COUNT(*) as total_registrations,
                    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_registrations,
                    SUM(price_paid) as total_revenue
                FROM registrations
            ";
            
            $reg_result = $conn->query($reg_sql);
            $reg_stats = $reg_result->fetch_assoc();

            $combined_stats = array_merge($stats, $reg_stats);

            send_response(true, $combined_stats);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Register for conference
    function register_for_conference($conn, $data) {
        // TODO: Implement proper authentication
        
        $required_fields = ['user_id', 'conference_id'];
        $errors = validate_input($data, $required_fields);
        
        if (!empty($errors)) {
            send_response(false, null, implode(', ', $errors), 400);
        }

        try {
            // Check if conference exists and is available
            $conf_sql = "SELECT * FROM conferences WHERE id = ? AND status = 'published' AND deleted_at IS NULL";
            $conf_stmt = $conn->prepare($conf_sql);
            $conf_stmt->bind_param('i', $data['conference_id']);
            $conf_stmt->execute();
            $conference = $conf_stmt->get_result()->fetch_assoc();

            if (!$conference) {
                send_response(false, null, 'Conference not found or not available', 404);
            }

            // Check if already registered
            $check_sql = "SELECT id FROM registrations WHERE user_id = ? AND conference_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('ii', $data['user_id'], $data['conference_id']);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                send_response(false, null, 'Already registered for this conference', 400);
            }

            // Check capacity
            if ($conference['current_attendees'] >= $conference['capacity']) {
                send_response(false, null, 'Conference is full', 400);
            }

            // Create registration
            $reg_sql = "
                INSERT INTO registrations (user_id, conference_id, ticket_type, price_paid, status)
                VALUES (?, ?, ?, ?, ?)
            ";
            
            $stmt = $conn->prepare($reg_sql);
            $ticket_type = $data['ticket_type'] ?? 'regular';
            $price = $data['price_paid'] ?? $conference['price'];
            $status = 'pending';
            
            $stmt->bind_param('iisds', $data['user_id'], $data['conference_id'], $ticket_type, $price, $status);
            
            if ($stmt->execute()) {
                $registration_id = $conn->insert_id;
                send_response(true, ['registration_id' => $registration_id], 'Registration successful');
            } else {
                send_response(false, null, 'Registration failed', 500);
            }

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Submit feedback
    function submit_feedback($conn, $data) {
        $required_fields = ['user_id', 'conference_id', 'overall_rating'];
        $errors = validate_input($data, $required_fields);
        
        if (!empty($errors)) {
            send_response(false, null, implode(', ', $errors), 400);
        }

        // Validate rating
        if ($data['overall_rating'] < 1 || $data['overall_rating'] > 5) {
            send_response(false, null, 'Rating must be between 1 and 5', 400);
        }

        try {
            // Check if already submitted feedback
            $check_sql = "SELECT id FROM feedback WHERE user_id = ? AND conference_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('ii', $data['user_id'], $data['conference_id']);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                send_response(false, null, 'Feedback already submitted', 400);
            }

            $sql = "
                INSERT INTO feedback (
                    user_id, conference_id, overall_rating, content_rating, 
                    speaker_rating, venue_rating, feedback_text, would_recommend
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'iiiiissi',
                $data['user_id'],
                $data['conference_id'],
                $data['overall_rating'],
                $data['content_rating'] ?? null,
                $data['speaker_rating'] ?? null,
                $data['venue_rating'] ?? null,
                $data['feedback_text'] ?? null,
                $data['would_recommend'] ?? null
            );
            
            if ($stmt->execute()) {
                send_response(true, ['feedback_id' => $conn->insert_id], 'Feedback submitted successfully');
            } else {
                send_response(false, null, 'Failed to submit feedback', 500);
            }

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

    // Search conferences
    function search_conferences($conn, $data) {
        $search_term = sanitize_input($data['search'] ?? '');
        
        if (empty($search_term)) {
            send_response(false, null, 'Search term is required', 400);
        }

        try {
            $sql = "
                SELECT 
                    c.id, c.title, c.slug, c.short_description,
                    c.start_date, c.location, c.price, c.image,
                    cat.name as category_name,
                    MATCH(c.title, c.description) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                WHERE (
                    MATCH(c.title, c.description) AGAINST(? IN NATURAL LANGUAGE MODE)
                    OR c.title LIKE ? 
                    OR c.location LIKE ?
                    OR cat.name LIKE ?
                ) AND c.status = 'published' AND c.deleted_at IS NULL
                ORDER BY relevance DESC, c.start_date ASC
                LIMIT 20
            ";
            
            $search_param = '%' . $search_term . '%';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssss', $search_term, $search_term, $search_param, $search_param, $search_param);
            $stmt->execute();
            $result = $stmt->get_result();

            $conferences = [];
            while ($row = $result->fetch_assoc()) {
                $conferences[] = $row;
            }

            send_response(true, $conferences);

        } catch (Exception $e) {
            send_response(false, null, 'Database error: ' . $e->getMessage(), 500);
        }
    }

} catch (Exception $e) {
    // Catch any uncaught exceptions
    error_log("Conferences API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Internal server error',
        'timestamp' => date('c')
    ]);
}
?>
