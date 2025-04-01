<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'satta_king';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
        }

        return $this->conn;
    }
}

// Game management class
class GameManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add new game
    public function addGame($name, $display_name, $time_slot) {
        try {
            $query = "INSERT INTO games (name, display_name, time_slot) VALUES (:name, :display_name, :time_slot)";
            $stmt = $this->conn->prepare($query);
            
            // Convert values before binding
            $name_lower = strtolower($name);
            $display_name_upper = strtoupper($display_name);
            
            // Bind parameters
            $stmt->bindParam(":name", $name_lower);
            $stmt->bindParam(":display_name", $display_name_upper);
            $stmt->bindParam(":time_slot", $time_slot);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error adding game: " . $e->getMessage());
            return false;
        }
    }

    // Get all games
    public function getAllGames() {
        try {
            $query = "SELECT * FROM games WHERE status = 'active' ORDER BY time_slot";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting games: " . $e->getMessage());
            return [];
        }
    }

    // Update game status
    public function updateGameStatus($id, $status) {
        try {
            $query = "UPDATE games SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            // Convert values before binding
            $status_value = strval($status);
            $id_value = intval($id);
            
            // Bind parameters
            $stmt->bindParam(":status", $status_value);
            $stmt->bindParam(":id", $id_value, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error updating game status: " . $e->getMessage());
            return false;
        }
    }
}

// Result management class
class ResultManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add new result
    public function addResult($game_id, $number, $date, $time) {
        try {
            $query = "INSERT INTO results (game_id, number, date, time) 
                     VALUES (:game_id, :number, :date, :time)
                     ON DUPLICATE KEY UPDATE 
                     number = :number, time = :time";
            
            $stmt = $this->conn->prepare($query);
            
            // Convert values before binding
            $game_id_value = intval($game_id);
            $number_value = intval($number);
            $date_value = strval($date);
            $time_value = strval($time);
            
            // Bind parameters
            $stmt->bindParam(":game_id", $game_id_value, PDO::PARAM_INT);
            $stmt->bindParam(":number", $number_value, PDO::PARAM_INT);
            $stmt->bindParam(":date", $date_value);
            $stmt->bindParam(":time", $time_value);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error adding result: " . $e->getMessage());
            return false;
        }
    }

    // Get today's results
    public function getTodayResults() {
        try {
            $query = "SELECT r.*, g.name, g.display_name 
                     FROM results r 
                     JOIN games g ON r.game_id = g.id 
                     WHERE r.date = CURDATE()
                     ORDER BY r.time";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting today's results: " . $e->getMessage());
            return [];
        }
    }

    // Get historical results
    public function getHistoricalResults($limit = 100) {
        try {
            $query = "SELECT 
                        r.date,
                        g.display_name,
                        r.number,
                        r.time,
                        COALESCE(r.status, 'WIN') as status
                     FROM results r 
                     JOIN games g ON r.game_id = g.id 
                     ORDER BY r.date DESC, r.time DESC 
                     LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            
            // Convert and bind limit parameter
            $limit_value = intval($limit);
            $stmt->bindParam(":limit", $limit_value, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting historical results: " . $e->getMessage());
            return [];
        }
    }
}
?>