<?php
require_once 'config.php';
require_once 'db_config.php';
require_once 'sheets_manager.php';

// Check if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize database and sheets manager
$database = new Database();
$db = $database->getConnection();
$sheetsManager = new SheetsManager($db);

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'import':
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
                    $tempFile = $_FILES['csv_file']['tmp_name'];
                    if ($sheetsManager->importFromCSV($tempFile)) {
                        $success = "Historical results imported successfully!";
                        log_admin_action('import_results', 'Imported historical results from CSV');
                    } else {
                        $error = "Failed to import results.";
                    }
                }
                break;
                
            case 'export':
                $days = isset($_POST['days']) ? (int)$_POST['days'] : 30;
                $filename = $sheetsManager->exportToCSV($days);
                if ($filename) {
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename="historical_results.csv"');
                    readfile($filename);
                    exit;
                } else {
                    $error = "Failed to export results.";
                }
                break;
        }
    }
}

// Get last 30 days results
$thirtyDaysResults = $sheetsManager->getLastThirtyDaysResults();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Historical Results - Satta King Admin</title>
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
                    <h1 class="text-xl font-bold">Satta King Admin</h1>
                    <a href="dashboard.php" class="text-gray-300 hover:text-white transition">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="manage_games.php" class="text-gray-300 hover:text-white transition">
                        <i class="fas fa-gamepad mr-2"></i>Games
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="profile.php" class="text-gray-300 hover:text-white transition">
                        <i class="fas fa-user mr-2"></i>Profile
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
        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Import/Export Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Import Form -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-6">Import Historical Results</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="import">
                    <div>
                        <label class="block text-gray-400 mb-2">CSV File</label>
                        <input type="file" name="csv_file" accept=".csv" required
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white">
                    </div>
                    <button type="submit" 
                            class="bg-blue-600 text-white rounded py-2 px-6 hover:bg-blue-700 transition duration-200">
                        Import Results
                    </button>
                </form>
            </div>

            <!-- Export Form -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-6">Export Historical Results</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="export">
                    <div>
                        <label class="block text-gray-400 mb-2">Number of Days</label>
                        <input type="number" name="days" value="30" min="1" max="365" required
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white">
                    </div>
                    <button type="submit" 
                            class="bg-green-600 text-white rounded py-2 px-6 hover:bg-green-700 transition duration-200">
                        Export Results
                    </button>
                </form>
            </div>
        </div>

        <!-- Historical Results Display -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-bold mb-6">Last 30 Days Results</h2>
            <div class="space-y-6">
                <?php foreach ($thirtyDaysResults as $date => $results): ?>
                    <div class="border-b border-gray-700 pb-4">
                        <h3 class="text-lg font-semibold mb-4"><?php echo $date; ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($results as $result): ?>
                                <div class="bg-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium"><?php echo htmlspecialchars($result['game']); ?></span>
                                        <span class="text-yellow-500 font-bold"><?php echo htmlspecialchars($result['number']); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center mt-2 text-sm text-gray-400">
                                        <span><?php echo htmlspecialchars($result['time']); ?></span>
                                        <span class="px-2 py-1 rounded-full bg-green-500 text-xs text-white">
                                            <?php echo htmlspecialchars($result['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide success/error messages after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const messages = document.querySelectorAll('.bg-green-500, .bg-red-500');
                messages.forEach(function(message) {
                    message.style.display = 'none';
                });
            }, 3000);
        });
    </script>
</body>
</html>