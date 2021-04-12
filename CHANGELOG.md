
# Changelog
All notable changes to this project will be documented in this file.

(At the time this repo was created, the bot was already in V3.0, so there may be a lack of information or inaccuracy in what features were developped for previous versions)

## V1.0 - 2021-03-21
 
### Added
 * Created the bot
 * Created ticket templates (available fields: type, project, urgency, context, activity)
 * You can create tickets with the bot:
   * `/ticket` -> command to start ticket creation
   * Asks for the name of the ticket
   * Display menu with buttons for each editable fields:
     * template, description, responsible, criticality, priority, estimated work
   * When finished, click on "Créer" to show a summary of the ticket. You can either modify it again or confirm its creation
 * `/stop` -> command to stop any ongoing command
 * `/state` -> displays current data for the user (i.e. current command, set fields, etc.)

## V1.1 - 2021-03-22
 
### Added
 * New fields in ticket templates: responsible (doesn't work until V4.0+), criticality
 * New editable fields with the bot: type, project, urgency, context, activity
 * Added icon for ticket template menu

### Changed
 * `idtelegram` and `chatIdTelegram` fields have been moved from Resource to User

### Fixed
 * Only models with accessible projects for the user are selectable

## V1.2 - 2021-03-23
 
### Added
 * `/report` -> sends user data to an admin for debugging
 
### Changed
 * Code cleanup
 * Projects are sorted by wbs
 * Commands name can be modified more easily (`$commands` array)

## V2.0 - 2021-03-28
 
### Added
 * You can display information about a ticket or an activity
   * `/afficher` -> starts the process
   * Displays a menu to select a class (available: Ticket, Activity)
   * Then displays a menu to select a project (project -> sub-project -> etc.) -> then "Choisir"
   * Displays a list of available elements for the selected class and project
   * Information about element (displayed if set):
     * Ticket: name, description, type, project, urgency, criticality, context, activity, responsible, work (`planned - real = left`), status
     * Activity: name, description, type, project, responsible, work (`planned - real = left`), component, product, status
   * Some buttons may appear below depending on the class and current status:
     * Assign
     * Start / stop work (doesn't work properly until V4.2+, work time is attributed to the wrong user)
 * `/about` -> displays information about the bot

## V2.1 - 2021-03-31

### Changed
 * When selecting an element to display, the choices are display as follow
   > name state-emoji priority-emoji

## V2.2 - 2021-04-05
 
### Changed
 * When selecting a project to display elements, you can choose only the selected project or include its sub-projects recursively

## V3.0 - 2021-04-07
 
### Added
 * You can create questions:
   * `/creer`
   * Asks for the name
   * Asks for a project (remembers last choice but can be modified)
   * Asks for a description
   * Asks for a responsible
   * "Dismiss" or "Validate"
   * When validated, question is saved and an "Assign" button appears
 * When a question is assigned via the bot, it is sent to the responsible. There is an "Answer" button
 * When answer is clicked, an answer is asked, then "Save" or "Send"
   * Save -> adds the answer to the current save answer
   * Send -> sends answer to the creator

### Changed
 * ~`/ticket`~ -> `/creer`

### Fixed
 * Only accessible projects to the user are available when display an element

## V4.0 - 2021-04-10
 
### Added
 * You can display questions with `/afficher`:
   * The "Assign" and "Answer" buttons may be available if the question has the right status
 * You can display elements from there reference
   > **This is intended for unique references, from V4.2+ there will be an option to choose from multiple elements with the same reference**
   * `/reference` -> asks for a reference and display information about the element
   * OR type `@BOT_NAME` and start writing a reference to be prompted with available references which you can select. When you click on one, it'll send the command `/reference selected-reference` which does the same as above

### Fixed
 * Responsible is now correctly set when a ticket template is used

## V4.1 - 2021-04-11
 
### Added
 * You can now attach files when creating a ticket
   * New button in the field selection menu
     * When you press "Ajouter", just send files to attach them. If you add a caption to the message containing the file, it will use this caption as its name. If you add a caption to a message containing multiple files, it will use this caption as the name for all the files. It may take a few seconds to add the files (rate limited to 1 file per second to avoid invalid data due to poor code structure, will be improved later)
     * If there are attachments, you can delete them by clicking on "Supprimer" and then selecting the ones you want to remove
   * Attachments are displayed on the ticket summary at the end of the creation process
