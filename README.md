# Kriit

Käesolev rakendus on loodud kutsekooli IT erialade sisseastumiskatsete läbiviimiseks ja iseseisvate tööde haldamiseks. Rakendus töötab Tahvel.Edu.Ee jaoks loodud [Õpetaja Assistendi](https://github.com/kriit-eu/opetaja-assistent) nimelise Chrome laiendi backendina.

# Funktsionaalsus

## Sisseastumiskatsed
- Administraator saab lisada katseülesandeid.
- Kandidaadid saavad isikukoodi alusel sisse logida ja katseid lahendada.
- Administraator saab vaadata kandidaatide tulemusi ja pingerida kandidaate.
- Administraator saab vaadata kandidaatide poolt lahendatud katseid.
- Administraator saab vaadata sündmuslogisid.

## Kodutööd

- Õpetajad saavad Tahvlisse lisatud ülesandeid automaatselt ka Kriiti lisada, et saada keskne ülevaade, kes mis ülesande on (mitte)esitanud ja võimaldada ülesandeid hõlpsasti tagasi lükata või hinnata (nõuab [Õpetaja assistent](http://kriit.eu/opetaja-assistent) Chrome'i laiendust ja õpetajale API võtme genereerimist administraatori poolt).
- Õpilased saavad ülesannetele lahendusi ja nende parandusi esitada.
- Õpetajad saavad esitatud ülesannete kohta meiliteavitusi.
- Õpetajad saavad määrata kriteeriumid kodutöö esitamiseks: õpilased peavad linnukesega kinnitama, et nad on iga kriteeriumi ära täitnud, enne kui Kriit lubab õpilasel kodutöö esitada.
- Õpetaja saab korraga kõigile kodutööd veel positiivselt mitte esitanud kodutöödele lisakriteeriume, kui ilmneb vajadus nõuda midagi lisaks, et tagada edasiste kodutööde parem kvaliteet.
- Õpetajad saavad ülevaate kõikide tema õpetatavate ainete ja gruppide kohta, kes on kodutöö esitanud ja kelle kodutöö on veel üle kontrollimata ning seal samas hinnata kodutööd, mis peale see õpilane saab koheselt meiliga teavituse.

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
7. Õpetajad saavad Tahvlisse lisatud ülesandeid automaatselt Kriiti lisada ja hallata.
8. Õpilased saavad oma kodutööd Kriiti esitada ja kinnitada, et nad on kõik kriteeriumid täitnud.
9. Õpetajad saavad kodutöid hinnata ja õpilasi teavitada.