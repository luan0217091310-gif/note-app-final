<?php
/**
 * Database Configuration
 * Kết nối MySQLi với Singleton Pattern và Prepared Statements
 */

class Database {
    private static $instance = null;
    private $connection;

    // Cấu hình database - thay đổi theo môi trường
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'note_app_db';
    private $port = 3306;
    private $charset = 'utf8mb4';

    /**
     * Constructor - Khởi tạo kết nối MySQLi
     */
    private function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database,
                $this->port
            );

            $this->connection->set_charset($this->charset);

        } catch (mysqli_sql_exception $e) {
            error_log("Database Error: " . $e->getMessage());
            die("Lỗi kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình.");
        }
    }

    /**
     * Singleton - Đảm bảo chỉ có 1 kết nối duy nhất
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Lấy đối tượng kết nối MySQLi
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Ngăn clone và unserialize (Singleton)
     */
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper - Lấy kết nối MySQLi nhanh
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
