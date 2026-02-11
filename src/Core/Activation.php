<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Core;

/**
 * Plugin Activation Handler
 *
 * @package Ethileo\EventsPro\Core
 */
class Activation
{
    /**
     * Run activation tasks
     *
     * @return void
     */
    public static function activate(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(ETHILEO_EVENTS_PRO_BASENAME);
            wp_die(
                esc_html__('Ethileo Events Pro requires PHP 7.4 or higher.', 'ethileo-events-pro')
            );
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.8', '<')) {
            deactivate_plugins(ETHILEO_EVENTS_PRO_BASENAME);
            wp_die(
                esc_html__('Ethileo Events Pro requires WordPress 5.8 or higher.', 'ethileo-events-pro')
            );
        }

        // Create database tables
        self::createDatabaseTables();

        // Create necessary directories
        self::createDirectories();

        // Set default options
        self::setDefaultOptions();

        // Add custom capabilities
        self::addCapabilities();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        update_option('ethileo_events_pro_activated', time());
    }

    /**
     * Create database tables
     *
     * @return void
     */
    private static function createDatabaseTables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix . 'ethileo_';

        $sqls = [];

        // Events table
        $sqls[] = "CREATE TABLE IF NOT EXISTS {$prefix}events (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid varchar(36) NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            title varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description longtext,
            event_date datetime NOT NULL,
            event_end_date datetime,
            location varchar(255),
            status varchar(20) DEFAULT 'draft',
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uuid (uuid),
            UNIQUE KEY slug (slug),
            KEY user_id (user_id),
            KEY event_date (event_date),
            KEY status (status)
        ) $charset_collate;";

        // Guests table
        $sqls[] = "CREATE TABLE IF NOT EXISTS {$prefix}guests (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid varchar(36) NOT NULL,
            event_id bigint(20) UNSIGNED NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100),
            email varchar(100),
            phone varchar(50),
            rsvp_status varchar(20) DEFAULT 'pending',
            plus_one tinyint(1) DEFAULT 0,
            plus_one_name varchar(200),
            dietary_restrictions text,
            notes text,
            qr_code varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uuid (uuid),
            UNIQUE KEY event_guest_email (event_id, email),
            KEY event_id (event_id),
            KEY rsvp_status (rsvp_status)
        ) $charset_collate;";

        // Invitations table
        $sqls[] = "CREATE TABLE IF NOT EXISTS {$prefix}invitations (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid varchar(36) NOT NULL,
            event_id bigint(20) UNSIGNED NOT NULL,
            guest_id bigint(20) UNSIGNED NOT NULL,
            type varchar(20) DEFAULT 'email',
            template_id bigint(20),
            sent_at datetime,
            opened_at datetime,
            clicked_at datetime,
            rsvp_at datetime,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uuid (uuid),
            KEY event_id (event_id),
            KEY guest_id (guest_id),
            KEY status (status)
        ) $charset_collate;";

        // Photos table
        $sqls[] = "CREATE TABLE IF NOT EXISTS {$prefix}photos (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid varchar(36) NOT NULL,
            event_id bigint(20) UNSIGNED NOT NULL,
            guest_id bigint(20) UNSIGNED,
            filename varchar(255) NOT NULL,
            filepath varchar(255) NOT NULL,
            filesize bigint(20),
            mime_type varchar(100),
            is_approved tinyint(1) DEFAULT 1,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uuid (uuid),
            KEY event_id (event_id),
            KEY guest_id (guest_id),
            KEY is_approved (is_approved)
        ) $charset_collate;";

        // QR Codes table
        $sqls[] = "CREATE TABLE IF NOT EXISTS {$prefix}qr_codes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid varchar(36) NOT NULL,
            event_id bigint(20) UNSIGNED NOT NULL,
            guest_id bigint(20) UNSIGNED,
            code varchar(255) NOT NULL,
            type varchar(50) DEFAULT 'guest',
            data longtext,
            scans int(11) DEFAULT 0,
            last_scanned_at datetime,
            expires_at datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uuid (uuid),
            UNIQUE KEY code (code),
            KEY event_id (event_id),
            KEY guest_id (guest_id),
            KEY type (type)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ($sqls as $sql) {
            dbDelta($sql);
        }
    }

    /**
     * Create necessary directories
     *
     * @return void
     */
    private static function createDirectories(): void
    {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/ethileo-events';

        $directories = [
            $base_dir,
            $base_dir . '/photos',
            $base_dir . '/qr-codes',
            $base_dir . '/temp',
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);

                // Create .htaccess for security
                file_put_contents(
                    $dir . '/.htaccess',
                    "Options -Indexes\n<Files *.php>\ndeny from all\n</Files>"
                );
            }
        }
    }

    /**
     * Set default plugin options
     *
     * @return void
     */
    private static function setDefaultOptions(): void
    {
        $defaults = [
            'ethileo_events_pro_version' => ETHILEO_EVENTS_PRO_VERSION,
            'ethileo_events_pro_settings' => [
                'default_rsvp_status' => 'pending',
                'allow_plus_one' => true,
                'photo_approval_required' => false,
                'max_photo_size' => 5, // MB
                'qr_code_expires_days' => 365,
                'email_from_name' => get_bloginfo('name'),
                'email_from_address' => get_option('admin_email'),
            ],
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Add custom capabilities
     *
     * @return void
     */
    private static function addCapabilities(): void
    {
        $admin = get_role('administrator');

        if ($admin) {
            $capabilities = [
                'manage_ethileo_events',
                'edit_ethileo_event',
                'edit_ethileo_events',
                'edit_others_ethileo_events',
                'publish_ethileo_events',
                'read_ethileo_event',
                'read_private_ethileo_events',
                'delete_ethileo_event',
                'delete_ethileo_events',
            ];

            foreach ($capabilities as $cap) {
                $admin->add_cap($cap);
            }
        }
    }
}
