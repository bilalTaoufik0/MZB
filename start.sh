#!/usr/bin/env bash
set -e

echo "Starting WordPress stack..."
docker compose up -d

echo "Done. Open http://localhost:${WORDPRESS_PORT:-8000}/ or http://<host>:${WORDPRESS_PORT:-8000}/"
