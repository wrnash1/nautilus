#!/bin/bash
# Fix SSL configuration issue

echo "Fixing SSL configuration..."

# Generate localhost certificate that ssl.conf expects
sudo openssl req -new -newkey rsa:2048 -days 365 -nodes -x509 \
  -subj "/C=US/ST=State/L=City/O=Local/CN=localhost" \
  -keyout /etc/pki/tls/private/localhost.key \
  -out /etc/pki/tls/certs/localhost.crt

sudo chmod 600 /etc/pki/tls/private/localhost.key

echo "Testing Apache configuration..."
sudo apachectl configtest

if [ $? -eq 0 ]; then
    echo "✓ Apache configuration is valid"
    echo "Restarting Apache..."
    sudo systemctl restart httpd

    if systemctl is-active --quiet httpd; then
        echo "✓ Apache restarted successfully!"
        echo ""
        echo "Access your application at:"
        echo "  https://nautilus.local/store/login"
        echo ""
        echo "Login credentials:"
        echo "  Email:    admin@nautilus.local"
        echo "  Password: password"
    else
        echo "✗ Apache failed to start"
        sudo systemctl status httpd
    fi
else
    echo "✗ Configuration still has errors"
    sudo apachectl configtest
fi
