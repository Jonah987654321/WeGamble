
# WeGamble

[![CC BY-NC 4.0][cc-by-nc-shield]][cc-by-nc]  ![Non-Commercial](https://img.shields.io/badge/license-non--commercial-red)

Online Gambling Website written in PHP - made only for private & fun purposes (localization is German!). The website is still WIP so there may be UI Elements with no implemented logic.  


## DISCLAIMER

This project was created **solely for educational and entertainment purposes**.  
It is **not intended** to be used for real-money gambling or any commercial activities.

* **No Real-Money Use:** Using this code for real-money gambling, betting, or any commercial gambling platforms is strictly **prohibited**.
* **Not a Gambling Service:** This project does **not** promote or encourage gambling. It is a simulation intended for learning and experimentation only.
* **No Commercial Use:** You may not sell, monetize, or otherwise use this software in any product or service that generates revenue.
* **Disclaimer of Liability:** The author assumes **no responsibility or liability** for any damages, losses, or legal consequences arising from the use of this software.
* **Gambling Addiction Warning:** Gambling can be addictive. If you or someone you know struggles with gambling, please seek professional help.

Refer to the [LICENSE](./LICENSE) file for details about usage permissions and restrictions.  
## Features

- Implemented games:
    - Roulette
    - Blackjack
    - Slots (not fully finished)
    - Hit the nick (Random 1:9 chance) 
- Leaderboard
- Playerstats like total playtime, winstreak, loosestreak, total wins, etc.
- Modular websocket server design for game backend
- Robust websocket client written implementing disconnects, reconnects and event handling
- Websocket using WSS or WS protocol (SSL support)
- Partial responsive optimization


## Tech Stack
- The project is mainly written in PHP
- Using my own [OmniRoute](https://github.com/Jonah987654321/OmniRoute) framework for URL Routing & utility purposes
- For icons, [fontawesome](fontawesome.com/) is used
- The rest of the frontend is written in HTML with plain CSS & JS
- The websocket server is written in PHP using [Ratchet](https://github.com/ratchetphp/Ratchet)
- Logging is realized with [Monolog](https://github.com/Seldaek/monolog)

## Run Locally

Clone the project

```bash
  git clone https://github.com/Jonah987654321/WeGamble.git
```

Go to the project directory

```bash
  cd WeGamble
```

Install dependencies

```bash
  composer install
```

Rename the example env to be your actual environment file & fill in your values

```bash
  mv .env.example .env
```

Start your local PHP development server
```bash
php -S localhost:8080 -t src
```

Start the websocket server
```bash
php src/webSocket/run.php
```
## Roadmap

- Daily login streak with rewards
- Overall minor UX/UI improvements
- Improve mobile optimization
- Social options like money transfer/profile images/messaging
- Implement more games like Crasher or multiplayer Poker
- Admin Dashboard

## Acknowledgements

 - [Playing card image files by Byron Knoll](https://code.google.com/archive/p/vector-playing-cards/)
 - [Gambling icon created by Freepik - Flaticon](https://www.flaticon.com/free-icons/gambling)
 - [Blackjack icon created by smashingstocks - Flaticon](https://www.flaticon.com/free-icons/blackjack)
 - [Casino-roulette icon created by Freepik - Flaticon](https://www.flaticon.com/free-icons/casino-roulette)
 - [Hit icon created by Freepik - Flaticon](https://www.flaticon.com/free-icons/hit)
 - [Slot machine icon created by iconixar - Flaticon](https://www.flaticon.com/free-icons/slot-machine)
 - [User icon created by Smashicons - Flaticon](https://www.flaticon.com/free-icons/user)


## Authors

- [@Jonah987654321](https://www.github.com/Jonah987654321)


## License
[![CC BY-NC 4.0][cc-by-nc-shield]][cc-by-nc]

This work is licensed under a
[Creative Commons Attribution-NonCommercial 4.0 International License][cc-by-nc].

[![CC BY-NC 4.0][cc-by-nc-image]][cc-by-nc]

[cc-by-nc]: https://creativecommons.org/licenses/by-nc/4.0/
[cc-by-nc-image]: https://licensebuttons.net/l/by-nc/4.0/88x31.png
[cc-by-nc-shield]: https://img.shields.io/badge/License-CC%20BY--NC%204.0-lightgrey.svg

