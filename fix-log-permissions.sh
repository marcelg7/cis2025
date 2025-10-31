#!/bin/bash

# Fix Log File Permissions Script
# This script fixes permissions on existing log files and ensures proper
# permissions for the storage/logs directory.
#
# Run this script with appropriate privileges (sudo or as apache user)
# Usage: sudo bash fix-log-permissions.sh

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Fixing log file permissions...${NC}"

# Get the script's directory (where the Laravel app is)
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOGS_DIR="${APP_DIR}/storage/logs"

echo "Working directory: ${APP_DIR}"
echo "Logs directory: ${LOGS_DIR}"

# Ensure storage/logs directory has proper permissions
echo -e "\n${YELLOW}Setting directory permissions...${NC}"
chmod 775 "${LOGS_DIR}"
echo "✓ Set ${LOGS_DIR} to 775"

# Change group ownership to apache for all log files
echo -e "\n${YELLOW}Changing group ownership to apache...${NC}"
chgrp -R apache "${LOGS_DIR}"
echo "✓ Changed group to apache for all files in ${LOGS_DIR}"

# Set proper permissions on all existing log files (664 = rw-rw-r--)
echo -e "\n${YELLOW}Setting file permissions to 664...${NC}"
find "${LOGS_DIR}" -type f -name "*.log" -exec chmod 664 {} \;
echo "✓ Set all .log files to 664"

# Show current permissions
echo -e "\n${GREEN}Current log directory permissions:${NC}"
ls -la "${LOGS_DIR}" | head -20

echo -e "\n${GREEN}Done! Log files should now be writable by both web server and CLI users.${NC}"
echo -e "${YELLOW}Note: New log files will automatically be created with correct permissions.${NC}"
