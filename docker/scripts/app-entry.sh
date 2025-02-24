#!/bin/bash

SCRIPT_PATH="/usr/local/bin/cron.sh"
service cron start

# Install Composer dependencies if not already installed
if [ ! -d "vendor" ]; then
	composer install
fi

# Cron job setup
CRON_JOB="*/5 * * * * /bin/bash $SCRIPT_PATH"
(
	crontab -l 2>/dev/null | grep -v "$SCRIPT_PATH"
	echo "$CRON_JOB"
) | crontab -

if [ $? -eq 0 ]; then
	echo "Cron job set to run $SCRIPT_PATH every 5 minutes."
else
	echo "Failed to set up cron job."
fi

# Start the PHP built-in server
exec php -S 0.0.0.0:$PORT -d display_errors=1 -d log_errors=1 -d error_log=php_errors.log
