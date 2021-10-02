1. Place the following files in the correct directory:

File name              | Where it goes
---------------------- | -----------------
iconTicketTemplate.svg | projeqtor/view/css/customIcons/new/
ticket_create_V1_1.php | projeqtor/tool/

2. Execute modifications_V1_1.sql
3. In "`flows.json`", replace PROJEQTOR-URL with the url to your ProjeQtOr (for example https://some.domain.com/projeqtor)
4. Import "`flows.json`" in node-red and replace the previous flow if it existed.
5. In projeqtor/view/css/projeqtorIcons.css, add the following line:
```SCSS
.ProjeQtOrNewGui .iconTicketTemplate { background-image: url(customIcons/new/iconTicketTemplate.svg);  background-repeat: no-repeat; }
```

**Requirements: V1.0**
