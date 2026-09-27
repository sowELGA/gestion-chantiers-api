#!/bin/bash
set -e

# Railway fournit le port à écouter via la variable $PORT
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

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

exec apache2-foreground
