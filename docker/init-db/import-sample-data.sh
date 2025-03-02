#!/bin/bash
set -e

# Check if LOAD_SAMPLE_DATA environment variable is set
if [ "${LOAD_SAMPLE_DATA}" = "true" ]; then
    echo "Importing sample data into the database..."
    
    # Wait for MySQL to be fully initialized
    sleep 10
    
    # Import sample data
    mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" < /docker-entrypoint-initdb.d/sample_data.sql
    
    echo "Sample data imported successfully."
else
    echo "Skipping sample data import. Set LOAD_SAMPLE_DATA=true to import sample data."
fi
