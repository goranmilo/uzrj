# UZRJ — Dokumentacija projekta

**Verzija:** 1.0 — Faza 1 (MVP) kompletirana  
**Datum:** 23. jun 2026.  
**Status:** Spremno za produkciju

---

## Sadržaj

1. [Pregled projekta](#1-pregled-projekta)
2. [Tehnološki steck](#2-tehnološki-steck)
3. [Instalacija i pokretanje](#3-instalacija-i-pokretanje)
4. [Struktura projekta](#4-struktura-projekta)
5. [Baza podataka](#5-baza-podataka)
6. [Implementirani moduli](#6-implementirani-moduli)
7. [Administracija](#7-administracija)
8. [Tema aplikacije](#8-tema-aplikacije)
9. [E-mail konfiguracija](#9-e-mail-konfiguracija)
10. [Plan za Fazu 2](#10-plan-za-fazu-2)
11. [Testovi](#11-testovi)
12. [Poznati problemi](#12-poznati-problemi)
13. [Git informacije](#13-git-informacije)

---

## 1. Pregled projekta

**UZRJ** je web aplikacija za upravljanje članstvom strukovnog udruženja zdravstvenih radnika Republike Srbije.

### Ključne funkcionalnosti (Faza 1 — MVP):

- **Evidencija članova** — CRUD sa JMBG validacijom, šifarnicima, Excel import/export
- **Članarina** — Periodi, zaduženja, uplate, pro-rata obračun
- **Edukacije (KME)** — Programi, QR kod prisustvo, automatska dodela bodova
- **Bodovni sistem** — Konfigurabilni pragovi, praćenje napretka
- **Dashboard** — Statistike, grafici, aktuelnosti
- **E-mail izveštaji** — Mesečni izveštaji, podsetnici
- **Administracija** — Korisnici, podešavanja, audit log, tema

### Uloge:

| Uloga | Pristup |
|-------|---------|
| **Administrator** | Pun pristup svim modulima, konfiguracija, upravljanje korisnicima |
| **Operater** | Unos članova, evidencija uplata, edukacije, prisustvo |
| **Član** | Nema pristup aplikaciji (Faza 1), informiše se e-mailom |

---

## 2. Tehnološki steck

| Komponenta | Tehnologija | Verzija |
|------------|-------------|---------|
| Backend | Laravel | 13.x |
| PHP | PHP | 8.3 |
| Frontend | Livewire + Alpine.js + Tailwind CSS | 3.x |
| Admin panel | Filament | 3.x |
| Baza podataka | PostgreSQL | 16 |
| Auth + 2FA | Laravel Fortify | - |
| Uloge/permisije | spatie/laravel-permission | 8.x |
| Excel import/export | maatwebsite/excel | 3.x |
| PDF generisanje | barryvdh/laravel-dompdf | 3.x |
| QR kodovi | bacon/bacon-qr-code | 3.x |

---

## 3. Instalacija i pokretanje

### Preduslovi

- PHP 8.3+ sa ekstenzijama: pgsql, mbstring, xml, curl, zip, bcmath, gd, intl
- Composer 2.x
- PostgreSQL 16+
- Node.js 18+ i npm

### Instalacija

```bash
# Kloniranje
git clone https://github.com/goranmilo/uzrj.git
cd uzrj

# PHP zavisnosti
composer install

# Konfiguracija
cp .env.example .env
php artisan key:generate

# Konfiguracija baze u .env fajlu:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uzrj_db
DB_USERNAME=uzrj
DB_PASSWORD=vasa_lozinka

# Pokretanje migracija i seed-ova
php artisan migrate
php artisan db:seed

# Frontend zavisnosti
npm install
npm run build

# Pokretanje razvojnog servera
php artisan serve
```

### Podrazumevani nalozi

| Uloga | Email | Lozinka |
|-------|-------|---------|
| Administrator | admin@uzrj.rs | admin123 |
| Operater | operater@uzrj.rs | operater123 |

> ⚠️ **Promenite lozinku nakon prvog logovanja!**

---

## 4. Struktura projekta

```
uzrj/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── PosaljiMesecniIzvestaj.php    # Artisan komanda za mesečni izveštaj
│   │       ├── PosaljiPodsetnikClanarine.php  # Podsetnik za neplaćenu članarinu
│   │       └── PosaljiUpozorenjeBodovi.php    # Upozorenje za članove ispod minimuma
│   ├── Exports/
│   │   ├── ClanExport.php                     # Export članova u Excel
│   │   └── ClanImportTemplate.php             # Šablon za import
│   ├── Filament/
│   │   ├── Pages/
│   │   │   ├── BodoviPregled.php              # Pregled bodova članova
│   │   │   ├── Dashboard.php                  # Glavni dashboard
│   │   │   ├── EmailManagement.php            # Upravljanje mejlovima
│   │   │   ├── QrScanner.php                  # Skeniranje QR kodova
│   │   │   ├── SystemConfiguration.php        # Konfiguracija sistema
│   │   │   ├── ThemeSettings.php              # Podešavanje teme
│   │   │   └── TwoFactorSetup.php             # 2FA podešavanja
│   │   ├── Resources/
│   │   │   ├── AktuelnostResource.php         # Upravljanje vestima
│   │   │   ├── AuditLogResource.php           # Audit log
│   │   │   ├── BodResource.php                # Upravljanje bodovima
│   │   │   ├── ClanResource.php               # CRUD članova
│   │   │   ├── ClanarinaKategorijaResource.php # Kategorije članarine
│   │   │   ├── ClanarinaPeriodResource.php    # Periodi članarine
│   │   │   ├── ClanarinaResource.php          # Zaduženja
│   │   │   ├── EdukacijaResource.php          # Edukacije
│   │   │   ├── MailIzvestajResource.php       # Log mejlova
│   │   │   ├── OdeljenjeResource.php          # Šifarnik odeljenja
│   │   │   ├── PodesavanjeResource.php        # Podešavanja sistema
│   │   │   ├── SpremaResource.php             # Šifarnik sprema
│   │   │   ├── UplataResource.php             # Evidencija uplata
│   │   │   ├── UserResource.php               # Upravljanje korisnicima
│   │   │   └── ZvanjeResource.php             # Šifarnik zvanja
│   │   └── Widgets/
│   │       ├── AktuelnostiWidget.php          # Widget aktuelnosti
│   │       ├── ClanBodoviWidget.php           # Bodovi po članu
│   │       ├── ClanStatsOverview.php          # Statistike članstva
│   │       ├── ClanTrendChart.php             # Trend učlanjenja
│   │       ├── EdukacijaDashboardWidget.php   # Statistike edukacija
│   │       ├── EdukacijaStatsWidget.php       # Stats za edukaciju
│   │       ├── FinansijskiPregled.php         # Finansijski pregled
│   │       └── PredstojeceEdukacijeWidget.php # Predstojeće edukacije
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ExcelController.php            # Download Excel fajlova
│   │   └── Middleware/
│   │       ├── ApplyTheme.php                 # Primena teme
│   │       └── CheckRole.php                  # Provera uloga
│   ├── Imports/
│   │   └── ClanImport.php                     # Import članova iz Excel-a
│   ├── Mail/
│   │   └── MesecniIzvestaj.php                # Mailable za mesečni izveštaj
│   ├── Observers/
│   │   └── AuditObserver.php                  # Upis izmena u audit_log
│   ├── Models/
│   │   ├── Aktuelnost.php
│   │   ├── AuditLog.php
│   │   ├── Bod.php
│   │   ├── Clan.php
│   │   ├── Clanarina.php
│   │   ├── ClanarinaKategorija.php
│   │   ├── ClanarinaPeriod.php
│   │   ├── Edukacija.php
│   │   ├── Licenca.php
│   │   ├── MailIzvestaj.php
│   │   ├── Odeljenje.php
│   │   ├── Podesavanje.php
│   │   ├── Prisustvo.php
│   │   ├── Sprema.php
│   │   ├── Uplata.php
│   │   ├── User.php
│   │   └── Zvanje.php
│   ├── Rules/
│   │   └── Jmbg.php                           # Validacija JMBG-a
│   ├── Services/
│   │   ├── BodoviService.php                  # Bodovi, licencni period i licencna godina
│   │   ├── ClanarinaService.php               # Servis za članarine
│   │   ├── EdukacijaService.php               # Servis za edukacije
│   │   ├── EmailService.php                   # Servis za mejlove
│   │   └── LicencaService.php                 # Čuvanje licence, datum isteka i status
│   └── Support/
│       └── Tema.php                           # Boje teme (paleta + CSS varijable)
├── database/
│   ├── migrations/                            # 16 migracija
│   └── seeders/
│       ├── AdminUserSeeder.php
│       ├── ClanarinaKategorijaSeeder.php (deo SifarnikSeeder)
│       ├── DatabaseSeeder.php
│       ├── EdukacijaSeeder.php
│       ├── FinansijeSeeder.php
│       ├── OperaterSeeder.php
│       ├── PodesavanjaSeeder.php
│       ├── RoleSeeder.php
│       └── SifarnikSeeder.php
├── resources/
│   └── views/
│       ├── emails/
│       │   └── mesecni-izvestaj.blade.php     # Šablon mejla
│       ├── filament/
│       │   ├── pages/
│       │   │   ├── bodovi-pregled.blade.php
│       │   │   ├── email-management.blade.php
│       │   │   ├── prisustvo-edukacije.blade.php
│       │   │   ├── qr-edukacije.blade.php
│       │   │   ├── qr-scanner.blade.php
│       │   │   ├── system-configuration.blade.php
│       │   │   ├── theme-settings.blade.php
│       │   │   └── two-factor-setup.blade.php
│       │   └── widgets/
│       │       ├── aktuelnosti-widget.blade.php
│       │       └── predstojece-edukacije-widget.blade.php
│       └── vendor/
│           └── filament/                      # Objavljene Filament komponente
├── routes/
│   ├── console.php                            # Zakazane komande
│   └── web.php                                # Web rute (Excel download)
└── tests/
    ├── Feature/                               # Servisi, audit log, forma člana
    └── Unit/                                  # JMBG validacija, boje teme
```

---

## 5. Baza podataka

### Tabele (16 migracija)

| Tabela | Svrha | Ključna polja |
|--------|-------|---------------|
| `users` | Nalozi za prijavu | name, email, password, 2fa_secret |
| `roles` | Uloge | admin, operater, clan |
| `clanovi` | Članstvo | ime, prezime, jmbg, email, status |
| `licence` | Podaci o licencama | clan_id, broj, datum_izdavanja, datum_isteka |
| `spreme` | Šifarnik sprema | naziv, aktivno |
| `zvanja` | Šifarnik zvanja | naziv, aktivno |
| `odeljenja` | Šifarnik odeljenja | naziv, aktivno |
| `clanarina_kategorije` | Kategorije članarine | naziv, iznos |
| `clanarina_periodi` | Periodi naplate | naziv, vrsta, vazi_od, vazi_do |
| `clanarine` | Zaduženja | clan_id, period_id, iznos_zaduzenja, iznos_placen, status |
| `uplate` | Istorija uplata | clanarina_id, iznos, datum, nacin |
| `edukacije` | KME programi | naziv, datum, lokacija, bodovi, status |
| `prisustva` | Prisustvo + QR | edukacija_id, clan_id, qr_token, prisutan |
| `bodovi` | Knjiga bodova | clan_id, edukacija_id, bodovi, licencna_godina |
| `aktuelnosti` | Vesti | naslov, sadrzaj, objavljeno |
| `podesavanja` | Konfiguracija | kljuc, vrednost, tip |
| `mail_izvestaji` | Log mejlova | clan_id, tip, poslat_at |
| `audit_log` | Trag izmena | user_id, akcija, entitet, pre/posle |

### Inicijalni podaci (seed-ovi)

- **Uloge:** admin, operater, clan
- **Šifarnici:** 6 sprema, 12 zvanja, 13 odeljenja, 4 kategorije članarine
- **Podešavanja:** godišnji prag bodova (20), ukupan prag (140), licencni period (7 godina)
- **Nalozi:** admin@uzrj.rs, operater@uzrj.rs

---

## 6. Implementirani moduli

### 6.1 Članstvo

**Lokacija:** Članstvo → Članovi

**Funkcionalnosti:**
- CRUD članova sa svim poljima (ime, prezime, JMBG, email, telefon, sprema, zvanje, odeljenje, kategorija)
- JMBG validacija (kontrolna cifra)
- Automatski status licence (+7 godina)
- Excel import/export
- Pretraga i filteri (odeljenje, zvanje, sprema)
- Pregled člana sa edukacijama i bodovima

**Uvoz iz Excel-a (Članovi → Import iz Excel-a):**

- Podržani formati: `.xlsx`, `.xls`, `.csv`; prvi red su nazivi kolona
  (`ime`, `prezime`, `jmbg`, `email`, `telefon`, `okg`, `status`,
  `datum_uclanjenja`, `clanski_broj`, `sprema`, `zvanje`, `odeljenje`,
  `kategorija_clanarine`, `licenca_broj`, `licenca_datum_izdavanja`,
  `licenca_datum_isteka`) — šablon se preuzima dugmetom „Preuzmi šablon"
- Poslati fajl se ne čuva na disku; obrađuje se kao privremeni upload, jer
  sadrži lične podatke
- Datumi se prihvataju u zapisima `d.m.Y`, `Y-m-d`, `d/m/Y`, `d-m-Y`, sa tačkom
  na kraju („15.12.2029.") i jednocifrenim danom/mesecom („7.4.1992."), kao i u
  Excel-ovom numeričkom zapisu; neprepoznat datum ostaje prazan i ne obara red
- Ćelija sa samo godinom („2022.") se tumači kao 1. januar te godine
- Ako je za licencu poznat samo datum isteka, datum izdavanja se izvodi unazad
  za dužinu licencnog perioda; ako nema nijednog datuma, licenca se ne upisuje,
  ali se član uvozi (razlog ide u log)
- Numeričke ćelije (JMBG, telefon, članski broj) se vraćaju u tekst sa vodećom
  nulom pre validacije
- JMBG se proverava isto kao u formi za unos (kontrolna cifra). Red koji ne
  prođe validaciju se preskače, ostali se uvoze, a razlog se prikazuje u
  notifikaciji i upisuje u log
- Prazni redovi (npr. na kraju tabele) se preskaču bez prijave greške
- Ako je kolona `prezime` prazna, a u koloni `ime` stoji puno ime, poslednja
  reč se uzima kao prezime
- Postojeći član se prepoznaje po JMBG-u i ažurira

**Ključne datoteke:**
- `app/Filament/Resources/ClanResource.php`
- `app/Filament/Resources/ClanResource/Pages/ListClans.php`
- `app/Imports/ClanImport.php`
- `app/Exports/ClanExport.php`
- `app/Rules/Jmbg.php`

### 6.2 Članarina

**Lokacija:** Finansije → Članarine / Periodi članarine / Uplate

**Funkcionalnosti:**
- Definisanje perioda (godišnje/mesečno/kvartalno)
- Kategorije sa iznosima
- Automatsko zaduženje svih članova
- Pro-rata obračun za srednje-periodne upise
- Evidentiranje uplata
- Praćenje dugovanja

**Ključne datoteke:**
- `app/Services/ClanarinaService.php`
- `app/Filament/Resources/ClanarinaResource.php`
- `app/Filament/Resources/ClanarinaPeriodResource.php`
- `app/Filament/Resources/UplataResource.php`

### 6.3 Edukacije (KME)

**Lokacija:** Edukacije → Edukacije / QR skener

**Funkcionalnosti:**
- CRUD edukacija sa KME podacima (akreditacija, bodovi, ciljna grupa)
- Prijava članova na edukacije
- QR kod generisanje za svakog člana
- Čekiranje prisustva skeniranjem QR koda
- Ručno čekiranje
- Automatska dodela bodova
- Status: planirana / održana / otkazana

**Ključne datoteke:**
- `app/Services/EdukacijaService.php`
- `app/Filament/Resources/EdukacijaResource.php`
- `app/Filament/Pages/QrScanner.php`

### 6.4 Bodovni sistem

**Lokacija:** Edukacije → Bodovi / Pregled bodova

**Funkcionalnosti:**
- Praćenje bodova po članu
- Konfigurabilni pragovi (godišnji minimum, ukupan za period)
- Prikaz napretka (procentualno)
- Upozorenja za članove ispod minimuma
- Ručna korekcija bodova

**Licencna godina (bitno za obračun):**

Licencna godina se računa **od datuma izdavanja licence**, a ne od 1. januara.
Licenca izdata 15.09.2023. sa periodom od 7 godina znači da tekuća licencna
godina traje 15.09.2025 — 14.09.2026. Bodovi se ne prenose iz jedne licencne
godine u drugu, pa se godišnji minimum sabira isključivo unutar tekuće licencne
godine, a ukupan prag unutar tekućeg licencnog perioda.

Kolona `bodovi.licencna_godina` čuva kalendarsku godinu u kojoj licencna godina
počinje (u primeru: 2025). Za člana bez upisane licence koristi se kalendarska
godina. Vrednost se popunjava automatski (polje u formi je samo za prikaz).

Relevantne metode u `BodoviService`:

| Metoda | Vraća |
|--------|-------|
| `licenca($clan)` | Merodavnu (najskorije izdatu) licencu |
| `pocetakLicencnogPerioda($clan, $datum)` | Datum početka tekućeg licencnog perioda |
| `pocetakLicencneGodine($clan, $datum)` | Datum početka licencne godine |
| `redniBrojLicencneGodine($clan, $datum)` | Redni broj godine u periodu (1..7) |
| `licencnaGodina($clan, $datum)` | Oznaka za `bodovi.licencna_godina` |

**Ključne datoteke:**
- `app/Services/BodoviService.php`
- `app/Services/LicencaService.php`
- `app/Filament/Resources/BodResource.php`
- `app/Filament/Pages/BodoviPregled.php`

### 6.5 Dashboard

**Lokacija:** Dashboard (početna strana)

**Widgeti:**
- Pregled članstva (ukupno, aktivnih, dugovanje)
- Finansijski pregled (naplata, dugujući)
- Statistike edukacija
- Trend učlanjenja (grafikon)
- Predstojeće edukacije
- Aktuelnosti

### 6.6 E-mail izveštavanje

**Lokacija:** Administracija → Upravljanje mejlovima

**Funkcionalnosti:**
- Mesečni izveštaj članovima (članarina, bodovi, edukacije, aktuelnosti)
- Podsetnik za neplaćenu članarinu
- Upozorenje za članove ispod minimuma bodova
- Zakazano slanje (scheduler)
- Log svih poslatih mejlova

**Ključne datoteke:**
- `app/Services/EmailService.php`
- `app/Mail/MesecniIzvestaj.php`
- `resources/views/emails/mesecni-izvestaj.blade.php`
- `routes/console.php` (scheduler)

---

## 7. Administracija

### 7.1 Upravljanje korisnicima

**Lokacija:** Administracija → Korisnici

- CRUD korisnika
- Dodela uloga (admin/operater)
- 2FA status
- Reset lozinke

### 7.2 Šifarnici

**Lokacija:** Administracija → Stručne spreme / Zvanja / Odeljenja / Kategorije članarine

- CRUD za sve šifarnike
- Aktivno/neaktivno status
- Redosled prikaza

### 7.3 Konfiguracija sistema

**Lokacija:** Administracija → Konfiguracija sistema

**Podešavanja:**
- Bodovni sistem (godišnji prag, ukupan prag, licencni period)
- Članarina (vrsta naplate, pro-rata)
- Podaci o udruženju (naziv, matični broj, adresa, kontakt)

### 7.4 Audit log

**Lokacija:** Administracija → Audit log

- Prikaz svih promena u sistemu
- Filtriranje po akciji, entitetu, korisniku
- Prikaz pre/posle vrednosti

**Implementacija:**

Upis obavlja `App\Observers\AuditObserver`, registrovan u `AppServiceProvider`
za modele nabrojane u `AppServiceProvider::AUDITOVANI_MODELI` (član, licenca,
članarina, period, kategorija, uplata, edukacija, prisustvo, bod, aktuelnost,
podešavanje, šifarnici, korisnik). Novi model se uključuje u praćenje dodavanjem
u tu listu.

- `akcija`: `create` / `update` / `delete`; `entitet`: naziv modela (`clan`, `uplata`…)
- Kod izmene se upisuju samo stvarno promenjena polja (stara i nova vrednost)
- Osetljivi atributi (lozinka, remember token, 2FA tajna, QR token) se ne upisuju
- Greška pri upisu se loguje i ne prekida poslovnu operaciju

---

## 8. Tema aplikacije

**Lokacija:** Administracija → Tema aplikacije

### Dostupne teme (8):

| Tema | Boja | Opis |
|------|------|------|
| Emerald | Zelena (#10B981) | Podrazumevana |
| Ocean | Plava (#3B82F6) | Profesionalna |
| Royal | Ljubičasta (#8B5CF6) | Elegantna |
| Crimson | Crvena (#EF4444) | Energična |
| Sunset | Narandžasta (#F97316) | Topla |
| Teal | Tirkizna (#14B8A6) | Moderna |
| Rose | Roze (#EC4899) | Nežna |
| Dark | Tamna (#6366F1) | Za noćni rad |

### Prilagođene boje:
- Primarna boja (color picker)
- Primarna boja tamna
- Akcent boja
- Tamni režim (toggle)

### Implementacija:
- Čuva se u `podesavanja` tabeli (ključevi: tema, tema_primary, tema_primary_dark, tema_accent, tema_dark_mode)
- `App\Support\Tema` je jedino mesto koje obrađuje boje: validira hex zapis,
  generiše Filament paletu nijansi (50–950) i CSS varijable
- `AdminPanelProvider` registruje paletu kao primarnu boju panela, pa Filament
  sam boji dugmad, navigaciju, badževe i ostale elemente
- Tamni režim koristi Filament-ov `ThemeMode::Dark`, ne prepisivanje CSS-a
- CSS varijable (`--theme-primary`, `--theme-primary-rgb`, `--theme-primary-dark`,
  `--theme-accent`) ubacuje Filament render hook (`HEAD_END`) unutar panela,
  a `ApplyTheme` middleware na stranicama van panela

**Ključne datoteke:**
- `app/Support/Tema.php`
- `app/Filament/Pages/ThemeSettings.php`
- `app/Http/Middleware/ApplyTheme.php`
- `app/Providers/Filament/AdminPanelProvider.php`

---

## 9. E-mail konfiguracija

### SMTP podešavanja (u .env fajlu):

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=vaskontakt@gmail.com
MAIL_PASSWORD=vasa_app_lozinka
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@uzrj.rs"
MAIL_FROM_NAME="UZRJ"
```

### Zakazane komande (u `routes/console.php`):

```php
// Mesečni izveštaj - svakog 1. u mesecu
Schedule::command('email:mesecni-izvestaj')->monthly();

// Podsetnik za članarinu - svakog ponedeljka
Schedule::command('email:podsetnik-clanarine')->weekly();

// Upozorenje za bodove - svakog 1. u mesecu
Schedule::command('email:upozorenje-bodovi')->monthly();
```

### Pokretanje scheduler-a:

```bash
# Dodati u crontab:
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 10. Plan za Fazu 2

### 10.1 Samouslužni portal člana
- Login za članove
- Sopstveni dashboard
- Prijava na edukacije
- Preuzimanje potvrda
- Pregled bodova i članarine

### 10.2 KME potvrde (PDF)
- Generisanje propisanih potvrda za svaku edukaciju
- Sva obavezna polja iz pravilnika
- Pečat i potpis (digitalni)
- Ćirilica

### 10.3 Napredni podsetnici
- Godišnji minimum bodova
- Istek licence < 60 dana
- Rizik od licencnog ispita

### 10.4 Online plaćanje
- Integracija sa lokalnim platnim provajderom
- Generisanje uplatnica sa pozivom na broj
- Automatska evidencija uplata

### 10.5 CSV/Excel poboljšanja
- Direktorijum članova sa naprednim filterima
- Bulk operacije
- Import istorije edukacija

---

## 11. Testovi

Testovi koriste zasebnu PostgreSQL bazu `uzrj_test` (isti drajver kao produkcija;
PDO sqlite ekstenzija nije među preduslovima projekta). Baza se kreira jednom:

```bash
createdb -h 127.0.0.1 -U uzrj uzrj_test
php artisan test
```

| Test | Pokriva |
|------|---------|
| `tests/Unit/JmbgTest.php` | Validaciju JMBG-a (kontrolna cifra, format, datum) |
| `tests/Unit/TemaTest.php` | Hex validaciju, paletu nijansi, CSS varijable |
| `tests/Feature/BodoviServiceTest.php` | Licencni period, licencnu godinu, zbir bodova |
| `tests/Feature/ClanarinaServiceTest.php` | Pro-rata, dvostruko zaduženje, uplate i statuse |
| `tests/Feature/LicencaServiceTest.php` | Čuvanje licence, datum isteka, status |
| `tests/Feature/ClanResourceLicencaTest.php` | Formu člana — unos i izmenu licence |
| `tests/Feature/ClanImportTest.php` | Uvoz iz CSV/XLSX — formati datuma, numerički JMBG |
| `tests/Feature/ClanImportActionTest.php` | Akciju „Import iz Excel-a" u panelu |
| `tests/Feature/AuditLogTest.php` | Upis create/update/delete i filtriranje osetljivih polja |

---

## 12. Poznati problemi

### 12.1 Browser kompatibilnost (Livewire/Filament)
- **Date picker** za edukacije ne radi u headless browseru (radi u Chrome/Firefox)
- **Modal za evidentiranje uplate** se ne otvara u headless browseru

### 12.2 Filament keširanje
- Filament kešira konfiguraciju panela
- Promena teme zahteva `php artisan filament:cache-components`
- Ili brisanje keša: `php artisan optimize:clear`

### 12.3 Grafikon boje
- Grafikon na dashboard-u koristi Filament-ove podrazumevane boje
- Ne menja se automatski sa temom (zahteva dodatnu konfiguraciju widgeta)

---

## 13. Git informacije

### Remote repository:
```
https://github.com/goranmilo/uzrj.git
```

### Grana:
```
main
```

### Git komande za nastavak rada:
```bash
# Kloniranje
git clone https://github.com/goranmilo/uzrj.git
cd uzrj

# Kreiranje nove grane za Fazu 2
git checkout -b faza-2

# Posle promena
git add .
git commit -m "Opis promene"
git push origin faza-2
```

---

## Kontakt i podrška

Za pitanja o projektu, obratiti se timu za razvoj.

---

*Dokumentacija generisana: 23. jun 2026.*
