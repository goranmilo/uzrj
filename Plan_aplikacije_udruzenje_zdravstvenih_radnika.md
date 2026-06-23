# Aplikacija za upravljanje članstvom strukovnog udruženja zdravstvenih radnika

**Dokument za planiranje i tehničku specifikaciju**

_Verzija 1.0 — radni dokument za razvoj (hand-off za Claude Code) · Jun 2026_

---

## Sadržaj

- [1. Uvod i kontekst](#1-uvod-i-kontekst)
  - [1.1. Priroda organizacije (ključno za arhitekturu)](#11-priroda-organizacije-ključno-za-arhitekturu)
- [2. Pravni okvir relevantan za aplikaciju](#2-pravni-okvir-relevantan-za-aplikaciju)
  - [2.1. Licenca i bodovi (KME)](#21-licenca-i-bodovi-kme)
  - [2.2. Sadržaj evidencije člana (potvrda/sertifikat)](#22-sadržaj-evidencije-člana-potvrdasertifikat)
  - [2.3. Zaštita podataka (GDPR / ZZPL)](#23-zaštita-podataka-gdpr-zzpl)
- [3. Predlozi tehnološkog steka](#3-predlozi-tehnološkog-steka)
  - [3.1. Varijanta A — Laravel + Livewire (preporučeno za MVP)](#31-varijanta-a-laravel-+-livewire-preporučeno-za-mvp)
  - [3.2. Varijanta B — NestJS (API) + React/Next.js](#32-varijanta-b-nestjs-api-+-reactnextjs)
  - [3.3. Varijanta C — Postojeće open-source rešenje + prilagođavanje](#33-varijanta-c-postojeće-open-source-rešenje-+-prilagođavanje)
  - [3.4. Preporuka](#34-preporuka)
- [4. Pregled postojećih sličnih rešenja](#4-pregled-postojećih-sličnih-rešenja)
  - [4.1. Zaključak istraživanja](#41-zaključak-istraživanja)
- [5. Funkcionalni moduli](#5-funkcionalni-moduli)
  - [5.1. Evidencija članstva](#51-evidencija-članstva)
  - [5.2. Evidencija članarine](#52-evidencija-članarine)
  - [5.3. Profesionalne edukacije (KME)](#53-profesionalne-edukacije-kme)
  - [5.4. Prisustvo i bodovni sistem](#54-prisustvo-i-bodovni-sistem)
  - [5.5. Prijava i 2FA](#55-prijava-i-2fa)
  - [5.6. Administrativni modul](#56-administrativni-modul)
  - [5.7. Dashboard (interni — za administratore i operatere)](#57-dashboard-interni-za-administratore-i-operatere)
  - [5.8. E-mail izveštavanje (glavni kanal ka članovima)](#58-e-mail-izveštavanje-glavni-kanal-ka-članovima)
- [6. Model podataka (pregled)](#6-model-podataka-pregled)
- [7. Uloge i prava pristupa](#7-uloge-i-prava-pristupa)
- [8. Nefunkcionalni zahtevi](#8-nefunkcionalni-zahtevi)
- [9. Predlog faza razvoja (roadmap)](#9-predlog-faza-razvoja-roadmap)
  - [Faza 1 — MVP (inicijalna verzija)](#faza-1-mvp-inicijalna-verzija)
  - [Faza 2 — Poboljšanja](#faza-2-poboljšanja)
  - [Faza 3 — Napredno](#faza-3-napredno)
- [10. Dodatni predlozi poboljšanja](#10-dodatni-predlozi-poboljšanja)
- [11. Napomene za implementaciju (hand-off)](#11-napomene-za-implementaciju-hand-off)

---

## 1. Uvod i kontekst

Ovaj dokument definiše funkcionalni i tehnički okvir za izradu CRUD aplikacije namenjene dobrovoljnom strukovnom udruženju zdravstvenih radnika. Aplikacija pokriva evidenciju članstva, naplatu članarine, organizaciju profesionalnih (KME) edukacija, evidenciju prisustva i jedinstven bodovni sistem, uz prijavu sa dvofaktorskom autentifikacijom (2FA) za administrativno osoblje, administrativni modul za konfiguraciju, interni dashboard i e-mail izveštavanje članova. U ovoj fazi članovi nemaju nalog/login — informišu se e-mailom.

Dokument je namenjen kao osnova koja se prosleđuje Claude Code-u (ili drugom modelu/timu) na dalji razvoj. Sadrži predloge tehnološkog steka u nekoliko varijanti sa obrazloženjima, model podataka, opis modula, pravni okvir relevantan za bodove i licence, pregled postojećih sličnih rešenja i predloge poboljšanja po fazama.

### 1.1. Priroda organizacije (ključno za arhitekturu)

**Organizacija je dobrovoljno strukovno udruženje, a NE komora. Ovo je bitna distinkcija:**

- Komora (npr. KMSZTS, LKS) je obavezna organizacija sa javnim ovlašćenjima: vodi imenik članova, izdaje/obnavlja/oduzima licence i zvanično evidentira KME bodove. Aplikacija NE preuzima ove funkcije.

- Udruženje organizuje akreditovane programe kontinuirane edukacije (KME), naplaćuje članarinu i prati aktivnost članova kroz prisustvo edukacijama. Bodovi koje članovi ostvaruju na edukacijama udruženja jesu zvanični KME bodovi (jer su programi akreditovani od strane Zdravstvenog saveta Srbije), ali se zvanično knjiže kod nadležne komore.

Posledica za aplikaciju: aplikacija vodi jedinstven bodovni sistem — jedan tok bodova po prisustvu edukacijama, sa praćenjem licencnog perioda i godišnjeg/ukupnog praga. Vrednosti pragova (godišnji minimum i ukupno za period) su konfigurabilne u administrativnom delu, jer broj poena koji član treba da skupi tokom godine još nije definisan. Detaljnije u poglavlju 5.

## 2. Pravni okvir relevantan za aplikaciju

Sledeća pravila proizlaze iz Zakona o komorama zdravstvenih radnika, Pravilnika o bližim uslovima za izdavanje, obnavljanje ili oduzimanje licence (Sl. glasnik RS 76/2022) i Pravilnika o sprovođenju kontinuirane edukacije (Sl. glasnik RS 17/2022). Ona oblikuju logiku bodovnog sistema i evidencije.

### 2.1. Licenca i bodovi (KME)

|** Pravilo** |** Vrednost / opis** |
| --- | --- |
| Trajanje licence | 7 godina (licencni period). Izuzetak: 70+ godina → 1 godina. |
| Ukupno bodova za obnovu | 140 bodova u licencnom periodu (20 bodova za 70+). |
| Godišnji minimum | Najmanje 10 bodova u svakoj licencnoj godini. |
| Prenos bodova | Bodovi se NE prenose iz jedne licencne godine u drugu. |
| Više programa | 140 bodova mora biti iz više različitih akreditovanih programa. |
| Rok za obnovu | Zahtev za obnavljanje najkasnije 60 dana pre isteka licence. |
| Akreditacija | Programe akredituje Zdravstveni savet Srbije; potvrde sadrže ev. broj akreditacije. |
| Posledica neispunjenja | Ako nije skupljeno 140 (ili 10/god) → polaganje licencnog ispita. |

**Napomena o konfigurabilnosti: vrednosti u tabeli (140, 10, 7 godina, 60 dana) predstavljaju zakonski okvir za zvaničnu obnovu licence kod komore. U aplikaciji se pragovi NE hardkoduju — godišnji prag bodova i ukupan prag za licencni period podešavaju se u administrativnom delu. Podrazumevani godišnji prag postavljen je na 20 bodova (izmenjivo), jer tačan broj poena koji član treba da skupi tokom godine za potrebe udruženja još nije konačno utvrđen.**

*Implikacija: aplikacija treba da za svakog člana zna datum izdavanja licence, da računa tekuću licencnu godinu, zbir bodova po godini i po periodu, i da generiše upozorenja (npr. „nedovoljno bodova u tekućoj licencnoj godini“, „licenca ističe za **<** 60 dana“) na osnovu konfigurisanih pragova.*

### 2.2. Sadržaj evidencije člana (potvrda/sertifikat)

Potvrda o učešću na KME programu, prema pravilniku, mora da sadrži: naziv organizatora i matični broj, mesto i datum, naziv teme, vrstu edukacije, broj rešenja/evidencioni broj akreditacije, broj dodeljenih bodova, ime/prezime i broj licence člana, pečat i potpis. Aplikacija treba da može da generiše ovakvu potvrdu (PDF) za svaku održanu edukaciju.

### 2.3. Zaštita podataka (GDPR / ZZPL)

Aplikacija obrađuje podatke o ličnosti (JMBG, kontakt, podaci o zaposlenju, zdravstvena struka). Mora biti usklađena sa Zakonom o zaštiti podataka o ličnosti (ZZPL, „Sl. glasnik RS“ 87/2018), koji je harmonizovan sa GDPR. Obavezno: pravni osnov obrade (članstvo/saglasnost), enkripcija osetljivih polja, audit log pristupa, pravo na brisanje i izvoz, retencija podataka i ograničenje pristupa po ulogama.

## 3. Predlozi tehnološkog steka

U nastavku su tri varijante steka. Sve tri zadovoljavaju zahteve (CRUD, 2FA, role, dashboard, mail). Razlikuju se po brzini razvoja, ceni održavanja i potrebnim veštinama tima.

### 3.1. Varijanta A — Laravel + Livewire (preporučeno za MVP)

| **Sloj** |** Tehnologija** |
| --- | --- |
| Backend | Laravel 11 (PHP 8.3) |
| Frontend | Blade + Livewire 3 (+ Alpine.js), Tailwind CSS |
| 2FA / Auth | Laravel Fortify (TOTP, recovery kodovi) + Sanctum |
| Role/Permisije | spatie/laravel-permission |
| Baza | PostgreSQL 16 (ili MySQL 8) |
| Mail/Queue | Laravel Mail + Queue (Redis), Laravel Scheduler za izveštaje |
| PDF | barryvdh/laravel-dompdf (potvrde, izveštaji) |
| Admin UI | Filament 3 (gotov admin panel nad Eloquent modelima) |

Zašto: najbrži put do funkcionalnog MVP-a. Fortify rešava 2FA „out of the box“, Filament daje kompletan administrativni modul uz minimalan kod, spatie/permission pokriva uloge. Jedan jezik (PHP) na celom steku, lako hostovanje (deljeni ili VPS), velika zajednica u regionu.

Mane: monolit; teže skaliranje na vrlo velike timove; Livewire je server-rendered (manje pogodan za bogato offline/mobilno iskustvo).

### 3.2. Varijanta B — NestJS (API) + React/Next.js

|** Sloj** |** Tehnologija** |
| --- | --- |
| Backend | NestJS (Node.js/TypeScript), Prisma ORM |
| Frontend | Next.js (React) + TypeScript, Tailwind, shadcn/ui |
| 2FA / Auth | Passport + otplib (TOTP), JWT + refresh tokeni |
| Role/Permisije | CASL ili nest-access-control |
| Baza | PostgreSQL 16 |
| Mail/Queue | BullMQ (Redis) + Nodemailer / Resend |
| PDF | Puppeteer ili pdfmake |

Zašto: čista podela frontend/backend, isti jezik (TypeScript) na obe strane, odlično za buduću mobilnu aplikaciju (React Native dele logiku) i za API koji koriste i drugi sistemi. Bolje za veće timove i dugoročno skaliranje.

Mane: više „glue“ koda i konfiguracije; nema gotovog admin panela ranga Filamenta — administrativni modul se gradi ručno; sporiji MVP.

### 3.3. Varijanta C — Postojeće open-source rešenje + prilagođavanje

Umesto gradnje od nule, uzeti zrelu AMS (Association Management Software) osnovu i prilagoditi je. Pregled u poglavlju 4. Najozbiljniji kandidat je Tendenci (Django/Python, aktivno održavan, MIT).

Zašto: članstvo, evente, plaćanja i direktorijum dobijate gotove. Štedi mesece rada na generičkim delovima.

Mane: specifičnosti srpskog KME sistema (licencni period, 140/10 bodova, akreditacioni brojevi, JMBG validacija, ćirilica, lokalni platni promet) i dalje se moraju dograditi. Prilagođavanje tuđeg modela podataka ume da bude skuplje od ciljane gradnje. Rizik od „zaključavanja“ u tuđu arhitekturu.

### 3.4. Preporuka

**Za prvu verziju preporučuje se Varijanta A (Laravel + Livewire + Filament).**

- Najbrži MVP uz najmanje rizika; administrativni modul (zahtev iz specifikacije) dobija se kroz Filament gotovo besplatno.

- 2FA, uloge i mail su rešeni zrelim paketima — manje custom bezbednosnog koda znači manje grešaka.

- Ako se kasnije ukaže potreba za zasebnom mobilnom aplikacijom ili javnim API-jem, Laravel lako izlaže REST/JSON API (Sanctum) bez prepravke domena.

*Varijantu B birati ako je od starta planiran ozbiljan API + zasebna mobilna aplikacija i postoji jak TypeScript tim. Varijantu C razmotriti samo ako je vremenski pritisak veliki i KME-specifičnosti se mogu tretirati kao dogradnja.*

## 4. Pregled postojećih sličnih rešenja

Istraženo je tržište softvera za upravljanje članstvom (membership / association management). Nijedan proizvod ne pokriva srpski KME/licencni sistem „iz kutije“, ali nekoliko ih nudi solidnu osnovu ili ideje za funkcionalnosti.

| **Rešenje** |** Tip / licenca** |** Relevantnost za nas** |
| --- | --- | --- |
| Tendenci | Open-source (Django), MIT | Najzreliji OSS AMS: članstvo, eventi, plaćanja, direktorijum, mejlovi. Dobra osnova za Varijantu C. |
| CiviCRM | Open-source (CRM za NPO) | Moćan za članstvo i kontribucije; integriše se sa Drupal/WordPress. Kompleksan za održavanje. |
| Admidio | Open-source (PHP) | Lagano, za manje organizacije: korisnici, uloge, eventi. Skroman za naprednije izveštaje. |
| Pino (pinomembers) | Open-source (Drupal) | Jednostavno članstvo + mejling; pogodno za male asocijacije. |
| Zenbership | Open-source (PHP) | Funkcionalno, ali napušten razvoj (od 2018) — ne preporučuje se za nove projekte. |
| Wild Apricot / Join It / ClubExpress | SaaS, komercijalni | Bogate funkcije (članarine, eventi, automatizacija), ali zatvoreni, mesečna pretplata, podaci van naše kontrole, bez KME logike. |
| MemberSuite / GrowthZone | SaaS (AMS) | Imaju CEU/sertifikaciono praćenje — koncept blizak KME bodovima; skupo i nije lokalizovano. |

### 4.1. Zaključak istraživanja

- Generičke funkcije (članstvo, eventi, plaćanja, mejling) su „rešen problem“ — postoji više zrelih OSS i SaaS opcija.

- Diferencijator i glavni deo posla je domenska logika srpskog KME sistema: licencni period 7g, 140/10 bodova, akreditacioni brojevi, generisanje propisanih potvrda, JMBG/OKG/licenca, ćirilica i lokalni platni promet.

- Preporuka: graditi ciljano (Varijanta A), a ideje za module (direktorijum članova, automatske obnove, podsetnici) pozajmiti iz Tendenci/Wild Apricot.

## 5. Funkcionalni moduli

### 5.1. Evidencija članstva

- CRUD nad članovima sa poljima sa forme: ime, prezime, JMBG, OKG (broj komore), licenca, e-mail, telefon, sprema, zvanje, odeljenje.

- Kategorija članarine: bira se ručno pri unosu/izmeni člana (npr. zaposlen/nezaposlen/penzioner/počasni); određuje iznos zaduženja.

- Šifarnici (konfigurabilni u admin delu): stručna sprema, zvanje, odeljenje.

- Status člana: aktivan / neaktivan / suspendovan; datum učlanjenja; broj članske karte.

- Podaci o licenci: broj, datum izdavanja, datum isteka (auto +7 god), status (važeća/ističe/istekla).

- Validacije: JMBG (kontrolna cifra), jedinstvenost JMBG/e-maila, format licence.

- Uvoz/izvoz: CSV/Excel import postojeće baze, izvoz direktorijuma.

### 5.2. Evidencija članarine

**Model članarine je fleksibilan i konfigurabilan u admin delu:**

- **Period naplate (konfigurabilno):** admin bira da li se članarina naplaćuje godišnje, mesečno ili kvartalno.

- **Iznos po kategoriji člana:** iznos zavisi od kategorije člana (npr. po zvanju/spremi ili statusu — zaposlen/nezaposlen/penzioner/počasni). Kategorije i njihovi iznosi definišu se u admin delu (šifarnik kategorija članarine).

- **Srazmerno (pro-rata):** član koji se učlani usred perioda zadužuje se srazmerno preostalom delu perioda, a ne punim iznosom.

- Admin definiše periode i iznose; po članu i periodu vodi se: zaduženje, uplaćeno, ostatak duga, status (plaćeno/delimično/dug).

- Istorija pojedinačnih uplata (audit): iznos, datum, način, referenca, ko je evidentirao.

- Automatski obračun ukupnog dugovanja i prikaz na dashboard-u i u mejlu.

- Opciono (kasnija faza): online plaćanje (lokalni provajder), generisanje uplatnice sa pozivom na broj.

### 5.3. Profesionalne edukacije (KME)

- CRUD edukacija: naziv, opis, datum/vreme, lokacija, kapacitet, predavači.

- Akreditacija: broj rešenja/evidencioni broj Zdravstvenog saveta, vrsta KME, dodeljeni bodovi po programu, ciljna grupa (zvanje/sprema).

- Status: planirana / održana / otkazana.

- Prijava članova (sa kapacitetom i listom čekanja).

- **QR kod mejlom pri prijavi:** svakom članu koji se prijavi na edukaciju automatski se generiše jedinstven QR kod (vezan za par član+edukacija) i šalje na e-mail (sa detaljima događaja). QR kod služi za brzo čekiranje prisustva na ulazu.

- Generisanje propisane potvrde/sertifikata (PDF) sa svim obaveznim poljima iz tačke 2.2.

### 5.4. Prisustvo i bodovni sistem

**Aplikacija vodi jedinstven bodovni sistem — jedan tok bodova koji članovi ostvaruju prisustvom na edukacijama. Pragovi (godišnji minimum i ukupno za licencni period) su konfigurabilni u admin delu.**

- **Čekiranje prisustva skeniranjem QR koda:** na ulazu se skenira QR kod iz člana mejla (telefonom/čitačem). Sistem trenutno evidentira prisustvo i dodeljuje bodove. Namenjeno skupovima sa više stotina učesnika — drastično ubrzava evidenciju u odnosu na ručno označavanje.

- Ručno čekiranje ostaje kao rezerva (npr. član bez mejla/QR koda): operater pretražuje listu i označava prisustvo.

- Zaštita od duplog skeniranja i skeniranja tuđeg/nevažećeg koda; evidencija vremena ulaska.

- Automatsko dodeljivanje bodova pri potvrdi prisustva na održanoj edukaciji.

- Obračun po licencnoj godini i licencnom periodu; indikatori: ukupno u periodu, u tekućoj godini, preostalo do ukupnog praga, da li je ispunjen godišnji minimum.

- **Konfigurabilni pragovi:** godišnji prag bodova i ukupan prag za period podešavaju se u administrativnom delu. Podrazumevani godišnji prag je 20 bodova (uz mogućnost izmene); ova vrednost je polazna jer ciljani broj poena udruženja još nije konačno definisan.

- Upozorenja: ispod godišnjeg praga u tekućoj licencnoj godini; licenca ističe za < 60 dana; rizik od licencnog ispita.

- Korekcije bodova (ručni unos uz razlog, audit).

### 5.5. Prijava i 2FA

**U ovoj fazi razvoja pravo prijave (login) imaju samo administratori i operateri. Članovi NEMAJU nalog niti pristup aplikaciji — njihovu evidenciju vode operateri, a članovi se informišu isključivo putem e-mail izveštaja.**

- E-mail + lozinka, pa obavezan drugi faktor (TOTP — Google Authenticator/Authy) za sve naloge (admin i operater).

- Recovery kodovi; opciono e-mail OTP kao rezerva.

- Politika lozinki, rate-limiting, zaključavanje naloga, „remember device“.

- Uloge sa pravom prijave: administrator i operater. (Samouslužni portal člana je predviđen za kasniju fazu — vidi poglavlje 9.)

### 5.6. Administrativni modul

- Upravljanje korisnicima i ulogama (admin / operater / član).

- Konfiguracija: periodi i vrsta naplate članarine (godišnje/mesečno/kvartalno), kategorije članarine i iznosi po kategoriji, pro-rata pravilo; pragovi bodova (godišnji podrazumevano 20, ukupni), KME parametri (period licence), šifarnici (sprema/zvanje/odeljenje).

- Šabloni e-mail izveštaja i podsetnika; učestalost slanja.

- Upravljanje aktuelnostima (vesti).

- Audit log (ko je šta menjao), izvoz podataka, backup.

### 5.7. Dashboard (interni — za administratore i operatere)

Pošto članovi u ovoj fazi nemaju login, dashboard je interni alat za administratore i operatere. Sadržaj koji bi inače video član prikazuje se operateru po izabranom članu, a članu se isti pregled dostavlja e-mailom (poglavlje 5.8).

- Aktuelnosti (vesti udruženja).

- Pregled po članu: status članarine (plaćeno / dugovanje sa iznosom).

- Predstojeće edukacije i evidencija prijava.

- Izveštaj o održanim/posećenim edukacijama + ukupan broj bodova i napredak ka konfigurisanom pragu.

- Agregatni pregled za upravu: broj članova, naplata članarine, popunjenost edukacija, članovi ispod godišnjeg praga bodova.

### 5.8. E-mail izveštavanje (glavni kanal ka članovima)

Budući da članovi nemaju login, e-mail je primarni način informisanja članova. Svaki član dobija personalizovani izveštaj sa sekcijama koje bi inače video na dashboard-u.

- Periodični izveštaj (npr. mesečni): aktuelnosti, status članarine/dug, predstojeće edukacije, pregled posećenih edukacija i sakupljeni bodovi sa napretkom ka pragu.

- Podsetnici: dospela/neplaćena članarina; predstojeća edukacija na koju je član prijavljen; nedovoljno bodova u licencnoj godini; skori istek licence.

- Log poslatih izveštaja; poštovanje saglasnosti i opt-out (ZZPL).

## 6. Model podataka (pregled)

Glavne tabele i njihova svrha. Detaljne migracije se generišu u fazi implementacije.

|** Tabela** |** Svrha** |** Ključna polja** |
| --- | --- | --- |
| users | Nalozi za prijavu | ime, prezime, email, password, role_id, 2fa_secret, aktivan |
| roles | Uloge | admin / operater / clan |
| clanovi | Članstvo | ime, prezime, jmbg, okg, licenca, email, telefon, sprema_id, zvanje_id, odeljenje_id, kategorija_clanarine_id, status, datum_uclanjenja |
| licence | Podaci o licenci člana | clan_id, broj, datum_izdavanja, datum_isteka, status |
| spreme / zvanja / odeljenja | Šifarnici | naziv, aktivno, redosled |
| clanarina_kategorije | Kategorije članarine sa iznosom | naziv (npr. zaposlen/nezaposlen/penzioner/počasni), iznos, aktivno |
| clanarina_periodi | Definicija perioda članarine | naziv, vrsta (godišnje/mesečno/kvartalno), vazi_od, vazi_do |
| clanarine | Zaduženje po članu | clan_id, period_id, kategorija_id, iznos_zaduzenja (pro-rata), iznos_placen, status |
| uplate | Istorija uplata | clanarina_id, iznos, datum, nacin, referenca, evidentirao |
| edukacije | KME programi/eventi | naziv, datum, lokacija, kapacitet, akreditacioni_broj, bodovi, vrsta_kme, status |
| prisustva | Prijava/prisustvo + QR | edukacija_id, clan_id, prijavljen, prisutan, qr_token (jedinstven), qr_poslat_at, vreme_cekiranja, dodeljeni_bodovi |
| bodovi | Knjiga bodova (ledger) | clan_id, edukacija_id, bodovi, licencna_godina, datum |
| aktuelnosti | Vesti | naslov, sadrzaj, datum_objave, objavljeno |
| podesavanja | Konfiguracija (key-value) | kljuc, vrednost, tip (npr. godisnji_prag_bodova=20, ukupan_prag_bodova, licencni_period_god, vrsta_naplate_clanarine) |
| mail_izvestaji | Log poslatih mejlova | clan_id, tip, poslat_at, status |
| audit_log | Trag izmena | user_id, akcija, entitet, pre/posle, vreme |

## 7. Uloge i prava pristupa

|** Uloga** |** Prava** |
| --- | --- |
| Administrator | Pun pristup: konfiguracija, korisnici/uloge, šifarnici, svi podaci, audit, izvoz/backup. |
| Operater | Operativni rad: unos članova, evidencija uplata, prisustvo, edukacije; bez sistemske konfiguracije. |
| Član | U ovoj fazi NEMA pristup aplikaciji (bez naloga). Evidenciju vodi operater; član se informiše e-mailom. Samouslužni portal je predviđen za kasniju fazu. |

## 8. Nefunkcionalni zahtevi

- Bezbednost: 2FA, hešovanje lozinki (bcrypt/argon2), enkripcija osetljivih polja (JMBG), HTTPS, zaštita od CSRF/XSS/SQLi, rate-limiting.

- Privatnost (ZZPL/GDPR): pravni osnov obrade, audit pristupa, retencija, pravo na izvoz i brisanje, opt-out na mejlove.

- Lokalizacija: srpski jezik, podrška za ćirilicu i latinicu, lokalni format datuma i valute (RSD).

- Performanse: paginacija i indeksi za velike spiskove članova; pozadinski redovi (queue) za mejlove i PDF.

- Pouzdanost: dnevni backup baze, monitoring, logovanje grešaka.

- Pristupačnost i responzivnost: upotrebljivo na mobilnom (članovi pristupaju dashboard-u sa telefona).

## 9. Predlog faza razvoja (roadmap)

### Faza 1 — MVP (inicijalna verzija)

- Auth + 2FA, uloge — login samo za administratore i operatere (članovi bez naloga).

- CRUD članova + šifarnici + podaci o licenci.

- Članarina: periodi, zaduženja, uplate, dug.

- Edukacije + prijava + QR kod mejlom + čekiranje prisustva skeniranjem QR koda + dodela bodova (jedinstven tok).

- Interni dashboard (za osoblje) + aktuelnosti.

- Administrativni modul (Filament) + podešavanja, uključujući konfigurabilne pragove bodova (godišnji i ukupni).

- Mesečni e-mail izveštaj članovima + osnovni podsetnici.

### Faza 2 — Poboljšanja

- Samouslužni portal člana (login za članove, sopstveni dashboard, prijava na edukacije, preuzimanje potvrda).

- Generisanje propisanih KME potvrda (PDF) i izveštaja za komoru.

- Napredni podsetnici (godišnji minimum, istek licence < 60 dana).

- Online plaćanje članarine (lokalni provajder), uplatnice sa pozivom na broj.

- Izvoz/uvoz (CSV/Excel), direktorijum članova sa filterima.

### Faza 3 — Napredno

- Javni REST API (Sanctum) i/ili zasebna mobilna aplikacija.

- Gejmifikacija bodova (rang-lista, bedževi, priznanja).

- Integracija sa kalendarom, e-učenje/testovi (elektronski KME).

- Napredna analitika i izveštaji za upravu udruženja.

- Automatska provera/sinhronizacija statusa licence.

## 10. Dodatni predlozi poboljšanja

- **Samouslužni portal člana (Faza 2):** kada članovi dobiju login, sami ažuriraju kontakt, prijavljuju se na edukacije i preuzimaju potvrde — smanjuje administrativni teret. U Fazi 1 ovo rade operateri.

- **„Health check“ licence:** vizuelni indikator (zeleno/žuto/crveno) napretka ka 140 bodova i godišnjem minimumu, sa projekcijom da li će član stići do obnove.

- **Automatske uplatnice i podsetnici za dug:** generisanje uplatnice (poziv na broj po članu) i zakazani mejl podsetnici za dospela dugovanja.

- **QR čekiranje (Faza 1):** brza evidencija prisustva na velikim skupovima (par stotina učesnika) skeniranjem QR koda koji je član dobio mejlom pri prijavi.

- **Audit i usaglašenost:** potpun audit log i alat za izvoz/brisanje podataka člana radi ZZPL usaglašenosti.

- **Šifarnici i konfiguracija bez koda:** svi parametri (iznosi, pravila bodova, KME pragovi, šabloni mejlova) menjaju se kroz admin UI, bez izmene koda.

- **Dvojezičnost pisma:** podrška za ćirilicu i latinicu u prikazu i PDF potvrdama.

## 11. Napomene za implementaciju (hand-off)

Smernice za model/tim koji preuzima razvoj (npr. Claude Code):

- Početi od Faze 1 (MVP), Varijanta A steka, osim ako naručilac odluči drugačije.

- Pragove bodova (godišnji i ukupni za period) i ostale domenske konstante (period licence, 60 dana) staviti u konfiguraciju (tabela podesavanja), ne hardkodovati. Godišnji prag postaviti na podrazumevanih 20 bodova, uz mogućnost izmene u admin delu.

- Članarina: vrsta naplate je konfigurabilna (godišnje/mesečno/kvartalno), iznos zavisi od kategorije člana (tabela clanarina_kategorije, ručni izbor pri unosu člana), a zaduženje za člana koji se učlani usred perioda računa se srazmerno (pro-rata).

- QR prisustvo: pri prijavi na edukaciju generisati jedinstven, neponovljiv qr_token po paru član+edukacija i poslati ga mejlom. Čekiranje na ulazu skeniranjem koda mora biti otporno na duplo skeniranje i nevažeće/tuđe kodove, sa evidencijom vremena. Predvideti ručno čekiranje kao rezervu.

- Bodovni sistem je jedinstven (jedan tok bodova po prisustvu). Ne uvoditi paralelne tokove. U modelu bodova čuvati licencna_godina radi obračuna godišnjeg minimuma.

- Login i 2FA implementirati samo za uloge administrator i operater. Članovi u ovoj fazi nemaju nalog; predvideti da se portal člana doda u Fazi 2 bez prepravke domena.

- Bezbednosne funkcije (2FA, uloge) graditi na zrelim paketima (Fortify, spatie/permission), ne ručno.

- Generisanje KME potvrde implementirati tako da pokriva sva obavezna polja iz tačke 2.2.

- Predvideti uvoz postojeće baze članova (CSV/Excel) na samom početku.

- Sve tekstove i šifarnike pripremiti za ćirilicu/latinicu i RSD format.

*Ovaj dokument je polazna specifikacija i očekuje se da se precizira tokom razvoja kroz konsultacije sa naručiocem (npr. tačan model članarine, da li je 2FA obavezan za sve uloge, izbor platnog provajdera).*
