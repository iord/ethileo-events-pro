<?php
/**
 * Plugin Name: Ethileo Events Pro
 * Plugin URI: https://ethileo.com
 * Description: Enterprise-grade event management system with clean architecture. Manage events, guests, invitations, QR codes, and photo sharing.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Ethileo Team
 * Author URI: https://ethileo.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: ethileo-events-pro
 * Domain Path: /resources/lang
 *
 * @package Ethileo\EventsPro
 */

declare(strict_types=1);

namespace Ethileo\EventsPro;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ETHILEO_EVENTS_PRO_VERSION', '1.0.0');
define('ETHILEO_EVENTS_PRO_FILE', __FILE__);
define('ETHILEO_EVENTS_PRO_PATH', plugin_dir_path(__FILE__));
define('ETHILEO_EVENTS_PRO_URL', plugin_dir_url(__FILE__));
define('ETHILEO_EVENTS_PRO_BASENAME', plugin_basename(__FILE__));

// Require Composer autoloader
if (file_exists(ETHILEO_EVENTS_PRO_PATH . 'vendor/autoload.php')) {
    require_once ETHILEO_EVENTS_PRO_PATH . 'vendor/autoload.php';
}

use Ethileo\EventsPro\Core\Application;
use Ethileo\EventsPro\Core\Exception\PluginException;

/**
 * Main plugin bootstrap function
 *
 * @return Application
 * @throws PluginException
 */
function ethileo_events_pro(): Application
{
    static $application = null;

    if ($application === null) {
        $application = Application::getInstance();
    }

    return $application;
}

/**
 * Initialize the plugin
 */
function ethileo_events_pro_init(): void
{
    try {
        ethileo_events_pro()->boot();
    } catch (PluginException $e) {
        add_action('admin_notices', function () use ($e) {
            printf(
                '<div class="notice notice-error"><p><strong>Ethileo Events Pro Error:</strong> %s</p></div>',
                esc_html($e->getMessage())
            );
        });

        // Log the error
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf('Ethileo Events Pro Error: %s', $e->getMessage()));
        }
    }
}

// Hook into WordPress
add_action('plugins_loaded', 'Ethileo\\EventsPro\\ethileo_events_pro_init', 10);

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function () {
    try {
        require_once ETHILEO_EVENTS_PRO_PATH . 'src/Core/Activation.php';
        Core\Activation::activate();
    } catch (\Exception $e) {
        wp_die(
            esc_html($e->getMessage()),
            esc_html__('Plugin Activation Error', 'ethileo-events-pro'),
            ['back_link' => true]
        );
    }
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function () {
    try {
        require_once ETHILEO_EVENTS_PRO_PATH . 'src/Core/Deactivation.php';
        Core\Deactivation::deactivate();
    } catch (\Exception $e) {
        error_log(sprintf('Ethileo Events Pro Deactivation Error: %s', $e->getMessage()));
    }
});

/**
 * Uninstall hook
 */
register_uninstall_hook(__FILE__, [Core\Uninstall::class, 'uninstall']);
