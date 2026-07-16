#!/bin/sh
set -e

# Render / Railway : port dynamique
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Render fournit l'URL publique (ex: https://barflow.onrender.com)
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Migrations : plusieurs tentatives (base externe parfois lente au demarrage)
attempt=1
max_attempts=12
while [ "$attempt" -le "$max_attempts" ]; do
  if php /var/www/html/bin/migrate.php; then
    break
  fi
  echo "[entrypoint] Migration echouee (tentative ${attempt}/${max_attempts}), nouvel essai dans 5s..."
  attempt=$((attempt + 1))
  sleep 5
done

exec apache2-foreground
