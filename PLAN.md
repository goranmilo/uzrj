# UZRJ - Plan Aplikacije
## Udruženje Zdravstvenih Radnika

---

## 1. Pregled

UZRJ je web aplikacija za upravljanje udruženjem zdravstvenih radnika.
Obuhvata evidenciju članova, članarine, edukaciju, bazu znanja i administraciju.

---

## 2. Tehnološki Stack

### Backend
| Komponenta | Tehnologija |
|------------|-------------|
| Jezik | Python 3.11+ |
| Framework | FastAPI |
| Baza podataka | PostgreSQL 16 |
| ORM | SQLAlchemy 2.0 (async) |
| Migracije | Alembic |
| Autentikacija | JWT (access + refresh tokeni) + bcrypt |
| 2FA | TOTP (pyotp) + QR kod (qrcode) |
| Email | SMTP (aiosmtplib) |
| Caching/Queue | Redis |
| Validacija | Pydantic v2 |
| Task queue | Celery (za email i pozadinske poslove) |

### Frontend
| Komponenta | Tehnologija |
|------------|-------------|
| Framework | React 18 + TypeScript |
| Build tool | Vite |
| Styling | Tailwind CSS |
| UI komponente | shadcn/ui |
| State management | Zustand |
| HTTP client | Axios + React Query |
| Routing | React Router v6 |
| Forme | React Hook Form + Zod |
| Charts | Recharts (dashboard) |

### Infrastruktura
| Komponenta | Tehnologija |
|------------|-------------|
| Kontejnerizacija | Docker + Docker Compose |
| Hosting | Proxmox LXC |
| Web server | Nginx (reverse proxy) |
| SSL | Let's Encrypt |
| Backup | pg_dump + rotacija |

---

## 3. Arhitektura

