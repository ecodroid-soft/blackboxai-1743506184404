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
            
            $stmt->bindParam(":name", strtolower($name));
            $stmt->bindParam(":display_name", strtoupper($display_name));
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
            
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":id", $id);
            
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
            
            $stmt->bindParam(":game_id", $game_id);
            $stmt->bindParam(":number", $number);
            $stmt->bindParam(":date", $date);
            $stmt->bindParam(":time", $time);
            
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
            $query = "SELECT r.*, g.name, g.display_name 
                     FROM results r 
                     JOIN games g ON r.game_id = g.id 
                     ORDER BY r.date DESC, r.time DESC 
                     LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting historical results: " . $e->getMessage());
            return [];
        }
    }
}
?>