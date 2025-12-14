#!/bin/bash
# =============================================================================
# Setup Local Server Environment
# =============================================================================
# Configure your PC as the production server with custom domain
# - Configures /etc/hosts
# - Configures Nginx
# - Creates .env.production files with local domains
#
# Usage: sudo ./setup-local-domain.sh
# =============================================================================

set -e

# Local domain
DOMAIN="dms-redevabilite.dev"

# Project paths (Local "Server" paths)
BACKEND_PATH="/var/www/html/gfa/gfa_se_backend_webapp"
DMS_PATH="/var/www/html/gfa/gfa_dms"
ADMIN_PATH="/var/www/html/gfa/gfa_admin"

# Source Git Repositories (for cloning if needed)
BACKEND_REPO="git@gitlab.com:.../gfa_se_backend_webapp.git" # Replace with actual URL if known or assume user will copy
# Note: Since we don't have the exact git URL handy in the variables, we'll assume the user wants to copy from current workspace
# or we will just create the directory and user must clone manually or we copy from workspace.
# Copying from workspace is safer for "first setup".

WORKSPACE_DIR="/home/pc-1/workspaces/GFA"

echo "========================================="
echo "Configuring PC as server: $DOMAIN"
echo "Target paths: /var/www/html/gfa/*"
echo "========================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Please run with sudo: sudo ./setup-local-domain.sh"
    exit 1
fi

# =============================================================================
# 0. Setup Directories & Permissions
# =============================================================================
echo ""
echo "📝 Step 0: Setting up /var/www/html/gfa..."

mkdir -p /var/www/html/gfa
chown $SUDO_USER:$SUDO_USER /var/www/html/gfa

# Function to setup project dir
setup_project_dir() {
    target_path=$1
    source_path=$2
    name=$3
    
    if [ ! -d "$target_path" ]; then
        echo "  Creating $target_path..."
        if [ -d "$source_path" ]; then
            echo "  Copying from workspace..."
            cp -r "$source_path" "$target_path"
        else
            echo "  ⚠ Source $source_path not found. Creating empty directory."
            mkdir -p "$target_path"
        fi
        chown -R $SUDO_USER:$SUDO_USER "$target_path"
    else
        echo "  ✓ $name directory exists"
    fi
}

setup_project_dir "$BACKEND_PATH" "$WORKSPACE_DIR/gfa_se_backend_webapp" "Backend"
setup_project_dir "$DMS_PATH" "$WORKSPACE_DIR/gfa_dms" "DMS"
setup_project_dir "$ADMIN_PATH" "$WORKSPACE_DIR/gfa_admin" "Admin"

# =============================================================================
# 1. Configure /etc/hosts
# =============================================================================
echo ""
echo "📝 Step 1: Configuring /etc/hosts..."

if ! grep -q "$DOMAIN" /etc/hosts; then
    cat >> /etc/hosts << EOF

# GFA Local Server - Added by setup-local-domain.sh
127.0.0.1   $DOMAIN
127.0.0.1   ug.$DOMAIN
127.0.0.1   organisation.$DOMAIN
127.0.0.1   admin.$DOMAIN
EOF
    echo "  ✓ Added hosts entries"
else
    echo "  ⚠ Hosts entries already exist"
fi

# =============================================================================
# 2. Configure Nginx
# =============================================================================
echo ""
echo "📝 Step 2: Configuring Nginx..."

if ! command -v nginx &> /dev/null; then
    echo "  Installing nginx..."
    apt-get update -qq && apt-get install -y nginx
fi

cat > /etc/nginx/sites-available/$DOMAIN << 'EOF'
# Backend API (port 8080)
server {
    listen 80;
    server_name dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}

# Frontend UG (port 3000)
server {
    listen 80;
    server_name ug.dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
    }
}

# Frontend Organisation (port 3001)
server {
    listen 80;
    server_name organisation.dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:3001;
        proxy_set_header Host $host;
    }
}

# Frontend Admin (port 3002)
server {
    listen 80;
    server_name admin.dms-redevabilite.dev;
    
    location / {
        proxy_pass http://127.0.0.1:3002;
        proxy_set_header Host $host;
    }
}
EOF

ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
echo "  ✓ Nginx configured and reloaded"

# =============================================================================
# 3. Create Backend .env.production with local domains
# =============================================================================
echo ""
echo "📝 Step 3: Creating Backend .env.production..."

