#!/bin/bash
# =============================================================================
# Setup Local Custom Domains
# =============================================================================
# Configure custom domains (dms-redevabilite.dev) on your PC
# GitLab CI will handle all deployment (git clone, docker-compose, etc.)
#
# This script ONLY configures:
# - /etc/hosts for custom domains
# - Nginx reverse proxy
# - SSH server (for GitLab CI access)
#
# Usage: sudo ./setup-local-domain.sh
# =============================================================================

set -e

DOMAIN="dms-redevabilite.dev"

echo "========================================="
echo "Setup Custom Domains for Local Server"
echo "========================================="
echo ""
echo "This script configures:"
echo "  - /etc/hosts"
echo "  - Nginx reverse proxy"
echo "  - SSH server"
echo ""
echo "GitLab CI will handle:"
echo "  - Git clone/pull"
echo "  - .env.production creation"
echo "  - Docker deployment"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Please run with sudo: sudo ./setup-local-domain.sh"
    exit 1
fi

# =============================================================================
# 1. Configure /etc/hosts
# =============================================================================
echo "📝 Step 1: Configuring /etc/hosts..."

if ! grep -q "$DOMAIN" /etc/hosts; then
    cat >> /etc/hosts << EOF

# GFA Custom Domains - Added by setup-local-domain.sh
127.0.0.1   $DOMAIN
127.0.0.1   ug.$DOMAIN
127.0.0.1   organisation.$DOMAIN
127.0.0.1   admin.$DOMAIN
EOF
    echo "  ✓ Added domain entries"
else
    echo "  ⚠ Domain entries already exist"
fi

# =============================================================================
# 2. Configure Nginx Reverse Proxy
# =============================================================================
echo ""
echo "📝 Step 2: Configuring Nginx..."

if ! command -v nginx &> /dev/null; then
    echo "  Installing nginx..."
    apt-get update -qq && apt-get install -y nginx
fi

cat > /etc/nginx/sites-available/$DOMAIN << 'EOF'
# Backend API (Docker port 8080 → HTTP 80)
server {
    listen 80;
    server_name dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Frontend UG (Docker port 3000 → HTTP 80)
server {
    listen 80;
    server_name ug.dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

# Frontend Organisation (Docker port 3001 → HTTP 80)
server {
    listen 80;
    server_name organisation.dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:3001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

# Frontend Admin (Docker port 3002 → HTTP 80)
server {
    listen 80;
    server_name admin.dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:3002;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/

# Test and reload
if nginx -t 2>/dev/null; then
    systemctl reload nginx
    echo "  ✓ Nginx configured and reloaded"
else
    echo "  ❌ Nginx configuration error"
    exit 1
fi

# =============================================================================
# 3. Enable SSH Server (for GitLab CI)
# =============================================================================
echo ""
echo "📝 Step 3: Checking SSH server..."

if ! command -v sshd &> /dev/null; then
    echo "  Installing openssh-server..."
    apt-get update -qq && apt-get install -y openssh-server
fi

if systemctl is-active --quiet ssh; then
    echo "  ✓ SSH server is running"
else
    echo "  Starting SSH server..."
    systemctl start ssh
    systemctl enable ssh
    echo "  ✓ SSH server started"
fi

# =============================================================================
# 4. Create deployment directory
# =============================================================================
echo ""
echo "📝 Step 4: Creating deployment directory..."

mkdir -p /var/www/html/gfa
chown -R $SUDO_USER:$SUDO_USER /var/www/html/gfa
echo "  ✓ Created /var/www/html/gfa/"

# =============================================================================
# Done
# =============================================================================
echo ""
echo "========================================="
echo "✅ Setup complete!"
echo "========================================="
echo ""
echo "Custom domains configured:"
echo "  http://$DOMAIN              → Backend API (port 8080)"
echo "  http://ug.$DOMAIN           → Frontend UG (port 3000)"
echo "  http://organisation.$DOMAIN → Frontend Org (port 3001)"
echo "  http://admin.$DOMAIN        → Frontend Admin (port 3002)"
echo ""
echo "Deployment directory:"
echo "  /var/www/html/gfa/"
echo ""
echo "Next steps:"
echo "  1. Configure GitLab CI Variables:"
echo "     - DEPLOY_TARGET = pc"
echo "     - LOCAL_SSH_HOST = 127.0.0.1"
echo "     - LOCAL_SSH_USER = $SUDO_USER"
echo "     - LOCAL_SSH_PASSWORD = <your password>"
echo ""
echo "  2. Push to trigger deployment:"
echo "     git push origin corine    # Backend"
echo "     git push origin tmp-ug    # Frontend UG"
echo "     git push origin main      # Frontend Admin"
echo ""
echo "GitLab CI will automatically:"
echo "  - Clone/pull code to /var/www/html/gfa/"
echo "  - Create .env.production"
echo "  - Run docker-compose up"
echo "  - Run health checks"
echo ""
