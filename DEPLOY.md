# UZRJ - Docker Deployment

## Brzo pokretanje (Development)

```bash
# 1. Kopiraj .env fajl
cp .env.example .env

# 2. Izmeni .env sa tvojim podešavanjima

# 3. Pokreni sve servise
docker compose up -d

# 4. Proveri status
docker compose ps

# 5. Pristupi aplikaciji
# Frontend: http://localhost:3000
# Backend API: http://localhost:8000
# API Docs: http://localhost:8000/docs
```

## Production deployment

```bash
# 1. Kopiraj i izmeni .env
cp .env.example .env
# Obavezno promeni:
# - POSTGRES_PASSWORD
# - REDIS_PASSWORD
# - SECRET_KEY
# - SMTP podešavanja
# - DOMAIN

# 2. Pokreni u production modu
docker compose -f docker-compose.prod.yml up -d

# 3. SSL sertifikat (Let's Encrypt)
# Instaliraj certbot na host-u:
apt install certbot

# Generiši sertifikat:
certbot certonly --webroot -w /var/lib/docker/volumes/uzrj_certbot_data/_data \
  -d uzrj.rs -d www.uzrj.rs

# Kopiraj sertifikate:
mkdir -p nginx/ssl
cp /etc/letsencrypt/live/uzrj.rs/fullchain.pem nginx/ssl/
cp /etc/letsencrypt/live/uzrj.rs/privkey.pem nginx/ssl/

# Odkomentariši SSL linije u nginx/nginx.conf

# Restartuj nginx:
docker compose restart nginx
```

## Korisne komande

```bash
# Pokreni sve
docker compose up -d

# Zaustavi sve
docker compose down

# Restartuj jedan servis
docker compose restart backend

# Prati logove
docker compose logs -f backend
docker compose logs -f celery

# Uđi u backend kontejner
docker compose exec backend bash

# Pokreni migracije
docker compose exec backend alembic upgrade head

# Kreiraj novu migraciju
docker compose exec backend alembic revision --autogenerate -m "opis"

# Backup baze
docker compose exec postgres pg_dump -U uzrj uzrj > backup_$(date +%Y%m%d).sql

# Restore baze
docker compose exec -T postgres psql -U uzrj uzrj < backup.sql
```

## Troubleshooting

```bash
# Proveri health statuse
docker compose ps

# Proveri resurse
docker stats

# Restartuj sve
docker compose down && docker compose up -d

# Obriši volume i kreni ispočetka
docker compose down -v
docker compose up -d
```
