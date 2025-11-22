#!/bin/bash

###############################################################################
# Automatic Database Backup Script for GestionSocios
# Author: GitHub Copilot
# Description: Creates compressed MySQL dumps with timestamp and rotation
###############################################################################

# Configuration
DB_HOST="192.168.1.22"
DB_USER="root"
DB_PASS="your_password_here"  # CHANGE THIS!
DB_NAME="asociacion_db"

# Backup settings
BACKUP_DIR="/opt/GestionSocios/backups"
RETENTION_DAYS=30  # Keep backups for 30 days
MAX_BACKUPS=60     # Maximum number of backups to keep

# Timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${TIMESTAMP}.sql.gz"

# Log file
LOG_FILE="${BACKUP_DIR}/backup.log"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Function to log messages
log_message() {
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] $1" | tee -a "$LOG_FILE"
}

# Function to send notification (optional - requires mail configured)
send_notification() {
    local subject="$1"
    local message="$2"
    
    # Uncomment if you have mail configured
    # echo "$message" | mail -s "$subject" admin@example.com
    
    log_message "Notification: $subject - $message"
}

###############################################################################
# Main backup process
###############################################################################

log_message "=========================================="
log_message "Starting database backup..."
log_message "Database: $DB_NAME"
log_message "Host: $DB_HOST"

# Check if mysqldump is available
if ! command -v mysqldump &> /dev/null; then
    log_message "ERROR: mysqldump command not found!"
    send_notification "Backup Failed" "mysqldump command not found on server"
    exit 1
fi

# Perform backup
log_message "Creating backup file: $BACKUP_FILE"

if mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-database \
    --databases "$DB_NAME" | gzip > "$BACKUP_FILE"; then
    
    # Backup successful
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_message "SUCCESS: Backup completed successfully"
    log_message "Backup size: $BACKUP_SIZE"
    
    # Verify backup integrity
    if gzip -t "$BACKUP_FILE" 2>/dev/null; then
        log_message "Backup integrity verified"
    else
        log_message "WARNING: Backup file may be corrupted!"
        send_notification "Backup Warning" "Backup created but integrity check failed"
    fi
    
else
    # Backup failed
    log_message "ERROR: Backup failed!"
    send_notification "Backup Failed" "Database backup failed for $DB_NAME"
    
    # Remove partial backup file if exists
    [ -f "$BACKUP_FILE" ] && rm -f "$BACKUP_FILE"
    exit 1
fi

###############################################################################
# Backup rotation and cleanup
###############################################################################

log_message "Running backup rotation..."

# Delete backups older than retention period
log_message "Deleting backups older than $RETENTION_DAYS days..."
find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete

# Keep only the most recent MAX_BACKUPS
BACKUP_COUNT=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f | wc -l)

if [ "$BACKUP_COUNT" -gt "$MAX_BACKUPS" ]; then
    log_message "Maximum backup count ($MAX_BACKUPS) exceeded, removing oldest backups..."
    EXCESS=$((BACKUP_COUNT - MAX_BACKUPS))
    
    find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f -printf '%T+ %p\n' | \
        sort | \
        head -n "$EXCESS" | \
        cut -d' ' -f2- | \
        xargs rm -f
    
    log_message "Removed $EXCESS old backup(s)"
fi

# Display current backup statistics
CURRENT_COUNT=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f | wc -l)
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)

log_message "Current backup statistics:"
log_message "  - Number of backups: $CURRENT_COUNT"
log_message "  - Total backup size: $TOTAL_SIZE"
log_message "Backup process completed successfully!"
log_message "=========================================="

exit 0
