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
    public function getAllConferences($limit = null, $offset = 0, $status = null)
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
                WHERE 1=1";

        $params = [];

        // Không lọc status nữa
        // if (!empty($status)) {
        //     $sql .= " AND c.status = ?";
        //     $params[] = $status;
        // }

        $sql .= " ORDER BY c.start_date DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
        }

        $conferences = $this->db->fetchAll($sql, $params);

        // Xử lý JSON fields
        return $this->processConferencesData($conferences);
    }    /**
         * Lấy hội nghị theo ID
         *
         * @param int $id ID của hội nghị
         * @return array|false Thông tin hội nghị hoặc false nếu không tìm thấy
         */
    public function getConferenceById($id)
    {
        $sql = "SELECT 
                    c.*,
                    cat.name as category_name,
                    cat.slug as category_slug,
                    cat.color as category_color,
                    v.name as venue_name,
                    v.address as venue_address,
                    v.city as venue_city,
                    v.state as venue_state,
                    v.country as venue_country,
                    v.postal_code as venue_postal_code,
                    v.description as venue_description,
                    v.contact_name as venue_contact_name,
                    v.contact_phone as venue_contact_phone,
                    v.contact_email as venue_contact_email,
                    v.website as venue_website,
                    v.capacity as venue_capacity,
                    v.parking_info as venue_parking_info,
                    v.transport_info as venue_transport_info,
                    v.amenities as venue_amenities,
                    v.images as venue_images,
                    creator.firstName as creator_first_name,
                    creator.lastName as creator_last_name,
                    creator.email as creator_email,
                    creator.avatar as creator_profile_image
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN users creator ON c.created_by = creator.id
                WHERE c.id = ?";

        $conference = $this->db->fetch($sql, [$id]);
        if ($conference) {
            // Xử lý các trường JSON
            $jsonFields = [
                'metadata',
                'tags',
                'features',
                'organizer_info',
                'pricing',
                'registration_form_fields',
                'sponsors',
                'seo',
                'venue_amenities',
                'venue_images',
                'social_links'
            ];

            $conference = $this->db->decodeJsonFields($conference, $jsonFields);

            // Xử lý JSON fields và trả về dữ liệu đã xử lý
            $processed = $this->processConferencesData([$conference]);
            return !empty($processed) ? $processed[0] : false;
        }

        return false;
    }    /**
         * Đếm tổng số hội nghị
         * 
         * @param string $status Trạng thái hội nghị
         * @return int Tổng số hội nghị
         */
    public function countAllConferences($status = null)
    {
        $sql = "SELECT COUNT(*) as total FROM conferences c WHERE 1=1";
        $params = [];

        // Không lọc status nữa
        // if (!empty($status)) {
        //     $sql .= " AND c.status = ?";
        //     $params[] = $status;
        // }

        $result = $this->db->fetch($sql, $params);
        return $result['total'];
    }/**
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
        $sql = "SELECT 
                    c.*,
                    cat.name as category_name,
                    cat.slug as category_slug,
                    cat.color as category_color,
                    v.name as venue_name,
                    v.city as venue_city,
                    u1.firstName as created_by_name,
                    u1.lastName as created_by_lastname                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN users u1 ON c.created_by = u1.id
                WHERE 1=1";
        $params = [];

        // Tìm kiếm theo từ khóa
        if (!empty($searchTerm)) {
            $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ? OR c.short_description LIKE ?)";
            $searchParam = '%' . $searchTerm . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        // Lọc theo danh mục
        if (!empty($category)) {
            $sql .= " AND cat.name LIKE ?";
            $params[] = '%' . $category . '%';
        }

        // Lọc theo thời gian
        if (!empty($dateFilter)) {
            $today = date('Y-m-d');
            switch ($dateFilter) {
                case 'upcoming':
                    $sql .= " AND c.start_date >= ?";
                    $params[] = $today;
                    break;
                case 'thisMonth':
                    $startMonth = date('Y-m-01');
                    $endMonth = date('Y-m-t');
                    $sql .= " AND c.start_date BETWEEN ? AND ?";
                    $params[] = $startMonth;
                    $params[] = $endMonth;
                    break;
                case 'thisYear':
                    $startYear = date('Y-01-01');
                    $endYear = date('Y-12-31');
                    $sql .= " AND c.start_date BETWEEN ? AND ?";
                    $params[] = $startYear;
                    $params[] = $endYear;
                    break;
                case 'past':
                    $sql .= " AND c.start_date < ?";
                    $params[] = $today;
                    break;
            }
        }

        // Sắp xếp theo ngày mới nhất
        $sql .= " ORDER BY c.start_date DESC";

        // Giới hạn số lượng
        if ($limit !== null) {
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limit;
        }

        $conferences = $this->db->fetchAll($sql, $params);

        // Xử lý JSON fields
        return $this->processConferencesData($conferences);
    }    /**
         * Đếm số hội nghị theo bộ lọc
         * 
         * @param string $searchTerm Từ khóa tìm kiếm
         * @param string $category Danh mục
         * @param string $dateFilter Bộ lọc thời gian
         * @return int Số lượng hội nghị
         */
    public function countFilteredConferences($searchTerm = '', $category = '', $dateFilter = '')
    {
        $sql = "SELECT COUNT(*) as total 
                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                WHERE 1=1";
        $params = [];

        // Tìm kiếm theo từ khóa
        if (!empty($searchTerm)) {
            $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ? OR c.short_description LIKE ?)";
            $searchParam = '%' . $searchTerm . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        // Lọc theo danh mục
        if (!empty($category)) {
            $sql .= " AND cat.name LIKE ?";
            $params[] = '%' . $category . '%';
        }

        // Lọc theo thời gian
        if (!empty($dateFilter)) {
            $today = date('Y-m-d');
            switch ($dateFilter) {
                case 'upcoming':
                    $sql .= " AND c.start_date >= ?";
                    $params[] = $today;
                    break;
                case 'thisMonth':
                    $startMonth = date('Y-m-01');
                    $endMonth = date('Y-m-t');
                    $sql .= " AND c.start_date BETWEEN ? AND ?";
                    $params[] = $startMonth;
                    $params[] = $endMonth;
                    break;
                case 'thisYear':
                    $startYear = date('Y-01-01');
                    $endYear = date('Y-12-31');
                    $sql .= " AND c.start_date BETWEEN ? AND ?";
                    $params[] = $startYear;
                    $params[] = $endYear;
                    break;
                case 'past':
                    $sql .= " AND c.start_date < ?";
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
     */    /**
          * Lấy lịch trình của một hội nghị theo ID
          * 
          * @param int $conferenceId ID của hội nghị
          * @return array|false Danh sách lịch trình hoặc false nếu không có
          */
    public function getConferenceSchedule($conferenceId)
    {
        return $this->db->fetchAll("
            SELECT 
                ss.*,
                s.name as speaker_name,
                s.title as speaker_title,
                s.company as speaker_company,
                s.image as speaker_image
            FROM schedule_sessions ss
            LEFT JOIN speakers s ON ss.speaker_id = s.id
            WHERE ss.conference_id = ? 
            ORDER BY ss.session_date ASC, ss.start_time ASC
        ", [$conferenceId]);
    }/**
     * Lấy danh sách diễn giả của một hội nghị theo ID
     * 
     * @param int $conferenceId ID của hội nghị
     * @return array|false Danh sách diễn giả hoặc false nếu không có
     */
    public function getConferenceSpeakers($conferenceId)
    {
        $speakers = $this->db->fetchAll("
            SELECT 
                s.id,
                s.name,
                s.title,
                s.company,
                s.bio,
                s.short_bio,
                s.image,
                s.email,
                s.website,
                s.linkedin,
                s.twitter,
                s.github,
                s.youtube,
                s.specialties,
                s.topics,
                cs.role,
                cs.talk_title,
                cs.talk_description,
                cs.talk_duration,
                cs.status as speaker_status
            FROM conference_speakers cs
            LEFT JOIN speakers s ON cs.speaker_id = s.id
            WHERE cs.conference_id = ? 
            AND cs.status = 'confirmed'
            ORDER BY 
                CASE cs.role 
                    WHEN 'keynote' THEN 1 
                    WHEN 'speaker' THEN 2 
                    WHEN 'panelist' THEN 3 
                    ELSE 4 
                END, 
                s.name ASC
        ", [$conferenceId]);

        // Xử lý dữ liệu diễn giả
        return $this->processSpeakersData($speakers);
    }

    /**
     * Lấy thông tin mục tiêu của hội nghị
     * 
     * @param int $conferenceId ID của hội nghị
     * @return array Danh sách mục tiêu
     */
    public function getConferenceObjectives($conferenceId)
    {
        $sql = "SELECT * FROM conference_objectives WHERE conference_id = ? ORDER BY display_order";
        return $this->db->fetchAll($sql, [$conferenceId]);
    }

    /**
     * Lấy thông tin đối tượng tham dự của hội nghị
     * 
     * @param int $conferenceId ID của hội nghị
     * @return array Danh sách đối tượng tham dự
     */
    public function getConferenceAudience($conferenceId)
    {
        $sql = "SELECT * FROM conference_audience WHERE conference_id = ? ORDER BY display_order";
        return $this->db->fetchAll($sql, [$conferenceId]);
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
    public function getRelatedConferences($conferenceId, $categoryId, $limit = 3)
    {
        return $this->db->fetchAll("
            SELECT id, title, start_date, image
            FROM conferences
            WHERE category_id = ? AND id != ?
            ORDER BY start_date DESC
            LIMIT ?
        ", [$categoryId, $conferenceId, $limit]);
    }    /**
         * Lấy hội nghị sắp tới
         *
         * @param int $limit Số lượng tối đa
         * @return array Danh sách hội nghị
         */
    public function getUpcomingConferences($limit = 10)
    {
        $sql = "SELECT 
                    c.*,
                    cat.name as category_name,
                    cat.slug as category_slug,
                    cat.color as category_color,
                    v.name as venue_name,
                    v.city as venue_city,
                    u1.firstName as created_by_name,
                    u1.lastName as created_by_lastname                FROM conferences c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN users u1 ON c.created_by = u1.id
                WHERE 1=1
                AND c.start_date >= CURDATE()
                ORDER BY c.start_date ASC
                LIMIT ?";

        $conferences = $this->db->fetchAll($sql, [$limit]);

        // Xử lý JSON fields
        return $this->processConferencesData($conferences);
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
                    title, short_description, description, start_date, end_date, location, 
                    category_id, price, capacity, status, image, 
                    organizer_name, organizer_email, organizer_phone, created_by
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?
                )
            ", [
                $data['title'],
                $data['short_description'] ?? '',
                $data['description'],
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['location'],
                $data['category_id'],
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
     * Cập nhật thông tin hội nghị (mở rộng theo schema mới)
     *
     * @param int $id ID hội nghị
     * @param array $data Thông tin cập nhật
     * @return bool Thành công hay không
     */
    public function updateConference($id, $data)
    {
        try {
            // Chuẩn bị danh sách trường và giá trị động
            $fields = [];
            $params = [];
            $schemaFields = [
                'title', 'slug', 'description', 'short_description', 'start_date', 'end_date',
                'category_id', 'venue_id', 'type', 'format', 'price', 'currency', 'capacity',
                'current_attendees', 'status', 'featured', 'trending', 'certificate_available',
                'website', 'contact_email', 'contact_phone', 'featured_image', 'social_links',
                'registration_enabled', 'is_featured', 'meta_data', 'updated_at'
            ];
            foreach ($schemaFields as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "$field = ?";
                    // Xử lý các trường JSON
                    if (in_array($field, ['meta_data', 'social_links'])) {
                        $params[] = is_array($data[$field]) ? json_encode($data[$field], JSON_UNESCAPED_UNICODE) : $data[$field];
                    } else {
                        $params[] = $data[$field];
                    }
                }
            }
            if (empty($fields)) return false;
            $params[] = $id;
            $sql = "UPDATE conferences SET ".implode(", ", $fields)." WHERE id = ?";
            $this->db->execute($sql, $params);
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
    }    /**
         * Tìm kiếm hội nghị
         * 
         * @param string $query Từ khóa tìm kiếm
         * @param string $category Danh mục
         * @param string $location Địa điểm
         * @param int $limit Số lượng kết quả
         * @return array Danh sách hội nghị tìm được
         */
    public function searchConferences($query = '', $category = '', $location = '', $limit = 20)
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
                LEFT JOIN categories cat ON c.category_id = cat.id                LEFT JOIN venues v ON c.venue_id = v.id
                LEFT JOIN users u1 ON c.created_by = u1.id
                WHERE 1=1";

        $params = [];

        // Tìm kiếm theo từ khóa
        if (!empty($query)) {
            $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.short_description LIKE ?)";
            $searchParam = '%' . $query . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        // Lọc theo danh mục
        if (!empty($category)) {
            $sql .= " AND cat.name LIKE ?";
            $params[] = '%' . $category . '%';
        }

        // Lọc theo địa điểm
        if (!empty($location)) {
            $sql .= " AND (c.location LIKE ? OR v.city LIKE ?)";
            $params[] = '%' . $location . '%';
            $params[] = '%' . $location . '%';
        }

        $sql .= " ORDER BY c.start_date DESC LIMIT ?";
        $params[] = $limit;

        $conferences = $this->db->fetchAll($sql, $params);

        // Xử lý JSON fields
        return $this->processConferencesData($conferences);
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
    }    /**
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

    /**
     * Xử lý dữ liệu hội nghị trả về từ database
     * Chuyển đổi JSON fields và chuẩn hóa dữ liệu
     *
     * @param array $conferences Danh sách hội nghị từ database
     * @return array Dữ liệu đã được xử lý
     */
    private function processConferencesData($conferences)
    {
        if (empty($conferences)) {
            return [];
        }

        $processedConferences = [];

        foreach ($conferences as $conference) {
            // Chuẩn hóa dữ liệu
            $processed = [
                'id' => intval($conference['id']),
                'title' => $conference['title'],
                'slug' => $conference['slug'],
                'description' => $conference['short_description'] ?: $conference['description'],
                'full_description' => $conference['description'],
                'date' => $conference['start_date'], // Alias for compatibility
                'start_date' => $conference['start_date'],
                'end_date' => $conference['end_date'],
                'location' => $conference['location'] ?: 'Chưa xác định',
                'venue' => $conference['venue_name'] ?? 'Chưa xác định',
                'venue_city' => $conference['venue_city'] ?? '',
                'category' => $conference['category_name'] ?? 'Chưa phân loại',
                'category_id' => intval($conference['category_id'] ?? 0),
                'category_slug' => $conference['category_slug'] ?? '',
                'category_color' => $conference['category_color'] ?? '#007bff',
                'price' => floatval($conference['price'] ?? 0),
                'currency' => $conference['currency'] ?? 'VND',
                'capacity' => intval($conference['capacity'] ?? 0),
                'current_attendees' => intval($conference['current_attendees'] ?? 0),
                'attendees' => intval($conference['current_attendees'] ?? 0), // Alias for compatibility
                'status' => $conference['status'] ?? 'draft',
                'type' => $conference['type'] ?? 'in_person',
                'format' => $conference['format'] ?? 'conference',
                'featured' => boolval($conference['featured'] ?? false),
                'image' => $conference['banner_image'] ?: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=400&fit=crop',
                'created_by' => intval($conference['created_by'] ?? 0),
                'created_by_name' => trim(($conference['created_by_name'] ?? '') . ' ' . ($conference['created_by_lastname'] ?? '')),
                'created_at' => $conference['created_at'],
                'updated_at' => $conference['updated_at']
            ];

            // Xử lý JSON fields nếu có
            if (isset($conference['tags']) && !empty($conference['tags'])) {
                $processed['tags'] = is_string($conference['tags']) ?
                    json_decode($conference['tags'], true) : $conference['tags'];
            }

            if (isset($conference['requirements']) && !empty($conference['requirements'])) {
                $processed['requirements'] = is_string($conference['requirements']) ?
                    json_decode($conference['requirements'], true) : $conference['requirements'];
            }

            if (isset($conference['social_links']) && !empty($conference['social_links'])) {
                $processed['social_links'] = is_string($conference['social_links']) ?
                    json_decode($conference['social_links'], true) : $conference['social_links'];
            }

            $processedConferences[] = $processed;
        }

        return $processedConferences;
    }

    /**
     * Xử lý dữ liệu của một hội nghị đơn lẻ
     * 
     * @param array $conference Dữ liệu hội nghị cần xử lý
     * @return array Dữ liệu hội nghị sau khi xử lý
     */
    private function processConferenceData($conference)
    {
        if (empty($conference))
            return [];

        // Xử lý ngày tháng theo định dạng chuẩn
        if (isset($conference['start_date'])) {
            $conference['start_date_formatted'] = date('d/m/Y', strtotime($conference['start_date']));
        }

        if (isset($conference['end_date'])) {
            $conference['end_date_formatted'] = date('d/m/Y', strtotime($conference['end_date']));
        }

        // Xử lý giá tiền định dạng chuẩn
        if (isset($conference['price'])) {
            $conference['price_formatted'] = number_format($conference['price'], 0, ',', '.') . ' ₫';
        }

        // Tính số chỗ trống
        if (isset($conference['capacity']) && isset($conference['current_attendees'])) {
            $conference['available_spots'] = max(0, $conference['capacity'] - $conference['current_attendees']);
        }

        // Kết hợp thông tin venue thành một đối tượng
        $venueFields = array_filter(array_keys($conference), function ($key) {
            return strpos($key, 'venue_') === 0;
        });

        if (!empty($venueFields)) {
            $venue = [];
            foreach ($venueFields as $field) {
                $newKey = substr($field, 6); // Loại bỏ prefix 'venue_'
                $venue[$newKey] = $conference[$field];
            }
            $conference['venue'] = $venue;
        }

        // Kết hợp thông tin creator thành một đối tượng
        $creatorFields = array_filter(array_keys($conference), function ($key) {
            return strpos($key, 'creator_') === 0;
        });

        if (!empty($creatorFields)) {
            $creator = [];
            foreach ($creatorFields as $field) {
                $newKey = substr($field, 8); // Loại bỏ prefix 'creator_'
                $creator[$newKey] = $conference[$field];
            }
            $conference['creator'] = $creator;
        }

        return $conference;
    }

    /**
     * Xử lý dữ liệu diễn giả sau khi lấy từ database
     * 
     * @param array $speakers Danh sách diễn giả
     * @return array Danh sách diễn giả đã được xử lý
     */
    private function processSpeakersData($speakers)
    {
        if (empty($speakers))
            return [];

        $result = [];
        foreach ($speakers as $speaker) {
            // Xử lý đường dẫn hình ảnh
            if (!empty($speaker['image']) && strpos($speaker['image'], 'http') !== 0) {
                // Nếu không phải URL đầy đủ thì thêm đường dẫn gốc
                if (strpos($speaker['image'], '/') === 0) {
                    // Nếu bắt đầu bằng / thì đường dẫn từ gốc
                    $speaker['image'] = 'http://' . $_SERVER['HTTP_HOST'] . $speaker['image'];
                } else {
                    // Ngược lại thêm vào thư mục gốc
                    $speaker['image'] = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $speaker['image'];
                }
            }

            // Xử lý các trường JSON
            $jsonFields = ['expertise', 'specialties', 'topics', 'social_links'];
            foreach ($jsonFields as $field) {
                if (isset($speaker[$field]) && is_string($speaker[$field])) {
                    $speaker[$field] = json_decode($speaker[$field], true);
                }
            }

            $result[] = $speaker;
        }

        return $result;
    }
}
?>