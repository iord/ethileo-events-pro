<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Core;

/**
 * Plugin Deactivation Handler
 *
 * @package Ethileo\EventsPro\Core
 */
class Deactivation
{
    /**
     * Run deactivation tasks
     *
     * @return void
     */
    public static function deactivate(): void
    {
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook('ethileo_events_pro_daily_cleanup');
        wp_clear_scheduled_hook('ethileo_events_pro_send_reminders');

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set deactivation flag
        update_option('ethileo_events_pro_deactivated', time());
    }
}
