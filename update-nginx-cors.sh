#!/bin/bash
# Update system Nginx configuration to preserve CORS headers

echo "Updating system Nginx configuration for CORS headers..."

sudo tee /etc/nginx/sites-available/dms-redevabilite.dev > /dev/null << 'EOF'
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
        
        # Preserve CORS headers from backend
        proxy_pass_header Access-Control-Allow-Origin;
        proxy_pass_header Access-Control-Allow-Methods;
        proxy_pass_header Access-Control-Allow-Headers;
        proxy_pass_header Access-Control-Allow-Credentials;
        proxy_pass_header Access-Control-Max-Age;
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

echo "Testing Nginx configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "Reloading Nginx..."
    sudo systemctl reload nginx
    echo "✅ System Nginx updated successfully!"
    echo ""
    echo "Test CORS headers with:"
    echo "curl -I -H 'Origin: https://admin.dms-redevabilite.dev' https://dms-redevabilite.dev/sanctum/csrf-cookie -k"
else
    echo "❌ Nginx configuration error!"
    exit 1
fi
