# Data Migration Guide

## Overview

This guide explains how to migrate data from your existing WordPress installation to the new Ethileo Events Pro plugin.

## Prerequisites

- Backup your database before migration
- Backup your files (especially `/wp-content/uploads/ethileo-events/`)
- Have WP-CLI access to your WordPress installation

## Migration Steps

### 1. Backup Your Data

```bash
# Backup database
wp db export backup-before-migration.sql

# Backup files
tar -czf ethileo-files-backup.tar.gz wp-content/uploads/ethileo-events/
```

### 2. Install the New Plugin

```bash
# Upload the plugin to wp-content/plugins/
# OR
wp plugin install ethileo-events-pro.zip

# Activate it
wp plugin activate ethileo-events-pro
```

### 3. Run the Migration Script

```bash
wp eval-file wp-content/plugins/ethileo-events-pro/database/migrations/migrate-from-old-wp.php
```

The migration script will:
- ✅ Migrate all events
- ✅ Migrate guest lists
- ✅ Copy photo files
- ✅ Migrate QR codes
- ✅ Transfer settings

### 4. Verify the Migration

#### Check Events

```bash
wp db query "SELECT COUNT(*) FROM wp_ethileo_events"
```

#### Check Guests

```bash
wp db query "SELECT COUNT(*) FROM wp_ethileo_guests"
```

#### Check Photos

```bash
wp db query "SELECT COUNT(*) FROM wp_ethileo_photos"
```

### 5. Test the Plugin

1. Go to **WordPress Admin → Ethileo Events**
2. Verify events are listed correctly
3. Check guest RSVPs
4. Test photo gallery
5. Verify QR codes are working

## Manual Migration (Alternative)

If the automatic migration doesn't work, you can manually migrate:

### Events

1. Export events from old system:
```bash
wp db query "SELECT * FROM wp_posts WHERE post_type='ethileo_event'" --format=csv > events.csv
```

2. Import using the admin interface

### Guests

1. Go to each event
2. Use the "Import Guests" feature
3. Upload CSV with columns: first_name, last_name, email, phone

### Photos

1. Copy photos to new directory:
```bash
cp -r wp-content/uploads/ethileo-events/photos/* wp-content/uploads/ethileo-events/photos/
```

2. Run photo sync:
```bash
wp ethileo photo sync
```

## Post-Migration

### Update Permalinks

```bash
wp rewrite flush
```

### Clear Cache

```bash
wp cache flush

# If using LiteSpeed Cache
wp litespeed-purge all
```

### Test Email Notifications

```bash
wp ethileo test-email --to=your@email.com
```

## Rollback (If Needed)

If something goes wrong:

```bash
# Restore database
wp db import backup-before-migration.sql

# Deactivate new plugin
wp plugin deactivate ethileo-events-pro

# Activate old system (if applicable)
wp plugin activate ethileo-old
```

## Troubleshooting

### Missing Events

```bash
# Check old post type
wp post list --post_type=ethileo_event

# Re-run migration
wp eval-file database/migrations/migrate-from-old-wp.php
```

### Photo Upload Errors

```bash
# Fix permissions
chmod 755 wp-content/uploads/ethileo-events/
chmod 644 wp-content/uploads/ethileo-events/photos/*
```

### QR Codes Not Working

```bash
# Regenerate QR codes
wp ethileo qr regenerate-all
```

## Support

If you encounter issues:

1. Check the error logs: `wp-content/debug.log`
2. Enable WP_DEBUG in wp-config.php
3. Contact support with:
   - WordPress version
   - PHP version
   - Error messages
   - Steps to reproduce

## Data Mapping

### Events

| Old Field | New Field |
|-----------|-----------|
| post_title | title |
| post_content | description |
| _event_date meta | event_date |
| _event_location meta | location |

### Guests

| Old Field | New Field |
|-----------|-----------|
| guest_name | first_name + last_name |
| guest_email | email |
| guest_phone | phone |
| rsvp | rsvp_status |

## Performance Tips

- Migration runs in batches of 100 records
- Large databases may take several minutes
- Don't interrupt the migration process
- Monitor disk space during photo copying
