<?php
/**
 * API for speaker profile operations
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

// Authentication check
if (!isLoggedIn()) {
    echo json_encode([
        'status' => false,
        'message' => 'Authentication required',
        'code' => 401
    ]);
    exit;
}

// Initialize database connection
$db = Database::getInstance();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get speaker profile by user ID or speaker ID
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
        $speakerId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Speaker access control
        if ($speakerId > 0) {
            // Anyone can view published speaker profiles by ID
            $speaker = $db->fetch(
                "SELECT s.*, u.firstName, u.lastName, u.email, u.company, u.position
                FROM speakers s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = ? AND s.status = 'active'",
                [$speakerId]
            );
        } else {
            // User viewing their own speaker profile
            $speaker = $db->fetch(
                "SELECT s.*, u.firstName, u.lastName, u.email, u.company, u.position
                FROM speakers s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.user_id = ?",
                [$userId]
            );
            
            // Permission check - only the user or admin can view their profile
            if ($speaker && $userId != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
                echo json_encode([
                    'status' => false,
                    'message' => 'Permission denied',
                    'code' => 403
                ]);
                exit;
            }
        }
        
        if ($speaker) {
            // Get speaker's conferences
            $conferences = $db->fetchAll(
                "SELECT c.id, c.title, c.slug, c.start_date, c.end_date, c.image,
                        v.name as venue_name, v.city, v.country,
                        cat.name as category_name, cat.color as category_color,
                        cs.role as speaker_role
                FROM conferences c
                INNER JOIN conference_speakers cs ON c.id = cs.conference_id
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN categories cat ON c.category_id = cat.id
                WHERE cs.speaker_id = ? 
                ORDER BY c.start_date DESC",
                [$speakerId > 0 ? $speakerId : $speaker['id']]
            );
            
            // Format specialties and languages from JSON
            $speaker['specialties'] = json_decode($speaker['specialties'] ?? '[]');
            $speaker['languages'] = json_decode($speaker['languages'] ?? '[]');
            
            // Add conferences to result
            $speaker['conferences'] = $conferences;
            
            echo json_encode([
                'status' => true,
                'data' => $speaker,
                'code' => 200
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Speaker profile not found',
                'code' => 404
            ]);
        }
        break;
        
    case 'POST':
    case 'PUT':
        // Create or update speaker profile
        $userId = $_SESSION['user_id'];
        
        // Get data from request body (JSON)
        $json_data = json_decode(file_get_contents('php://input'), true);
        $data = !empty($json_data) ? $json_data : $_POST;
        
        // Check required fields
        if (empty($data['name']) || empty($data['title']) || empty($data['bio'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Missing required fields: name, title, and bio are required',
                'code' => 400
            ]);
            exit;
        }
        
        // Check if speaker profile already exists for this user
        $existingSpeaker = $db->fetch(
            "SELECT id FROM speakers WHERE user_id = ?", 
            [$userId]
        );
        
        // Generate slug if not provided
        $slug = $data['slug'] ?? strtolower(str_replace(' ', '-', $data['name']));
        
        // Handle specialties and languages as JSON arrays
        $specialties = isset($data['specialties']) ? 
            (is_array($data['specialties']) ? json_encode($data['specialties']) : $data['specialties']) : 
            '[]';
            
        $languages = isset($data['languages']) ?
            (is_array($data['languages']) ? json_encode($data['languages']) : $data['languages']) :
            '[]';
        
        if ($existingSpeaker) {
            // Update existing speaker profile
            $result = $db->execute(
                "UPDATE speakers 
                SET name = ?, slug = ?, title = ?, company = ?, bio = ?, short_bio = ?,
                    email = ?, website = ?, social_linkedin = ?, social_twitter = ?,
                    specialties = ?, languages = ?, experience_years = ?, updated_at = NOW()
                WHERE user_id = ?",
                [
                    $data['name'],
                    $slug,
                    $data['title'],
                    $data['company'] ?? null,
                    $data['bio'],
                    $data['short_bio'] ?? substr($data['bio'], 0, 200),
                    $data['email'] ?? null,
                    $data['website'] ?? null,
                    $data['social_linkedin'] ?? null,
                    $data['social_twitter'] ?? null,
                    $specialties,
                    $languages,
                    $data['experience_years'] ?? null,
                    $userId
                ]
            );
            
            $speakerId = $existingSpeaker['id'];
            $actionType = 'updated';
        } else {
            // Create new speaker profile
            $result = $db->execute(
                "INSERT INTO speakers 
                (user_id, name, slug, title, company, bio, short_bio, email, website, 
                social_linkedin, social_twitter, specialties, languages, 
                experience_years, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())",
                [
                    $userId,
                    $data['name'],
                    $slug,
                    $data['title'],
                    $data['company'] ?? null,
                    $data['bio'],
                    $data['short_bio'] ?? substr($data['bio'], 0, 200),
                    $data['email'] ?? null,
                    $data['website'] ?? null,
                    $data['social_linkedin'] ?? null,
                    $data['social_twitter'] ?? null,
                    $specialties,
                    $languages,
                    $data['experience_years'] ?? null
                ]
            );
            
            $speakerId = $db->lastInsertId();
            $actionType = 'created';
            
            // Update user role to speaker if successful
            if ($speakerId) {
                $db->execute(
                    "UPDATE users SET role = 'speaker', updated_at = NOW() WHERE id = ? AND role = 'user'",
                    [$userId]
                );
            }
        }
        
        if ($result) {
            // Get the updated/created speaker profile
            $speaker = $db->fetch(
                "SELECT s.*, u.firstName, u.lastName, u.email, u.company, u.position
                FROM speakers s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = ?",
                [$speakerId]
            );
            
            echo json_encode([
                'status' => true,
                'message' => "Speaker profile {$actionType} successfully",
                'data' => $speaker,
                'code' => 200
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => "Failed to {$actionType} speaker profile",
                'code' => 500
            ]);
        }
        break;
        
    case 'DELETE':
        // Only admins can delete speaker profiles
        if ($_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'status' => false,
                'message' => 'Only administrators can delete speaker profiles',
                'code' => 403
            ]);
            exit;
        }
        
        // Get speaker ID from request
        $json_data = json_decode(file_get_contents('php://input'), true);
        $speakerId = intval($json_data['id'] ?? 0);
        
        if ($speakerId <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid speaker ID',
                'code' => 400
            ]);
            exit;
        }
        
        try {
            // Begin transaction
            $db->beginTransaction();
            
            // Delete from speaker relations first
            $db->execute("DELETE FROM conference_speakers WHERE speaker_id = ?", [$speakerId]);
            $db->execute("DELETE FROM session_speakers WHERE speaker_id = ?", [$speakerId]);
            
            // Get user ID associated with this speaker
            $userId = $db->fetch("SELECT user_id FROM speakers WHERE id = ?", [$speakerId])['user_id'] ?? 0;
            
            // Delete speaker record
            $result = $db->execute("DELETE FROM speakers WHERE id = ?", [$speakerId]);
            
            // If user exists and no longer has speaker roles, update user role back to user
            if ($userId > 0) {
                // Check if user still has speaker roles
                $hasRoles = $db->fetch("SELECT COUNT(*) as count FROM speakers WHERE user_id = ?", [$userId])['count'] ?? 0;
                if ($hasRoles == 0) {
                    $db->execute("UPDATE users SET role = 'user', updated_at = NOW() WHERE id = ? AND role = 'speaker'", [$userId]);
                }
            }
            
            // Commit transaction
            $db->commit();
            
            echo json_encode([
                'status' => true,
                'message' => 'Speaker profile deleted successfully',
                'code' => 200
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollback();
            
            echo json_encode([
                'status' => false,
                'message' => 'Failed to delete speaker profile: ' . $e->getMessage(),
                'code' => 500
            ]);
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
