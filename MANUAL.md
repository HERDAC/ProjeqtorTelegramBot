# How to use the bot

Thanks to this bot, you can interact with ProjeQtOr from your phone without having to open your web browser.
In this file, you will find explanations on the different commands you can execute (depending on the version).
Screenshots will be added later.
(commands are sorted by order of implementation)

Of course you can change the commands name to whatever you want (from V1.2 and over, see CHANGELOG.md)

---

## If you're new to Telegram:

### How to talk to the bot
After you've created the bot, just search its name in the search bar, for example "@YourBot".
When you start a new conversation with the bot, you will normally be prompted with a message asking you to run "/start".
Just click on it to initialize your discussion.

### How to send a command
A command is just a simple message starting with a "/".

---

## `/ticket` (V1.0 to V3.0) or `/creer` (V3.0+)
This command let you create tickets, questions (V3.0+), and other elements (VX.X).

## `/stop` (V1.0+)
This command stops any other ongoing command.

## `/state` (V1.0+)
This command displays current data for the user. (For debugging purposes)

## `/report` (V1.2+)
This command sends user data to an admin for debugging.

## `/afficher` (V2.0+)
This command let you display information about a ticket or an activity (V2.0+), a question (V4.0+), or any other elements (VX.X).

## `/about` (V2.0+)
This command displays information about the bot.

## `/reference` (V4.0+)
This command let you display information about an elements from there reference.

## Special inline command: @YourBot (V4.0+)
When you type "@YourBot " (replace YourBot with the name of your bot), you will see a list of references appear above the text input box.
As you start typing a reference after "@YourBot", it will filter the list to matching entries.
If you select one, it will run the command "/reference" with the corresponding reference.
