# jsiBok

Ett redovisningssystem för små svenska bolag.

En instans av detta finns på [huvudboken.se](https://huvudboken.se) där jag har som ambition att erbjuda tjänsten gratis och/eller billigt.

Jag tar gärna emot hjälp med allt.

------

Ärendehantering och bugg-rapporter finns hos [Codeberg](https://codeberg.org/idstam/jsiBok/issues)

Git-repot hos Github är en spegel av [jsiBok hos Codeberg](https://codeberg.org/idstam/jsiBok)

-----

Systemet är skrivet i PHP och använder ett mvc-ramverk som heter Codeigniter.

För att komma igång borde det räcka med att installera Composer, PHP 8.4 och de paket som [Codeigniter kräver](https://codeigniter.com/user_guide/intro/requirements.html).

Kopiera env-filen i /ci-app till .env

Skapa en databas i MySql eller MariaDB och fyll i uppgifterna i .env

Ställ dig i /ci-app och kör: 
```
php spark migrate:refresh
```

Nu bör du ha tabeller och grunddata i din databas.

Längst ner i .env kan du tillåta användare att skapa konton själva. Aktivera det för att skapa ditt första konto. Du kan sätta flaggan till off om du inte vill att någon anna ska kunna använda din instans när du väl skapat din första användare.

Kör sedan:

```
php spark serve
```

Nu borde systemet vara tillgängligt på [http://localhost:8080](http://localhost:8080)
