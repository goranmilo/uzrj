#!/usr/bin/env bash
#
# Objavljivanje nove verzije UZRJ aplikacije na serveru.
#
# Pokretanje (kao korisnik `deploy`, iz korena projekta):
#   ./deploy/deploy.sh            # povlači tekuću granu
#   ./deploy/deploy.sh main       # povlači zadatu granu
#
# Skripta staje na prvoj grešci i u svakom slučaju vraća aplikaciju iz
# režima održavanja.

set -euo pipefail

KOREN="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
GRANA="${1:-$(git -C "$KOREN" rev-parse --abbrev-ref HEAD)}"
PHP_FPM_SERVIS="php8.3-fpm"

cd "$KOREN"

poruka() { printf '\n\033[1;32m==>\033[0m %s\n' "$1"; }
upozorenje() { printf '\033[1;33m   %s\033[0m\n' "$1"; }

vrati_iz_odrzavanja() {
    if [ -f storage/framework/maintenance.php ]; then
        php artisan up || true
    fi
}
trap vrati_iz_odrzavanja EXIT

poruka "Provera radnog stabla"
if [ -n "$(git status --porcelain)" ]; then
    echo "Radno stablo nije čisto — izmene na serveru bi bile pregažene." >&2
    git status --short >&2
    exit 1
fi

poruka "Povlačenje grane $GRANA"
git fetch --prune origin
git checkout "$GRANA"
git pull --ff-only origin "$GRANA"

poruka "Režim održavanja"
php artisan down --retry=15

poruka "PHP zavisnosti"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

poruka "Migracije"
php artisan migrate --force

poruka "Asset-i i keš"
php artisan filament:assets
php artisan optimize:clear
php artisan optimize
php artisan filament:cache-components

poruka "Queue worker"
# Radnici sami preuzimaju signal i restartuju se posle tekućeg posla.
php artisan queue:restart

poruka "Izlazak iz režima održavanja"
php artisan up

# Opcache drži staru verziju PHP fajlova dok se FPM ne osveži.
poruka "Osvežavanje PHP-FPM"
if sudo -n systemctl reload "$PHP_FPM_SERVIS" 2>/dev/null; then
    echo "   $PHP_FPM_SERVIS osvežen."
else
    upozorenje "Nema prava za 'sudo systemctl reload $PHP_FPM_SERVIS'."
    upozorenje "Pokreni ručno ili dodaj u sudoers:"
    upozorenje "  deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload $PHP_FPM_SERVIS"
fi

poruka "Gotovo — verzija $(git rev-parse --short HEAD) je objavljena."
