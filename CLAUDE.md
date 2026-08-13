# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Jezik projekta

Ceo projekat je na srpskom: nazivi klasa, modela, tabela, kolona i metoda
(`Clan`, `clanovi`, `BodoviService::ukupnoBodovaPeriod`), komentari, PHPDoc,
poruke korisniku i commit poruke. Novi kod prati istu konvenciju — ne uvoditi
engleske nazive u domenski sloj.

## Komande

```bash
# Razvojni server (server + queue + logovi + vite, sve odjednom)
composer dev

# Ili pojedinačno
php artisan serve
npm run dev

# Testovi (composer test prvo očisti config keš — bitno posle izmene .env/config)
composer test
php artisan test
php artisan test --filter=BodoviServiceTest
php artisan test tests/Feature/LicencaServiceTest.php
php artisan test --testsuite=Unit

# Formatiranje (Pint, podrazumevani Laravel preset — nema pint.json)
vendor/bin/pint
vendor/bin/pint app/Services/BodoviService.php

# Build frontenda
npm run build
```

Testovi koriste **zasebnu PostgreSQL bazu `uzrj_test`** (vidi `phpunit.xml`) —
PDO sqlite ekstenzija nije među preduslovima projekta. Baza se kreira jednom:

```bash
createdb -h 127.0.0.1 -U uzrj uzrj_test
```

Filament kešira konfiguraciju panela. Posle izmene teme, panela ili resursa:

```bash
php artisan optimize:clear
```

## Arhitektura

Laravel 13 + Filament 3 admin panel. **Nema javnog frontenda** — cela
aplikacija je Filament panel na `/admin` (`AdminPanelProvider`); `routes/web.php`
sadrži samo `/` (welcome) i dve Excel download rute. U Fazi 1 članovi nemaju
pristup aplikaciji, samo admin i operater.

### Servisni sloj je nosilac domenske logike

`app/Services/*` — statičke klase u kojima živi sva poslovna logika. Filament
resursi, Artisan komande i widgeti su tanki i pozivaju servise. Kad menjaš
pravila obračuna, menjaš servis, ne resurs.

| Servis | Odgovornost |
|---|---|
| `BodoviService` | Licencni period/godina, zbirovi bodova, pragovi, korekcije |
| `LicencaService` | Čuvanje/popunjavanje licence iz forme člana, status i datum isteka |
| `ClanarinaService` | Zaduženja po periodu, pro-rata, uplate, statusi dugovanja |
| `EdukacijaService` | Prijava, QR čekiranje, dodela bodova, ručno čekiranje |
| `EmailService` | Mesečni izveštaj, podsetnici za članarinu, upozorenja za bodove |

### Licencna godina, ne kalendarska — centralni koncept

Bodovi se ne grupišu po kalendarskoj godini nego po **licencnoj**, koja teče od
`licence.datum_izdavanja`. `BodoviService` izvodi ceo lanac:
`pocetakLicencnogPerioda()` → `pocetakLicencneGodine()` → `licencnaGodina()`
(kalendarska godina u kojoj licencna počinje — to je vrednost koja se upisuje u
`bodovi.licencna_godina`). Merodavna licenca je **najskorije izdata**
(`BodoviService::licenca()`), ne ona označena kao `vazeca`. Bodovi se **ne
prenose** iz jedne licencne godine u drugu. Član bez licence pada na fallback
po kalendarskoj godini.

### Podešavanja u bazi, ne u kodu

Pragovi i parametri se čitaju iz tabele `podesavanja` preko
`Podesavanje::get($kljuc, $default)` (tipizovano: integer/boolean/json/string).
Nikad ne hardkodovati ove vrednosti:

`godisnji_prag_bodova` (20) · `ukupan_prag_bodova` (140) ·
`licencni_period_god` (7) · `dani_pre_isteka_upozorenje` (60) ·
`vrsta_naplate_clanarine` · `pro_rata_racunanje` · podaci o udruženju

Uređuju se kroz `SystemConfiguration` stranicu i `PodesavanjeResource`.

### Licenca nije polje modela Clan

Licenca živi u zasebnoj tabeli (`Clan::licence()` je `hasMany`), a forma člana
je prikazuje kao ugnežden `licenca` niz. Zato stranice člana moraju ručno da
premoste formu i model:

- `mutateFormDataBeforeFill()` → `LicencaService::podaciZaFormu()` (Edit i View)
- `mutateFormDataBeforeSave()` / `mutateFormDataBeforeCreate()` → izdvoji `$data['licenca']`, `unset` pre snimanja
- `afterSave()` / `afterCreate()` → `LicencaService::sacuvaj()`

Videti [EditClan.php](app/Filament/Resources/ClanResource/Pages/EditClan.php) kao referentni obrazac.

### Prava pristupa

`spatie/laravel-permission` sa ulogama `admin`, `operater`, `clan`. Kontrola se
radi **po Filament klasi**, ne rutama: `canAccess()` na resursima i
`shouldRegisterNavigation()` na stranicama, uz `Auth::user()?->hasRole([...])`.
`CheckRole` middleware postoji za rute van panela. Administratorske stvari
(korisnici, podešavanja, audit, mail izveštaji) su zaključane na `admin`.

### Audit log je automatski

`AuditObserver` se u `AppServiceProvider::boot()` kači na sve modele iz
konstante `AppServiceProvider::AUDITOVANI_MODELI`. **Novi domenski model se
audituje tako što se doda u tu listu** — ne registruj observer ručno. Osetljivi
atributi (lozinke, 2FA tajne, `qr_token`) se filtriraju u observeru.

### Tema se čuva u bazi

`App\Support\Tema` čita boje iz `podesavanja` i primenjuje ih u
`AdminPanelProvider` (paleta + CSS varijable injektovane kroz `HEAD_END`
render hook). Zbog Filament keša, izmena teme traži `php artisan optimize:clear`.

### Uvoz članova iz Excel-a

`app/Imports/ClanImport.php` — najosetljiviji deo (najviše ispravki u istoriji).
Normalizuje srpske zapise datuma, Excel serijske datume, JMBG kao broj, puno ime
u jednoj koloni, prazne redove i licencu bez datuma izdavanja. Svaki red ide u
zasebnoj transakciji; greške se skupljaju, ne prekidaju uvoz. Pri izmeni obavezno
pokrenuti `ClanImportTest` i `ClanImportActionTest`.

### Zakazani poslovi

`routes/console.php` zakazuje tri komande iz `app/Console/Commands/`: mesečni
izveštaj, nedeljni podsetnik za članarinu i mesečno upozorenje za bodove. Sve
delegiraju na `EmailService`.

## Testovi

`RefreshDatabase` uz stvarni PostgreSQL. Filament stranice se testiraju kroz
`Livewire::test(EditClan::class, ...)` uz `Filament::setCurrentPanel()` i
prethodno kreiranu ulogu (`Role::findOrCreate('admin', 'web')`) — vidi
[ClanResourceLicencaTest.php](tests/Feature/ClanResourceLicencaTest.php).

Poznato ograničenje: date picker i modal za uplatu ne rade u headless browseru
(rade u Chrome/Firefox), pa se ti tokovi pokrivaju Livewire testovima, ne
browser testovima.

## Dokumentacija

- [DOKUMENTACIJA.md](DOKUMENTACIJA.md) — potpuna dokumentacija (baza, moduli, plan Faze 2, poznati problemi)
- [Plan_aplikacije_udruzenje_zdravstvenih_radnika.md](Plan_aplikacije_udruzenje_zdravstvenih_radnika.md) — originalna specifikacija
