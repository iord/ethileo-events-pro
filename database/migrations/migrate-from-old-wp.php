<?php
/**
 * Data Migration Script
 * 
 * Migrates data from the old WordPress installation to the new Ethileo Events Pro plugin
 *
 * Usage: wp eval-file database/migrations/migrate-from-old-wp.php
 *
 * @package Ethileo\EventsPro
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}

class EthileoDataMigration
{
    private \wpdb $wpdb;
    private string $oldUploadDir;
    private string $newUploadDir;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $upload = wp_upload_dir();
        $this->oldUploadDir = $upload['basedir'] . '/ethileo-events';
        $this->newUploadDir = $upload['basedir'] . '/ethileo-events';
    }

    /**
     * Run the migration
     */
    public function run(): void
    {
        echo "Starting Ethileo Events Pro data migration...\n\n";

        try {
            $this->migrateUsers();
            $this->migrateEvents();
            $this->migrateGuests();
            $this->migratePhotos();
            $this->migrateSettings();

            echo "\n✅ Migration completed successfully!\n";
        } catch (\Exception $e) {
            echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Migrate users
     */
    private function migrateUsers(): void
    {
        echo "📊 Migrating users...\n";

        // In WordPress, users are already in the system
        // We just need to add custom capabilities if needed

        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_ethileo_events');
        }

        echo "   ✓ User capabilities updated\n";
    }

    /**
     * Migrate events from old system
     */
    private function migrateEvents(): void
    {
        echo "📅 Migrating events...\n";

        $eventsTable = $this->wpdb->prefix . 'ethileo_events';

        // Check if there's old event data in wp_posts or custom tables
        $oldEvents = $this->wpdb->get_results("
            SELECT * FROM {$this->wpdb->posts}
            WHERE post_type = 'ethileo_event'
            AND post_status IN ('publish', 'draft')
        ");

        $count = 0;
        foreach ($oldEvents as $oldEvent) {
            // Create new event entry
            $uuid = $this->generateUuid();

            $this->wpdb->insert($eventsTable, [
                'uuid' => $uuid,
                'user_id' => $oldEvent->post_author,
                'title' => $oldEvent->post_title,
                'slug' => $oldEvent->post_name,
                'description' => $oldEvent->post_content,
                'event_date' => get_post_meta($oldEvent->ID, '_event_date', true) ?: date('Y-m-d H:i:s'),
                'location' => get_post_meta($oldEvent->ID, '_event_location', true),
                'status' => $oldEvent->post_status === 'publish' ? 'published' : 'draft',
                'settings' => json_encode([
                    'old_post_id' => $oldEvent->ID,
                ]),
                'created_at' => $oldEvent->post_date,
                'updated_at' => $oldEvent->post_modified,
            ]);

            $count++;
        }

        echo "   ✓ Migrated {$count} events\n";
    }

    /**
     * Migrate guests
     */
    private function migrateGuests(): void
    {
        echo "👥 Migrating guests...\n";

        $guestsTable = $this->wpdb->prefix . 'ethileo_guests';
        $eventsTable = $this->wpdb->prefix . 'ethileo_events';

        // Check for old guest data
        $oldGuests = $this->wpdb->get_results("
            SELECT * FROM {$this->wpdb->prefix}ethileo_old_guests
        ", ARRAY_A);

        if (!$oldGuests) {
            echo "   ℹ No old guest data found\n";
            return;
        }

        $count = 0;
        foreach ($oldGuests as $oldGuest) {
            // Find corresponding new event
            $newEventId = $this->wpdb->get_var($this->wpdb->prepare("
                SELECT id FROM {$eventsTable}
                WHERE JSON_EXTRACT(settings, '$.old_post_id') = %d
            ", $oldGuest['event_id']));

            if (!$newEventId) {
                continue;
            }

            $uuid = $this->generateUuid();

            $this->wpdb->insert($guestsTable, [
                'uuid' => $uuid,
                'event_id' => $newEventId,
                'first_name' => $oldGuest['first_name'],
                'last_name' => $oldGuest['last_name'] ?? '',
                'email' => $oldGuest['email'] ?? '',
                'phone' => $oldGuest['phone'] ?? '',
                'rsvp_status' => $oldGuest['rsvp_status'] ?? 'pending',
                'plus_one' => $oldGuest['plus_one'] ?? 0,
                'qr_code' => $oldGuest['qr_code'] ?? '',
            ]);

            $count++;
        }

        echo "   ✓ Migrated {$count} guests\n";
    }

    /**
     * Migrate photos
     */
    private function migratePhotos(): void
    {
        echo "📸 Migrating photos...\n";

        $photosTable = $this->wpdb->prefix . 'ethileo_photos';

        // Scan old photo directory
        if (!file_exists($this->oldUploadDir . '/photos')) {
            echo "   ℹ No old photos found\n";
            return;
        }

        $photos = glob($this->oldUploadDir . '/photos/*.*');
        $count = 0;

        foreach ($photos as $photoPath) {
            $uuid = $this->generateUuid();
            $filename = basename($photoPath);

            // Copy to new location if different
            $newPath = $this->newUploadDir . '/photos/' . $filename;
            if ($photoPath !== $newPath && !file_exists($newPath)) {
                copy($photoPath, $newPath);
            }

            $this->wpdb->insert($photosTable, [
                'uuid' => $uuid,
                'event_id' => 1, // Default, should be updated
                'filename' => $filename,
                'filepath' => 'photos/' . $filename,
                'filesize' => filesize($photoPath),
                'mime_type' => mime_content_type($photoPath),
                'is_approved' => 1,
            ]);

            $count++;
        }

        echo "   ✓ Migrated {$count} photos\n";
    }

    /**
     * Migrate settings
     */
    private function migrateSettings(): void
    {
        echo "⚙️  Migrating settings...\n";

        // Copy old settings to new format
        $oldSettings = get_option('ethileo_old_settings', []);
        $newSettings = get_option('ethileo_events_pro_settings', []);

        $mappedSettings = array_merge($newSettings, [
            'default_rsvp_status' => $oldSettings['rsvp_status'] ?? 'pending',
            'allow_plus_one' => $oldSettings['allow_plus_one'] ?? true,
            'photo_approval_required' => $oldSettings['photo_moderation'] ?? false,
        ]);

        update_option('ethileo_events_pro_settings', $mappedSettings);

        echo "   ✓ Settings migrated\n";
    }

    /**
     * Generate UUID
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

// Run migration
$migration = new EthileoDataMigration();
$migration->run();
