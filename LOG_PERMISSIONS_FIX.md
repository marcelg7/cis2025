# Log File Permissions Fix

## Problem
Daily log files were being created with restrictive permissions (644) that prevented both the web server (Apache) and CLI user (marcelg) from writing to the same log files. This caused "Permission denied" errors when:
- Apache created a log file, then CLI commands tried to write to it
- CLI commands created a log file, then web requests tried to write to it

## Permanent Solution Implemented

### 1. Updated Logging Configuration (config/logging.php)
Added `'permission' => 0664` to all file-based log channels:
- `single` channel
- `daily` channel
- `security` channel
- `emergency` channel

This ensures all new log files are created with `rw-rw-r--` (664) permissions, allowing:
- Owner (apache or marcelg) to read/write
- Group (apache) to read/write
- Others to read only

### 2. Set Proper Umask (bootstrap/app.php)
Added `umask(0002)` at the top of the bootstrap file to set the default file creation mask. This means:
- New files created by the application will have 664 permissions (666 - 002 = 664)
- New directories will have 775 permissions (777 - 002 = 775)

This affects ALL file creation operations in Laravel, not just logs.

### 3. Configuration Cache Cleared
Ran `php artisan config:clear && php artisan config:cache` to ensure the new settings take effect immediately.

## Fixing Existing Log Files

The permanent fix above only affects NEW log files. To fix existing log files with wrong permissions, run:

```bash
sudo bash fix-log-permissions.sh
```

Or manually:
```bash
sudo chgrp -R apache storage/logs/
sudo chmod 775 storage/logs/
sudo find storage/logs/ -type f -name "*.log" -exec chmod 664 {} \;
```

## How It Works

**User Context:**
- Web requests run as `apache:apache` user
- CLI commands run as `marcelg:apache` user (marcelg is member of apache group)

**With 664 Permissions:**
- Both `apache` user (owner) and `apache` group (group) can read/write
- This allows both web and CLI contexts to write to the same log files

**With umask 0002:**
- PHP's `fopen()`, `file_put_contents()`, etc. create files with group-writable permissions
- Monolog log handlers respect this umask when creating new daily log files

## Verification

After implementing this fix:

1. **Check new log files are created with correct permissions:**
   ```bash
   ls -la storage/logs/
   # New files should show: -rw-rw-r-- 1 apache apache ...
   ```

2. **Test web request logging:**
   - Visit the application in a browser
   - Check that today's log file was created or updated

3. **Test CLI logging:**
   ```bash
   php artisan tinker
   >>> Log::info('Test from CLI');
   >>> exit
   ```
   - Check that the log was written successfully

4. **Verify no permission errors:**
   - Monitor `storage/logs/` for any new "Permission denied" errors
   - Check Laravel error logs in the browser

## Files Modified

1. `config/logging.php` - Added permission settings to all file-based channels
2. `bootstrap/app.php` - Added umask(0002) for proper file creation permissions
3. `fix-log-permissions.sh` - Created script to fix existing log files (requires sudo)

## Prevention

With this fix in place:
- ✅ New daily log files automatically get correct permissions
- ✅ Both web and CLI contexts can write to logs
- ✅ No manual permission fixes needed for new files
- ✅ Works across server restarts and deployments

## Important Notes

- The `fix-log-permissions.sh` script requires sudo/root access to change ownership of files owned by apache
- The umask setting affects the entire Laravel application
- Group membership is critical: CLI user must be in the `apache` group
- Verify with: `groups marcelg` should show `apache` in the list

## Date Implemented
October 31, 2025 (Halloween Fix! 🎃)
