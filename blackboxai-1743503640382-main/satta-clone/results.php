<?php
header('Content-Type: application/json');
require_once 'admin/db_config.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$gameManager = new GameManager($db);
$resultManager = new ResultManager($db);

try {
    // Get all active games
    $games = $gameManager->getAllGames();
    
    if (empty($games)) {
        throw new Exception('No active games found');
    }
    
    // Get today's results
    $todayResults = $resultManager->getTodayResults();
    
    // Format results for response
    $formattedResults = [];
    foreach ($todayResults as $result) {
        $formattedResults[$result['name']] = [
            'name' => $result['display_name'],
            'time' => date('h:i A', strtotime($result['time'])),
            'result' => $result['number'],
            'status' => $result['status'],
            'date' => $result['date']
        ];
    }
    
    // Get historical results
    $historicalResults = $resultManager->getHistoricalResults(100); // Last 100 results
    
    // Format historical results
    $formattedHistorical = array_map(function($result) {
        return [
            'date' => $result['date'],
            'game' => $result['display_name'],
            'number' => $result['number'],
            'time' => date('h:i A', strtotime($result['time'])),
            'status' => $result['status']
        ];
    }, $historicalResults);
    
    // Format games data
    $formattedGames = array_map(function($game) {
        return [
            'name' => $game['name'],
            'display_name' => $game['display_name'],
            'time_slot' => date('h:i A', strtotime($game['time_slot'])),
            'status' => $game['status']
        ];
    }, $games);
    
    // Prepare response
    $response = [
        'date' => date('Y-m-d'),
        'results' => $formattedResults,
        'historical' => $formattedHistorical,
        'games' => $formattedGames,
        'status' => 'success',
        'message' => 'Results fetched successfully',
        'next_update' => date('h:i A', strtotime('+5 minutes'))
    ];
    
    // Send JSON response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log error
    error_log("Error fetching results: " . $e->getMessage());
    
    // Send error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch results. Please try again later.'
    ]);
}
