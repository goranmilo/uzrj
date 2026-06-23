# UZRJ — Upravljanje članstvom strukovnog udruženja zdravstvenih radnika

Web aplikacija za upravljanje članstvom, naplatom članarine, profesionalnim
(KME) edukacijama, prisustvom i bodovnim sistemom jednog dobrovoljnog
strukovnog udruženja zdravstvenih radnika Republike Srbije.

> Aplikacija vodi jedinstven bodovni sistem — jedan tok bodova po prisustvu
> edukacijama, sa praćenjem licencnog perioda i godišnjeg/ukupnog praga.
> Pragovi su konfigurabilni u administrativnom delu (ne hardkodovano).

## Status

- **Faza:** Faza 1 — MVP (u razvoju)
- **Stek:** Laravel 11 · PHP 8.3 · Filament 3 · PostgreSQL 16 · Fortify (2FA)
- **Detaljna specifikacija:** vidi
  [`Plan_aplikacije_udruzenje_zdravstvenih_radnika.md`](./Plan_aplikacije_udruzenje_zdravstvenih_radnika.md)

## Faze razvoja

1. **Faza 1 — MVP**: Auth + 2FA (admin/operater), CRUD članova + šifarnici,
   članarina (zaduženja, uplate, dug), edukacije + QR čekiranje + bodovi,
   interni dashboard, mesečni e-mail izveštaj.
2. **Faza 2**: Samouslužni portal člana, KME potvrde (PDF), napredni
   podsetnici, online plaćanje, CSV/Excel uvoz-izvoz.
3. **Faza 3**: REST API (Sanctum), mobilna aplikacija, napredna analitika.

## Pokretanje

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
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=uzrj_db
# DB_USERNAME=uzrj
# DB_PASSWORD=vasa_lozinka

# Pokretanje migracija i seed-ova
php artisan migrate
php artisan db:seed

# Frontend zavisnosti
npm install
npm run build

# Pokretanje razvojnog servera
php artisan serve
```

### Podrazumevani nalog

- **Email:** admin@uzrj.rs
- **Lozinka:** admin123
- **Uloga:** Administrator

> ⚠️ Promenite lozinku nakon prvog logovanja!

## Struktura projekta

```
uzrj/
├── app/
│   ├── Filament/
│   │   ├── Resources/        # Filament CRUD resursi
│   │   └── Widgets/          # Dashboard widgeti
│   ├── Models/               # Eloquent modeli
│   └── Providers/
├── database/
│   ├── migrations/           # Migracije baze
│   └── seeders/              # Inicijalni podaci
├── resources/
│   └── views/                # Blade šabloni
└── .env                      # Konfiguracija
```

## Model podataka

Glavne tabele:

| Tabela | Svrha |
|--------|-------|
| users | Nalozi za prijavu (admin/operater) |
| clanovi | Evidencija članova |
| licence | Podaci o licencama |
| clanarine | Zaduženja po članu |
| uplate | Istorija uplata |
| edukacije | KME programi |
| prisustva | Prijava/prisustvo + QR |
| bodovi | Knjiga bodova |
| aktuelnosti | Vesti |
| podesavanja | Konfiguracija (key-value) |
| audit_log | Trag izmena |

## Uloge i prava pristupa

| Uloga | Prava |
|-------|-------|
| Administrator | Pun pristup: konfiguracija, korisnici/uloge, šifarnici, svi podaci, audit, izvoz/backup |
| Operater | Operativni rad: unos članova, evidencija uplata, prisustvo, edukacije; bez sistemske konfiguracije |
| Član | U Fazi 1 NEMA pristup aplikaciji |

## Licenca

Interni projekat — prava određuje naručilac (udruženje).
