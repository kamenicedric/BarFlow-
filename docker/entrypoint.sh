#!/bin/sh
set -e

# Render / Railway : port dynamique
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Render fournit l'URL publique ; Railway via RAILWAY_PUBLIC_DOMAIN
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi
if [ -z "$APP_URL" ] && [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
  export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
fi

# Migrations : ne bloquent pas le demarrage Apache (portfolio / cold start)
attempt=1
max_attempts=6
while [ "$attempt" -le "$max_attempts" ]; do
  if php /var/www/html/bin/migrate.php; then
    break
  fi
  echo "[entrypoint] Migration echouee (tentative ${attempt}/${max_attempts}), nouvel essai dans 3s..."
  attempt=$((attempt + 1))
  sleep 3
done

# Eviter le conflit MPM au redemarrage
a2dismod mpm_event 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

exec apache2-foreground
