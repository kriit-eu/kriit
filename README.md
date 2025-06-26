# Kriit

Veebirakendus kutsekoolide IT-erialadele

* sisseastumiskatsete korraldamiseks
* iseseisvate tööde esitamiseks ja hindamiseks

## Sisukord

- [Peamised võimalused](#peamised-võimalused)
  - [Administraator saab](#administraator-saab)
  - [Õpetaja saab](#õpetaja-saab)
  - [Kandidaat saab](#kandidaat-saab)
  - [Õppija saab](#õppija-saab)
- [Nõuded keskkonnale](#nõuded-keskkonnale)
- [Konteinerhalduse seadistamine](#konteinerhalduse-seadistamine)
  - [A. Docker seadistamine](#a-docker-seadistamine)
    - [Docker paigaldamine](#docker-paigaldamine)
      - [macOS](#macos)
      - [Windows](#windows)
      - [Linux (Debian/Ubuntu)](#linux-debianubuntu)
      - [Linux (Fedora/RHEL/CentOS)](#linux-fedorarhel-centos)
      - [Linux (Alpine)](#linux-alpine)
  - [B. Podman seadistamine](#b-podman-seadistamine)
    - [Miks valida Podman?](#miks-valida-podman)
    - [Podman paigaldamine](#podman-paigaldamine)
      - [macOS](#macos-1)
      - [Linux (Debian/Ubuntu)](#linux-debianubuntu-1)
      - [Linux (Fedora/RHEL/CentOS)](#linux-fedorarhel-centos-1)
      - [Linux (Alpine)](#linux-alpine-1)
      - [Windows](#windows-1)
    - [Docker käskude asendamine Podman käskudega](#docker-käskude-asendamine-podman-käskudega)
- [Kiire start](#kiire-start)
  - [Konteinerid](#konteinerid)
  - [Kasulikud käsud (Docker ja Podman)](#kasulikud-käsud-docker-ja-podman)
  - [Docker-spetsiifilised märkused](#docker-spetsiifilised-märkused)
  - [Podman-spetsiifilised märkused](#podman-spetsiifilised-märkused)
    - [Podman levinud veateated ja lahendused](#podman-levinud-veateated-ja-lahendused)
- [Arendus](#arendus)
  - [Harunimed](#harunimed)
  - [MariaDB sätted](#mariadb-sätted)

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

- **Konteinerihaldustarkvara** (vali üks):
    - **Docker** - kõige levinum valik
        - macOS-i kasutajatele on Dockeri jõudlusprobleemide tõttu parem valik [OrbStack](https://orbstack.dev/)
    - **Podman** - turvalisem ja kiirem alternatiiv (soovitatud)
        - Töötab ilma root õigusteta (rootless)
        - Parem jõudlus ja väiksem ressursikasutus
        - Automaatne seadistus läbi `docker/podman.override.yml`
- [Bun](https://bun.sh/) - Node'i kiirem alternatiiv, millele see projekt üles ehitatud on.

## Konteinerhalduse seadistamine

### A. Docker seadistamine

#### Docker paigaldamine

##### macOS
```bash
# Laadi alla ja paigalda Docker Desktop
# https://www.docker.com/products/docker-desktop/

# Alternatiivina võid kasutada OrbStack'i (parem jõudlus macOS-il)
# https://orbstack.dev/
brew install orbstack

# Kontrolli, et Docker töötab
docker info
docker run hello-world
```

##### Windows
```powershell
# Laadi alla ja paigalda Docker Desktop
# https://www.docker.com/products/docker-desktop/

# Veendu, et WSL 2 on paigaldatud ja seadistatud
wsl --install

# Kontrolli, et Docker töötab
docker info
docker run hello-world
```

##### Linux (Debian/Ubuntu)
```bash
# Paigalda Docker Engine
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Lisa oma kasutaja docker gruppi (et vältida sudo kasutamist)
sudo usermod -aG docker $USER
newgrp docker

# Käivita Docker teenus (kui see pole automaatselt käivitunud)
sudo systemctl start docker

# Kontrolli, et Docker töötab
docker info
docker run hello-world
```

##### Linux (Fedora/RHEL/CentOS)
```bash
# Paigaldamine
sudo dnf -y install dnf-plugins-core
sudo dnf config-manager --add-repo https://download.docker.com/linux/fedora/docker-ce.repo
sudo dnf install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Lisa oma kasutaja docker gruppi (et vältida sudo kasutamist)
sudo usermod -aG docker $USER
newgrp docker

# Käivita Docker teenus (kui see pole automaatselt käivitunud)
sudo systemctl start docker

# Kontrolli, et Docker töötab
docker info
docker run hello-world
```

##### Linux (Alpine)
```bash
# Paigalda Docker ja Docker Compose
sudo apk update
sudo apk add docker docker-compose docker-cli-compose

# Lisa oma kasutaja docker gruppi
sudo addgroup $USER docker
newgrp docker

# Käivita Docker teenus
sudo rc-update add docker boot
sudo service docker start

# Kontrolli, et Docker töötab
docker info
docker run hello-world
```

### B. Podman seadistamine

#### Miks valida Podman?
- **Turvalisem** – töötab ilma root õigusteta (rootless mode)
- **Kiirem** – väiksem ressursikasutus, ei vaja deemonit
- **Ühilduv** – kasutab samu Docker Compose faile
- **Lihtne kasutada** – käsud on samad nagu Dockeril (`podman` vs `docker`)

#### Podman paigaldamine

##### macOS
```bash
# Paigaldamine Homebrew abil
brew install podman
brew install podman-compose

# Käivita virtuaalmasin Podman jaoks
podman machine init --cpus 2 --memory 4096 --disk-size 20

# Käivita Podman masin
podman machine start

# Kontrolli, et Podman töötab
podman info
```

##### Linux (Debian/Ubuntu)
```bash
# Vajalikud paketid
sudo apt-get update
sudo apt-get install -y podman podman-docker podman-compose

# Kontrolli, et Podman töötab
podman info
```

##### Linux (Fedora/RHEL/CentOS)
```bash
# Paigaldamine
sudo dnf install -y podman podman-docker podman-compose

# Kontrolli, et Podman töötab
podman info
```

##### Linux (Alpine)
```bash
# Paigalda Podman ja vajalikud tööriistad
sudo apk update
sudo apk add podman podman-compose fuse-overlayfs slirp4netns

# Seadista rootless Podman (valikuline, kuid soovitatud)
echo "$USER:100000:65536" | sudo tee -a /etc/subuid
echo "$USER:100000:65536" | sudo tee -a /etc/subgid

# Kontrolli, et Podman töötab
podman info
```

##### Windows
```powershell
# Paigalda Chocolatey kaudu
choco install podman
choco install podman-compose

# Käivita virtuaalmasin Podman jaoks
podman machine init --cpus 2 --memory 4096 --disk-size 20

# Käivita Podman masin
podman machine start

# Kontrolli, et Podman töötab
podman info
```

#### Docker käskude asendamine Podman käskudega

Erinevatel platvormidel ja shell'ides saad seadistada aliased, et `docker` käsud käivitaksid tegelikult `podman`:

##### Linux ja macOS

###### Bash
Lisa järgmised read oma `.bashrc` faili (Linux) või `.bash_profile` faili (macOS):
```bash
alias docker='podman'
alias docker-compose='podman-compose'
```
Seejärel laadi konfiguratsioonifail uuesti:
```bash
source ~/.bashrc  # Linux
# või
source ~/.bash_profile  # macOS
```

###### Zsh
Lisa järgmised read oma `.zshrc` faili:
```bash
alias docker='podman'
alias docker-compose='podman-compose'
```
Seejärel laadi konfiguratsioonifail uuesti:
```bash
source ~/.zshrc
```

###### Fish
Lisa järgmised read oma `~/.config/fish/config.fish` faili:
```fish
alias docker='podman'
alias docker-compose='podman-compose'
```
Seejärel laadi konfiguratsioonifail uuesti:
```fish
source ~/.config/fish/config.fish
```

##### Linux (Alpine - ash shell)
Lisa järgmised read oma `.profile` faili:
```sh
alias docker='podman'
alias docker-compose='podman-compose'
```
Seejärel laadi konfiguratsioonifail uuesti:
```sh
. ~/.profile
```

##### Windows (PowerShell)
Loo või muuda oma PowerShell profiili:
```powershell
# Profiili loomine, kui seda pole
if (!(Test-Path -Path $PROFILE)) {
    New-Item -ItemType File -Path $PROFILE -Force
}

# Lisa aliased profiili
Add-Content -Path $PROFILE -Value 'function docker { podman $args }'
Add-Content -Path $PROFILE -Value 'function docker-compose { podman-compose $args }'

# Laadi profiil uuesti
. $PROFILE
```

Pärast nende seadistamist saad kasutada `docker` käske, mis tegelikult käivitavad `podman`.

## Kiire start

1. Tee koopia config.php failist ja seadista see vastavalt oma keskkonnale. Konteinerite kasutamisel pole vaja muuta midagi, kuid meilid tulevad siis MailHogi:
   ```bash
   cp config.php.sample config.php
   ```
2. Käivita alljärgnev käsk, et paigaldada sõltuvused ja käivitada konteinerid:
   ```bash
   bun start
   ```
   
   **Märkus:** `bun start` käsk paigaldab automaatselt kõik sõltuvused (nii Node.js kui ka PHP) ja käivitab konteinerid.

   **Märkus:** Projekt töötab nii Dockeri kui ka Podmaniga. Podman kasutajad saavad automaatse seadistuse läbi `docker/podman.override.yml` faili.
3. Ava **[http://localhost:8080](http://localhost:8080)** ja logi sisse:
    - *admin* — `41111111115` `demo`
    - *õppija* — `31111111114` `demo`

### Konteinerid

| Teenus     | Kirjeldus   | Aadress                                            |
|------------|-------------|----------------------------------------------------|
| nginx      | veebiserver | [http://localhost:8080](http://localhost:8080)     |
| phpMyAdmin | DB-haldus   | [http://localhost:8081](http://localhost:8081)     |
| MailHog    | testmeilid  | [http://localhost:8025](http://localhost:8025)     |
| MariaDB    | andmebaas   | Konteineritest `db:8006`, väljast `localhost:8006` |

### Kasulikud käsud (Docker ja Podman)

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

### Docker-spetsiifilised märkused

- Docker Desktop kasutajad võivad muuta konteinerite ressursikasutust Docker Desktop seadete alt
- OrbStack kasutajad saavad paremini optimeeritud jõudluse macOS-il

### Podman-spetsiifilised märkused

Projekt sisaldab automaatset Podman tuge läbi `docker/podman.override.yml` faili, mis:
- Lahendab failide õiguste probleemid rootless Podman keskkonnas
- Lisab turvalisuse läbi kasutaja nimeruum isolatsiooni
- Toetab SELinux süsteeme (RedHat, Fedora, CentOS)
- Töötab automaatselt ilma lisaseadistusteta

#### Podman levinud veateated ja lahendused:

1. **"Permission denied" failidele ligipääsul**
   - Veendu, et SELinux süsteemides on failidel õiged labelid
   ```bash
   sudo chcon -Rt container_file_t ./
   ```

2. **"Error: invalid IP address"**
   - Taaskäivita Podman masin
   ```bash
   podman machine stop
   podman machine start
   ```

3. **"Error: container create failed"**
   - Kontrolli, et sul on piisavalt kettaruumi
   ```bash
   podman machine info
   ```
   - Vajadusel suurenda kettaruumi
   ```bash
   podman machine stop
   podman machine set --disk-size 40
   podman machine start
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
