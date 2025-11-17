#!/usr/bin/env bash
set -euo pipefail

echo "Initialisation du dossier ./wp (tous les fichiers WordPress seront ici)"

if [ ! -f .env ]; then
  echo ".env not found. Copier .env.example -> .env et éditer avant d'initialiser."
  exit 1
fi

# Create wp directory if missing
mkdir -p wp

# If directory is essentially empty, download WordPress core
if [ -z "$(ls -A wp)" ]; then
  echo "Dossier wp vide — téléchargement de WordPress..."
  if command -v curl >/dev/null 2>&1 && command -v tar >/dev/null 2>&1; then
    curl -Ls https://wordpress.org/latest.tar.gz -o latest.tar.gz
    tar xzf latest.tar.gz -C wp --strip-components=1
    rm latest.tar.gz
  else
    echo "curl et/ou tar introuvables. Vous pouvez télécharger https://wordpress.org/latest.tar.gz manuellement et extraire dans ./wp"
    exit 1
  fi
else
  echo "Dossier wp non vide — sa vérification est terminée."
fi

echo "Création des répertoires et réglage des permissions..."
mkdir -p wp/wp-content/uploads

# Try to set ownership to www-data (uid 33) so WordPress peut mettre à jour et téléverser
if id -u www-data >/dev/null 2>&1; then
  chown -R www-data:www-data wp || true
else
  # fallback: set permissive permissions if www-data user doesn't exist on host
  chmod -R g+rwX wp || true
  chmod -R o+rwX wp || true
fi

echo "Initialisation terminée. Vous pouvez maintenant démarrer la stack : ./start.sh"
