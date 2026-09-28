#!/bin/bash
set -e

# Railway fournit le port à écouter via la variable $PORT
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Il n'y a pas de fichier .env dans ce conteneur (tout passe par les variables
# Railway), donc "php artisan key:generate" ne peut PAS être exécuté ici : il
# a besoin d'écrire dans un .env qui n'existe pas. La clé doit être générée en
# local une fois ("php artisan key:generate --show") puis collée dans les
# variables Railway sous le nom APP_KEY.
# Génère la clé d'application si elle n'existe pas encore (sécurité, normalement définie via Railway)
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi


# Vide les caches de config potentiellement obsolètes (venant de l'image buildée précédemment)
php artisan config:clear
php artisan route:clear

# Applique les migrations en attente (idempotent, sans danger à chaque redéploiement)
php artisan migrate --force

# Recompile les caches avec les vraies variables d'environnement de Railway
php artisan config:cache
php artisan route:cache

# Sécurité : s'assurer qu'un seul MPM (prefork) est actif au démarrage
rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf

echo "=== MPM actifs ==="
ls /etc/apache2/mods-enabled | grep -i mpm || echo "aucun fichier mpm trouvé"

exec apache2-foreground