```
┌─────────────────────────────────────────────────┐
│                    NGINX                         │
│              (reverse proxy + SSL)               │
├────────────────────┬────────────────────────────┤
│    /api/*          │       /*                   │
│                    │                            │
│    FASTAPI         │       REACT                │
│    (port 8000)     │       (port 3000)          │
│                    │                            │
├────────────────────┴────────────────────────────┤
│                                                 │
│    ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│    │PostgreSQL│  │  Redis   │  │  Celery  │    │
│    │ (5432)   │  │  (6379)  │  │  Worker  │    │
│    └──────────┘  └──────────┘  └──────────┘    │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 4. Role i Prava Pristupa (RBAC)

| Rola | Opis | Prava |
|------|------|-------|
| super_admin | Sistemski administrator | SVE + upravljanje admin nalozima |
| admin | Administrator udruženja | CRUD članovi, članarina, edukacija, sadržaj, notifikacije |
| moderator | Moderator sadržaja | Upravljanje edukacijom, bazom znanja, objave |
| clan | Standardni član | Pregled profila, članarina, edukacija, baza znanja |
| guest | Javni pristup | Samo javne stranice (landing, prijava) |

### Matrica dozvola

| Resurs | super_admin | admin | moderator | clan | guest |
|--------|:-----------:|:-----:|:---------:|:----:|:-----:|
| Članovi (CRUD) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Članarina | ✅ | ✅ | ❌ | 👁️ | ❌ |
| Edukacija | ✅ | ✅ | ✅ | 👁️ | ❌ |
| Baza znanja | ✅ | ✅ | ✅ | 👁️ | 👁️ |
| Notifikacije | ✅ | ✅ | ✅ | ❌ | ❌ |
| Administracija | ✅ | ✅ | ❌ | ❌ | ❌ |
| Dashboard | ✅ | ✅ | ✅ | 👁️ | ❌ |
| Korisnički nalozi | ✅ | ✅ | ❌ | 👁️* | ❌ |

👁️ = čitanje | 👁️* = sopstveni profil

---

## 5. Moduli Aplikacije

### 5.1 Autentikacija (Auth)
- Login stranica (email + lozinka)
- Registracija (admin-om odobrena ili self-service)
- Reset lozinke putem emaila
- 2FA (TOTP - Google Authenticator)
- Refresh token rotacija
- Rate limiting na login pokušaje
- Audit log (prijave, odjave, promene)

### 5.2 Članovi
- CRUD operacije nad članovima
- Polja: ime, prezime, JMBG, email, telefon, adresa, datum rođenja,
  stručna sprema, radno mesto, odeljenje, datum učlanjenja, status
- Pretraga i filteri (po imenu, odeljenju, statusu, stručnoj spremi)
- Eksport u CSV/Excel
- Profil člana sa istorijom

### 5.3 Članarina
- Evidencija uplata (iznos, datum, period, način plačanja)
- Status članarine (plaćena, dospela, neplaćena)
- Automatski email podsetnici (30, 15, 7 dana pre dospeća)
- Izveštaji (ukupan prihod, po periodu, po odeljenju)
- Generisanje uplatnica (PDF)

### 5.4 Edukacija
- Kreiranje kurseva/seminara
- Prijava članova na edukacije
- Praćenje prisustva i napretka
- Sertifikati (PDF generisanje)
- Kalendar edukacija
- Automatske notifikacije (poziv, podsetnik, sertifikat)

### 5.5 Baza Znanja
- Dokumenti po kategorijama
- Pretraga po naslovu, sadržaju, tagovima
- Upload fajlova (PDF, DOC, slike)
- Verzionisanje dokumenata
- Komentari na dokumente

### 5.6 Notifikacije (Email)
- Email šabloni (HTML + tekst)
- Automatski okidači:
  - Dobrodošlica (novi član)
  - Podsetnik za članarinu
  - Poziv na edukaciju
  - Nova objava / aktuelnost
  - Reset lozinke
  - 2FA verifikacija
- Istorija poslatih emailova
- Queue sistem (Celery + Redis)

### 5.7 Administracija
- Odeljenja (CRUD)
- Stručne spreme (CRUD)
- Radna mesta (CRUD)
- Kategorije dokumenata
- Email šabloni
- Sistemska podešavanja
- Audit log pregled

### 5.8 Dashboard
- Ukupan broj članova (po statusu)
- Članarina (uplaćena/dospela/neplaćena)
- Predstojeće edukacije
- Poslednje aktivnosti
- Grafički prikazi (Recharts)

---

## 6. Baza Podataka - Ključne Tabele

```sql
-- Korisnici i autentikacija
users               (id, email, password_hash, role, is_active, 2fa_secret, ...)
sessions            (id, user_id, refresh_token, expires_at, ...)

-- Članovi
members             (id, user_id, ime, prezime, jmbg, email, telefon, adresa,
                     datum_rodjenja, strucna_sprema_id, radno_mesto_id,
                     odeljenje_id, datum_uclanjenja, status, ...)

-- Organizaciona struktura
odeljenja           (id, naziv, opis, is_active)
strucne_spreme      (id, naziv, nivo, is_active)
radna_mesta         (id, naziv, opis, is_active)

-- Članarina
clanarine           (id, member_id, iznos, period_od, period_do, datum_uplate,
                     nacin_placanja, status, ...)

-- Edukacija
edukacije           (id, naziv, opis, datum, trajanje, max_polaznika, ...)
prijave_edukacija   (id, edukacija_id, member_id, status, prisustvo, ...)
sertifikati         (id, prijava_id, broj, datum_izdavanja, ...)

-- Baza znanja
dokumenti           (id, naslov, sadrzaj, kategorija_id, author_id, ...)
kategorije          (id, naziv, parent_id, ...)
tags                (id, naziv)
document_tags       (document_id, tag_id)

-- Notifikacije
email_templates     (id, name, subject, body_html, body_text, ...)
email_logs          (id, recipient, template_id, status, sent_at, ...)
notifications       (id, user_id, type, message, is_read, ...)

