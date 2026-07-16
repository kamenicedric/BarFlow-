#!/bin/sh
set -e

# Railway (et la plupart des PaaS) fournissent le port via $PORT
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Migrations idempotentes (creation tables + colonnes + admin par defaut)
php /var/www/html/bin/migrate.php || echo "Migration ignoree (base indisponible ?)"

exec apache2-foreground
