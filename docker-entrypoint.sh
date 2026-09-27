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
if [ -z "$APP_KEY" ]; then
    echo "=================================================================="
    echo "ERREUR : la variable d'environnement APP_KEY n'est pas définie."
    echo "Génère-la en local avec : php artisan key:generate --show"
    echo "Puis ajoute-la dans Railway (Variables) sous le nom APP_KEY."
    echo "=================================================================="
    exit 1
fi

# Vide les caches de config potentiellement obsolètes (venant de l'image buildée précédemment)
php artisan config:clear
php artisan route:clear

# Applique les migrations en attente (idempotent, sans danger à chaque redéploiement)
php artisan migrate --force

# Recompile les caches avec les vraies variables d'environnement de Railway
php artisan config:cache
php artisan route:cache

exec apache2-foreground
