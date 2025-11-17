# siteEcommerce-MZB

Un projet WordPress minimal prêt à l'emploi avec Docker, conçu pour être portable entre plusieurs PC sans repartir de zéro.

## Ce que contient ce dépôt

- `docker-compose.yml` : compose stack WordPress + MySQL
- `.env.example` : variables d'environnement à copier en `.env` et éditer
- `start.sh` / `stop.sh` : scripts simples pour démarrer/arrêter la stack
- `backup.sh` / `restore.sh` : sauvegarde et restauration (DB + fichiers WP)
- `.gitignore` : ignore les secrets et sauvegardes

## Prérequis

- Docker
- Docker Compose (v2 de préférence, accessible via `docker compose`)

Remarque importante sur les fichiers WordPress dans le dépôt

Ce projet peut contenir TOUTES les fichiers WordPress dans le dossier `./wp` (core, thèmes, plugins et uploads). Cela permet :

- d'avoir le site complet versionné dans le repo (portable),
- de redéployer sur une autre machine en copiant simplement le dossier `wp` + `backups/`.

Impacts et recommandations :

- Le dossier `./wp` peut devenir volumineux (thèmes, plugins, uploads). Pense au .gitignore pour fichiers volumineux si besoin.
- Lorsque WordPress est mis à jour depuis l'admin ou via WP-CLI, les fichiers du core changent — il faudra commit/ push ces changements pour les propager.
- Par défaut, le projet est configuré pour monter `./wp` dans le conteneur pour que WordPress puisse écrire (uploads, mises à jour). Utilise `./init_wp.sh` pour initialiser le dossier avant le premier démarrage.

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

## Sauvegarde

Pour sauvegarder la base et les fichiers WordPress :

```bash
./backup.sh
```

Les fichiers de sauvegarde sont placés dans `backups/`.

## Restauration

Exemple de restauration (fichiers présents dans `backups/`) :

```bash
./restore.sh backups/db_202501011200.sql backups/wp_files_202501011200.tar.gz
```

Note : arrêtez la stack si nécessaire avant de restaurer pour éviter des conflits.

## Déployer sur un autre PC (portabilité)

1. Sur la machine cible, installez Docker et Docker Compose.
2. Copiez ce dossier du projet (git clone ou archive) sur la machine cible.
3. Copiez `.env.example` en `.env` et ajustez les valeurs si besoin (mot de passe, port).
4. Si vous voulez transférer les données existantes (site en production), faites une sauvegarde sur la machine source (`./backup.sh`) et transférez `backups/*` sur la machine cible, puis restaurez (`./restore.sh ...`).
	- Si le dossier `./wp` est inclus dans le dépôt (recommandé pour portabilité), le core et tous les fichiers seront déjà présents après un `git clone`.
	- Si `./wp` n'est pas présent dans le dépôt, exécutez `./init_wp.sh` sur la machine cible pour télécharger le core WordPress et ajuster les permissions avant de démarrer.
5. Lancez `./start.sh` sur la machine cible.

Cette approche évite de recommencer la configuration de WordPress : la base de données et les fichiers peuvent être importés via les scripts.

## Conseils de sécurité / bonnes pratiques

- Ne commitez jamais votre fichier `.env` (il est listé dans `.gitignore`).
- Utilisez des mots de passe MySQL forts.
- Pour un déploiement en production, envisagez d'ajouter un reverse proxy, HTTPS (Let's Encrypt), et de ne pas exposer MySQL à l'extérieur.

## Fichiers importants

- `docker-compose.yml` — configuration des services
- `.env.example` — variables à personnaliser
- `backup.sh` / `restore.sh` — sauvegarde et restauration

---

Si tu veux, je peux :

- ajouter un Dockerfile personnalisé pour WordPress (thèmes/plugins préinstallés),
- ajouter une petite page d'installation automatique pour importer un dump SQL et fichiers au premier démarrage,
- ou créer des actions Makefile / GitHub Actions pour sauvegarder automatiquement.

Dis-moi ce que tu veux en plus.