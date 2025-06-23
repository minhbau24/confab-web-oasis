<?php
/**
 * Lớp User quản lý thông tin người dùng
 */
class User
{
    private $db;

    /**
     * Khởi tạo lớp với kết nối database
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy thông tin người dùng theo ID
     *
     * @param int $id ID của người dùng
     * @return array|false Thông tin người dùng hoặc false nếu không tìm thấy
     */    public function getUserById($id)
    {
        return $this->db->fetch("
            SELECT id, firstName, lastName, email, role, phone, company, position, bio, 
                   profile_image, status, email_verified, last_login, created_at, updated_at
            FROM users 
            WHERE id = ?
        ", [$id]);
    }

    /**
     * Cập nhật thông tin người dùng
     *
     * @param int $id ID của người dùng
     * @param array $data Dữ liệu cập nhật
     * @return bool Thành công hay không
     */    public function updateUser($id, $data)
    {
        try {
            // Build dynamic update query based on provided fields
            $updateFields = [];
            $params = [];
            
            // Add fields if they exist in the data array
            if (isset($data['firstName'])) {
                $updateFields[] = "firstName = ?";
                $params[] = $data['firstName'];
            }
            
            if (isset($data['lastName'])) {
                $updateFields[] = "lastName = ?";
                $params[] = $data['lastName'];
            }
            
            if (isset($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = $data['phone'];
            }
            
            if (isset($data['company'])) {
                $updateFields[] = "company = ?";
                $params[] = $data['company'];
            }
            
            if (isset($data['position'])) {
                $updateFields[] = "position = ?";
                $params[] = $data['position'];
            }
            
            if (isset($data['bio'])) {
                $updateFields[] = "bio = ?";
                $params[] = $data['bio'];
            }
            
            if (isset($data['status'])) {
                $updateFields[] = "status = ?";
                $params[] = $data['status'];
            }
            
            // Always add updated_at
            $updateFields[] = "updated_at = NOW()";
            
            // Add the user ID to params
            $params[] = $id;
            
            // Execute the update if we have fields to update
            if (!empty($updateFields)) {
                $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $this->db->execute($query, $params);
            }

            return true;
        } catch (Exception $e) {
            error_log('Error updating user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Thay đổi mật khẩu người dùng
     *
     * @param int $id ID của người dùng
     * @param string $newPassword Mật khẩu mới
     * @return bool Thành công hay không
     */
    public function changePassword($id, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $this->db->execute("
                UPDATE users
                SET 
                    password = ?,
                    updated_at = NOW()
                WHERE id = ?
            ", [$hashedPassword, $id]);

            return true;
        } catch (Exception $e) {
            error_log('Error changing password: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách các hội nghị đã tham gia
     *
     * @param int $userId ID người dùng
     * @return array Danh sách hội nghị
     */    public function getJoinedConferences($userId)
    {
        return $this->db->fetchAll("
            SELECT c.*, v.name as venue_name, v.city as venue_city, v.country as venue_country,
                   cat.name as category_name, cat.color as category_color, cat.icon as category_icon
            FROM conferences c
            INNER JOIN registrations r ON c.id = r.conference_id
            LEFT JOIN venues v ON c.venue_id = v.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE r.user_id = ? AND r.status = 'confirmed'
            ORDER BY c.start_date DESC
        ", [$userId]);
    }

    /**
     * Lấy số lượng hội nghị đã tham gia
     *
     * @param int $userId ID người dùng
     * @return int Số lượng hội nghị
     */
    public function getJoinedConferencesCount($userId)
    {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM registrations
            WHERE user_id = ? AND status = 'confirmed'
        ", [$userId]);

        return $result ? $result['count'] : 0;
    }

    /**
     * Thêm người dùng mới
     *
     * @param array $data Thông tin người dùng
     * @return int|false ID của người dùng mới hoặc false nếu có lỗi
     */    public function addUser($data)
    {
        try {
            // Kiểm tra email đã tồn tại chưa
            $existingUser = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE email = ?
            ", [$data['email']]);

            if ($existingUser && $existingUser['count'] > 0) {
                return false;
            }

            // Hash mật khẩu
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Set default values for optional fields
            $role = $data['role'] ?? 'user';
            $status = $data['status'] ?? 'active';
            $email_verified = $data['email_verified'] ?? 0;
            $currentDate = date('Y-m-d H:i:s');

            // Thêm người dùng
            $this->db->execute("
                INSERT INTO users (
                    firstName, lastName, email, password, role, phone, company, position,
                    bio, status, email_verified, created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $hashedPassword,
                $role,
                $data['phone'] ?? null,
                $data['company'] ?? null,
                $data['position'] ?? null,
                $data['bio'] ?? null,
                $status,
                $email_verified,
                $currentDate,
                $currentDate
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('Error adding user: ' . $e->getMessage());
            return false;
        }
    }    /**
     * Xóa người dùng
     *
     * @param int $id ID người dùng cần xóa
     * @return bool Thành công hay không
     */
    public function deleteUser($id)
    {
        try {
            // Check if user exists
            $userExists = $this->getUserById($id);
            if (!$userExists) {
                return false;
            }
            
            // Handle related data in this order to respect foreign key constraints
            
            // Delete from various related tables that reference this user
            $this->db->execute("DELETE FROM registrations WHERE user_id = ?", [$id]);
            
            // Delete from notifications if exists in schema
            $this->db->execute("DELETE FROM notifications WHERE user_id = ? OR created_by = ?", [$id, $id]);
            
            // If user is a speaker, delete from speaker relations
            if ($userExists['role'] == 'speaker') {
                // Delete from session_speakers if exists
                $this->db->execute("DELETE FROM session_speakers WHERE speaker_id IN (SELECT id FROM speakers WHERE user_id = ?)", [$id]);
                
                // Delete from conference_speakers if exists
                $this->db->execute("DELETE FROM conference_speakers WHERE speaker_id IN (SELECT id FROM speakers WHERE user_id = ?)", [$id]);
                
                // Delete from speakers table
                $this->db->execute("DELETE FROM speakers WHERE user_id = ?", [$id]);
            }
            
            // Delete user's feedback if exists
            $this->db->execute("DELETE FROM feedback WHERE user_id = ?", [$id]);
            
            // Delete user's certificates if exists
            $this->db->execute("DELETE FROM certificates WHERE user_id = ?", [$id]);
            
            // Finally, delete the user
            $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);

            return true;
        } catch (Exception $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách tất cả người dùng
     *
     * @param int $limit Giới hạn số lượng
     * @param int $offset Vị trí bắt đầu
     * @return array Danh sách người dùng
     */    public function getAllUsers($limit = null, $offset = 0)
    {
        $sql = "SELECT id, firstName, lastName, email, role, phone, company, position, 
                       status, email_verified, last_login, created_at, updated_at 
                FROM users ORDER BY id";
        $params = [];

        if ($limit !== null) {
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
        }

        return $this->db->fetchAll($sql, $params);
    }
}
?>