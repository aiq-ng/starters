#!/bin/bash

DUE_BILLS_SCRIPT_PATH="/usr/local/bin/due-bills-cronjob.sh"
EXPIRED_ITEMS_SCRIPT_PATH="/usr/local/bin/expired-items-cronjob.sh"
BACKUP_SCRIPT_PATH="/usr/local/bin/backup-cronjob.sh"

# Start cron service
service cron start

# Install Composer dependencies if not already installed
if [ ! -d "vendor" ]; then
	composer install
fi

# Cron job setup
DUE_BILLS_CRON="*/5 * * * * /bin/bash $DUE_BILLS_SCRIPT_PATH"
EXPIRED_ITEMS_CRON="0 9 * * * /bin/bash $EXPIRED_ITEMS_SCRIPT_PATH"
BACKUP_CRON="0 2 * * * /bin/bash $BACKUP_SCRIPT_PATH"

(
	crontab -l 2>/dev/null | grep -v -E \
		"$DUE_BILLS_SCRIPT_PATH|$EXPIRED_ITEMS_SCRIPT_PATH|$BACKUP_SCRIPT_PATH"
	echo "$DUE_BILLS_CRON"
	echo "$EXPIRED_ITEMS_CRON"
	echo "$BACKUP_CRON"
) | crontab -

if [ $? -eq 0 ]; then
	echo "Cron jobs set successfully."
	echo "- Due bills script runs every 5 minutes."
	echo "- Expired items script runs daily at 9 AM."
	echo "- Database backup runs daily at 2 AM."
else
	echo "Failed to set up cron jobs."
fi

# Start the PHP built-in server
exec php -S 0.0.0.0:$PORT \
	-d display_errors=1 \
	-d log_errors=1 \
	-d error_log=logs/php_errors.log
