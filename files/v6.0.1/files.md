1. Place the following files in the correct directory:

File name               | Where it goes
----------------------- | -----------------
telegram_bot_V6_0_1.php | projeqtor/tool/


2. In "`flows.json`", replace PROJEQTOR-URL with the url to your ProjeQtOr (for example https://some.domain.com/projeqtor)
3. Import "`flows.json`" in node-red and replace the previous flow if it existed.
4. Add the translations to projeqtor (beware of doubles):
   - `i18n_en.js` -> english
   - `i18n_fr.js` -> french
   You can either add them through the interface or directly to the corresponding file in `projetqor/plugin/nls/{lang}/lang.js`.

**Requirements: V6.0.0**
