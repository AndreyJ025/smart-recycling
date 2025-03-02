#!/bin/bash
set -e

# Check if database.php exists
if [ -f /var/www/html/database.php ]; then
    echo "Updating database configuration..."
    
    # Create backup of original file
    cp /var/www/html/database.php /var/www/html/database.php.bak
    
    # Make sed commands more robust by using different delimiters
    sed -i "s|\$host = ['\"][^'\"]*['\"];|\$host = '$DB_HOST';|" /var/www/html/database.php
    sed -i "s|\$user = ['\"][^'\"]*['\"];|\$user = '$DB_USER';|" /var/www/html/database.php
    sed -i "s|\$password = ['\"][^'\"]*['\"];|\$password = '$DB_PASSWORD';|" /var/www/html/database.php
    sed -i "s|\$database = ['\"][^'\"]*['\"];|\$database = '$DB_NAME';|" /var/www/html/database.php
    
    echo "Database configuration updated successfully."
    
    # Show differences for debugging
    echo "Changes made to database.php:"
    diff -u /var/www/html/database.php.bak /var/www/html/database.php || true
else
    echo "Warning: database.php not found. Creating a new one..."
    
    # Create a new database.php file with environment variables
    cat > /var/www/html/database.php << EOF
<?php
/**
 * Database connection configuration
 * Created automatically by Docker setup
 */

// Database connection parameters
\$host = '$DB_HOST';
\$user = '$DB_USER';
\$password = '$DB_PASSWORD';
\$database = '$DB_NAME';

// Create connection
\$conn = new mysqli(\$host, \$user, \$password, \$database);

// Check connection
if (\$conn->connect_error) {
    die("Connection failed: " . \$conn->connect_error);
}

// Set charset to ensure proper handling of special characters
\$conn->set_charset("utf8mb4");
?>
EOF
    echo "Created new database.php file."
fi
