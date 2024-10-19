# SD777Slots

SD777Slots is a PHP-based slot machine game project with additional casino games and an admin panel for managing users and game settings.

## Project Structure

```
project/
├── admin/
├── app/
│   └── Controllers/
├── assets/
│   ├── css/
│   └── js/
│       └── slot-machine.js
├── controllers/
│   ├── AdminController.php
│   ├── HomeController.php
│   ├── SlotMachineController.php
│   └── ...
├── includes/
│   ├── Database.php
│   ├── ErrorHandler.php
│   ├── Logger.php
│   ├── Validator.php
│   ├── autoloader.php
│   ├── db_config.php
│   ├── db_connection.php
│   ├── functions.php
│   └── rate_limiter.php
├── tests/
├── views/
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── manage_players.php
│   │   └── manage_settings.php
│   ├── home.php
│   ├── slot_machine.php
│   └── ...
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

## Admin Panel

The admin panel allows authorized users to manage player accounts and game settings.

To access the admin panel:

1. Navigate to `/admin/login`
2. Log in with admin credentials
3. Use the dashboard to manage players, view pending cashouts, and adjust game settings

### Admin Features

- View and manage player accounts
- Approve or block pending cashouts
- Adjust game settings (e.g., payout rates, minimum bets)

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

## Controllers

- `AdminController`: Manages the admin panel and related actions
- `HomeController`: Manages the main pages of the application
- `SlotMachineController`: Handles the slot machine game logic

## Contributing

1. Fork the repository
2. Create a new branch for your feature
3. Commit your changes
4. Push to your branch
5. Create a pull request

## License

[MIT License](LICENSE)