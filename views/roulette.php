<div class="container">
    <h1>Roulette</h1>
    <div id="roulette-game">
        <div id="wheel"></div>
        <div id="betting-area">
            <h2>Place Your Bets</h2>
            <div>
                <label for="bet-amount">Bet Amount:</label>
                <input type="number" id="bet-amount" min="1" max="100" value="1">
            </div>
            <div>
                <label for="bet-type">Bet Type:</label>
                <select id="bet-type">
                    <option value="number">Number</option>
                    <option value="color">Color</option>
                    <option value="odd_even">Odd/Even</option>
                </select>
            </div>
            <div id="bet-value-container">
                <label for="bet-value">Bet Value:</label>
                <input type="number" id="bet-value-number" min="0" max="36" value="0">
                <select id="bet-value-color" style="display: none;">
                    <option value="red">Red</option>
                    <option value="black">Black</option>
                </select>
                <select id="bet-value-odd-even" style="display: none;">
                    <option value="odd">Odd</option>
                    <option value="even">Even</option>
                </select>
            </div>
            <button id="place-bet">Place Bet</button>
        </div>
        <div id="bet-list"></div>
        <button id="spin-wheel">Spin Wheel</button>
        <div id="result"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const betType = document.getElementById('bet-type');
    const betValueNumber = document.getElementById('bet-value-number');
    const betValueColor = document.getElementById('bet-value-color');
    const betValueOddEven = document.getElementById('bet-value-odd-even');
    const placeBetButton = document.getElementById('place-bet');
    const spinWheelButton = document.getElementById('spin-wheel');
    const betList = document.getElementById('bet-list');
    const resultDiv = document.getElementById('result');

    let bets = [];

    betType.addEventListener('change', function() {
        betValueNumber.style.display = 'none';
        betValueColor.style.display = 'none';
        betValueOddEven.style.display = 'none';

        switch(this.value) {
            case 'number':
                betValueNumber.style.display = 'inline-block';
                break;
            case 'color':
                betValueColor.style.display = 'inline-block';
                break;
            case 'odd_even':
                betValueOddEven.style.display = 'inline-block';
                break;
        }
    });

    placeBetButton.addEventListener('click', function() {
        const betAmount = parseInt(document.getElementById('bet-amount').value);
        const betType = document.getElementById('bet-type').value;
        let betValue;

        switch(betType) {
            case 'number':
                betValue = betValueNumber.value;
                break;
            case 'color':
                betValue = betValueColor.value;
                break;
            case 'odd_even':
                betValue = betValueOddEven.value;
                break;
        }

        bets.push({ type: betType, value: betValue, amount: betAmount });
        updateBetList();
    });

    spinWheelButton.addEventListener('click', function() {
        if (bets.length === 0) {
            alert('Please place at least one bet before spinning the wheel.');
            return;
        }

        fetch('/roulette/spin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ bets: bets })
        })
        .then(response => response.json())
        .then(data => {
            resultDiv.textContent = `Result: ${data.result.number} ${data.result.color}. You won ${data.winnings} credits!`;
            bets = [];
            updateBetList();
        })
        .catch((error) => {
            console.error('Error:', error);
            resultDiv.textContent = 'An error occurred. Please try again.';
        });
    });

    function updateBetList() {
        betList.innerHTML = '<h3>Current Bets:</h3>';
        bets.forEach((bet, index) => {
            betList.innerHTML += `<p>Bet ${index + 1}: ${bet.amount} credits on ${bet.type} ${bet.value}</p>`;
        });
    }
});
</script>

<style>
#roulette-game {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

#betting-area {
    margin-bottom: 20px;
}

#betting-area > div {
    margin-bottom: 10px;
}

#bet-list {
    margin-bottom: 20px;
}

#result {
    font-size: 24px;
    font-weight: bold;
    margin-top: 20px;
}
</style>