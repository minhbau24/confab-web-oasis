<?php
require_once __DIR__ . '/../includes/config.php';
/**
 * Class Database - Xử lý kết nối và truy vấn cơ sở dữ liệu
 */
class Database
{
    private $conn;
    private static $instance;

    /**
     * Khởi tạo kết nối
     */
    private function __construct()
    {
        try {
            $this->conn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage());
        }
    }

    /**
     * Lấy instance của Database (Singleton pattern)
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
      /**
     * Lấy kết nối PDO
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Thực thi truy vấn và trả về kết quả dưới dạng mảng
     *
     * @param string $sql Câu truy vấn SQL
     * @param array $params Tham số cho câu truy vấn
     * @return array Kết quả truy vấn
     */
    public function fetchAll($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Thực thi truy vấn và trả về một dòng kết quả
     *
     * @param string $sql Câu truy vấn SQL
     * @param array $params Tham số cho câu truy vấn
     * @return array|false Kết quả truy vấn hoặc false nếu không tìm thấy
     */
    public function fetch($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thực thi một câu lệnh SQL (INSERT, UPDATE, DELETE)
     *
     * @param string $sql Câu lệnh SQL
     * @param array $params Tham số cho câu lệnh
     * @return bool|int Kết quả thực thi hoặc số dòng bị ảnh hưởng
     */
    public function execute($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy ID của dòng vừa chèn
     *
     * @return int|string ID của dòng vừa chèn
     */
    public function lastInsertId()
    {
        return $this->conn->lastInsertId();
    }

    /**
     * Bắt đầu transaction
     */
    public function beginTransaction()
    {
        $this->conn->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->conn->rollBack();
    }

    /**
     * Giải mã các trường JSON trong kết quả truy vấn
     * 
     * @param array $data Dữ liệu cần xử lý
     * @param array $jsonFields Danh sách các trường JSON
     * @return array Dữ liệu sau khi xử lý
     */
    public function decodeJsonFields($data, $jsonFields = [])
    {
        if (empty($data) || empty($jsonFields)) {
            return $data;
        }
        
        // Xử lý một mảng kết quả
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as &$row) {
                foreach ($jsonFields as $field) {
                    if (isset($row[$field]) && is_string($row[$field])) {
                        $row[$field] = json_decode($row[$field], true);
                    }
                }
            }
            return $data;
        }
        
        // Xử lý một dòng kết quả
        foreach ($jsonFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = json_decode($data[$field], true);
            }
        }
        
        return $data;
    }
}
?>