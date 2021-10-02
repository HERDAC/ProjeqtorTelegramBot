### **WARNING: This version and future ones use/will use the personalized translations plugin.**

1. Place the following files in the correct directory:

File name                                 | Where it goes
----------------------------------------- | -----------------
TelegramBotUser.php                       | projeqtor/model/
TelegramDisplayTemplate.php               | projeqtor/model/
TelegramSummaryTemplate.php               | projeqtor/model/
iconTelegramDisplayTemplate.svg           | projeqtor/view/css/customIcons/new/
iconTelegramSummaryTemplate.svg           | projeqtor/view/css/customIcons/new/
telegram_bot_V6_0_0.php                   | projeqtor/tool/
telegramDisplayTemplateVisibleButtons.php | projeqtor/tool/


2. Execute modifications_V6_0_0.sql
3. In "`flows.json`", replace PROJEQTOR-URL with the url to your ProjeQtOr (for example https://some.domain.com/projeqtor)
4. Import "`flows.json`" in node-red and replace the previous flow if it existed.
5. In projeqtor/view/css/projeqtorIcons.css, add the following lines:
    ```SCSS
    .ProjeQtOrNewGui .iconTelegramDisplayTemplate { background-image: url(customIcons/new/iconTelegramDisplayTemplate.svg);  background-repeat: no-repeat; }
    .ProjeQtOrNewGui .iconTelegramSummaryTemplate { background-image: url(customIcons/new/iconTelegramSummaryTemplate.svg);  background-repeat: no-repeat; }
    ```
6. Modify the file projeqtor/model/Parameter.php as follows:
   - Near
      ```php
      case ('globalParameter'):
        $parameterList=array(
      ```

     Add at the end of the array the following values:
      ```
      'tabTelegramBot'=>"tab",
        'newColumnTelegramBot'=>'newColumnFull',
        'sectionTelegramBotGeneral'=>'section',
          'telegramBotToken'=>'longtext',
          'telegramBotProjeqtorUser'=>'list',
          'telegramBotProjeqtorUrl'=>'text',
          'telegramBotAdminChatId'=>'longnumber',
          'telegramBotEnableStateCmd'=>'list',
        'sectionTelegramBotCommands'=>'section',
          'telegramBotCmdAbout'=>'text',
          'telegramBotCmdCreate'=>'text',
          'telegramBotCmdReport'=>'text',
          'telegramBotCmdSearch'=>'text',
          'telegramBotCmdState'=>'text',
          'telegramBotCmdStop'=>'text'
      ```
   - After
      ```php
      case 'newGui':
        $list = array(true=>i18n('newGuiTrue'),
                      false=>i18n('newGuiFalse'));
        break;
      ```

     Add the following lines:
      ```php
      case 'telegramBotEnableStateCmd':
        $list = array(false=>i18n('displayNo'),
                      true=>i18n('displayYes'));
        break;
      case 'telegramBotProjeqtorUser':
        if (sessionUserExists()) {
          $user=getSessionUser();
          $listVisible= getUserVisibleResourcesList(true, "List",'',false, false,false,true,true);
        } else {
          $listVisible=SqlList::getList('User');
        }
        foreach ($listVisible as $key=>$val) {
          $list[$key]=$val;
        }
        // At least, one admin in the list
        if (empty($list)) {
          $crit = array("idProfile" => "1");
          $user = SqlElement::getFirstSqlElementFromCriteria("User", $crit);
          if (isset($user->id)) {
            $list[$user->id] = $user->name;
          }
        }
        break;
      ```
7. Add the translations to projeqtor:
   - `i18n_en.js` -> english
   - `i18n_fr.js` -> french
   
   You can either add them through the interface or directly to the corresponding file in `projetqor/plugin/nls/{lang}/lang.js`.

8. You can now configure the bot through projeqtor, in the global parameters menu, under the Telegram Bot section.

**Requirements: V1.1**
