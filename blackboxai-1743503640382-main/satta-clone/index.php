<?php
require_once 'admin/db_config.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$gameManager = new GameManager($db);
$resultManager = new ResultManager($db);

// Get all active games
$games = $gameManager->getAllGames();

// Get today's results
$todayResults = $resultManager->getTodayResults();

// Format results for display
$formattedResults = [];
foreach ($todayResults as $result) {
    $formattedResults[$result['name']] = [
        'name' => $result['display_name'],
        'time' => date('h:i A', strtotime($result['time'])),
        'result' => $result['number'],
        'status' => $result['status']
    ];
}

// Sample data for 30-day record with exact format
$thirtyDayRecord = [
    [
        'date' => '01-04-2025',
        'results' => [
            ['game' => 'DS', 'number' => "10\n05:59 PM", 'time' => true],
            ['game' => 'GAME', 'number' => '--', 'time' => false],
            ['game' => 'HARA', 'number' => "15\n05:32 PM", 'time' => true],
            ['game' => 'SIRSA', 'number' => '--', 'time' => false],
            ['game' => 'FARIDABAD', 'number' => "50\n05:50 PM", 'time' => true],
            ['game' => 'GAZIYABAD', 'number' => '--', 'time' => false],
            ['game' => 'GALI', 'number' => '--', 'time' => false]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satta King</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- [Previous sections remain the same until the 30-day record] -->

        <!-- 30 Days Record Table -->
        <div class="thirty-day-record">
            <h3>📊 LAST 30 DAYS RECORD</h3>
            <div class="record-table-wrapper">
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <?php foreach ($thirtyDayRecord[0]['results'] as $result): ?>
                            <th><?php echo $result['game']; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($thirtyDayRecord as $record): ?>
                        <tr>
                            <td class="date-cell"><?php echo $record['date']; ?></td>
                            <?php foreach ($record['results'] as $result): ?>
                            <td class="result-cell <?php echo $result['time'] ? 'has-time' : ''; ?>">
                                <?php echo $result['number']; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- [Rest of the sections remain the same] -->

    </div>
    <script src="script.js"></script>
</body>
</html>
