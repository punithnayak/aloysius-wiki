#!/usr/bin/env bash
# Starts your own Aloysius Wiki: MediaWiki 1.43 + the three workshop extensions.
# Run it again any time. It reuses your wiki if it already exists.
set -euo pipefail
cd "$(dirname "$0")"

IMAGE="mediawiki:1.43"
NAME="aloysius-wiki"
PORT="${PORT:-8080}"
PASS="aloysius2026"

mkdir -p data
chmod 777 data
# The web server inside Docker runs as another user: let it read our files.
chmod -R a+rX workshop extensions 2>/dev/null || true

# Tell the wiki its public address, so links work in the browser.
SERVER=""
if [ -n "${WEB_HOST:-}" ]; then                       # Google Cloud Shell
  SERVER="https://$PORT-$WEB_HOST"
elif [ -n "${CODESPACE_NAME:-}" ]; then               # GitHub Codespaces
  SERVER="https://$CODESPACE_NAME-$PORT.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
fi
printf '%s' "$SERVER" > data/server.txt
chmod 644 data/server.txt

if docker ps -a --format '{{.Names}}' | grep -qx "$NAME"; then
  echo "▶ Starting your wiki again..."
  docker start "$NAME" >/dev/null
else
  echo "▶ Downloading MediaWiki and starting it (first time takes about a minute)..."
  docker run -d --name "$NAME" -p "$PORT:80" \
    -v "$PWD/data:/var/www/data" \
    -v "$PWD/workshop:/var/www/html/workshop" \
    -v "$PWD/extensions:/var/www/html/extensions" \
    "$IMAGE" >/dev/null
fi

# Same again from inside the container (works whoever owns the files)
docker exec "$NAME" chmod -R a+rX /var/www/html/workshop /var/www/html/extensions
if ! docker exec -u www-data "$NAME" test -r /var/www/html/workshop/Workshop.php; then
  echo "❌ MediaWiki cannot read workshop/Workshop.php. Run: sudo chmod -R a+rX workshop extensions"
  exit 1
fi

if [ ! -f data/LocalSettings.php ]; then
  echo "▶ Installing the wiki..."
  docker exec -u www-data "$NAME" php maintenance/run.php install \
    --dbtype=sqlite --dbpath=/var/www/data --confpath=/var/www/data \
    --server="http://localhost:$PORT" --scriptpath="" --lang=en \
    --pass="$PASS" "Aloysius Wiki" Admin >/dev/null
  docker exec -u www-data "$NAME" sh -c \
    'printf "\n// Workshop settings: edit workshop/Workshop.php, not this file.\nrequire_once \"\$IP/workshop/Workshop.php\";\n" >> /var/www/data/LocalSettings.php'
fi

# MediaWiki looks for /var/www/html/LocalSettings.php: point it at ours in data/
docker exec "$NAME" sh -c 'rm -f /var/www/html/LocalSettings.php && printf "<?php\nrequire \"/var/www/data/LocalSettings.php\";\n" > /var/www/html/LocalSettings.php && chmod 644 /var/www/html/LocalSettings.php'

CONF="--conf=/var/www/data/LocalSettings.php"
if [ ! -f data/.pages-added ]; then
  echo "▶ Adding the demo pages..."
  for f in workshop/pages/*.wiki; do
    title="$(basename "$f" .wiki | tr '_' ' ')"
    docker exec -i -u www-data "$NAME" php maintenance/run.php edit $CONF \
      --user Admin --summary "Workshop setup" "$title" < "$f" >/dev/null
  done
  docker exec -u www-data "$NAME" php maintenance/run.php initSiteStats $CONF --update >/dev/null
  touch data/.pages-added
fi

cat <<MSG

✅ Your wiki is running!

   Open it:      ${SERVER:-http://localhost:$PORT}

   Cloud Shell:  click the Web Preview button (top right, the square with an eye)
                 and choose "Preview on port $PORT".
   Codespaces:   open the "Ports" tab and click the globe next to $PORT.
   Your laptop:  http://localhost:$PORT

   Log in as:    Admin   /   $PASS

MSG
