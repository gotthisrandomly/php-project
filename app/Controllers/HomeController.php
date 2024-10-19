<?php

namespace App\Controllers;

class HomeController
{
    private $symbols = ['🍒', '🍋', '🍊', '🍇', '🔔', '💎'];
    private $payoutTable = [
        '🍒🍒🍒' => 10,
        '🍋🍋🍋' => 20,
        '🍊🍊🍊' => 30,
        '🍇🍇🍇' => 40,
        '🔔🔔🔔' => 50,
        '💎💎💎' => 100,
    ];

    public function index()
    {
        $title = "Welcome to the Slot Machine Game!";
        $content = "Welcome to our exciting slot machine game. Are you ready to try your luck?";
        require_once __DIR__ . '/../../views/home.php';
    }

    public function play()
    {
        $title = "Slot Machine Game";
        $content = "Spin the slot machine!";
        $symbols = json_encode($this->symbols);
        $payoutTable = json_encode($this->payoutTable);
        require_once __DIR__ . '/../../views/slot_machine.php';
    }

    public function spin()
    {
        $bet = $_POST['bet'] ?? 1;
        $bet = max(1, min(100, intval($bet))); // Ensure bet is between 1 and 100

        $result = $this->spinReels();
        $winAmount = $this->calculateWin($result, $bet);

        $response = [
            'reels' => $result,
            'win' => $winAmount,
            'message' => $this->getResultMessage($winAmount, $bet),
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function spinReels()
    {
        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $result[] = $this->symbols[array_rand($this->symbols)];
        }
        return $result;
    }

    private function calculateWin($result, $bet)
    {
        $combination = implode('', $result);
        $multiplier = $this->payoutTable[$combination] ?? 0;
        return $multiplier * $bet;
    }

    private function getResultMessage($winAmount, $bet)
    {
        if ($winAmount > 0) {
            return "Congratulations! You won $" . number_format($winAmount, 2) . "!";
        } else {
            return "Sorry, you lost $" . number_format($bet, 2) . ". Try again!";
        }
    }
}