<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Core;

/**
 * Plugin Uninstall Handler
 *
 * @package Ethileo\EventsPro\Core
 */
class Uninstall
{
    /**
     * Run uninstall tasks
     *
     * @return void
     */
    public static function uninstall(): void
    {
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            exit;
        }

        // Check if user wants to keep data
        $keep_data = get_option('ethileo_events_pro_keep_data_on_uninstall', false);

        if (!$keep_data) {
            self::dropDatabaseTables();
            self::deleteOptions();
            self::deleteUploadedFiles();
            self::removeCapabilities();
        }
    }

    /**
     * Drop database tables
     *
     * @return void
     */
    private static function dropDatabaseTables(): void
    {
        global $wpdb;

        $prefix = $wpdb->prefix . 'ethileo_';

        $tables = [
            $prefix . 'events',
            $prefix . 'guests',
            $prefix . 'invitations',
            $prefix . 'photos',
            $prefix . 'qr_codes',
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }

    /**
     * Delete plugin options
     *
     * @return void
     */
    private static function deleteOptions(): void
    {
        delete_option('ethileo_events_pro_version');
        delete_option('ethileo_events_pro_settings');
        delete_option('ethileo_events_pro_activated');
        delete_option('ethileo_events_pro_deactivated');
        delete_option('ethileo_events_pro_keep_data_on_uninstall');
    }

    /**
     * Delete uploaded files
     *
     * @return void
     */
    private static function deleteUploadedFiles(): void
    {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/ethileo-events';

        if (file_exists($base_dir)) {
            self::deleteDirectory($base_dir);
        }
    }

    /**
     * Recursively delete directory
     *
     * @param string $dir
     * @return bool
     */
    private static function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * Remove custom capabilities
     *
     * @return void
     */
    private static function removeCapabilities(): void
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
                $admin->remove_cap($cap);
            }
        }
    }
}
