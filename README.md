# ProjeQtOr Telegram Bot
This project is about a telegram bot I created to interact with [ProjeQtOr](https://www.projeqtor.org/en/), a quality based open-source project organizer.
The files available in this repository are all written by myself, with the exception of some snippets copied and modified from ProjeQtOr's source code.
This is a personal project but it is open-source (AGPL V3). If you have any questions or issues, don't hesitate to contact me.
The ideas for this bot are from my brother and myself, but feel free to send your own, though you should probably post them on [ProjeQtOr's forum](https://www.projeqtor.org/en/forum)

## How it works

To make this bot work, I first had to create a new user on projeqtor with admin rights. I obvisously created a [telegram bot](https://core.telegram.org/bots) and a node-red flow.

### The projeqtor user

Nothing special, just admin rights

### The bot

Copy the token in the files at the appropriate places (`$TOKEN`). A separated parameter will be used in the future for easier access.
From V4.0+, set the bot to [inline mode](https://core.telegram.org/bots/inline).

### Node-red

The node-red flow is essential. It gets the message updates from the bot and calls the script. The files "`flows.json`" contain the importable flow for each version. It uses the module [node-red-contrib-telegrambot](https://flows.nodered.org/node/node-red-contrib-telegrambot)

## Other

Until V5.0, the bot is split into two files: `telegram_bot.php` and `ticket_create.php`. After V5.0, only `telegram_bot.php` is used.
Until V5.0, the file `telegram_bot.php` uses the API to retrieve information so you will need the API key for the new user.
