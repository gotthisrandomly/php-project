<?php
class SlotMachineController {
    private $symbols = ['🍒', '🍋', '🍊', '🍇', '🔔', '💎'];
    private $payouts = [
        '🍒🍒🍒' => 10,
        '🍋🍋🍋' => 20,
        '🍊🍊🍊' => 30,
        '🍇🍇🍇' => 40,
        '🔔🔔🔔' => 50,
        '💎💎💎' => 100
    ];
    private $bonusSymbol = '⭐';

    public function spin($bet) {
        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $result[] = $this->symbols[array_rand($this->symbols)];
        }

        $payout = $this->calculatePayout($result, $bet);
        $bonusMultiplier = $this->checkBonus($result);

        return [
            'symbols' => $result,
            'payout' => $payout * $bonusMultiplier,
            'bonus' => $bonusMultiplier > 1
        ];
    }

    private function calculatePayout($result, $bet) {
        $resultString = implode('', $result);
        foreach ($this->payouts as $combination => $multiplier) {
            if ($resultString === $combination) {
                return $bet * $multiplier;
            }
        }
        return 0;
    }

    private function checkBonus($result) {
        $bonusCount = array_count_values($result)[$this->bonusSymbol] ?? 0;
        switch ($bonusCount) {
            case 1:
                return 2;
            case 2:
                return 3;
            case 3:
                return 5;
            default:
                return 1;
        }
    }
}
?>