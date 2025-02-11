# Sisseastumiskatsete rakendus

Käesolev rakendus on loodud kutsekooli IT erialade sisseastumiskatsete läbiviimiseks ja iseseisvate tööde haldamiseks.

## Funktsionaalsus

- Administraator saab lisada kandidaate ja nende isikukoodi alusel kandidaatidele katseid määrata.
- Administraator saab lisada katseülesandeid.
- Kandidaadid saavad isikukoodi alusel sisse logida ja katseid lahendada.
- Administraator saab vaadata kandidaatide tulemusi ja pingerida kandidaate.
- Administraator saab vaadata kandidaatide poolt lahendatud katseid.
- Administraator saab vaadata sündmuslogisid.
- Õpetajad saavad Tahvlisse lisatud ülesandeid automaatselt ka Kriiti lisada, et saada keskne ülevaade, kes mis ülesande on (mitte)esitanud ja võimaldada ülesandeid hõlpsasti tagasi lükata või hinnata (nõuab [Õpetaja assistent](http://kriit.eu/opetaja-assistent) Chrome'i laiendust ja õpetajale API võtme genereerimist administraatori poolt)
- Õpilased saavad ülesannetele lahendusi ja nende parandusi esitada.
- Õpetajad saavad esitatud ülesannete kohta teavitusi.

## Eeldused

- Veebiserver PHP 8.3+-ga
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

## Täiendavad märkused Docker'i kohta

- Docker'i käskude kasutamine:
  - pead vahetama classes/App/Deployment-docker.php selleks classes/App/Deployment.php faili
  - `docker compose up` käivitab kõik teenused, mis on määratud docker-compose.yml failis.
  - `docker compose down` peatab ja eemaldab kõik teenused.