-- Audit
audit_log           (id, user_id, action, resource, details, ip, created_at)
```

---

## 7. API Struktura

```
/api/v1/
├── /auth
│   ├── POST   /login
│   ├── POST   /register
│   ├── POST   /refresh
│   ├── POST   /logout
│   ├── POST   /forgot-password
│   ├── POST   /reset-password
│   ├── POST   /2fa/setup
│   ├── POST   /2fa/verify
│   └── POST   /2fa/disable
│
├── /users
│   ├── GET    /me
│   ├── PUT    /me
│   ├── GET    /                    (admin)
│   ├── PUT    /{id}/role           (super_admin)
│   └── DELETE /{id}               (super_admin)
│
├── /members
│   ├── GET    /
│   ├── POST   /
│   ├── GET    /{id}
│   ├── PUT    /{id}
│   ├── DELETE /{id}
│   ├── GET    /search
│   └── GET    /export
│
├── /clanarine
│   ├── GET    /
│   ├── POST   /
│   ├── GET    /{id}
│   ├── GET    /member/{member_id}
│   ├── GET    /overdue
│   └── POST   /send-reminders
│
├── /edukacije
│   ├── GET    /
│   ├── POST   /
│   ├── GET    /{id}
│   ├── PUT    /{id}
│   ├── DELETE /{id}
│   ├── POST   /{id}/register
│   ├── PUT    /{id}/attendance
│   └── GET    /{id}/certificate
│
├── /documents
│   ├── GET    /
│   ├── POST   /
│   ├── GET    /{id}
│   ├── PUT    /{id}
│   ├── DELETE /{id}
│   ├── GET    /categories
│   └── POST   /upload
│
├── /notifications
│   ├── GET    /
│   ├── PUT    /{id}/read
│   ├── PUT    /read-all
│   └── GET    /unread-count
│
├── /admin
│   ├── /odeljenja      (CRUD)
│   ├── /strucne-spreme (CRUD)
│   ├── /radna-mesta    (CRUD)
│   ├── /email-templates (CRUD)
│   └── /audit-log      (GET)
│
└── /dashboard
    ├── GET    /stats
    ├── GET    /charts
    └── GET    /recent-activity
