<?php
require_once 'config.php';
require_once 'db_config.php';
require_once 'google_sheets.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$gameManager = new GameManager($db);
$resultManager = new ResultManager($db);

// Check if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Load admin profile
$profileFile = __DIR__ . '/../data/admin_profile.json';
$profile = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true) : [];

// Initialize Google Sheets integration
$sheets = new GoogleSheetsIntegration(GOOGLE_SHEETS_ID, GOOGLE_SHEETS_NAME);

// Get all active games
$games = $gameManager->getAllGames();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = sanitize_input($_POST['game_id'] ?? '');
    $number = sanitize_input($_POST['number'] ?? '');
    $time = sanitize_input($_POST['time'] ?? '');
    
    if ($game_id && $number && $time) {
        // Add result to database
        if ($resultManager->addResult($game_id, $number, date('Y-m-d'), $time)) {
            // Prepare result data for Google Sheets
            $game = array_filter($games, function($g) use ($game_id) {
                return $g['id'] == $game_id;
            });
            $game = reset($game);
            
            $resultData = [
                'date' => date('Y-m-d'),
                'game' => $game['display_name'],
                'number' => $number,
                'time' => $time,
                'status' => 'WIN'
            ];
            
            // Save to Google Sheets
            $sheets->appendResult($resultData);
            
            // Log the action
            log_admin_action('add_result', json_encode($resultData));
            
            $success = "Result added successfully!";
        } else {
            $error = "Failed to add result.";
        }
    }
}

// Get historical results from database
$historicalResults = $resultManager->getHistoricalResults();

// Get today's results
$todayResults = $resultManager->getTodayResults();
$todayCount = count($todayResults);

// Count pending results
$pendingCount = count($games) - $todayCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-8">
                    <h1 class="text-xl font-bold"><?php echo SITE_NAME; ?> Admin</h1>
                    <a href="manage_games.php" class="text-gray-300 hover:text-white transition">
                        <i class="fas fa-gamepad mr-2"></i>Manage Games
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="profile.php" class="text-gray-300 hover:text-white transition">
                        <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($profile['name'] ?? 'Admin'); ?>
                    </a>
                    <a href="?logout=1" class="text-red-400 hover:text-red-300 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Results</p>
                        <h3 class="text-2xl font-bold"><?php echo count($historicalResults); ?></h3>
                    </div>
                    <div class="text-blue-500 text-2xl">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Today's Results</p>
                        <h3 class="text-2xl font-bold"><?php echo $todayCount; ?></h3>
                    </div>
                    <div class="text-green-500 text-2xl">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Pending Results</p>
                        <h3 class="text-2xl font-bold"><?php echo $pendingCount; ?></h3>
                    </div>
                    <div class="text-yellow-500 text-2xl">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Active Games</p>
                        <h3 class="text-2xl font-bold"><?php echo count($games); ?></h3>
                    </div>
                    <div class="text-purple-500 text-2xl">
                        <i class="fas fa-gamepad"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($success)): ?>
            <div class="bg-green-500 text-white p-4 rounded-lg mb-6 flex items-center justify-between">
                <div>
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Add Result Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-6">Add New Result</h2>
            <?php if (empty($games)): ?>
                <div class="bg-yellow-500 text-white p-4 rounded-lg mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No active games found. Please <a href="manage_games.php" class="underline">add some games</a> first.
                </div>
            <?php else: ?>
                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-gray-400 mb-2">Game</label>
                            <select name="game_id" required class="w-full bg-gray-700 rounded-lg px-4 py-2 text-white">
                                <?php foreach ($games as $game): ?>
                                <option value="<?php echo $game['id']; ?>"><?php echo $game['display_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Number</label>
                            <input type="number" name="number" required min="0" max="99"
                                   class="w-full bg-gray-700 rounded-lg px-4 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-2">Time</label>
                            <input type="time" name="time" required
                                   class="w-full bg-gray-700 rounded-lg px-4 py-2 text-white">
                        </div>
                    </div>
                    <button type="submit" 
                            class="bg-blue-600 text-white rounded-lg py-2 px-6 hover:bg-blue-700 transition duration-200">
                        Add Result
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Historical Results -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-bold mb-6">Historical Results</h2>
            <?php if (empty($historicalResults)): ?>
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-history text-4xl mb-4"></i>
                    <p>No historical results found. Results will appear here after you add them.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-700">
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Game</th>
                                <th class="px-4 py-2 text-left">Number</th>
                                <th class="px-4 py-2 text-left">Time</th>
                                <th class="px-4 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historicalResults as $result): ?>
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($result['date']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($result['game']); ?></td>
                                <td class="px-4 py-2 font-bold text-yellow-500"><?php echo htmlspecialchars($result['number']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($result['time']); ?></td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 rounded-full bg-green-500 text-xs">
                                        <?php echo htmlspecialchars($result['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success message after 3 seconds
            const successMessage = document.querySelector('.bg-green-500');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.remove();
                }, 3000);
            }
        });
    </script>
</body>
</html>