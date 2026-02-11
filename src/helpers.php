<?php

declare(strict_types=1);

/**
 * Global Helper Functions
 *
 * @package Ethileo\EventsPro
 */

use Ethileo\EventsPro\Core\Application;

if (!function_exists('ethileo_app')) {
    /**
     * Get the application instance
     *
     * @return Application
     */
    function ethileo_app(): Application
    {
        return Application::getInstance();
    }
}

if (!function_exists('ethileo_make')) {
    /**
     * Resolve a dependency from the container
     *
     * @template T
     * @param string|class-string<T> $name
     * @return mixed|T
     */
    function ethileo_make(string $name)
    {
        return ethileo_app()->make($name);
    }
}

if (!function_exists('ethileo_path')) {
    /**
     * Get the plugin path
     *
     * @param string $path
     * @return string
     */
    function ethileo_path(string $path = ''): string
    {
        return ethileo_app()->path($path);
    }
}

if (!function_exists('ethileo_url')) {
    /**
     * Get the plugin URL
     *
     * @param string $path
     * @return string
     */
    function ethileo_url(string $path = ''): string
    {
        return ethileo_app()->url($path);
    }
}

if (!function_exists('ethileo_view')) {
    /**
     * Render a view
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    function ethileo_view(string $view, array $data = []): string
    {
        $viewPath = ethileo_path('resources/views/' . str_replace('.', '/', $view) . '.php');

        if (!file_exists($viewPath)) {
            return '';
        }

        extract($data);
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}

if (!function_exists('ethileo_config')) {
    /**
     * Get configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function ethileo_config(string $key, $default = null)
    {
        $settings = get_option('ethileo_events_pro_settings', []);
        $keys = explode('.', $key);
        $value = $settings;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

if (!function_exists('ethileo_format_date')) {
    /**
     * Format a date using WordPress settings
     *
     * @param string|\DateTime $date
     * @param string|null $format
     * @return string
     */
    function ethileo_format_date($date, ?string $format = null): string
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d H:i:s');
        }

        $format = $format ?? get_option('date_format');
        return date_i18n($format, strtotime($date));
    }
}

if (!function_exists('ethileo_sanitize_text_field')) {
    /**
     * Sanitize text field with additional security
     *
     * @param string $value
     * @return string
     */
    function ethileo_sanitize_text_field(string $value): string
    {
        return sanitize_text_field(wp_unslash($value));
    }
}

if (!function_exists('ethileo_current_user_can')) {
    /**
     * Check if current user can manage events
     *
     * @param string $capability
     * @return bool
     */
    function ethileo_current_user_can(string $capability = 'manage_ethileo_events'): bool
    {
        return current_user_can($capability);
    }
}
