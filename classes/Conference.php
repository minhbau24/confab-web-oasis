<?php
/**
 * Lớp Conference quản lý thông tin hội nghị
 * Phiên bản: 3.0 (Complete Edition) - Tương thích với schema mới
 */
class Conference
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
     * Lấy tất cả hội nghị (với JOIN categories và venues)
     *
     * @param int $limit Số lượng tối đa
     * @param int $offset Vị trí bắt đầu
     * @param string $status Trạng thái hội nghị
     * @return array Danh sách hội nghị
     */
    public function getAllConferences($limit = null, $offset = 0, $status = 'published')
    {
        $sql = "SELECT 
                    c.*,
                    cat.name as category_name,
                    cat.slug as category_slug,
                    cat.color as category_color,
                    v.name as venue_name,
                    v.city as venue_city,
                    u1.firstName as created_by_name,
                    u1.lastName as created_by_lastname
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN users u1 ON c.created_by = u1.id
                WHERE c.deleted_at IS NULL";
        
        $params = [];

        if (!empty($status)) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.start_date DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
        }

        $conferences = $this->db->fetchAll($sql, $params);
        
        // Xử lý JSON fields
        return $this->processConferencesData($conferences);
    }

    /**
     * Lấy hội nghị theo ID
     *
     * @param int $id ID của hội nghị
     * @return array|false Thông tin hội nghị hoặc false nếu không tìm thấy
     */
    public function getConferenceById($id)
    {
        return $this->db->fetch("
            SELECT * FROM conferences 
            WHERE id = ?
        ", [$id]);
    }    /**
         * Đếm tổng số hội nghị
         * 
         * @return int Tổng số hội nghị
         */
    public function countAllConferences()
    {
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM conferences");
        return $result['total'];
    }

    /**
     * Lọc hội nghị theo các điều kiện
     * 
     * @param string $searchTerm Từ khóa tìm kiếm
     * @param string $category Danh mục
     * @param string $dateFilter Bộ lọc thời gian
     * @param int $limit Số lượng tối đa
     * @param int $offset Vị trí bắt đầu
     * @return array Danh sách hội nghị
     */
    public function filterConferences($searchTerm = '', $category = '', $dateFilter = '', $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM conferences WHERE 1=1";
        $params = [];

        // Tìm kiếm theo từ khóa
        if (!empty($searchTerm)) {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
            $searchParam = '%' . $searchTerm . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        // Lọc theo danh mục
        if (!empty($category)) {
            $sql .= " AND category LIKE ?";
            $params[] = '%' . $category . '%';
        }

        // Lọc theo thời gian
        if (!empty($dateFilter)) {
            $today = date('Y-m-d');
            switch ($dateFilter) {
                case 'upcoming':
                    $sql .= " AND date >= ?";
                    $params[] = $today;
                    break;
                case 'thisMonth':
                    $startMonth = date('Y-m-01');
                    $endMonth = date('Y-m-t');
                    $sql .= " AND date BETWEEN ? AND ?";
                    $params[] = $startMonth;
                    $params[] = $endMonth;
                    break;
                case 'thisYear':
                    $startYear = date('Y-01-01');
                    $endYear = date('Y-12-31');
                    $sql .= " AND date BETWEEN ? AND ?";
                    $params[] = $startYear;
                    $params[] = $endYear;
                    break;
                case 'past':
                    $sql .= " AND date < ?";
                    $params[] = $today;
                    break;
            }
        }

        // Sắp xếp theo ngày mới nhất
        $sql .= " ORDER BY date DESC";

        // Giới hạn số lượng
        if ($limit !== null) {
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Đếm số hội nghị theo bộ lọc
     * 
     * @param string $searchTerm Từ khóa tìm kiếm
     * @param string $category Danh mục
     * @param string $dateFilter Bộ lọc thời gian
     * @return int Số lượng hội nghị
     */
    public function countFilteredConferences($searchTerm = '', $category = '', $dateFilter = '')
    {
        $sql = "SELECT COUNT(*) as total FROM conferences WHERE 1=1";
        $params = [];

        // Tìm kiếm theo từ khóa
        if (!empty($searchTerm)) {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
            $searchParam = '%' . $searchTerm . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        // Lọc theo danh mục
        if (!empty($category)) {
            $sql .= " AND category LIKE ?";
            $params[] = '%' . $category . '%';
        }

        // Lọc theo thời gian
        if (!empty($dateFilter)) {
            $today = date('Y-m-d');
            switch ($dateFilter) {
                case 'upcoming':
                    $sql .= " AND date >= ?";
                    $params[] = $today;
                    break;
                case 'thisMonth':
                    $startMonth = date('Y-m-01');
                    $endMonth = date('Y-m-t');
                    $sql .= " AND date BETWEEN ? AND ?";
                    $params[] = $startMonth;
                    $params[] = $endMonth;
                    break;
                case 'thisYear':
                    $startYear = date('Y-01-01');
                    $endYear = date('Y-12-31');
                    $sql .= " AND date BETWEEN ? AND ?";
                    $params[] = $startYear;
                    $params[] = $endYear;
                    break;
                case 'past':
                    $sql .= " AND date < ?";
                    $params[] = $today;
                    break;
            }
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'];
    }

    /**
     * Lấy hội nghị sắp tới
     *
     * @param int $limit Số lượng tối đa
     * @return array Danh sách hội nghị
     */
    /**
     * Lấy lịch trình của một hội nghị theo ID
     * 
     * @param int $conferenceId ID của hội nghị
     * @return array|false Danh sách lịch trình hoặc false nếu không có
     */
    public function getConferenceSchedule($conferenceId)
    {
        return $this->db->fetchAll("
            SELECT * FROM conference_schedule 
            WHERE conference_id = ? 
            ORDER BY eventDate ASC, startTime ASC
        ", [$conferenceId]);
    }

    /**
     * Lấy danh sách diễn giả của một hội nghị theo ID
     * 
     * @param int $conferenceId ID của hội nghị
     * @return array|false Danh sách diễn giả hoặc false nếu không có
     */
    public function getConferenceSpeakers($conferenceId)
    {
        return $this->db->fetchAll("
            SELECT * FROM conference_speakers 
            WHERE conference_id = ? 
            ORDER BY name ASC
        ", [$conferenceId]);
    }

    /**
     * Lấy mục tiêu của hội nghị
     *
     * @param int $conferenceId ID của hội nghị
     * @return array Danh sách mục tiêu
     */
    public function getConferenceObjectives($conferenceId)
    {
        return $this->db->fetchAll("
            SELECT * FROM conference_objectives
            WHERE conference_id = ?
            ORDER BY order_num ASC
        ", [$conferenceId]);
    }

    /**
     * Lấy đối tượng tham dự của hội nghị
     *
     * @param int $conferenceId ID của hội nghị
     * @return array Danh sách đối tượng
     */
    public function getConferenceAudience($conferenceId)
    {
        return $this->db->fetchAll("
            SELECT * FROM conference_audience
            WHERE conference_id = ?
            ORDER BY order_num ASC
        ", [$conferenceId]);
    }

    /**
     * Lấy FAQ của hội nghị
     *
     * @param int $conferenceId ID của hội nghị
     * @return array Danh sách FAQ
     */
    public function getConferenceFaq($conferenceId)
    {
        return $this->db->fetchAll("
            SELECT * FROM conference_faq
            WHERE conference_id = ?
            ORDER BY order_num ASC
        ", [$conferenceId]);
    }

    /**
     * Kiểm tra người dùng đã đăng ký tham gia hội nghị chưa
     *
     * @param int $userId ID người dùng
     * @param int $conferenceId ID hội nghị
     * @return bool Đã đăng ký hay chưa
     */
    public function isUserRegistered($userId, $conferenceId)
    {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM registrations
            WHERE user_id = ? AND conference_id = ? AND status != 'cancelled'
        ", [$userId, $conferenceId]);

        return ($result && $result['count'] > 0);
    }

    /**
     * Đăng ký tham gia hội nghị
     *
     * @param int $userId ID người dùng
     * @param int $conferenceId ID hội nghị
     * @return array Kết quả đăng ký
     */
    public function registerConference($userId, $conferenceId)
    {
        try {
            // Kiểm tra người dùng đã đăng ký chưa
            if ($this->isUserRegistered($userId, $conferenceId)) {
                return [
                    'success' => false,
                    'message' => 'Bạn đã đăng ký tham gia hội nghị này.'
                ];
            }

            // Kiểm tra còn chỗ không
            $conference = $this->getConferenceById($conferenceId);
            if (!$conference) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin hội nghị.'
                ];
            }

            if ($conference['attendees'] >= $conference['capacity']) {
                return [
                    'success' => false,
                    'message' => 'Hội nghị đã hết chỗ.'
                ];
            }

            if ($conference['status'] != 'active') {
                return [
                    'success' => false,
                    'message' => 'Hội nghị không còn nhận đăng ký.'
                ];
            }

            // Tiến hành đăng ký
            $this->db->execute("
                INSERT INTO registrations (user_id, conference_id, status, registration_date)
                VALUES (?, ?, 'confirmed', NOW())
                ON DUPLICATE KEY UPDATE status = 'confirmed', registration_date = NOW()
            ", [$userId, $conferenceId]);

            // Cập nhật số người tham dự
            $this->db->execute("
                UPDATE conferences 
                SET attendees = attendees + 1 
                WHERE id = ? AND attendees < capacity
            ", [$conferenceId]);

            return [
                'success' => true,
                'message' => 'Đăng ký tham gia hội nghị thành công!'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hủy đăng ký tham gia hội nghị
     *
     * @param int $userId ID người dùng
     * @param int $conferenceId ID hội nghị
     * @return array Kết quả hủy đăng ký
     */
    public function unregisterConference($userId, $conferenceId)
    {
        try {
            // Kiểm tra người dùng đã đăng ký chưa
            if (!$this->isUserRegistered($userId, $conferenceId)) {
                return [
                    'success' => false,
                    'message' => 'Bạn chưa đăng ký tham gia hội nghị này.'
                ];
            }

            // Cập nhật trạng thái đăng ký
            $this->db->execute("
                UPDATE registrations 
                SET status = 'cancelled' 
                WHERE user_id = ? AND conference_id = ?
            ", [$userId, $conferenceId]);

            // Giảm số người tham dự
            $this->db->execute("
                UPDATE conferences 
                SET attendees = GREATEST(attendees - 1, 0)
                WHERE id = ?
            ", [$conferenceId]);

            return [
                'success' => true,
                'message' => 'Đã hủy đăng ký tham gia hội nghị!'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy hội nghị liên quan (cùng danh mục)
     *
     * @param int $conferenceId ID hội nghị hiện tại
     * @param string $category Danh mục
     * @param int $limit Số lượng tối đa
     * @return array Danh sách hội nghị liên quan
     */
    public function getRelatedConferences($conferenceId, $category, $limit = 3)
    {
        return $this->db->fetchAll("
            SELECT id, title, date, image
            FROM conferences
            WHERE category LIKE ? AND id != ? AND status = 'active'
            ORDER BY date DESC
            LIMIT ?
        ", ['%' . $category . '%', $conferenceId, $limit]);
    }

    /**
     * Lấy hội nghị sắp tới
     *
     * @param int $limit Số lượng tối đa
     * @return array Danh sách hội nghị
     */
    public function getUpcomingConferences($limit = 3)
    {
        return $this->db->fetchAll("
            SELECT * FROM conferences 
            WHERE date >= CURDATE() 
            ORDER BY date ASC 
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Thêm hội nghị mới
     *
     * @param array $data Thông tin hội nghị
     * @return int|false ID của hội nghị mới hoặc false nếu có lỗi
     */
    public function addConference($data)
    {
        try {
            $this->db->execute("
                INSERT INTO conferences (
                    title, description, date, endDate, location, 
                    category, price, capacity, status, image, 
                    organizer_name, organizer_email, organizer_phone, created_by
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?
                )
            ", [
                $data['title'],
                $data['description'],
                $data['date'],
                $data['endDate'],
                $data['location'],
                $data['category'],
                $data['price'],
                $data['capacity'],
                $data['status'],
                $data['image'],
                $data['organizer_name'],
                $data['organizer_email'],
                $data['organizer_phone'],
                $data['created_by']
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('Error adding conference: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật thông tin hội nghị
     *
     * @param int $id ID hội nghị
     * @param array $data Thông tin cập nhật
     * @return bool Thành công hay không
     */
    public function updateConference($id, $data)
    {
        try {
            $this->db->execute("
                UPDATE conferences
                SET 
                    title = ?,
                    description = ?,
                    date = ?,
                    endDate = ?,
                    location = ?,
                    category = ?,
                    price = ?,
                    capacity = ?,
                    status = ?,
                    image = ?,
                    organizer_name = ?,
                    organizer_email = ?,
                    organizer_phone = ?,
                    updated_at = NOW()
                WHERE id = ?
            ", [
                $data['title'],
                $data['description'],
                $data['date'],
                $data['endDate'],
                $data['location'],
                $data['category'],
                $data['price'],
                $data['capacity'],
                $data['status'],
                $data['image'],
                $data['organizer_name'],
                $data['organizer_email'],
                $data['organizer_phone'],
                $id
            ]);

            return true;
        } catch (Exception $e) {
            error_log('Error updating conference: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa hội nghị
     *
     * @param int $id ID hội nghị cần xóa
     * @return bool Thành công hay không
     */
    public function deleteConference($id)
    {
        try {
            // Xóa các dữ liệu liên quan trước
            $this->db->execute("DELETE FROM conference_attendees WHERE conference_id = ?", [$id]);
            $this->db->execute("DELETE FROM conference_speakers WHERE conference_id = ?", [$id]);
            $this->db->execute("DELETE FROM conference_schedule WHERE conference_id = ?", [$id]);

            // Xóa hội nghị
            $this->db->execute("DELETE FROM conferences WHERE id = ?", [$id]);

            return true;
        } catch (Exception $e) {
            error_log('Error deleting conference: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tìm kiếm hội nghị
     *
     * @param string $query Từ khóa tìm kiếm
     * @param string $category Danh mục (tùy chọn)
     * @param string $location Địa điểm (tùy chọn)
     * @return array Kết quả tìm kiếm
     */
    public function searchConferences($query, $category = '', $location = '')
    {
        $params = [];
        $sql = "SELECT * FROM conferences WHERE 1=1";

        if (!empty($query)) {
            $sql .= " AND (title LIKE ? OR description LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if (!empty($location)) {
            $sql .= " AND location LIKE ?";
            $params[] = "%$location%";
        }

        $sql .= " ORDER BY date ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Thêm người tham dự vào hội nghị
     *
     * @param int $conferenceId ID hội nghị
     * @param int $userId ID người dùng
     * @return bool Thành công hay không
     */
    public function addAttendee($conferenceId, $userId)
    {
        try {
            // Kiểm tra người dùng đã đăng ký chưa
            $existing = $this->db->fetch(
                "SELECT * FROM conference_attendees WHERE conference_id = ? AND user_id = ?",
                [$conferenceId, $userId]
            );

            if ($existing) {
                return false; // Đã đăng ký rồi
            }

            // Kiểm tra số lượng người tham dự
            $conference = $this->getConferenceById($conferenceId);
            $attendees = $this->getAttendeeCount($conferenceId);

            if ($attendees >= $conference['capacity']) {
                return false; // Hội nghị đã đầy
            }

            // Thêm người tham dự
            $this->db->execute(
                "INSERT INTO conference_attendees (conference_id, user_id, registration_date, status) 
                VALUES (?, ?, NOW(), 'registered')",
                [$conferenceId, $userId]
            );

            return true;
        } catch (Exception $e) {
            error_log('Error adding attendee: ' . $e->getMessage());
            return false;
        }
    }    /**
         * Lấy số lượng người tham dự
         *
         * @param int $conferenceId ID hội nghị
         * @return int Số lượng người tham dự
         */
    public function getAttendeeCount($conferenceId)
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM conference_attendees WHERE conference_id = ?",
            [$conferenceId]
        );

        return $result ? $result['count'] : 0;
    }

    /**
     * Lấy tổng số người tham dự tất cả hội nghị
     *
     * @return int Tổng số người tham dự
     */
    public function getTotalAttendees()
    {
        $result = $this->db->fetch("
            SELECT SUM(attendees) as total 
            FROM conferences
        ");

        return $result ? intval($result['total']) : 0;
    }
    /**
     * Lấy tổng doanh thu từ tất cả hội nghị
     *
     * @return float Tổng doanh thu
     */
    public function getTotalRevenue()
    {
        $result = $this->db->fetch("
            SELECT SUM(price * attendees) as total 
            FROM conferences
        ");

        return $result ? intval($result['total']) : 0;
    }

    /**
     * Lấy danh sách hội nghị theo người tạo
     *
     * @param int $userId ID người dùng
     * @return array Danh sách hội nghị
     */
    public function getConferencesByUserId($userId)
    {
        return $this->db->fetchAll("
            SELECT * FROM conferences 
            WHERE created_by = ? 
            ORDER BY date DESC
        ", [$userId]);
    }
}
?>