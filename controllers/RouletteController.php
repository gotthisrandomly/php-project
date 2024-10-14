<?php
class RouletteController {
    private $numbers = range(0, 36);
    private $colors = ['red', 'black'];

    public function index() {
        session_start();
        if (!is_logged_in()) {
            header('Location: /login');
            exit;
        }

        $title = 'Roulette - SD777Slots';
        
        ob_start();
        include __DIR__ . '/../views/roulette.php';
        $content = ob_get_clean();

        include __DIR__ . '/../views/layout.php';
    }

    public function spin($bets) {
        $result = [
            'number' => $this->numbers[array_rand($this->numbers)],
            'color' => $this->colors[array_rand($this->colors)]
        ];

        $winnings = $this->calculateWinnings($bets, $result);

        return [
            'result' => $result,
            'winnings' => $winnings
        ];
    }

    private function calculateWinnings($bets, $result) {
        $winnings = 0;
        foreach ($bets as $bet) {
            switch ($bet['type']) {
                case 'number':
                    if ($bet['value'] == $result['number']) {
                        $winnings += $bet['amount'] * 35;
                    }
                    break;
                case 'color':
                    if ($bet['value'] == $result['color']) {
                        $winnings += $bet['amount'] * 2;
                    }
                    break;
                case 'odd_even':
                    if (($bet['value'] == 'odd' && $result['number'] % 2 == 1) ||
                        ($bet['value'] == 'even' && $result['number'] % 2 == 0)) {
                        $winnings += $bet['amount'] * 2;
                    }
                    break;
            }
        }
        return $winnings;
    }
}