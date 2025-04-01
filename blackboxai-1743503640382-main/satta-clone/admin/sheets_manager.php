<?php
class SheetsManager {
    private $spreadsheetId;
    private $credentialsPath;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->spreadsheetId = getenv('GOOGLE_SHEETS_ID') ?: ''; // Set your default spreadsheet ID
        $this->credentialsPath = __DIR__ . '/credentials.json';
    }

    // Import historical results from CSV
    public function importFromCSV($file) {
        try {
            if (($handle = fopen($file, "r")) !== FALSE) {
                // Skip header row
                fgetcsv($handle);
                
                $stmt = $this->db->prepare("
                    INSERT INTO results (game_id, number, date, time, status)
                    SELECT g.id, :number, :date, :time, :status
                    FROM games g
                    WHERE g.name = :game_name
                    ON DUPLICATE KEY UPDATE
                    number = VALUES(number),
                    status = VALUES(status)
                ");

                while (($data = fgetcsv($handle)) !== FALSE) {
                    $stmt->bindParam(':game_name', $data[1]); // Game name
                    $stmt->bindParam(':number', $data[2]); // Number
                    $stmt->bindParam(':date', $data[0]); // Date
                    $stmt->bindParam(':time', $data[3]); // Time
                    $stmt->bindParam(':status', $data[4]); // Status
                    $stmt->execute();
                }
                fclose($handle);
                return true;
            }
        } catch (Exception $e) {
            error_log("CSV Import Error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    // Export results to CSV
    public function exportToCSV($days = 30) {
        try {
            $query = "
                SELECT 
                    r.date,
                    g.name as game_name,
                    r.number,
                    r.time,
                    r.status
                FROM results r
                JOIN games g ON r.game_id = g.id
                WHERE r.date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                ORDER BY r.date DESC, r.time DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $filename = __DIR__ . '/../data/historical_results.csv';
            $fp = fopen($filename, 'w');
            
            // Write header
            fputcsv($fp, ['Date', 'Game', 'Number', 'Time', 'Status']);
            
            // Write data
            foreach ($results as $result) {
                fputcsv($fp, $result);
            }
            
            fclose($fp);
            return $filename;
        } catch (Exception $e) {
            error_log("CSV Export Error: " . $e->getMessage());
            return false;
        }
    }

    // Get last 30 days results for display
    public function getLastThirtyDaysResults() {
        try {
            $query = "
                SELECT 
                    DATE_FORMAT(r.date, '%d-%m-%Y') as formatted_date,
                    g.display_name,
                    r.number,
                    DATE_FORMAT(r.time, '%h:%i %p') as formatted_time,
                    r.status
                FROM results r
                JOIN games g ON r.game_id = g.id
                WHERE r.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                ORDER BY r.date DESC, r.time DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($results[$row['formatted_date']])) {
                    $results[$row['formatted_date']] = [];
                }
                $results[$row['formatted_date']][] = [
                    'game' => $row['display_name'],
                    'number' => $row['number'],
                    'time' => $row['formatted_time'],
                    'status' => $row['status']
                ];
            }
            
            return $results;
        } catch (Exception $e) {
            error_log("Error getting 30 days results: " . $e->getMessage());
            return [];
        }
    }
}
?>