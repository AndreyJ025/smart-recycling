#!/bin/bash
set -e

echo "Checking database tables..."

# List of required tables
REQUIRED_TABLES=(
  "tbl_bulk_requests"
  "tbl_faqs"
  "tbl_inventory"
  "tbl_pickup_notifications"
  "tbl_pickups"
  "tbl_remit"
  "tbl_users"
)

# Check if each required table exists
for table in "${REQUIRED_TABLES[@]}"; do
  echo "Checking for table: $table"
  
  # Count tables matching the name
  TABLE_EXISTS=$(mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$MYSQL_DATABASE' AND table_name='$table';" | tail -1)
  
  if [ "$TABLE_EXISTS" -eq 0 ]; then
    echo "Table $table does not exist. Creating default structure..."
    
    # Create table with basic structure based on its name
    case "$table" in
      "tbl_remit")
        mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" << EOF
CREATE TABLE IF NOT EXISTS tbl_remit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  remit_type VARCHAR(50) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_created_at (created_at)
);
EOF
        ;;
      *)
        # Generic table structure for other tables
        mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" << EOF
CREATE TABLE IF NOT EXISTS $table (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
EOF
        ;;
    esac
    
    echo "Created default structure for $table"
  else
    echo "Table $table exists."
  fi
done

echo "Table check completed."
