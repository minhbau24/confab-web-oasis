<?php
/**
 * API for venue operations
 */
// CORS and API headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../classes/Database.php';

// Initialize database connection
$db = Database::getInstance();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get venue by ID or list all venues
        $venueId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($venueId > 0) {
            // Get specific venue by ID
            $venue = $db->fetch(
                "SELECT * FROM venues WHERE id = ? AND status = 'active'",
                [$venueId]
            );
            
            if ($venue) {
                // Get conferences held at this venue
                $conferences = $db->fetchAll(
                    "SELECT c.id, c.title, c.slug, c.start_date, c.end_date, c.image,
                            cat.name as category_name, cat.color as category_color
                     FROM conferences c
                     LEFT JOIN categories cat ON c.category_id = cat.id
                     WHERE c.venue_id = ? AND c.status = 'published'
                     ORDER BY c.start_date DESC
                     LIMIT 5",
                    [$venueId]
                );
                
                $venue['conferences'] = $conferences;
                
                echo json_encode([
                    'status' => true,
                    'data' => $venue,
                    'code' => 200
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Venue not found',
                    'code' => 404
                ]);
            }
        } else {
            // List all venues with pagination
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $offset = ($page - 1) * $limit;
            
            // Default sorting is by name
            $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
            $sortOrder = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'desc' ? 'DESC' : 'ASC';
            
            // Optional city filter
            $cityFilter = isset($_GET['city']) ? $_GET['city'] : '';
            $countryFilter = isset($_GET['country']) ? $_GET['country'] : '';
            
            // Search term
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            
            // Build WHERE clause
            $where = ["status = 'active'"];
            $params = [];
            
            if ($cityFilter) {
                $where[] = "city = ?";
                $params[] = $cityFilter;
            }
            
            if ($countryFilter) {
                $where[] = "country = ?";
                $params[] = $countryFilter;
            }
            
            if ($search) {
                $where[] = "(name LIKE ? OR description LIKE ? OR address LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : '';
            
            // Validate and sanitize sort column
            $allowedSortColumns = ['name', 'city', 'country', 'capacity', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'name';
            }
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM venues {$whereClause}";
            $totalCount = $db->fetch($countQuery, $params)['total'];
            
            // Get venues
            $venues = $db->fetchAll(
                "SELECT id, name, slug, description, address, city, country, capacity, 
                        contact_name, contact_phone, contact_email, created_at, updated_at
                 FROM venues 
                 {$whereClause}
                 ORDER BY {$sortBy} {$sortOrder}
                 LIMIT {$offset}, {$limit}",
                $params
            );
            
            // Calculate pagination metadata
            $totalPages = ceil($totalCount / $limit);
            
            echo json_encode([
                'status' => true,
                'data' => [
                    'items' => $venues,
                    'pagination' => [
                        'total' => $totalCount,
                        'per_page' => $limit,
                        'current_page' => $page,
                        'last_page' => $totalPages,
                        'from' => $offset + 1,
                        'to' => min($offset + $limit, $totalCount)
                    ]
                ],
                'code' => 200
            ]);
        }
        break;
        
    case 'POST':
        // Create new venue - admin only
        if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'status' => false,
                'message' => 'Permission denied',
                'code' => 403
            ]);
            exit;
        }
        
        // Get data from request body (JSON)
        $json_data = json_decode(file_get_contents('php://input'), true);
        $data = !empty($json_data) ? $json_data : $_POST;
        
        // Check required fields
        $required = ['name', 'address', 'city', 'country', 'capacity'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            echo json_encode([
                'status' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missing),
                'code' => 400
            ]);
            exit;
        }
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(str_replace(' ', '-', $data['name']));
        }
        
        // Check if venue with this slug already exists
        $existingVenue = $db->fetch(
            "SELECT id FROM venues WHERE slug = ?",
            [$data['slug']]
        );
        
        if ($existingVenue) {
            echo json_encode([
                'status' => false,
                'message' => 'A venue with this slug already exists',
                'code' => 400
            ]);
            exit;
        }
        
        try {
            // Insert new venue
            $db->execute(
                "INSERT INTO venues (
                    name, slug, description, address, city, country, capacity,
                    contact_name, contact_phone, contact_email, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())",
                [
                    $data['name'],
                    $data['slug'],
                    $data['description'] ?? '',
                    $data['address'],
                    $data['city'],
                    $data['country'],
                    $data['capacity'],
                    $data['contact_name'] ?? null,
                    $data['contact_phone'] ?? null,
                    $data['contact_email'] ?? null,
                    isset($data['is_featured']) ? ($data['is_featured'] ? 1 : 0) : 0
                ]
            );
            
            $venueId = $db->lastInsertId();
            
            // Get the newly created venue
            $venue = $db->fetch("SELECT * FROM venues WHERE id = ?", [$venueId]);
            
            echo json_encode([
                'status' => true,
                'message' => 'Venue created successfully',
                'data' => $venue,
                'code' => 201
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Failed to create venue: ' . $e->getMessage(),
                'code' => 500
            ]);
        }
        break;
        
    case 'PUT':
        // Update venue - admin only
        if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'status' => false,
                'message' => 'Permission denied',
                'code' => 403
            ]);
            exit;
        }
        
        // Get data from request body (JSON)
        $json_data = json_decode(file_get_contents('php://input'), true);
        $data = !empty($json_data) ? $json_data : $_POST;
        
        // Check if venue ID is provided
        if (empty($data['id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Venue ID is required',
                'code' => 400
            ]);
            exit;
        }
        
        $venueId = $data['id'];
        
        // Check if venue exists
        $existingVenue = $db->fetch("SELECT * FROM venues WHERE id = ?", [$venueId]);
        if (!$existingVenue) {
            echo json_encode([
                'status' => false,
                'message' => 'Venue not found',
                'code' => 404
            ]);
            exit;
        }
        
        // Build update fields and parameters
        $updateFields = [];
        $params = [];
        
        $fields = [
            'name', 'slug', 'description', 'address', 'city', 'country', 'capacity',
            'contact_name', 'contact_phone', 'contact_email', 'featured_image', 'status'
        ];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['is_featured'])) {
            $updateFields[] = "is_featured = ?";
            $params[] = $data['is_featured'] ? 1 : 0;
        }
        
        // Always add updated_at
        $updateFields[] = "updated_at = NOW()";
        
        // Add venue ID as the last parameter
        $params[] = $venueId;
        
        if (empty($updateFields)) {
            echo json_encode([
                'status' => false,
                'message' => 'No fields to update',
                'code' => 400
            ]);
            exit;
        }
        
        try {
            // Update venue
            $db->execute(
                "UPDATE venues SET " . implode(', ', $updateFields) . " WHERE id = ?",
                $params
            );
            
            // Get updated venue
            $venue = $db->fetch("SELECT * FROM venues WHERE id = ?", [$venueId]);
            
            echo json_encode([
                'status' => true,
                'message' => 'Venue updated successfully',
                'data' => $venue,
                'code' => 200
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Failed to update venue: ' . $e->getMessage(),
                'code' => 500
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete venue - admin only
        if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'status' => false,
                'message' => 'Permission denied',
                'code' => 403
            ]);
            exit;
        }
        
        // Get venue ID
        $parts = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
        $venueId = intval(end($parts));
        
        // If no ID in URL, try to get from request body
        if ($venueId <= 0) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            $venueId = intval($json_data['id'] ?? 0);
        }
        
        if ($venueId <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid venue ID',
                'code' => 400
            ]);
            exit;
        }
        
        // Check if venue exists
        $existingVenue = $db->fetch("SELECT * FROM venues WHERE id = ?", [$venueId]);
        if (!$existingVenue) {
            echo json_encode([
                'status' => false,
                'message' => 'Venue not found',
                'code' => 404
            ]);
            exit;
        }
        
        // Check if venue is in use
        $inUse = $db->fetch(
            "SELECT COUNT(*) as count FROM conferences WHERE venue_id = ?", 
            [$venueId]
        )['count'];
        
        if ($inUse > 0) {
            // Set status to inactive instead of deleting
            try {
                $db->execute(
                    "UPDATE venues SET status = 'inactive', updated_at = NOW() WHERE id = ?",
                    [$venueId]
                );
                
                echo json_encode([
                    'status' => true,
                    'message' => 'Venue marked as inactive (cannot be deleted because it is in use by conferences)',
                    'code' => 200
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to update venue status: ' . $e->getMessage(),
                    'code' => 500
                ]);
            }
        } else {
            // Delete the venue
            try {
                $db->execute("DELETE FROM venues WHERE id = ?", [$venueId]);
                
                echo json_encode([
                    'status' => true,
                    'message' => 'Venue deleted successfully',
                    'code' => 200
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to delete venue: ' . $e->getMessage(),
                    'code' => 500
                ]);
            }
        }
        break;
        
    default:
        echo json_encode([
            'status' => false,
            'message' => 'Method not allowed',
            'code' => 405
        ]);
}
?>
