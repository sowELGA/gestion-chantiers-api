#!/bin/bash
set -e

# Railway fournit le port à écouter via la variable $PORT
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Pas de fichier .env dans ce conteneur : la clé doit venir des variables Railway
if [ -z "$APP_KEY" ]; then
    echo "=================================================================="
    echo "ERREUR : la variable d'environnement APP_KEY n'est pas définie."
    echo "Génère-la en local avec : php artisan key:generate --show"
    echo "Puis ajoute-la dans Railway (Variables) sous le nom APP_KEY."
    echo "=================================================================="
    exit 1
fi

# Vide les caches de config potentiellement obsolètes
php artisan config:clear
php artisan route:clear

# Applique les migrations en attente (idempotent)
php artisan migrate --force

# Création initiale des rôles et comptes (une seule fois, base vide uniquement)
if [ "$SEED_INITIAL" = "true" ]; then
    USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" | tail -n 1 | tr -d '[:space:]')
    if [ "$USER_COUNT" = "0" ]; then
        echo "=== Seed initial : rôles + comptes ==="
        php artisan db:seed --class=RoleSeeder --force
        php artisan db:seed --class=UserSeeder --force
    else
        echo "=== Seed ignoré : utilisateurs déjà présents ou comptage impossible (${USER_COUNT}) ==="
    fi
fi

# Recompile les caches avec les vraies variables d'environnement
php artisan config:cache
php artisan route:cache

# Un seul MPM Apache actif (prefork)
rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf

exec apache2-foreground