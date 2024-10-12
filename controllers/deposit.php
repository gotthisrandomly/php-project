<?php
require_once '../includes/functions.php';
require_once '../includes/validation.php';
require_once '../includes/payment_gateway.php';
require_once '../includes/responsible_gambling.php';

session_start();

if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

$user_id = $_SESSION['user_id'];
$balance = get_user_balance($user_id);
$deposit_limit = get_deposit_limit($user_id);

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token";
    } else {
        $amount = floatval($_POST['amount']);
        if ($amount <= 0) {
            $errors[] = "Invalid deposit amount";
        } elseif ($deposit_limit && !check_deposit_limit($user_id, $amount)) {
            $errors[] = "This deposit would exceed your daily deposit limit";
        } else {
            $payment = initiate_payment($amount, $user_id);
            header("Location: " . $payment['redirect_url']);
            exit;
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit - SD777Slots</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Deposit</h1>
        <p>Your current balance: $<?php echo number_format($balance, 2); ?></p>
        <?php if ($deposit_limit): ?>
            <p>Your daily deposit limit: $<?php echo number_format($deposit_limit, 2); ?></p>
        <?php endif; ?>
        
        <?php
        if (!empty($errors)) {
            echo "<ul class='error'>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        if ($success_message) {
            echo "<p class='success'>$success_message</p>";
        }
        ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <label for="amount">Deposit Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" min="1" required><br>
            
            <button type="submit">Deposit</button>
        </form>
        
        <p><a href="/">Back to Home</a></p>
    </div>
</body>
</html>