<div class="container">
    <h1>Slot Machine</h1>
    <div id="slot-machine">
        <div id="reels">
            <div class="reel" id="reel1"></div>
            <div class="reel" id="reel2"></div>
            <div class="reel" id="reel3"></div>
        </div>
        <div id="controls">
            <label for="bet">Bet Amount:</label>
            <input type="number" id="bet" name="bet" min="1" max="100" value="1">
            <button id="spin-button">Spin</button>
        </div>
        <div id="result"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const spinButton = document.getElementById('spin-button');
    const betInput = document.getElementById('bet');
    const resultDiv = document.getElementById('result');
    const reels = [
        document.getElementById('reel1'),
        document.getElementById('reel2'),
        document.getElementById('reel3')
    ];

    spinButton.addEventListener('click', function() {
        const bet = parseInt(betInput.value);
        if (isNaN(bet) || bet < 1 || bet > 100) {
            alert('Please enter a valid bet amount between 1 and 100.');
            return;
        }

        fetch('/slot-machine/spin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ bet: bet })
        })
        .then(response => response.json())
        .then(data => {
            // Update reels
            data.symbols.forEach((symbol, index) => {
                reels[index].textContent = symbol;
            });

            // Display result
            let resultText = `You ${data.payout > 0 ? 'won' : 'lost'} ${Math.abs(data.payout)} credits.`;
            if (data.bonus) {
                resultText += ' Bonus multiplier applied!';
            }
            resultDiv.textContent = resultText;
        })
        .catch((error) => {
            console.error('Error:', error);
            resultDiv.textContent = 'An error occurred. Please try again.';
        });
    });
});
</script>

<style>
#slot-machine {
    text-align: center;
    margin-top: 20px;
}

#reels {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.reel {
    font-size: 48px;
    margin: 0 10px;
    padding: 10px;
    border: 2px solid #000;
    border-radius: 5px;
    min-width: 60px;
}

#controls {
    margin-bottom: 20px;
}

#result {
    font-size: 24px;
    font-weight: bold;
}
</style>