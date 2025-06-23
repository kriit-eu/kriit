# Kriit

Veebirakendus kutsekoolide IT-erialadele

* sisseastumiskatsete korraldamiseks
* iseseisvate tööde esitamiseks ja hindamiseks

---

## Peamised võimalused

### Administraator saab

- lisada kandidaate ja katseülesandeid
- määrata katseülesannete lahendamiseks piiraja
- näha pingerida ja sündmuslogisid
- näha, kes millised katseülesanded lahendas
- piirata kandidaatide sisselogimist IP-aadressi põhiselt

### Õpetaja saab

- sünkroonida Tahvli iseseisvaid töid Kriiti<sup>📌</sup>
- teavitusi lahenduste esitustest
- hinnata või tagasilükata ja kommenteerida lahendusi
- sünkroonida hinded Tahvlisse<sup>📌</sup>

<sup>📌 Vajab [Õpetaja assistent 2](http://kriit.eu/opetaja-assistent2) Chrome'i laiendust ja API võtme määramist.</sup>

### Kandidaat saab

- isikukoodiga sisselogida
- lahendada katseülesandeid
- näha, palju aega järel on
- näha, millised katseülesanded on olemas
- näha, millised katseülesanded on lahendatud

### Õppija saab

- näha oma iseseisvaid töid:
    - mis vaja esitada,
    - mis kontrollimisel,
    - mis hinnatud ja mis hindega
- meeldetuletusi tööde tähtaegade kohta
- teavitusi hinnatud tööde kohta
- vastata õpetaja kommentaaridele

---

## Nõuded keskkonnale

- **Docker** või muu konteinerihaldustarkvara
    - macOS-i kasutajatele on Dockeri jõudlusprobleemide tõttu parem valik [OrbStack](https://orbstack.dev/).
- [Bun](https://bun.sh/) - Node'i kiirem alternatiiv, millele see projekt üles ehitatud on.

## Kiire start

1. Tee koopia config.php failist ja seadista see vastavalt oma keskkonnale. Kui kasutada Dockerit, siis pole vaja muuta midagi, kuid meilid tulevad siis MailHogi:
   ```bash
   cp config.php.sample config.php
   ```
2. Käivita alljärgnev käsk, et paigaldada sõltuvused ja käivitada konteinerid
   ```bash
   bun start 
   ```
2. Ava **[http://localhost:8080](http://localhost:8080)** ja logi sisse:
    - *admin* — `41111111115` `demo`
    - *õppija* — `31111111114` `demo`

### Konteinerid

| Teenus     | Kirjeldus   | Aadress                                            |
|------------|-------------|----------------------------------------------------|
| nginx      | veebiserver | [http://localhost:8080](http://localhost:8080)     |
| phpMyAdmin | DB-haldus   | [http://localhost:8081](http://localhost:8081)     |
| MailHog    | testmeilid  | [http://localhost:8025](http://localhost:8025)     |
| MariaDB    | andmebaas   | Konteineritest `db:8006`, väljast `localhost:8006` |

### Kasulikud käsud

```bash
bun stop            # peata ja eemalda kõik konteinerid
bun restart         # taaskäivita rakendus
bun logs[:teenus]   # logid (nginx, app, db …)

bun composer        # Composer: nt bun composer install
bun db:import       # impordib doc/database.sql failist andmed andmebaasi
bun db:export       # ekspordib andmebaasist andmed doc/database.sql faili

bun shell           # PHP konteiner
bun shell:db        # DB konteiner
```

---

## Arendus

### Harunimed

Enne iga uue haru loomist tuleb luua uus GitHub'i issue ja kasutada selle numbrit haru nime alguses:

`<issue-number>_<kirjeldus_snake_case>`

Näide: `42_lisa_kasutaja_profiil`

### MariaDB sätted

```text
host: (konteineris: db, väljastpoolt 127.0.0.1)
port: 8006
user: root
pass: kriitkriit
db:   kriit
```

Näide:

```bash
mysql -h 127.0.0.1 -P8006 -uroot -pkriitkriit kriit
```
