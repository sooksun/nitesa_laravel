#!/bin/bash

# NITESA Database Backup Script
# Usage: ./backup-db.sh

set -e

# Configuration
BACKUP_DIR="/var/backups/nitesa"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Load environment variables
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Create backup directory
mkdir -p $BACKUP_DIR

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "🗄️  Starting database backup..."

# Backup Database
BACKUP_FILE="$BACKUP_DIR/db_$DATE.sql.gz"
echo "Creating backup: $BACKUP_FILE"

mysqldump -h ${DB_HOST:-127.0.0.1} \
          -P ${DB_PORT:-3306} \
          -u ${DB_USERNAME} \
          -p${DB_PASSWORD} \
          ${DB_DATABASE} | gzip > $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database backup created: $BACKUP_FILE${NC}"
    
    # Get file size
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "  Size: $SIZE"
else
    echo "❌ Database backup failed!"
    exit 1
fi

# Backup Files
FILES_BACKUP="$BACKUP_DIR/files_$DATE.tar.gz"
echo "Creating files backup: $FILES_BACKUP"

if [ -d "storage/app" ]; then
    tar -czf $FILES_BACKUP storage/app 2>/dev/null || {
        echo -e "${YELLOW}⚠ Files backup failed (may be empty)${NC}"
    }
    
    if [ -f "$FILES_BACKUP" ]; then
        echo -e "${GREEN}✓ Files backup created: $FILES_BACKUP${NC}"
        SIZE=$(du -h "$FILES_BACKUP" | cut -f1)
        echo "  Size: $SIZE"
    fi
fi

# Cleanup old backups
echo "Cleaning up backups older than $RETENTION_DAYS days..."
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "files_*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo -e "${GREEN}✓ Backup completed!${NC}"

# List recent backups
echo -e "\nRecent backups:"
ls -lh $BACKUP_DIR | tail -5
