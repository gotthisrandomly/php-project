document.addEventListener('DOMContentLoaded', () => {
    const spinButton = document.getElementById('spin-button');
    const betAmount = document.getElementById('bet-amount');
    const reels = document.querySelectorAll('.reel');
    const balanceElement = document.getElementById('balance');
    const resultElement = document.getElementById('result');

    spinButton.addEventListener('click', () => {
        const bet = parseFloat(betAmount.value);
        if (isNaN(bet) || bet <= 0) {
            alert('Please enter a valid bet amount');
            return;
        }

        spinButton.disabled = true;
        resultElement.textContent = 'Spinning...';

        fetch('/spin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ bet_amount: bet }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Update reels
            data.symbols.forEach((symbol, index) => {
                reels[index].textContent = symbol;
            });

            // Update balance
            balanceElement.textContent = data.newBalance.toFixed(2);

            // Display result
            if (data.winAmount > 0) {
                resultElement.textContent = `You won $${data.winAmount.toFixed(2)}!`;
            } else {
                resultElement.textContent = 'Better luck next time!';
            }
        })
        .catch(error => {
            resultElement.textContent = `Error: ${error.message}`;
        })
        .finally(() => {
            spinButton.disabled = false;
        });
    });
});