#!/bin/bash

# Define the paths for the cron jobs
DUE_BILLS_SCRIPT_PATH="/usr/local/bin/due-bills-cronjob.sh"
EXPIRED_ITEMS_SCRIPT_PATH="/usr/local/bin/expired-items-cronjob.sh"
BACKUP_SCRIPT_PATH="/usr/local/bin/backup-cronjob.sh"

# Start cron service
echo "Starting cron service..."
service cron start

# Install Composer dependencies if not already installed
if [ ! -d "vendor" ]; then
	echo "Installing Composer dependencies..."
	composer install
fi

# Set up cron jobs for different scripts
DUE_BILLS_CRON="*/5 * * * * /bin/bash $DUE_BILLS_SCRIPT_PATH"
EXPIRED_ITEMS_CRON="0 9 * * * /bin/bash $EXPIRED_ITEMS_SCRIPT_PATH"
BACKUP_CRON="0 2 * * * /bin/bash $BACKUP_SCRIPT_PATH"

# Add the cron jobs to the crontab
echo "Setting up cron jobs..."
(
	crontab -l 2>/dev/null | grep -v -E \
		"$DUE_BILLS_SCRIPT_PATH|$EXPIRED_ITEMS_SCRIPT_PATH|$BACKUP_SCRIPT_PATH"
	echo "$DUE_BILLS_CRON"
	echo "$EXPIRED_ITEMS_CRON"
	echo "$BACKUP_CRON"
) | crontab -

# Check if cron jobs were set up successfully
if [ $? -eq 0 ]; then
	echo "Cron jobs set successfully."
	echo "- Due bills script runs every 5 minutes."
	echo "- Expired items script runs daily at 9 AM."
	echo "- Database backup runs daily at 2 AM."
else
	echo "Failed to set up cron jobs."
fi

# Start PHP-FPM in the background
echo "Starting PHP-FPM..."
php-fpm -D

# Ensure the container stays running and keeps the cron service active
# echo "Container is running. Monitoring processes..."
# tail -f /dev/null

# Start the PHP built-in server
exec php -S 0.0.0.0:$PORT \
	-d display_errors=1 \
	-d log_errors=1 \
	-d error_log=logs/php_errors.log
