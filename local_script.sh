
#!/bin/bash

# Define functions for logging and error handling

log_message() {
  local level="$1"
  local message="$2"
  echo "$(date +'%Y-%m-%d %H:%M:%S') [$level] $message"
}

error_exit() {
  local message="$1"
  log_message "ERROR" "$message"
  exit 1
}

# Define cron schedules
CRON_SCHEDULE_1="00 20 * * * php /var/www/html/ems/artisan run:manualCheckInCheckOut >> /dev/null 2>&1"
CRON_SCHEDULE_2="*/1 * * * * php /var/www/html/ems/artisan command:shift_observer >> /dev/null 2>&1"

# Add cron schedules to crontab
existing_crontab=$(crontab -l 2>/dev/null)

if [ -z "$existing_crontab" ]; then
  log_message "WARN" "Failed to read crontab."
  new_crontab="${CRON_SCHEDULE_1}
${CRON_SCHEDULE_2}"
else
  new_crontab="${existing_crontab}
${CRON_SCHEDULE_1}
${CRON_SCHEDULE_2}"

  echo "$new_crontab" | crontab -
  log_message "INFO" "Cron schedules added successfully."
fi

# Change directory
if ! cd /var/www/html/ems/; then
  error_exit "Failed to change directory to /var/www/html/ems/"
fi

# Set file permissions
if ! chown -R www-data:www-data storage; then
  error_exit "Failed to set ownership of 'storage' directory."
fi
if ! chmod +x artisan; then
  error_exit "Failed to set execute permission for 'artisan' file."
fi
if ! chmod -R 755 public; then
  error_exit "Failed to set permissions for 'public' directory."
fi
if ! chown -R www-data:www-data public; then
  error_exit "Failed to set ownership of 'public' directory."
fi

if ! find . -type d -exec chmod 755 {} \+ 2>/dev/null; then
  log_message "WARN" "Failed to set permissions for some directories."
fi

if ! find . -name "*.php" -exec chmod 644 {} \+ 2>/dev/null; then
  log_message "WARN" "Failed to set permissions for some PHP files."
fi

if ! find . -type f ! -name "*.php" -exec chmod 644 {} \+ 2>/dev/null; then
  log_message "WARN" "Failed to set permissions for some files."
fi

# Run migrations
if ! php artisan migrate; then
  error_exit "Failed to run migrations."
fi

log_message "INFO" "File permissions set and migrations run successfully."

