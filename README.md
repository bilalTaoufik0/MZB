# siteEcommerce-MZB

Un projet WordPress minimal prêt à l'emploi avec Docker, conçu pour être portable entre plusieurs PC sans repartir de zéro.

## Prérequis

- Docker
- Docker Compose (v2 de préférence, accessible via `docker compose`)

## Installation rapide

1. Copier l'exemple d'environnement et l'éditer :

```bash
cp .env.example .env
# Éditez .env (mot de passe MySQL, port, etc.)
```

2. Rendre les scripts exécutables (une seule fois) :

```bash
chmod +x start.sh stop.sh backup.sh restore.sh
```

3. Démarrer le site :

```bash
./start.sh
```

Le site sera accessible sur http://localhost:8000 (ou le port défini dans `.env`).

Les fichiers de sauvegarde sont placés dans `backups/`.

## Restauration

Restauration des backups (fichiers présents dans `backups/`) :

```bash
./restore.sh backups/db_202512110052.sql backups/wp_files_202512110052.tar.gz
```

## Sauvegarde

Pour sauvegarder la base et les fichiers WordPress :

```bash
./backup.sh
```

## Stop les conteneurs

Exemple de restauration (fichiers présents dans `backups/`) :

```bash
./stop.sh
```

