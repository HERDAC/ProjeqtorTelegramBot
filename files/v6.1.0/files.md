1. Place the following files in the correct directory:

File name                   | Where it goes
--------------------------- | -----------------
telegram_bot_V6_1_0.php     | projeqtor/tool/
TelegramDisplayTemplate.php | projeqtor/model/


2. In "`flows.json`", replace PROJEQTOR-URL with the url to your ProjeQtOr (for example https://some.domain.com/projeqtor)
3. Import "`flows.json`" in node-red and replace the previous flow if it existed.
4. Click on the "Bot Script" node and configure authentification with a username and password
5. Copy these credentials in the bot script in place of AUTH_USER and AUTH_PWD
6. Add the translations to projeqtor (beware of doubles):
   - `i18n_en.js` -> english
   - `i18n_fr.js` -> french
   
   You can either add them through the interface or directly to the corresponding file in `projeqtor/plugin/nls/{lang}/lang.js`.

7. Modify the file projeqtor/view/parameter.php
   - After
      ```php
      htmlDrawCrossTable(array('orgaVisibilityList'=>i18n('organizationVisibilityList'),'orgaVisibilityScreen'=>i18n('organizationVisibilityScreen')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listOrgaSubOrga') ;
      echo '</div><br/>';
      ```

     Add the following lines:
      ```php
      $titlePane="habilitationOther_TelegramRights";
      echo '<div dojoType="dijit.TitlePane"';
      echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
      echo ' id="' . $titlePane . '" ';
      echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
      echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
      echo ' title="' . i18n('telegramRights') . '">';
      htmlDrawCrossTable(array(
          'tgBotCreate'=>i18n('telegramBotRightCreate'),
          'tgBotSearch'=>i18n('telegramBotRightSearch')),
          'scope', 'profile','idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
      echo '</div><br/>';
      ```
8. Modify the file projeqtor/tool/saveParameter.php
   - After
      ```php
      'orgaVisibilityList'=>i18n('organizationVisibilityList'),
          'orgaVisibilityScreen'=>i18n('organizationVisibilityScreen'),
      ```

     Add the following lines:
      ```php
      'tgBotCreate'=>i18n('telegramBotRightCreate'),
          'tgBotSearch'=>i18n('telegramBotRightSearch')
      ```

**Requirements: V6.0.1**
