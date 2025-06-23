# Kriit

Käesolev rakendus on loodud kutsekooli IT erialade sisseastumiskatsete läbiviimiseks ja iseseisvate tööde esitamiseks ja hindamiseks.

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

- Docker on paigaldatud
- Bun on paigaldatud

Rakendus kasutab Docker Compose'i mitme konteineriga arhitektuuri:
- **nginx** - veebserver
- **app** - PHP-FPM rakendusserver
- **db** - MariaDB andmebaasiserver
- **phpmyadmin** - andmebaasi haldusliides
- **mailhog** - e-kirjade testimiseks

## Kiire juhend

1. Käivitage rakendus: `bun start`
2. Külastage http://localhost:8080 ja logige sisse:
   - administraatori kasutaja `41111111115`, parool `demo`
   - tavakasutaja `31111111114`, parool `demo`

### Peamised käsud

```bash
bun start   # Rakenduse käivitamine
bun stop    # Rakenduse peatamine
bun restart # Rakenduse taaskäivitamine

bun logs              # Kõigi konteinerite logide vaatamine
bun logs:nginx        # Nginx logide vaatamine
bun logs:app          # PHP rakenduse logide vaatamine
bun logs:db           # MariaDB logide vaatamine
bun logs:phpmyadmin   # phpMyAdmin logide vaatamine
bun logs:mailhog      # MailHog logide vaatamine

bun composer # Composer käskude käivitamine
bun db:import # Andmebaasi import doc/database.sql failist
bun db:export # Andmebaasi eksport doc/database.sql faili

bun shell    # PHP konteineri shelli avamine
bun shell:db # Andmebaasi konteineri shelli avamine
```

## Arendus

### Harunimede konventsioon

Selles projektis kasutame kindlat harunimede formaati, et hoida ajalugu selge ja seostatuna tööülesannetega. Iga haru nimi peab sellele mustrile:

- **Formaat:** `<issue-number>_<description_in_snake_case>`
- **Näide:** `42_lisa_kasutaja_profiil`

Selle reegli jõustamiseks kasutame `pre-push` Git hook'i, mida haldab **Husky**. See tähendab, et enne koodi üleslaadimist (`git push`) kontrollitakse automaatselt, kas haru nimi vastab formaadile.

### Juurdepääsupunktid

- **Rakendus**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MailHog veebiliides**: http://localhost:8025

### Andmebaasi andmed

- **Andmebaas**: kriit
- **Kasutajanimi**: root
- **Parool**: kriitkriit
- **Host**: localhost (väliselt) / db (konteinerite vahel)
- **Port**: 8006

### Otse andmebaasiga ühendamine

Väliselt saate ühenduda andmebaasiga:
```bash
mysql -h127.0.0.1 -P8006 -uroot -pkriitkriit kriit
```