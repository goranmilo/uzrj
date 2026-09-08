# Objavljivanje na Hetzner server

Uputstvo za postavljanje UZRJ aplikacije na Hetzner Cloud server sa Ubuntu 24.04.
Prateći fajlovi su u [deploy/](deploy):

| Fajl | Namena |
|---|---|
| [deploy/nginx/uzrj.conf](deploy/nginx/uzrj.conf) | Nginx vhost |
| [deploy/systemd/uzrj-queue.service](deploy/systemd/uzrj-queue.service) | systemd servis za queue worker |
| [deploy/deploy.sh](deploy/deploy.sh) | Objavljivanje naredne verzije |

Aplikacija nema javni frontend — sve je Filament panel na `/admin`. Node.js nije
potreban na serveru: Filament isporučuje kompajlirane asset-e
(`php artisan filament:assets`), a `welcome.blade.php` radi i bez Vite manifesta.

---

## 1. Server

- **Tip:** CX22 (2 vCPU, 4 GB RAM, 40 GB) je dovoljan za nekoliko hiljada članova
- **Lokacija:** Nuremberg ili Falkenstein — baza sadrži JMBG i podatke o
  zdravstvenim radnicima, pa podaci treba da ostanu u EU
- **Image:** Ubuntu 24.04 LTS (nosi PHP 8.3 u standardnim repozitorijumima)
- Pri kreiranju dodaj SSH ključ i uključi **Backups**
- U Hetzner Cloud Firewall-u pusti samo portove 22, 80 i 443

Ako panel ne treba da bude otvoren ka celom internetu, ograniči 80/443 na IP
adrese udruženja ili postavi pristup kroz VPN.

## 2. Osnovno obezbeđenje

```bash
adduser deploy && usermod -aG sudo deploy && rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy
```

U `/etc/ssh/sshd_config` postavi `PasswordAuthentication no` i
`PermitRootLogin no`, pa `systemctl restart ssh`.

```bash
apt update && apt install -y fail2ban unattended-upgrades && ufw allow OpenSSH && ufw enable
```

## 3. Paketi

```bash
apt update && apt install -y nginx postgresql-16 php8.3-fpm php8.3-pgsql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl git unzip composer
```

Nginx registruje svoj `ufw` profil tek pri instalaciji, pa se pravilo za portove
80/443 dodaje sad, ne u prethodnom koraku:

```bash
ufw allow 'Nginx Full'
```

U `/etc/php/8.3/fpm/php.ini` podigni limite za uvoz članova iz Excel-a:

```
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 180
```

## 4. Baza podataka

```bash
sudo -u postgres psql -c "CREATE ROLE uzrj LOGIN PASSWORD 'jaka-lozinka'; CREATE DATABASE uzrj_db OWNER uzrj;"
```

## 5. Kod

Repozitorijum je privatan, pa napravi deploy ključ na serveru i dodaj javni deo
u GitHub → Settings → Deploy keys (dovoljan je read-only pristup):

```bash
sudo -u deploy ssh-keygen -t ed25519 -N '' -f /home/deploy/.ssh/id_ed25519 && cat /home/deploy/.ssh/id_ed25519.pub
```

```bash
git clone git@github.com:goranmilo/uzrj.git /var/www/uzrj && cd /var/www/uzrj && composer install --no-dev --optimize-autoloader && cp .env.example .env && php artisan key:generate
```

### Vrednosti u `.env` koje se razlikuju od razvojnih

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://uzrj.example.rs

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uzrj_db
DB_USERNAME=uzrj
DB_PASSWORD=jaka-lozinka

