# ProjeQtOr Telegram Bot
This project is about a telegram bot I created to interact with [ProjeQtOr](https://www.projeqtor.org/en/), a quality based open-source project organizer.
The files available in this repository are all written by myself, with the exception of some snippets copied and modified from ProjeQtOr's source code.
This project is developed for HERDAC society but it is open-source (AGPL V3), as defined by [ProjeQtOr's license](https://www.projeqtor.org/en/copyright). If you have any questions or issues, don't hesitate to contact me.
Feel free to send your own ideas, though you should probably post them on [ProjeQtOr's forum](https://www.projeqtor.org/en/forum)

## How it works

To make this bot work, I first had to create a new user on projeqtor with admin rights. I obvisously created a [telegram bot](https://core.telegram.org/bots) and also a node-red flow.

### The projeqtor user

Nothing special, just admin rights.

Replace "`PROJEQTOR-USER`" in the files with its name. Replace "`PROJEQTOR-PWD`" in the files with the password for this user. Replace "`PROJEQTOR-API`" in the files with the api key of this user. (V1.2-)

### The bot

Replace "`BOT-TOKEN`" in the files with the token. A separated parameter will be used in the future for easier access.
From V4.0+, set the bot to [inline mode](https://core.telegram.org/bots/inline).

### Node-red

The node-red flow is essential. It gets the message updates from the bot and calls the script. The files "`flows.json`" contain the importable flow for each version. It uses the module [node-red-contrib-telegrambot](https://flows.nodered.org/node/node-red-contrib-telegrambot). Befor importing, replace all "`PROJEQTOR-URL`" with the url to your projeqtor. When imported, for all the blue nodes (telegram nodes), set the `bot` field to your configured bot.

## Other

Until V5.0, the bot is split into two files: `telegram_bot.php` and `ticket_create.php`. After V5.0, only `telegram_bot.php` is used.
Until V5.0, the file `telegram_bot.php` uses the API to retrieve information so you will need the API key for the new user.
Replace "`PROJEQTOR-URL`" in the files with the url to your projeqtor. Replace "`ADMIN-CHATID`" in the files with the chat id of the admin responsible for the bot.
