# SD777Slots

SD777Slots is a PHP-based slot machine game project with additional casino games.

## Project Structure

```
project/
├── admin/
├── app/
│   └── Controllers/
│       └── HomeController.php
├── assets/
│   ├── css/
│   └── js/
│       └── slot-machine.js
├── controllers/
├── includes/
├── tests/
├── views/
│   ├── home.php
│   └── slot_machine.php
├── index.php
└── README.md
```

## Setup

1. Clone the repository
2. Set up a PHP server (e.g., Apache, Nginx) and point it to the project directory
3. Configure your database connection in `includes/db_config.php`
4. Run any necessary database migrations or import SQL files

## Usage

1. Navigate to the project URL in your web browser
2. The home page (`/`) will display a welcome message and links to available games
3. Click on "Play Slots" to access the slot machine game
4. Set your bet amount and click "Spin" to play the slot machine

## Slot Machine Game

The slot machine game features:

- Three reels with various symbols
- Customizable bet amounts
- Payout table displaying winning combinations
- Realistic spinning animation
- Server-side result calculation for fairness

### How to Play

1. Set your bet amount using the input field
2. Click the "Spin" button to start the game
3. Wait for the reels to stop spinning
4. Check the result message to see if you've won

## HomeController

The `HomeController` manages the main pages of the application:

- `index()`: Displays the home page with links to available games
- `play()`: Shows the slot machine game page
- `spin()`: Handles the slot machine game logic and returns results

To add new pages or functionality, extend the `HomeController` or create new controller classes in the `app/Controllers/` directory.

## Contributing

1. Fork the repository
2. Create a new branch for your feature
3. Commit your changes
4. Push to your branch
5. Create a pull request

## License

[MIT License](LICENSE)