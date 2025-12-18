#!/bin/bash
# =============================================================================
# Setup Local Custom Domains with HTTPS
# =============================================================================
# Configure custom domains (dms-redevabilite.dev) on your PC
# GitLab CI will handle all deployment (git clone, docker-compose, etc.)
#
# This script ONLY configures:
# - /etc/hosts for custom domains
# - SSL certificates with mkcert
# - Nginx reverse proxy with HTTPS
# - SSH server (for GitLab CI access)
#
# Usage: sudo ./setup-local-domain.sh
# =============================================================================

set -e

DOMAIN="dms-redevabilite.dev"
SSL_DIR="/etc/nginx/ssl"

echo "========================================="
echo "Setup Custom Domains for Local Server"
echo "========================================="
echo ""
echo "This script configures:"
echo "  - /etc/hosts"
echo "  - SSL certificates (HTTPS)"
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
# 1. Detect Server IP Address
# =============================================================================
echo "📝 Step 1: Detecting server IP address..."

# Get the primary network IP (excluding localhost and docker IPs)
NETWORK_IP=$(hostname -I | awk '{print $1}')

echo ""
echo "Detected IP addresses:"
echo "  - Localhost: 127.0.0.1 (for local access)"
echo "  - Network IP: $NETWORK_IP (for network access)"
echo ""
echo "Configuring both IPs for maximum compatibility..."

# =============================================================================
# 2. Configure /etc/hosts
# =============================================================================
echo ""
echo "📝 Step 2: Configuring /etc/hosts..."

# Remove old entries if they exist
sed -i '/# GFA Custom Domains/,+8d' /etc/hosts

# Add new entries with both IPs
cat >> /etc/hosts <<EOF

# GFA Custom Domains - Added by setup-local-domain.sh
# Network access (from other machines)
$NETWORK_IP   $DOMAIN
$NETWORK_IP   ug.$DOMAIN
$NETWORK_IP   organisation.$DOMAIN
$NETWORK_IP   admin.$DOMAIN
# Local access (from this server)
127.0.0.1   $DOMAIN
127.0.0.1   ug.$DOMAIN
127.0.0.1   organisation.$DOMAIN
127.0.0.1   admin.$DOMAIN
EOF

echo "  ✓ Added domain entries with Network IP: $NETWORK_IP"
echo "  ✓ Added domain entries with Localhost: 127.0.0.1"

# =============================================================================
# 3. Install mkcert and Generate SSL Certificates
# =============================================================================
echo ""
echo "📝 Step 3: Setting up SSL certificates..."

# Install mkcert if not present
if ! command -v mkcert &> /dev/null; then
    echo "  Installing mkcert..."
    apt-get update -qq && apt-get install -y libnss3-tools wget
    wget -q https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-amd64 -O /usr/local/bin/mkcert
    chmod +x /usr/local/bin/mkcert
    echo "  ✓ mkcert installed"
fi

# Install local CA for the actual user (not root)
if ! sudo -u $SUDO_USER mkcert -CAROOT &> /dev/null; then
    echo "  Installing local Certificate Authority for user $SUDO_USER..."
    sudo -u $SUDO_USER mkcert -install
    echo "  ✓ Local CA installed"
else
    echo "  ⚠ Local CA already installed"
fi

# Create SSL directory
mkdir -p $SSL_DIR

# Generate certificates as the actual user, then copy to SSL directory
if [ ! -f "$SSL_DIR/$DOMAIN.crt" ]; then
    echo "  Generating SSL certificates..."
    
    # Generate in user's home directory (where they have write permissions)
    TEMP_DIR=$(sudo -u $SUDO_USER mktemp -d)
    cd $TEMP_DIR
    
    sudo -u $SUDO_USER mkcert \
        $DOMAIN \
        "*.$DOMAIN" \
        localhost \
        127.0.0.1 \
        ::1
    
    # Copy to SSL directory and rename
    cp ${DOMAIN}+4.pem $SSL_DIR/$DOMAIN.crt
    cp ${DOMAIN}+4-key.pem $SSL_DIR/$DOMAIN.key
    
    # Set proper permissions
    chmod 644 $SSL_DIR/$DOMAIN.crt
    chmod 600 $SSL_DIR/$DOMAIN.key
    
    # Cleanup temp directory
    rm -rf $TEMP_DIR
    
    echo "  ✓ SSL certificates generated"
else
    echo "  ⚠ SSL certificates already exist"
fi

# =============================================================================
# 4. Configure Nginx Reverse Proxy
# =============================================================================
echo ""
echo "📝 Step 4: Configuring Nginx..."

if ! command -v nginx &> /dev/null; then
    echo "  Installing nginx..."
    apt-get update -qq && apt-get install -y nginx
fi

cat > /etc/nginx/sites-available/$DOMAIN <<'EOF'
# Backend API - HTTPS (Docker port 8080 → HTTPS 443)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name dms-redevabilite.dev;
    
    ssl_certificate /etc/nginx/ssl/dms-redevabilite.dev.crt;
    ssl_certificate_key /etc/nginx/ssl/dms-redevabilite.dev.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Backend API - HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name dms-redevabilite.dev;
    return 301 https://$server_name$request_uri;
}

# Frontend UG - HTTPS (Docker port 3000 → HTTPS 443)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ug.dms-redevabilite.dev;
    
    ssl_certificate /etc/nginx/ssl/dms-redevabilite.dev.crt;
    ssl_certificate_key /etc/nginx/ssl/dms-redevabilite.dev.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Frontend UG - HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name ug.dms-redevabilite.dev;
    return 301 https://$server_name$request_uri;
}

# Frontend Organisation - HTTPS (Docker port 3001 → HTTPS 443)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name organisation.dms-redevabilite.dev;
    
    ssl_certificate /etc/nginx/ssl/dms-redevabilite.dev.crt;
    ssl_certificate_key /etc/nginx/ssl/dms-redevabilite.dev.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    location / {
        proxy_pass http://127.0.0.1:3001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Frontend Organisation - HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name organisation.dms-redevabilite.dev;
    return 301 https://$server_name$request_uri;
}

# Frontend Admin - HTTPS (Docker port 3002 → HTTPS 443)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name admin.dms-redevabilite.dev;
    
    ssl_certificate /etc/nginx/ssl/dms-redevabilite.dev.crt;
    ssl_certificate_key /etc/nginx/ssl/dms-redevabilite.dev.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    location / {
        proxy_pass http://127.0.0.1:3002;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Frontend Admin - HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name admin.dms-redevabilite.dev;
    return 301 https://$server_name$request_uri;
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
# 5. Enable SSH Server (for GitLab CI)
# =============================================================================
echo ""
echo "📝 Step 5: Checking SSH server..."

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
# 6. Create deployment directory
# =============================================================================
echo ""
echo "📝 Step 6: Creating deployment directory..."

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
echo "Server IPs configured:"
echo "  - Network IP: $NETWORK_IP (access from other machines)"
echo "  - Localhost: 127.0.0.1 (access from this server)"
echo ""
echo "Custom domains configured:"
echo "  https://$DOMAIN              → Backend API (port 8080)"
echo "  https://ug.$DOMAIN           → Frontend UG (port 3000)"
echo "  https://organisation.$DOMAIN → Frontend Org (port 3001)"
echo "  https://admin.$DOMAIN        → Frontend Admin (port 3002)"
echo "  http://localhost:8081        → phpMyAdmin"
echo ""
echo "SSL certificates:"
echo "  Certificate: $SSL_DIR/$DOMAIN.crt"
echo "  Private key: $SSL_DIR/$DOMAIN.key"
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
