<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Function to handle POST requests
function handlePost($pdo) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_user':
                // Handle user update (block, edit, delete)
                break;
            case 'send_email':
                // Handle sending individual emails
                break;
            case 'update_account':
                // Handle manual debit/credit of user account
                break;
            case 'update_settings':
                // Handle updating application settings
                break;
            case 'manage_bonus':
                // Handle bonus management
                break;
            case 'manage_bot':
                // Handle bot management
                break;
            case 'toggle_maintenance':
                // Handle toggling maintenance mode
                break;
            case 'clear_cache':
                // Handle clearing cache
                break;
            case 'run_db_update':
                // Handle running database updates
                break;
            case 'execute_scheduled_task':
                // Handle executing scheduled tasks
                break;
        }
    }
}

// Handle POST request
handlePost($pdo);

// Fetch data for display
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$transactions = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 100")->fetchAll();
$games = $pdo->query("SELECT * FROM games ORDER BY played_at DESC LIMIT 100")->fetchAll();

// Fetch application settings
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <h1 class="mt-4">Advanced Admin Dashboard</h1>
        
        <!-- Navigation tabs -->
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="dashboard-tab" data-toggle="tab" href="#dashboard" role="tab">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="users-tab" data-toggle="tab" href="#users" role="tab">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="transactions-tab" data-toggle="tab" href="#transactions" role="tab">Transactions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="games-tab" data-toggle="tab" href="#games" role="tab">Games</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab">Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="maintenance-tab" data-toggle="tab" href="#maintenance" role="tab">Maintenance</a>
            </li>
        </ul>
        
        <!-- Tab content -->
        <div class="tab-content" id="adminTabContent">
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                <h2 class="mt-4">Analytics and Statistics</h2>
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="userRegistrationChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="dailyRevenueChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <h2 class="mt-4">User Management</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['balance']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary">Edit</button>
                                <button class="btn btn-sm btn-danger">Block</button>
                                <button class="btn btn-sm btn-info">Email</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Transactions Tab -->
            <div class="tab-pane fade" id="transactions" role="tabpanel">
                <h2 class="mt-4">Account Transactions</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Games Tab -->
            <div class="tab-pane fade" id="games" role="tabpanel">
                <h2 class="mt-4">Games History</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Game</th>
                            <th>Bet</th>
                            <th>Win</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($game['id']); ?></td>
                            <td><?php echo htmlspecialchars($game['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($game['game_type']); ?></td>
                            <td><?php echo htmlspecialchars($game['bet_amount']); ?></td>
                            <td><?php echo htmlspecialchars($game['win_amount']); ?></td>
                            <td><?php echo htmlspecialchars($game['played_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Settings Tab -->
            <div class="tab-pane fade" id="settings" role="tabpanel">
                <h2 class="mt-4">Application Settings</h2>
                <form method="post">
                    <input type="hidden" name="action" value="update_settings">
                    <div class="form-group">
                        <label for="colorScheme">Color Scheme</label>
                        <select class="form-control" id="colorScheme" name="color_scheme">
                            <option value="light" <?php echo $settings['color_scheme'] == 'light' ? 'selected' : ''; ?>>Light</option>
                            <option value="dark" <?php echo $settings['color_scheme'] == 'dark' ? 'selected' : ''; ?>>Dark</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="layout">Layout</label>
                        <select class="form-control" id="layout" name="layout">
                            <option value="boxed" <?php echo $settings['layout'] == 'boxed' ? 'selected' : ''; ?>>Boxed</option>
                            <option value="full-width" <?php echo $settings['layout'] == 'full-width' ? 'selected' : ''; ?>>Full-width</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="defaultLanguage">Default Language</label>
                        <input type="text" class="form-control" id="defaultLanguage" name="default_language" value="<?php echo htmlspecialchars($settings['default_language']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="sessionLifetime">Session Lifetime (minutes)</label>
                        <input type="number" class="form-control" id="sessionLifetime" name="session_lifetime" value="<?php echo htmlspecialchars($settings['session_lifetime']); ?>">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="emailVerification" name="email_verification" <?php echo $settings['email_verification'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="emailVerification">Enable Email Verification</label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
                </form>
            </div>
            
            <!-- Maintenance Tab -->
            <div class="tab-pane fade" id="maintenance" role="tabpanel">
                <h2 class="mt-4">Maintenance</h2>
                <form method="post" class="mb-3">
                    <input type="hidden" name="action" value="toggle_maintenance">
                    <button type="submit" class="btn btn-warning">Toggle Maintenance Mode</button>
                </form>
                <form method="post" class="mb-3">
                    <input type="hidden" name="action" value="clear_cache">
                    <button type="submit" class="btn btn-info">Clear Cache</button>
                </form>
                <form method="post" class="mb-3">
                    <input type="hidden" name="action" value="run_db_update">
                    <button type="submit" class="btn btn-primary">Run Database Updates</button>
                </form>
                <form method="post">
                    <input type="hidden" name="action" value="execute_scheduled_task">
                    <button type="submit" class="btn btn-secondary">Execute Scheduled Tasks</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Add Chart.js implementations here
        // Example:
        var ctx = document.getElementById('userRegistrationChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'User Registrations',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {}
        });
    </script>
</body>
</html>