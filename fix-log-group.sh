#!/bin/bash
# Fix log directory to ensure new files inherit apache group

echo "Fixing log directory group inheritance..."

# Set setgid bit on storage/logs directory
# This makes new files inherit the directory's group (apache)
chmod 2775 storage/logs/

# Fix existing log files to have apache group
chgrp apache storage/logs/*.log 2>/dev/null || true

# Verify the fix
echo ""
echo "Current storage/logs/ permissions:"
ls -ld storage/logs/

echo ""
echo "Setgid bit should show as 's' in group permissions (drwxrwsr-x)"
echo "If you see 'drwxrwxr-x' instead, the setgid bit is NOT set"

echo ""
echo "Recent log files:"
ls -la storage/logs/ | tail -10
