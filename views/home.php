<?php include 'header.php'; ?>

<div class="container mt-5">
    <h1>Welcome to our Online Casino</h1>
    <p>Enjoy our selection of games and have a great time!</p>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <h2>Blackjack</h2>
            <p>Try your luck at our Blackjack table!</p>
            <a href="/blackjack" class="btn btn-primary">Play Blackjack</a>
        </div>
        <div class="col-md-4">
            <h2>Roulette</h2>
            <p>Spin the wheel and win big!</p>
            <a href="/roulette" class="btn btn-primary">Play Roulette</a>
        </div>
        <div class="col-md-4">
            <h2>Slot Machine</h2>
            <p>Pull the lever and see if you hit the jackpot!</p>
            <a href="/slot-machine" class="btn btn-primary">Play Slots</a>
        </div>
    </div>

    <div class="mt-5">
        <h3>Admin Access</h3>
        <a href="/admin" class="btn btn-secondary">Admin Login</a>
    </div>
</div>

<?php include 'footer.php'; ?>