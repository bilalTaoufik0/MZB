#!/usr/bin/env bash
set -e

echo "Stopping WordPress stack..."
docker compose down

echo "Stopped."
