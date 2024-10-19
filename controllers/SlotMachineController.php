<?php

class SlotMachineController {
    private $symbols = ['🍒', '🍋', '🍊', '🍇', '7️⃣', '💎'];
    private $payouts = [
        '🍒' => 2,
        '🍋' => 3,
        '🍊' => 4,
        '🍇' => 5,
        '7️⃣' => 10,
        '💎' => 20
    ];
    private $bonusSymbol = '🌟';
    private $bonusMultiplier = 2;
    public function index() {
        // Prepare data for the view
        $title = 'Slot Machine Game';
        $symbols = json_encode($this->symbols);
        $payoutTable = json_encode($this->payouts);
        
        // Display the slot machine page
        include 'views/slot_machine.php';
    }
    public function spin() {
        // Validate user input
        $betAmount = $this->validateBetAmount($_POST['bet_amount'] ?? 0);
        if ($betAmount === false) {
            return json_encode(['error' => 'Invalid bet amount']);
        }

        // Check if user has enough balance
        $user = getCurrentUser();
        if ($user['balance'] < $betAmount) {
            return json_encode(['error' => 'Insufficient balance']);
        }

        // Perform the spin
        $result = $this->performSpin();

        // Calculate winnings
        $winAmount = $this->calculateWinnings($result, $betAmount);

        // Update user balance
        $newBalance = $user['balance'] - $betAmount + $winAmount;
        updateUserBalance($user['id'], $newBalance);

        // Log the game result
        $this->logGameResult($user['id'], $betAmount, $winAmount, $result);

        // Prepare and return the result
        $response = [
            'symbols' => $result,
            'winAmount' => $winAmount,
            'newBalance' => $newBalance,
        ];

        return json_encode($response);
    }

    private function validateBetAmount($amount) {
        $amount = filter_var($amount, FILTER_VALIDATE_FLOAT);
        if ($amount === false || $amount <= 0) {
            return false;
        }
        return $amount;
    }

    private function performSpin() {
        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $result[] = $this->symbols[array_rand($this->symbols)];
        }
        // 10% chance of getting a bonus symbol
        if (mt_rand(1, 100) <= 10) {
            $result[mt_rand(0, 2)] = $this->bonusSymbol;
        }
        return $result;
    }

    private function calculateWinnings($result, $betAmount) {
        $winAmount = 0;
        if (count(array_unique($result)) === 1) {
            // All symbols are the same
            $winAmount = $betAmount * $this->payouts[$result[0]];
        }
        if (in_array($this->bonusSymbol, $result)) {
            $winAmount *= $this->bonusMultiplier;
        }
        return $winAmount;
    }

    private function logGameResult($userId, $betAmount, $winAmount, $result) {
        $logEntry = [
            'user_id' => $userId,
            'bet_amount' => $betAmount,
            'win_amount' => $winAmount,
            'result' => implode(',', $result),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        appendToJsonFile('game_logs.json', $logEntry);
    }
}