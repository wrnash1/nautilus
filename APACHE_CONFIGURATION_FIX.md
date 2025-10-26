# Apache Configuration Fix for Nautilus

## Problem

The application homepage works at `https://pangolin.local/` but login routes fail:
- ❌ `https://pangolin.local/nautilus/public/store/login` → Route not found
- ❌ `https://pangolin.local/nautilus/public/account/login` → Route not found
- ✅ `https://pangolin.local/` → Works (shows homepage)

## Root Cause

Apache's DocumentRoot is pointing to `/var/www/html/nautilus/public` and serving it at the root (`/`), but the application router is not correctly detecting the base path.

## Solution Options

### Option 1: Configure .env File (RECOMMENDED)

Set the `APP_BASE_PATH` in the `.env` file to tell the router there's no base path:

1. Edit the .env file:
```bash
sudo nano /var/www/html/nautilus/.env
```

2. Add or update this line:
```env
APP_BASE_PATH=
```

This tells the router that the application is at the root level (no prefix).

3. Save and test:
```
https://pangolin.local/store/login
https://pangolin.local/account/login
```

### Option 2: Check Apache Virtual Host Configuration

Your Apache is configured to serve the application at the root. Verify the configuration:

```bash
# Find the virtual host configuration
sudo grep -r "pangolin.local" /etc/apache2/sites-enabled/
```

The DocumentRoot should be:
```apache
DocumentRoot /var/www/html/nautilus/public
```

### Option 3: Add Base Path to .env (If Using Subdirectory)

If you want to access the app at `https://pangolin.local/nautilus/public/`:

1. Edit Apache virtual host to use a different DocumentRoot
2. Or set APP_BASE_PATH:
```env
APP_BASE_PATH=/nautilus/public
```

## Immediate Fix to Test

### Step 1: Set APP_BASE_PATH in .env

```bash
cd /var/www/html/nautilus
sudo nano .env
```

Add this line at the top of the file:
```
APP_BASE_PATH=
```

Or if the file doesn't exist:
```bash
sudo cp /var/www/html/nautilus/.env.example /var/www/html/nautilus/.env
sudo nano /var/www/html/nautilus/.env
```

Make sure these are set:
```env
APP_BASE_PATH=
APP_ENV=local
APP_DEBUG=true
APP_URL=https://pangolin.local

DB_HOST=localhost
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

### Step 2: Test the URLs

After saving .env, test these URLs:

**Staff Login:**
```
https://pangolin.local/store/login
```

**Customer Login:**
```
https://pangolin.local/account/login
```

**Homepage:**
```
https://pangolin.local/
```

**Shop:**
```
https://pangolin.local/shop
```

## Additional Issue: Missing /shop/cart/count Route

The browser console shows: `Failed to load resource: /shop/cart/count:1 404`

This route is missing from the application. We need to add it.

### Fix: Add Missing Cart Count Route

1. Edit the routes file:
```bash
sudo nano /var/www/html/nautilus/routes/web.php
```

2. Find the Shop routes section (around line 195):
```php
$router->get('/shop', 'Shop\ShopController@index');
$router->get('/shop/product/{id}', 'Shop\ShopController@productDetail');
$router->post('/shop/cart/add', 'Shop\ShopController@addToCart', [CsrfMiddleware::class]);
$router->get('/shop/cart', 'Shop\ShopController@cart');
$router->post('/shop/cart/update', 'Shop\ShopController@updateCart', [CsrfMiddleware::class]);
```

3. Add this route after `/shop/cart`:
```php
$router->get('/shop/cart/count', 'Shop\ShopController@cartCount');
```

4. Add the method to ShopController:
```bash
sudo nano /var/www/html/nautilus/app/Controllers/Shop/ShopController.php
```

5. Add this method before the closing brace:
```php
public function cartCount()
{
    $cart = $this->cartService->getCart();
    $count = array_sum(array_column($cart, 'quantity'));

    header('Content-Type: application/json');
    echo json_encode(['count' => $count]);
}
```

## Expected Behavior After Fix

After applying the fixes:

✅ Staff login page accessible at: `https://pangolin.local/store/login`
✅ Customer login page accessible at: `https://pangolin.local/account/login`
✅ Homepage works at: `https://pangolin.local/`
✅ Shop works at: `https://pangolin.local/shop`
✅ Cart count API works at: `https://pangolin.local/shop/cart/count`

## Verification Commands

```bash
# Check if .env exists
ls -la /var/www/html/nautilus/.env

# View current .env settings
sudo cat /var/www/html/nautilus/.env

# Check Apache error log
sudo tail -50 /var/log/apache2/error.log

# Check application log
sudo tail -50 /var/www/html/nautilus/storage/logs/app.log
```

## Quick Test Script

Save this as `test-routes.sh` and run it:

```bash
#!/bin/bash

echo "Testing Nautilus Routes..."
echo ""

echo "1. Homepage:"
curl -s https://pangolin.local/ | head -5
echo ""

echo "2. Staff Login:"
curl -s https://pangolin.local/store/login
echo ""

echo "3. Customer Login:"
curl -s https://pangolin.local/account/login
echo ""

echo "4. Shop:"
curl -s https://pangolin.local/shop | head -5
echo ""

echo "5. Cart Count:"
curl -s https://pangolin.local/shop/cart/count
echo ""
```

Run it:
```bash
chmod +x test-routes.sh
./test-routes.sh
```

## Troubleshooting

### If routes still don't work after .env fix:

1. **Check PHP errors:**
```bash
sudo tail -100 /var/log/apache2/error.log | grep -i "nautilus"
```

2. **Enable debug mode in .env:**
```env
APP_DEBUG=true
APP_ENV=local
```

3. **Check mod_rewrite is enabled:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

4. **Verify .htaccess is being read:**
```bash
cat /var/www/html/nautilus/public/.htaccess
```

5. **Check Apache AllowOverride:**
```bash
sudo grep -A 10 "Directory /var/www" /etc/apache2/apache2.conf
```

Should show:
```apache
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

If it says `AllowOverride None`, change it to `AllowOverride All` and restart Apache.

---

**Apply Option 1 first (set APP_BASE_PATH in .env) and test. This should fix the routing issue immediately.**
