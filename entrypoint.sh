#!/bin/bash

# Function to run SQL scripts in a specific order
run_sql_scripts() {
	echo "Running SQL scripts in order..."
	sql_files=(
		"/workspace/starters/schema/init-db.sql"
	)

	for sql_file in "${sql_files[@]}"; do
		if [ -f "$sql_file" ]; then
			echo "Executing $sql_file"
			PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f "$sql_file"
		else
			echo "SQL file not found: $sql_file"
		fi
	done
}

# Install Composer dependencies if not already installed
if [ ! -d "vendor" ]; then
	composer install
fi

# Run SQL scripts if the directory exists
if [ -d "/workspace/starters/schema" ]; then
	run_sql_scripts
else
	echo "No SQL scripts directory found."
fi

# Start the PHP built-in server
exec php -S 0.0.0.0:$PORT -d display_errors=1 -d log_errors=1 -d error_log=php_errors.log
