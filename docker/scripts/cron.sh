#!/bin/bash

set -e

PHP_BINARY="/usr/local/bin/php"
LOG_FILE="/workspace/starters/app/listener.log"
ENV_FILE="/workspace/starters/app/.env"
SCRIPT_PATH="/workspace/starters/app/listener.php"
MAX_LINES=100

init() {
	touch "$LOG_FILE"
	chmod 664 "$LOG_FILE"
	chmod +x "$SCRIPT_PATH"
}

# Function to trim the log file if it exceeds MAX_LINES
trim_log_file() {
	local line_count
	line_count=$(wc -l <"$LOG_FILE")

	if [ "$line_count" -gt "$MAX_LINES" ]; then
		sed -i -e :a -e "\$q;N;$MAX_LINES,\$D;ba" "$LOG_FILE"
	fi
}

# Function to load environment variables if ENV_FILE exists
load_env() {
	if [ -f "$ENV_FILE" ]; then
		# shellcheck source=/workspace/starters/app/.env
		source "$ENV_FILE"
		export DB_HOST DB_NAME DB_USER DB_PASSWORD DB_PORT ENV
	fi
}

run_script() {
	echo "[$(date '+%Y-%m-%d %H:%M:%S')] Running listener.php" >>"$LOG_FILE"
	"$PHP_BINARY" "$SCRIPT_PATH" >>"$LOG_FILE" 2>&1
	local exit_code=$?

	if [ "$exit_code" -eq 0 ]; then
		echo "[$(date '+%Y-%m-%d %H:%M:%S')] Script completed successfully" >>"$LOG_FILE"
	else
		echo "[$(date '+%Y-%m-%d %H:%M:%S')] Script failed with exit code $exit_code" >>"$LOG_FILE"
	fi
}

main() {
	init
	load_env
	trim_log_file
	run_script
}

main
