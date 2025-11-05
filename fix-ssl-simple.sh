#!/bin/bash
# Simple SSL fix - disable default SSL vhost, keep only Nautilus

echo "Fixing SSL configuration (simple method)..."
echo ""

# Backup the original ssl.conf
if [ ! -f /etc/httpd/conf.d/ssl.conf.backup ]; then
    echo "Backing up original ssl.conf..."
    sudo cp /etc/httpd/conf.d/ssl.conf /etc/httpd/conf.d/ssl.conf.backup
fi

# Rename ssl.conf so it's not loaded (our nautilus.conf has SSL)
echo "Disabling default SSL configuration..."
sudo mv /etc/httpd/conf.d/ssl.conf /etc/httpd/conf.d/ssl.conf.disabled

echo "Testing Apache configuration..."
sudo apachectl configtest

if [ $? -eq 0 ]; then
    echo "✓ Apache configuration is valid"
    echo ""
    echo "Restarting Apache..."
    sudo systemctl restart httpd

    if systemctl is-active --quiet httpd; then
        echo "✓ Apache restarted successfully!"
        echo ""
        echo "=========================================="
        echo "🎉 Setup Complete!"
        echo "=========================================="
        echo ""
        echo "Access your application:"
        echo "  https://nautilus.local/store/login"
        echo "  OR"
        echo "  https://localhost/store/login"
        echo ""
        echo "Login credentials:"
        echo "  Email:    admin@nautilus.local"
        echo "  Password: password"
        echo ""
        echo "⚠️  You'll see a browser security warning (normal for self-signed certs)"
        echo "    Click 'Advanced' → 'Proceed to nautilus.local'"
        echo ""
    else
        echo "✗ Apache failed to start"
        sudo systemctl status httpd
        echo ""
        echo "To restore original config:"
        echo "  sudo mv /etc/httpd/conf.d/ssl.conf.disabled /etc/httpd/conf.d/ssl.conf"
    fi
else
    echo "✗ Configuration still has errors"
    sudo apachectl configtest
    echo ""
    echo "Restoring original ssl.conf..."
    sudo mv /etc/httpd/conf.d/ssl.conf.disabled /etc/httpd/conf.d/ssl.conf
fi
