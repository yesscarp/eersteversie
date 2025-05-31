<?php
// config/database.php - Updated version
class Database {
    private $host = 'localhost:3306';
    private $db_name = 'yesspta';
    private $username = 'ptav1-yess';
    private $password = 'RX@@2nC45fjafzxz';
    private $conn;

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } catch(PDOException $exception) {
                error_log("Database connection error: " . $exception->getMessage());
                throw new Exception("Database verbinding mislukt. Probeer het later opnieuw.");
            }
        }
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}

// Global database instance
$database = new Database();
$db = $database->getConnection();
?>

