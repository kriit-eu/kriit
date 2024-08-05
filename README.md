# Sisseastumiskatsete rakendus

Käesolev rakendus on loodud kutsekooli IT erialade sisseastumiskatsete läbiviimiseks.

## Eeldused

- Veebiserver PHP 8.0+-ga
- Composer
- MariaDB 10.5+
- Node.js 14.0+
- npm 6.0+

## Paigaldus

1. Kloonige see projekt oma arvutisse veebiserveri juurkausta.
2. Käivitage käsk `composer install` rakenduse sõltuvuste paigaldamiseks.
3. Kopeerige fail `config.php.sample` failiks `config.php` ja seadistage see vastavalt oma keskkonnale.
4. Käivitage käsk `npm install` rakenduse sõltuvuste paigaldamiseks (jQuery, Bootstrap).
5. Importige andmebaasi struktuur failist `doc/database.sql`.

## Kasutamine

1. Külastage rakenduse avalehte.
2. Logige sisse administraatori kasutajaga (vaikimisi kasutajanimi on `admin` ja parool `admin`, mis tuleks kindlasti ära muuta).
3. Lisage admin/applicants lehel kandidaatide andmed.
4. Lisage admin/exercises lehel katsete ülesanded.
5. Kandidaadid saavad oma isikukoodiga sisse logides lahendada katseid.
6. Administraator saab kandidaatide pingerida vaadata admin/applicants lehel.