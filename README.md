# UZRJ - Udruženje Zdravstvenih Radnika

Web aplikacija za upravljanje udruženjem zdravstvenih radnika.

## Funkcionalnosti

- **Autentikacija** - Login, registracija, 2FA, RBAC
- **Članovi** - Evidencija, pretraga, eksport
- **Članarina** - Uplate, status, automatski podsetnici
- **Edukacija** - Kursevi, sertifikati, kalendar
- **Baza znanja** - Dokumenti, kategorije, pretraga
- **Notifikacije** - Email šabloni, automatski okidači
- **Administracija** - Odeljenja, stručne spreme, radna mesta
- **Dashboard** - Statistika, grafovi, brzi pregledi

## Tehnološki Stack

| Komponenta | Tehnologija |
|------------|-------------|
| Backend | Python 3.11 + FastAPI + SQLAlchemy 2.0 |
| Frontend | React 18 + TypeScript + Vite + Tailwind CSS + shadcn/ui |
| Baza | PostgreSQL 16 |
| Cache/Queue | Redis + Celery |
| Email | SMTP (aiosmtplib) |
| Auth | JWT + bcrypt + TOTP 2FA |
| Infra | Docker + Nginx |

## Pokretanje

```bash
# Development
docker compose up -d

# Backend
cd backend && pip install -r requirements.txt
uvicorn app.main:app --reload

# Frontend
cd frontend && npm install && npm run dev
```

## Struktura

```
uzrj/
├── backend/         # FastAPI backend
├── frontend/        # React frontend
├── nginx/           # Nginx konfiguracija
├── docker-compose.yml
└── PLAN.md          # Detaljan plan aplikacije
```

## Autor

goranmilo
