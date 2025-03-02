#!/bin/bash
set -e

# Display environment variables for debugging (will be shown in container logs)
echo "Environment variables:"
echo "DB_HOST: $DB_HOST"
echo "DB_USER: $DB_USER"
echo "DB_NAME: $DB_NAME"
echo "Database password is set: $(if [ -z "$DB_PASSWORD" ]; then echo "No"; else echo "Yes"; fi)"

# Update database configuration
/usr/local/bin/update-db-config.sh

# Create uploads directory if it doesn't exist
if [ ! -d /var/www/html/uploads ]; then
    echo "Creating uploads directory..."
    mkdir -p /var/www/html/uploads
    chown www-data:www-data /var/www/html/uploads
    chmod 775 /var/www/html/uploads
    echo "Uploads directory created with proper permissions."
fi

# First arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- apache2-foreground "$@"
fi

exec "$@"
