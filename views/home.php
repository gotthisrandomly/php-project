<div class="container mt-5">
    <h1>Welcome to SD777Slots</h1>
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

    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="mt-5">
        <h2>Join Now</h2>
        <p>Create an account to start playing and winning!</p>
        <a href="/signup" class="btn btn-success">Sign Up</a>
        <a href="/login" class="btn btn-secondary">Login</a>
    </div>
    <?php endif; ?>
</div>