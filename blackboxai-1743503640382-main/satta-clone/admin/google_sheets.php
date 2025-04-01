<?php
require_once __DIR__ . '/config.php';

class GoogleSheetsIntegration {
    private $spreadsheetId;
    private $sheetName;
    
    public function __construct($spreadsheetId, $sheetName = 'Results') {
        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;
    }
    
    // Function to append result to Google Sheet
    public function appendResult($data) {
        // In production, implement Google Sheets API
        // For now, save to CSV as backup
        $this->saveToCSV($data);
    }
    
    // Backup function to save results to CSV
    private function saveToCSV($data) {
        $csvFile = __DIR__ . '/../data/historical_results.csv';
        $isNewFile = !file_exists($csvFile);
        
        $fp = fopen($csvFile, 'a');
        
        // Add headers if new file
        if ($isNewFile) {
            fputcsv($fp, ['Date', 'Game', 'Number', 'Time', 'Status']);
        }
        
        // Add data
        fputcsv($fp, [
            $data['date'],
            $data['game'],
            $data['number'],
            $data['time'],
            $data['status']
        ]);
        
        fclose($fp);
    }
    
    // Function to get historical results
    public function getHistoricalResults() {
        $results = [];
        $csvFile = __DIR__ . '/../data/historical_results.csv';
        
        if (file_exists($csvFile)) {
            if (($handle = fopen($csvFile, "r")) !== FALSE) {
                // Skip headers
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    $results[] = [
                        'date' => $data[0],
                        'game' => $data[1],
                        'number' => $data[2],
                        'time' => $data[3],
                        'status' => $data[4]
                    ];
                }
                fclose($handle);
            }
        }
        
        return $results;
    }
}

// Example usage:
// $sheets = new GoogleSheetsIntegration('YOUR_SPREADSHEET_ID');
// $sheets->appendResult([
//     'date' => '2025-04-01',
//     'game' => 'FARIDABAD',
//     'number' => '45',
//     'time' => '6:00 PM',
//     'status' => 'WIN'
// ]);
?>