<?php
session_start();

// Check if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Load admin profile data
$profileFile = '../data/admin_profile.json';
$profile = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true) : [
    'name' => 'Admin',
    'mobile' => '',
    'about' => '',
    'email' => ''
];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile['name'] = $_POST['name'] ?? $profile['name'];
    $profile['mobile'] = $_POST['mobile'] ?? $profile['mobile'];
    $profile['about'] = $_POST['about'] ?? $profile['about'];
    $profile['email'] = $_POST['email'] ?? $profile['email'];
    
    // Save updated profile
    file_put_contents($profileFile, json_encode($profile));
    $success = "Profile updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Satta King</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-gray-800 p-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-xl font-bold">Satta King Admin</h1>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-300 hover:text-white">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="?logout=1" class="text-red-400 hover:text-red-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container mx-auto p-6">
            <!-- Success Message -->
            <?php if (isset($success)): ?>
                <div class="bg-green-500 text-white p-4 rounded mb-6">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Form -->
            <div class="bg-gray-800 rounded-lg p-6 max-w-2xl mx-auto">
                <h2 class="text-xl font-bold mb-6">Edit Profile</h2>
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Mobile Number</label>
                        <input type="tel" name="mobile" value="<?php echo htmlspecialchars($profile['mobile']); ?>" required
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>"
                               class="w-full bg-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">About</label>
                        <textarea name="about" rows="4" 
                                  class="w-full bg-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($profile['about']); ?></textarea>
                    </div>
                    
                    <button type="submit" 
                            class="bg-blue-600 text-white rounded py-2 px-6 hover:bg-blue-700 transition duration-200">
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add any necessary JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any components
        });
    </script>
</body>
</html>