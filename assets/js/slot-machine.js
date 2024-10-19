document.addEventListener('DOMContentLoaded', () => {
    const reels = document.querySelectorAll('.reel');
    const spinButton = document.getElementById('spin-button');
    const betInput = document.getElementById('bet-amount');
    const balanceElement = document.getElementById('balance');
    const resultElement = document.getElementById('result');

    let spinning = false;

    spinButton.addEventListener('click', () => {
        if (spinning) return;
        spinning = true;

        const bet = parseInt(betInput.value);
        const balance = parseFloat(balanceElement.textContent.replace('$', '').replace(',', ''));

        if (bet > balance) {
            alert('Insufficient balance!');
            spinning = false;
            return;
        }

        balanceElement.textContent = '$' + (balance - bet).toFixed(2);

        // Simulate spinning animation
        reels.forEach(reel => {
            reel.textContent = '🎰';
            reel.style.animation = 'none';
            reel.offsetHeight; // Trigger reflow
            reel.style.animation = 'spin 0.5s linear infinite';
        });

        // Send spin request to server
        fetch('/slot-machine/spin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `bet=${bet}`
        })
        .then(response => response.json())
        .then(data => {
            // Stop spinning animation
            reels.forEach((reel, index) => {
                setTimeout(() => {
                    reel.style.animation = 'none';
                    reel.textContent = data.reels[index];
                }, (index + 1) * 500);
            });

            // Update balance and show result
            setTimeout(() => {
                const newBalance = balance - bet + data.win;
                balanceElement.textContent = '$' + newBalance.toFixed(2);
                resultElement.textContent = data.message;
                spinning = false;
            }, 2000);
        })
        .catch(error => {
            console.error('Error:', error);
            spinning = false;
        });
    });
});