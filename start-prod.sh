#!/bin/bash
# Script de lancement production (build local)
# Usage: ./start-prod.sh

echo "🏭 Starting PRODUCTION environment..."

# Vérifier que .env.production existe
if [ ! -f .env.production ]; then
    echo "❌ ERROR: .env.production not found!"
    echo "   Copy .env.production.example to .env.production and fill in values"
    echo "   cp .env.production.example .env.production"
    exit 1
fi

# Créer le réseau Docker s'il n'existe pas
docker network create gfa-network 2>/dev/null || true

# Lancer docker-compose avec config production
echo "🐳 Starting containers with production config..."
docker-compose --env-file .env.production \
  -f docker-compose.yml \
  -f docker-compose.prod.yml \
  up -d --build

echo "✅ Done!"
echo "   Backend API:    http://localhost:8080"
echo "   phpMyAdmin:     http://localhost:8081"