```

---

## 8. Sigurnost

- HTTPS obavezan (Let's Encrypt)
- JWT kratkoročni access tokeni (15min) + dugoročni refresh (7d)
- bcrypt lozinke (cost factor 12)
- Rate limiting (5 login pokušaja / 15min)
- CORS whitelist
- CSP (Content Security Policy) headers
- SQL injection zaštitа (SQLAlchemy ORM)
- XSS zaštitа (React auto-escaping + CSP)
- CSRF tokeni za state-changing operacije
- Input validacija (Pydantic backend, Zod frontend)
- Audit log za sve kriticke operacije
- 2FA za admin naloge (obavezno), za članove (opcionalno)
- Enkripcija osetljivih podataka u bazi (JMBG)

---

## 9. Faze Razvoja

### Faza 1: Osnova (2-3 nedelje)
- Projektna struktura (backend + frontend + Docker)
- Baza podataka + migracije
- Auth sistem (login, registracija, JWT, 2FA)
- RBAC middleware
- Login stranica (frontend)
- Osnovni dashboard layout

### Faza 2: Članovi i Administracija (2 nedelje)
- CRUD članovi
- Administracija (odeljenja, stručne spreme, radna mesta)
- Pretraga i filteri
- Eksport podataka

### Faza 3: Članarina (1-2 nedelje)
- Evidencija uplata
- Status članarine
- Email podsetnici
- Izveštaji

### Faza 4: Edukacija (2 nedelje)
- Kursevi/seminari
- Prijava i praćenje
- Sertifikati (PDF)
- Kalendar

### Faza 5: Baza Znanja + Notifikacije (2 nedelje)
- Dokumenti i kategorije
- Pretraga
- Email šabloni i okidači
- Notification centar

### Faza 6: Dashboard i Poliranje (1-2 nedelje)
- Statistika i grafovi
- Mobile responsive
- Optimizacija performansi
- Security audit

---

## 10. Projektna Struktura

```
uzrj/
├── backend/
│   ├── app/
│   │   ├── __init__.py
│   │   ├── main.py              # FastAPI app
│   │   ├── config.py            # Settings
│   │   ├── database.py          # SQLAlchemy engine
│   │   ├── models/              # SQLAlchemy modeli
│   │   │   ├── user.py
│   │   │   ├── member.py
│   │   │   ├── clanarina.py
│   │   │   ├── edukacija.py
│   │   │   ├── document.py
│   │   │   └── notification.py
│   │   ├── schemas/             # Pydantic schema
│   │   │   ├── auth.py
│   │   │   ├── member.py
│   │   │   └── ...
│   │   ├── api/                 # API rute
│   │   │   ├── v1/
│   │   │   │   ├── auth.py
│   │   │   │   ├── members.py
│   │   │   │   ├── clanarine.py
│   │   │   │   ├── edukacije.py
│   │   │   │   ├── documents.py
│   │   │   │   ├── admin.py
│   │   │   │   └── dashboard.py
│   │   │   └── deps.py          # Dependencies
│   │   ├── services/            # Business logika
│   │   ├── middleware/           # RBAC, rate limit
│   │   ├── tasks/               # Celery taskovi
│   │   └── utils/               # Helperi
│   ├── alembic/                 # Migracije
│   ├── tests/
│   ├── requirements.txt
│   ├── Dockerfile
│   └── alembic.ini
│
├── frontend/
│   ├── src/
│   │   ├── components/          # Reusable komponente
│   │   ├── pages/               # Stranice
│   │   │   ├── Login.tsx
│   │   │   ├── Dashboard.tsx
│   │   │   ├── Members.tsx
│   │   │   ├── Clanarine.tsx
│   │   │   ├── Edukacije.tsx
│   │   │   ├── Documents.tsx
│   │   │   └── Admin.tsx
│   │   ├── hooks/               # Custom hooks
│   │   ├── stores/              # Zustand stores
│   │   ├── services/            # API pozivi
│   │   ├── types/               # TypeScript tipovi
│   │   ├── layouts/             # Layout komponente
│   │   └── lib/                 # Utility
│   ├── package.json
│   ├── tailwind.config.ts
│   ├── tsconfig.json
│   ├── vite.config.ts
│   └── Dockerfile
│
├── nginx/
│   └── nginx.conf
│
├── docker-compose.yml
├── docker-compose.prod.yml
├── .env.example
└── README.md
```

---

## 11. Docker Compose

```yaml
# docker-compose.yml
services:
  postgres:
    image: postgres:16
    volumes:
      - postgres_data:/var/lib/postgresql/data
    environment:
      POSTGRES_DB: uzrj
      POSTGRES_USER: uzrj
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    ports:
      - "5432:5432"

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  backend:
    build: ./backend
    depends_on:
      - postgres
      - redis
    environment:
      DATABASE_URL: postgresql+asyncpg://uzrj:${DB_PASSWORD}@postgres:5432/uzrj
      REDIS_URL: redis://redis:6379/0
      JWT_SECRET: ${JWT_SECRET}
      SMTP_HOST: ${SMTP_HOST}
      SMTP_PORT: ${SMTP_PORT}
      SMTP_USER: ${SMTP_USER}
      SMTP_PASSWORD: ${SMTP_PASSWORD}
    ports:
      - "8000:8000"

  celery:
    build: ./backend
    command: celery -A app.tasks worker -l info
    depends_on:
      - postgres
      - redis

  frontend:
    build: ./frontend
    ports:
      - "3000:3000"

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf
      - /etc/letsencrypt:/etc/letsencrypt
    depends_on:
      - backend
      - frontend

volumes:
  postgres_data:
```

---

Dokument sačuvan na: /root/uzrj/PLAN.md