MAIL_MAILER=smtp
MAIL_HOST=smtp.provajder.rs
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="noreply@uzrj.rs"
MAIL_FROM_NAME="UZRJ"
```

> `MAIL_MAILER` je u razvoju postavljen na `log`. Ako to ostane, mesečni
> izveštaji i podsetnici se neće poslati nikome — samo će se upisati u log.

`SESSION_DRIVER`, `CACHE_STORE` i `QUEUE_CONNECTION` ostaju na `database`.

### Prvo pokretanje

```bash
php artisan migrate --force && php artisan db:seed --force && php artisan filament:assets && php artisan optimize && php artisan filament:cache-components
```

`db:seed` upisuje uloge, šifarnike, podešavanja i dva naloga. Demo podaci
(`EdukacijaSeeder`, `FinansijeSeeder`) nisu deo `DatabaseSeeder`, pa ne ulaze u
produkciju.

```bash
chown -R deploy:www-data /var/www/uzrj && chmod -R 775 /var/www/uzrj/storage /var/www/uzrj/bootstrap/cache
```

## 6. Nginx

```bash
cp /var/www/uzrj/deploy/nginx/uzrj.conf /etc/nginx/sites-available/uzrj && ln -sf /etc/nginx/sites-available/uzrj /etc/nginx/sites-enabled/uzrj && rm -f /etc/nginx/sites-enabled/default && nginx -t && systemctl reload nginx
```

U fajlu zameni `uzrj.example.rs` stvarnim domenom. Zatim HTTPS:

```bash
apt install -y certbot python3-certbot-nginx && certbot --nginx -d uzrj.example.rs
```

## 7. Queue i scheduler

```bash
cp /var/www/uzrj/deploy/systemd/uzrj-queue.service /etc/systemd/system/ && systemctl daemon-reload && systemctl enable --now uzrj-queue
```

Scheduler pokreće tri komande iz `routes/console.php` (mesečni izveštaj,
nedeljni podsetnik za članarinu, mesečno upozorenje za bodove):

```bash
sudo -u deploy crontab -e
```

```
* * * * * cd /var/www/uzrj && php artisan schedule:run >> /dev/null 2>&1
```

## 8. Posle prvog logovanja

- Promeni lozinke za `admin@uzrj.rs` i `operater@uzrj.rs` — podrazumevane su
  navedene u dokumentaciji, dakle javne
- Uključi 2FA na admin nalogu (Administracija → 2FA podešavanja)
- Unesi podatke o udruženju u Konfiguraciji sistema

## 9. Backup

Hetzner backup čuva ceo disk, ali dodaj i dnevni dump baze:

```
0 2 * * * pg_dump -U uzrj uzrj_db | gzip > /var/backups/uzrj-$(date +\%F).sql.gz
```

Jednom probaj vraćanje dumpa u praznu bazu — backup koji nikad nije vraćen nije
backup. Dampove drži van servera (npr. Hetzner Storage Box).

## 10. Objavljivanje naredne verzije

Sa lokalne mašine `git push origin main`, a zatim na serveru:

```bash
sudo -u deploy /var/www/uzrj/deploy/deploy.sh main
```

Skripta redom: proverava da je radno stablo čisto, povlači granu, uključuje
režim održavanja, instalira zavisnosti, pokreće migracije, osvežava keš i
Filament komponente, restartuje queue radnike i vraća aplikaciju iz održavanja.
Na kraju osvežava PHP-FPM zbog opcache-a — za to je potrebno jedno pravo u
`/etc/sudoers.d/uzrj`:

```
deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm
```

Bez tog prava skripta ne pada, samo ispiše upozorenje da FPM treba osvežiti
ručno.

## 11. Napomene

- **Tema:** boje panela se čitaju iz baze i keširaju, pa posle promene teme
  treba `php artisan filament:cache-components`
- **Privremeni upload-i:** fajlovi poslati kroz uvoz članova ostaju u
  `storage/app/private/livewire-tmp` do 24h; Livewire ih sam briše
- **Logovi:** `storage/logs/laravel.log` raste — dodaj logrotate ili postavi
  `LOG_DAILY_DAYS` i `LOG_CHANNEL=daily`
- **Testna baza** (`uzrj_test`) je potrebna samo na razvojnoj mašini
