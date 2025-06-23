<?php
/**
 * API for category operations
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
        // Get category by ID/slug or list all categories
        $categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
        
        if ($categoryId > 0 || !empty($slug)) {
            // Get specific category by ID or slug
            $params = [];
            $whereClause = "";
            
            if ($categoryId > 0) {
                $whereClause = "id = ?";
                $params[] = $categoryId;
            } else {
                $whereClause = "slug = ?";
                $params[] = $slug;
            }
            
            $category = $db->fetch(
                "SELECT * FROM categories WHERE {$whereClause} AND status = 'active'",
                $params
            );
            
            if ($category) {
                // Get conferences in this category
                $conferences = $db->fetchAll(
                    "SELECT c.id, c.title, c.slug, c.short_description, c.start_date, c.end_date, c.image,
                            c.location, c.type, c.format, c.price, c.currency,
                            v.name as venue_name, v.city as venue_city 
                     FROM conferences c
                     LEFT JOIN venues v ON c.venue_id = v.id
                     WHERE c.category_id = ? AND c.status = 'published'
                     ORDER BY c.start_date DESC
                     LIMIT 6",
                    [$category['id']]
                );
                
                $category['conferences'] = $conferences;
                $category['conferences_count'] = $db->fetch(
                    "SELECT COUNT(*) as count FROM conferences WHERE category_id = ? AND status = 'published'",
                    [$category['id']]
                )['count'];
                
                echo json_encode([
                    'status' => true,
                    'data' => $category,
                    'code' => 200
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Category not found',
                    'code' => 404
                ]);
            }
        } else {
            // List all categories with optional filters
            $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
            $withCounts = isset($_GET['with_counts']) ? (bool)$_GET['with_counts'] : false;
            
            $whereClause = "WHERE status = 'active'";
            if ($featured) {
                $whereClause .= " AND is_featured = 1";
            }
            
            $categories = $db->fetchAll(
                "SELECT * FROM categories {$whereClause} ORDER BY name ASC"
            );
            
            // Add conference counts if requested
            if ($withCounts && !empty($categories)) {
                foreach ($categories as &$category) {
                    $category['conferences_count'] = $db->fetch(
                        "SELECT COUNT(*) as count FROM conferences WHERE category_id = ? AND status = 'published'",
                        [$category['id']]
                    )['count'];
                }
            }
            
            echo json_encode([
                'status' => true,
                'data' => $categories,
                'code' => 200
            ]);
        }
        break;
        
    case 'POST':
        // Create new category - admin only
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
        if (empty($data['name'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Category name is required',
                'code' => 400
            ]);
            exit;
        }
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(str_replace(' ', '-', $data['name']));
        }
        
        // Check if category with this slug already exists
        $existingCategory = $db->fetch(
            "SELECT id FROM categories WHERE slug = ?",
            [$data['slug']]
        );
        
        if ($existingCategory) {
            echo json_encode([
                'status' => false,
                'message' => 'A category with this slug already exists',
                'code' => 400
            ]);
            exit;
        }
        
        try {
            // Insert new category
            $db->execute(
                "INSERT INTO categories (
                    name, slug, description, color, icon,
                    status, is_featured, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, 'active', ?, NOW(), NOW())",
                [
                    $data['name'],
                    $data['slug'],
                    $data['description'] ?? '',
                    $data['color'] ?? '#007bff',
                    $data['icon'] ?? 'fas fa-folder',
                    isset($data['is_featured']) ? ($data['is_featured'] ? 1 : 0) : 0
                ]
            );
            
            $categoryId = $db->lastInsertId();
            
            // Get the newly created category
            $category = $db->fetch("SELECT * FROM categories WHERE id = ?", [$categoryId]);
            
            echo json_encode([
                'status' => true,
                'message' => 'Category created successfully',
                'data' => $category,
                'code' => 201
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Failed to create category: ' . $e->getMessage(),
                'code' => 500
            ]);
        }
        break;
        
    case 'PUT':
        // Update category - admin only
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
        
        // Check if category ID is provided
        if (empty($data['id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Category ID is required',
                'code' => 400
            ]);
            exit;
        }
        
        $categoryId = $data['id'];
        
        // Check if category exists
        $existingCategory = $db->fetch("SELECT * FROM categories WHERE id = ?", [$categoryId]);
        if (!$existingCategory) {
            echo json_encode([
                'status' => false,
                'message' => 'Category not found',
                'code' => 404
            ]);
            exit;
        }
        
        // Build update fields and parameters
        $updateFields = [];
        $params = [];
        
        $fields = ['name', 'slug', 'description', 'color', 'icon', 'status'];
        
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
        
        // Add category ID as the last parameter
        $params[] = $categoryId;
        
        if (empty($updateFields)) {
            echo json_encode([
                'status' => false,
                'message' => 'No fields to update',
                'code' => 400
            ]);
            exit;
        }
        
        try {
            // Update category
            $db->execute(
                "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = ?",
                $params
            );
            
            // Get updated category
            $category = $db->fetch("SELECT * FROM categories WHERE id = ?", [$categoryId]);
            
            echo json_encode([
                'status' => true,
                'message' => 'Category updated successfully',
                'data' => $category,
                'code' => 200
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Failed to update category: ' . $e->getMessage(),
                'code' => 500
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete category - admin only
        if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'status' => false,
                'message' => 'Permission denied',
                'code' => 403
            ]);
            exit;
        }
        
        // Get category ID
        $parts = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
        $categoryId = intval(end($parts));
        
        // If no ID in URL, try to get from request body
        if ($categoryId <= 0) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            $categoryId = intval($json_data['id'] ?? 0);
        }
        
        if ($categoryId <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid category ID',
                'code' => 400
            ]);
            exit;
        }
        
        // Check if category exists
        $existingCategory = $db->fetch("SELECT * FROM categories WHERE id = ?", [$categoryId]);
        if (!$existingCategory) {
            echo json_encode([
                'status' => false,
                'message' => 'Category not found',
                'code' => 404
            ]);
            exit;
        }
        
        // Check if category is in use
        $inUse = $db->fetch(
            "SELECT COUNT(*) as count FROM conferences WHERE category_id = ?", 
            [$categoryId]
        )['count'];
        
        if ($inUse > 0) {
            // Set status to inactive instead of deleting
            try {
                $db->execute(
                    "UPDATE categories SET status = 'inactive', updated_at = NOW() WHERE id = ?",
                    [$categoryId]
                );
                
                echo json_encode([
                    'status' => true,
                    'message' => 'Category marked as inactive (cannot be deleted because it is used by conferences)',
                    'code' => 200
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to update category status: ' . $e->getMessage(),
                    'code' => 500
                ]);
            }
        } else {
            // Delete the category
            try {
                $db->execute("DELETE FROM categories WHERE id = ?", [$categoryId]);
                
                echo json_encode([
                    'status' => true,
                    'message' => 'Category deleted successfully',
                    'code' => 200
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to delete category: ' . $e->getMessage(),
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
