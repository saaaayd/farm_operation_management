#!/bin/sh

cd /var/www/html

# Cache configuration, routes, and events
# We do this at runtime so that environment variables (like DB connection) from the platform are picked up.
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optional: Run migrations
# It's often safer to run this as a separate deployment step or job, 
# but for simple setups, you can uncomment this.
# php artisan migrate --force

# Start Docker
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
