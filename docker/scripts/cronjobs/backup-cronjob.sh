#!/bin/bash

set -e

# Define the backup path
DB_BACKUP_PATH="/workspace/starters/app/storage/backups"

mkdir -p "$DB_BACKUP_PATH"
BACKUP_FILE="$DB_BACKUP_PATH/$(date +%F_%T)_backup.sql"

backup_database() {
	mysqldump -h "$DB_HOST" -P "$DB_PORT" \
		-u "$DB_USERNAME" -p"$DB_PASSWORD" \
		"$DB_DATABASE" >"$BACKUP_FILE"

	if [ $? -eq 0 ]; then
		echo "Backup successfully created: $BACKUP_FILE"
	else
		echo "Backup failed"
	fi
}

cleanup_old_backups() {
	# Find and delete backups older than the 10 most recent ones
	ls -1t "$DB_BACKUP_PATH"/*.sql | tail -n +11 | xargs -d '\n' rm -f
}

backup_database
cleanup_old_backups
