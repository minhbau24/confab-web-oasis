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
     */
    public function getUserById($id)
    {
        return $this->db->fetch("
            SELECT id, firstName, lastName, email, role, phone, created_at, updated_at
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
     */
    public function updateUser($id, $data)
    {
        try {
            $this->db->execute("
                UPDATE users
                SET 
                    firstName = ?,
                    lastName = ?,
                    phone = ?,
                    updated_at = NOW()
                WHERE id = ?
            ", [
                $data['firstName'],
                $data['lastName'],
                $data['phone'],
                $id
            ]);

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
     */
    public function getJoinedConferences($userId)
    {
        return $this->db->fetchAll("
            SELECT c.* 
            FROM conferences c
            INNER JOIN registrations r ON c.id = r.conference_id
            WHERE r.user_id = ? AND r.status = 'confirmed'
            ORDER BY c.date DESC
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
     */
    public function addUser($data)
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

            // Thêm người dùng
            $this->db->execute("
                INSERT INTO users (
                    firstName, lastName, email, password, role, phone, created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $hashedPassword,
                $data['role'],
                $data['phone']
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
            // Xóa các dữ liệu liên quan trước
            $this->db->execute("DELETE FROM registrations WHERE user_id = ?", [$id]);

            // Xóa người dùng
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
     */
    public function getAllUsers($limit = null, $offset = 0)
    {
        $sql = "SELECT id, firstName, lastName, email, role, phone, created_at FROM users ORDER BY id";
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