if [ -f "$BACKEND_PATH/.env.production.example" ]; then
    # Copy and replace domains
    cp "$BACKEND_PATH/.env.production.example" "$BACKEND_PATH/.env.production"
    
    # Replace production domains with local domains
    sed -i "s|https://dms-redevabilite.com:8443|http://$DOMAIN|g" "$BACKEND_PATH/.env.production"
    sed -i "s|https://ug.dms-redevabilite.com|http://ug.$DOMAIN|g" "$BACKEND_PATH/.env.production"
    sed -i "s|https://organisation.dms-redevabilite.com|http://organisation.$DOMAIN|g" "$BACKEND_PATH/.env.production"
    sed -i "s|https://admin.dms-redevabilite.com|http://admin.$DOMAIN|g" "$BACKEND_PATH/.env.production"
    
    chown $SUDO_USER:$SUDO_USER "$BACKEND_PATH/.env.production"
    echo "  ✓ Created $BACKEND_PATH/.env.production"
else
    echo "  ⚠ $BACKEND_PATH/.env.production.example not found"
fi

# =============================================================================
# 4. Create Frontend DMS .env.production with local API
# =============================================================================
echo ""
echo "📝 Step 4: Creating Frontend DMS .env.production..."

if [ -f "$DMS_PATH/.env.production.example" ]; then
    cp "$DMS_PATH/.env.production.example" "$DMS_PATH/.env.production"
    sed -i "s|https://dms-redevabilite.com:8443|http://$DOMAIN|g" "$DMS_PATH/.env.production"
    chown $SUDO_USER:$SUDO_USER "$DMS_PATH/.env.production"
    echo "  ✓ Created $DMS_PATH/.env.production"
else
    # Create new file
    cat > "$DMS_PATH/.env.production" << EOF
VITE_API_BASE_URL=http://$DOMAIN
VITE_PUSHER_APP_KEY=ae75f3a73201ef37c668
VITE_PUSHER_APP_CLUSTER=eu
VITE_PUSHER_BASE_URL=http://$DOMAIN
EOF
    chown $SUDO_USER:$SUDO_USER "$DMS_PATH/.env.production"
    echo "  ✓ Created $DMS_PATH/.env.production"
fi

# =============================================================================
# 5. Create Frontend Admin .env.production with local API
# =============================================================================
echo ""
echo "📝 Step 5: Creating Frontend Admin .env.production..."

if [ -f "$ADMIN_PATH/.env.production.example" ]; then
    cp "$ADMIN_PATH/.env.production.example" "$ADMIN_PATH/.env.production"
    sed -i "s|https://dms-redevabilite.com:8443|http://$DOMAIN|g" "$ADMIN_PATH/.env.production"
    chown $SUDO_USER:$SUDO_USER "$ADMIN_PATH/.env.production"
    echo "  ✓ Created $ADMIN_PATH/.env.production"
else
    cat > "$ADMIN_PATH/.env.production" << EOF
VITE_API_BASE_URL=http://$DOMAIN
VITE_PUSHER_APP_KEY=ae75f3a73201ef37c668
VITE_PUSHER_APP_CLUSTER=eu
VITE_PUSHER_BASE_URL=http://$DOMAIN
EOF
    chown $SUDO_USER:$SUDO_USER "$ADMIN_PATH/.env.production"
    echo "  ✓ Created $ADMIN_PATH/.env.production"
fi

# =============================================================================
# 6. Enable SSH server (for GitLab CI deployment)
# =============================================================================
echo ""
echo "📝 Step 6: Checking SSH server..."

if systemctl is-active --quiet ssh; then
    echo "  ✓ SSH server is running"
else
    echo "  Starting SSH server..."
    systemctl start ssh
    systemctl enable ssh
    echo "  ✓ SSH server started"
fi

# =============================================================================
# 7. Create Docker network if needed
# =============================================================================
echo ""
echo "📝 Step 7: Checking Docker network..."

if docker network ls | grep -q "gfa-network"; then
    echo "  ✓ gfa-network already exists"
else
    docker network create gfa-network
    echo "  ✓ Created gfa-network"
fi

# =============================================================================
# Done
# =============================================================================
echo ""
echo "========================================="
echo "✅ Setup complete!"
echo "========================================="
echo ""
echo "URLs:"
echo "  http://$DOMAIN              → Backend API"
echo "  http://ug.$DOMAIN           → Frontend UG"
echo "  http://organisation.$DOMAIN → Frontend Organisation"
echo "  http://admin.$DOMAIN        → Frontend Admin"
echo ""
echo "Source Paths (Workspace):"
echo "  $WORKSPACE_DIR"
echo ""
echo "Deploy Paths (Simulated Server):"
echo "  $BACKEND_PATH"
echo "  $DMS_PATH"
echo "  $ADMIN_PATH"
echo ""
echo "GitLab CI Variables to configure:"
echo "  DEPLOY_TARGET      = pc"
echo "  LOCAL_SSH_HOST     = 127.0.0.1"
echo "  LOCAL_SSH_USER     = $SUDO_USER"
echo "  LOCAL_SSH_PASSWORD = <your password>"
echo ""
echo "Start services (if testing locally without CI):"
echo "  cd $BACKEND_PATH && ./start-local.sh"
echo ""
