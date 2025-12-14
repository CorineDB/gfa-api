#!/bin/bash
# Script de lancement local
# Usage: ./start-local.sh

echo "🚀 Starting local development environment..."

# Copier .env.example si .env n'existe pas
if [ ! -f .env ]; then
    echo "📝 Creating .env from .env.example..."
    cp .env.example .env
fi

# Créer le réseau Docker s'il n'existe pas
docker network create gfa-network 2>/dev/null || true

# Lancer docker-compose
echo "🐳 Starting containers..."
docker-compose up -d --build

echo "✅ Done!"
echo "   Backend API:    http://localhost:8080"
echo "   phpMyAdmin:     http://localhost:8081"
