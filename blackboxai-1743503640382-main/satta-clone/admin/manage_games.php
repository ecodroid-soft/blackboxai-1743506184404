<?php
require_once 'config.php';
require_once 'db_config.php';

// Check if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$gameManager = new GameManager($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitize_input($_POST['name']);
                $display_name = sanitize_input($_POST['display_name']);
                $time_slot = sanitize_input($_POST['time_slot']);
                
                if ($gameManager->addGame($name, $display_name, $time_slot)) {
                    $success = "Game added successfully!";
                    log_admin_action('add_game', "Added game: $name");
                } else {
                    $error = "Failed to add game.";
                }
                break;
                
            case 'update_status':
                $game_id = (int)$_POST['game_id'];
                $status = sanitize_input($_POST['status']);
                
                if ($gameManager->updateGameStatus($game_id, $status)) {
                    $success = "Game status updated successfully!";
                    log_admin_action('update_game_status', "Updated game ID: $game_id to $status");
                } else {
                    $error = "Failed to update game status.";
                }
                break;
        }
    }
}

// Get all games
$games = $gameManager->getAllGames();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Games - Satta King Admin</title>
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
        <?php if (isset($success)): ?>
            <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Add New Game Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-6">Add New Game</h2>
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-gray-400 mb-2">Game Name</label>
                        <input type="text" name="name" required
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white">
                    </div>
                    
                    <div>
                        <label class="block text-gray-400 mb-2">Display Name</label>
                        <input type="text" name="display_name" required
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white">
                    </div>
                    
                    <div>
                        <label class="block text-gray-400 mb-2">Time Slot</label>
                        <input type="time" name="time_slot" required
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white">
                    </div>
                </div>
                
                <button type="submit" 
                        class="bg-blue-600 text-white rounded py-2 px-6 hover:bg-blue-700 transition duration-200">
                    Add Game
                </button>
            </form>
        </div>

        <!-- Games List -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-bold mb-6">Manage Games</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-700">
                            <th class="px-4 py-2 text-left">Game Name</th>
                            <th class="px-4 py-2 text-left">Display Name</th>
                            <th class="px-4 py-2 text-left">Time Slot</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($games as $game): ?>
                        <tr class="border-b border-gray-700">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($game['name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($game['display_name']); ?></td>
                            <td class="px-4 py-2"><?php echo date('h:i A', strtotime($game['time_slot'])); ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full <?php echo $game['status'] === 'active' ? 'bg-green-500' : 'bg-red-500'; ?> text-xs">
                                    <?php echo ucfirst($game['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                                    <input type="hidden" name="status" 
                                           value="<?php echo $game['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                    <button type="submit" 
                                            class="text-sm <?php echo $game['status'] === 'active' ? 'text-red-400' : 'text-green-400'; ?> hover:underline">
                                        <?php echo $game['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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