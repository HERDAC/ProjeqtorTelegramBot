# How to install this bot to ProjeQtOr

This file will cover every steps required to install any version of this bot.
It is a cumulative install: each version contains only the new or changed files, so some other required files are located in previous versions, but don't worry, everything is explained below.

### This repository only contains the necessary files for the telegram bot. You need [ProjeQtOr](https://www.projeqtor.org/en/) for it to work.

## 1. The Bot

This is the first step to make the whole thing work.
1. Make sure you have [Telegram](https://telegram.org/)
2. Create a bot with [Bot Father](https://t.me/botfather)
  
### V4.0+
  
3. With [Bot Father](https://t.me/botfather), set your bot to inline mode (`/setinline`).

## 2. Node-red

What is it ?
> Node-RED is a programming tool for wiring together hardware devices, APIs and online services in new and interesting ways.
>
> It provides a browser-based editor that makes it easy to wire together flows using the wide range of nodes in the palette that can be deployed to its runtime in a single-click.
> https://nodered.org/

In this case, it is used to retrieve message updates from the bot and call the bot script.

1. [Install node-red](https://nodered.org/docs/getting-started/)
2. Install the [node-red-contrib-telegrambot](https://flows.nodered.org/node/node-red-contrib-telegrambot) package
3. Choose the "flows.json" file for the version you wish to install.
4. Replace all "`PROJEQTOR-URL`" with the url to your projeqtor.
5. Open node-red in a web browser. In the top-right corner menu, select import and choose the "flows.json" file.
6. For all blue nodes, change the "Bot" field to your configured telegram bot.
The first time, you'll need to add the bot by clicking on the pencil icon and filling the fields (just name and token are sufficient for it to work).
7. Save and deploy

## 3. ProjeQtOr modifications and additions

### 3.1 Files
Some files have been added (for example `TicketTemplate.php`) and other modified (for example `UserMain.php`). In the version folder, read the "`files.md`" (if any) and follow the instructions. For most versions, there's only the script file and the node-red flow (often only the url to the script is changed in the Http Request node).

There may also be a "`modifications.sql`" file which you will need to execute in your projeqtor database.

### 3.2 User
In order for the script to access ProjeQtOr's classes and functions, it needs to "log in" as a user. You need to create a new user in ProjeQtOr, give it admin rights and copy its name, as you will need to replace it in some files (`PROJEQTOR-USER`)

## 4. Translations

Translations for new menus and fields added to ProjeQtOr are not included in these files, but their name are normally quite descriptive.
If you encounter any non translated name like "\[TicketTemplate\]", just add a translation for your language in the menu.

As of V4.1, no translation is provided for the bot's messages, buttons and feedbacks. Everything is in French. This is a planned evolution but I haven't come round to it yet.
Additionnally, every message is currently hard-coded in the script file.
In a future version (normally soon, maybe V6.0), these messages will be moved to the database and it will use the translation system for easier internationalization.
So until then, you will have to translate everything in the file.
