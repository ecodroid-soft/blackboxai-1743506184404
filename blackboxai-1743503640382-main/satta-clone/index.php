<?php
require_once 'admin/db_config.php';
require_once 'admin/sheets_manager.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$gameManager = new GameManager($db);
$resultManager = new ResultManager($db);
$sheetsManager = new SheetsManager($db);

// Get all active games
$games = $gameManager->getAllGames();

// Get today's results
$todayResults = $resultManager->getTodayResults();

// Get 30 days historical results
$historicalResults = $sheetsManager->getLastThirtyDaysResults();

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
        <!-- Navigation Bar -->
        <nav class="navbar">
            <div class="home-icon">
                <a href="#"><i class="fas fa-home"></i></a>
            </div>
            <div class="nav-links">
                <a href="#" class="nav-link">SATTA KING 786</a>
                <a href="#" class="nav-link">SATTA CHART</a>
                <a href="#" class="nav-link">TAJ SATTA KING</a>
                <a href="#" class="nav-link">SATTA LEAK</a>
            </div>
        </nav>

        <!-- Marquee Section -->
        <div class="marquee-section">
            <marquee>SATTA KING, SATTAKING, SATTA RESULT</marquee>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>आज का सट्टा नंबर यहाँ देखें</h1>
                <p>【FARIDABAD GAZIYABAD GALI DS】</p>
                <p class="after-pass">AFTER PASS AFTER PASS</p>
                <h2>MUMBAI HEAD BRANCH (MD)</h2>
                <p class="add-text">ADD</p>
                <button class="satta-king-btn">SATTA KING</button>
            </div>

            <div class="result-section">
                <h2>Satta king | Satta result | सट्टा किंग</h2>
                
                <!-- Live Results Section -->
                <div class="live-results">
                    <h3 class="result-title">🔴 LIVE RESULTS</h3>
                    <div class="result-grid">
                        <?php foreach ($games as $game): ?>
                        <div class="result-card" data-game="<?php echo htmlspecialchars($game['name']); ?>">
                            <div class="card-header">
                                <h4><?php echo htmlspecialchars($game['display_name']); ?></h4>
                                <p class="time"><?php echo date('h:i A', strtotime($game['time_slot'])); ?></p>
                            </div>
                            <div class="number-display <?php echo !isset($formattedResults[$game['name']]) ? 'loading' : ''; ?>">
                                <p class="number">
                                    <?php echo isset($formattedResults[$game['name']]) ? 
                                          htmlspecialchars($formattedResults[$game['name']]['result']) : 
                                          '--'; ?>
                                </p>
                                <div class="number-animation"></div>
                            </div>
                            <span class="status <?php echo isset($formattedResults[$game['name']]) ? 'win' : 'pending'; ?>">
                                <i class="fas <?php echo isset($formattedResults[$game['name']]) ? 
                                                   'fa-check-circle' : 
                                                   'fa-clock'; ?>"></i>
                                <?php echo isset($formattedResults[$game['name']]) ? 'WIN' : 'PENDING'; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="next-update">
                        <p>Next Update In: <span id="countdown">05:00</span></p>
                    </div>
                </div>

                <!-- Historical Results in Google Sheets Style -->
                <div class="sheets-results">
                    <h3>📊 LAST 30 DAYS RECORD</h3>
                    <div class="sheets-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <?php foreach ($games as $game): ?>
                                    <th><?php echo htmlspecialchars($game['display_name']); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historicalResults as $date => $dayResults): ?>
                                <tr>
                                    <td class="date-cell"><?php echo $date; ?></td>
                                    <?php 
                                    foreach ($games as $game) {
                                        $result = array_filter($dayResults, function($r) use ($game) {
                                            return $r['game'] === $game['display_name'];
                                        });
                                        $result = reset($result);
                                        echo '<td class="number-cell">';
                                        if ($result) {
                                            echo '<span class="result-number">' . htmlspecialchars($result['number']) . '</span>';
                                            echo '<span class="result-time">' . htmlspecialchars($result['time']) . '</span>';
                                        } else {
                                            echo '--';
                                        }
                                        echo '</td>';
                                    }
                                    ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="holi-section">
                    <h3>【HOLI DHAMAK】</h3>
                    <p>FARIDABAD | GAZIYABAD | GALI | DS</p>
                    <p class="highlight">【 DIRECT COMPANY SE LEAK JODI 】</p>
                </div>

                <div class="notice-section">
                    <p>AAJ APNA LOSS COVER KARNA CHAHTE HO ,GAME SINGAL JODI MAI HE MILEGA ,GAME KISI KO AAP NAHI KAAT SAKTA ,APNI BOOKING KARANE K LIYE ABHI WHATSAPP YA CALL KARE !</p>
                    <p class="after-pass">AFTER PASS AFTER PASS</p>
                    <h3>RAJBEER SING(CEO)</h3>
                    <h2>SATTA KING HEAD BRANCH MD MUMBAI</h2>
                    <p class="contact">9262372454</p>
                </div>
            </div>
        </main>

        <!-- Floating Action Buttons -->
        <div class="floating-buttons">
            <div class="play-online">
                <p>Play Online</p>
                <p>Satta 100%</p>
                <p>Trusted</p>
            </div>
            <div class="app-download">
                <p>Satta App</p>
                <p>Fast</p>
                <p>Withdrawal</p>
                <p>App Download</p>
                <p>Now</p>
            </div>
            <div class="telegram-icon">
                <a href="#"><i class="fab fa-telegram"></i></a>
            </div>
            <div class="whatsapp-icon">
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <span class="notification-badge">4</span>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
