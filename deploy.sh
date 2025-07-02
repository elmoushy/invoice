# Install PHP 8.1+ and extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-gd php8.1-curl php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-intl php8.1-redis

#Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

#Install Node.js and npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

#Install PHP Project dependencies
cd /invoice
composer install --no-dev --optimize-autoloader

# ================================
# DEPLOYMENT STEPS AFTER INSTALLATION
# ================================

# 1. Set up environment configuration
cp .env.example .env

# 2. Generate application key
php artisan key:generate

# 3. Configure database settings in .env file
# For SQLite (simpler for deployment):
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_HOST=127.0.0.1/#DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=3306/#DB_PORT=3306/' .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=\/invoice\/database\/database.sqlite/' .env
sed -i 's/DB_USERNAME=root/#DB_USERNAME=root/' .env
sed -i 's/DB_PASSWORD=/#DB_PASSWORD=/' .env

# Create SQLite database file if it doesn't exist
touch database/database.sqlite

# For MySQL (uncomment and edit these lines instead):
# Edit .env file with your database credentials:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database_name
# DB_USERNAME=your_db_username
# DB_PASSWORD=your_db_password

# 4. Install Node.js dependencies and build assets
npm install
npm run build

# 5. Run database migrations
php artisan migrate --force

# 6. Seed database (optional)
# php artisan db:seed --force

# 7. Create storage symlink
php artisan storage:link

# 5. Set up database
# Create database (if using MySQL)
sudo mysql -u root -p -e "CREATE DATABASE invoice_db;"
sudo mysql -u root -p -e "GRANT ALL PRIVILEGES ON invoice_db.* TO 'invoice_user'@'localhost' IDENTIFIED BY 'your_password';"
sudo mysql -u root -p -e "FLUSH PRIVILEGES;"

# 6. Run database migrations
php artisan migrate

# 7. Seed database (if you have seeders)
php artisan db:seed

# 8. Set proper permissions
sudo chown -R www-data:www-data /invoice/storage
sudo chown -R www-data:www-data /invoice/bootstrap/cache
sudo chmod -R 775 /invoice/storage
sudo chmod -R 775 /invoice/bootstrap/cache

# 9. Clear and cache configurations
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Set up web server (Apache example)
sudo apt install -y apache2
sudo a2enmod rewrite
sudo systemctl start apache2
sudo systemctl enable apache2

# Create virtual host configuration
sudo tee /etc/apache2/sites-available/invoice.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /invoice/public
    
    <Directory /invoice/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/invoice_error.log
    CustomLog \${APACHE_LOG_DIR}/invoice_access.log combined
</VirtualHost>
EOF

# Enable the site
sudo a2ensite invoice.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2

# 11. Set up SSL (optional but recommended)
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com

# 12. Set up process manager for queue workers (if using queues)
sudo apt install -y supervisor
sudo tee /etc/supervisor/conf.d/invoice-worker.conf > /dev/null <<EOF
[program:invoice-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /invoice/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/invoice/storage/logs/worker.log
stopwaitsecs=3600
EOF

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start invoice-worker:*

# 13. Set up scheduled tasks (Laravel Cron)
sudo crontab -e
# Add this line:
# * * * * * cd /invoice && php artisan schedule:run >> /dev/null 2>&1

# 14. Final security and performance optimizations
sudo apt install -y ufw
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw --force enable

# Install and configure Redis (if needed)
sudo apt install -y redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server

# ================================
# QUICK START COMMANDS
# ================================

# For development/testing (runs on port 8000):
php artisan serve --host=0.0.0.0 --port=8000

# Check application status:
php artisan about

# View logs:
tail -f /invoice/storage/logs/laravel.log
