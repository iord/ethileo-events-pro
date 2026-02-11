<?php

declare(strict_types=1);

namespace Ethileo\EventsPro\Core;

use DI\Container;
use DI\ContainerBuilder;
use Ethileo\EventsPro\Core\Exception\PluginException;
use Ethileo\EventsPro\Core\ServiceProvider\ServiceProviderInterface;

/**
 * Main Application Class
 * 
 * Singleton pattern for managing the plugin lifecycle and dependency injection
 *
 * @package Ethileo\EventsPro\Core
 */
final class Application
{
    /**
     * Application instance
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Dependency injection container
     *
     * @var Container
     */
    private Container $container;

    /**
     * Service providers
     *
     * @var ServiceProviderInterface[]
     */
    private array $providers = [];

    /**
     * Boot status
     *
     * @var bool
     */
    private bool $booted = false;

    /**
     * Private constructor (Singleton pattern)
     *
     * @throws PluginException
     */
    private function __construct()
    {
        $this->buildContainer();
        $this->registerServiceProviders();
    }

    /**
     * Get application instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Build the dependency injection container
     *
     * @return void
     * @throws PluginException
     */
    private function buildContainer(): void
    {
        try {
            $builder = new ContainerBuilder();
            $builder->useAutowiring(true);
            $builder->useAttributes(false);

            // Add definitions
            $configPath = ETHILEO_EVENTS_PRO_PATH . 'config/';
            if (file_exists($configPath . 'dependencies.php')) {
                $builder->addDefinitions($configPath . 'dependencies.php');
            }

            $this->container = $builder->build();

            // Bind the container itself
            $this->container->set(Container::class, $this->container);
            $this->container->set(Application::class, $this);
        } catch (\Exception $e) {
            throw new PluginException(
                'Failed to build dependency injection container: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Register service providers
     *
     * @return void
     */
    private function registerServiceProviders(): void
    {
        $providers = [
            \Ethileo\EventsPro\Infrastructure\ServiceProvider\DatabaseServiceProvider::class,
            \Ethileo\EventsPro\Infrastructure\ServiceProvider\StorageServiceProvider::class,
            \Ethileo\EventsPro\Infrastructure\ServiceProvider\EmailServiceProvider::class,
            \Ethileo\EventsPro\Application\ServiceProvider\EventServiceProvider::class,
            \Ethileo\EventsPro\Application\ServiceProvider\GuestServiceProvider::class,
            \Ethileo\EventsPro\Application\ServiceProvider\QRCodeServiceProvider::class,
            \Ethileo\EventsPro\Application\ServiceProvider\PhotoServiceProvider::class,
            \Ethileo\EventsPro\Presentation\ServiceProvider\AdminServiceProvider::class,
            \Ethileo\EventsPro\Presentation\ServiceProvider\FrontendServiceProvider::class,
            \Ethileo\EventsPro\Presentation\ServiceProvider\APIServiceProvider::class,
        ];

        foreach ($providers as $providerClass) {
            if (class_exists($providerClass)) {
                $provider = $this->container->get($providerClass);
                if ($provider instanceof ServiceProviderInterface) {
                    $this->providers[] = $provider;
                    $provider->register();
                }
            }
        }
    }

    /**
     * Boot the application
     *
     * @return void
     * @throws PluginException
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        try {
            // Boot all service providers
            foreach ($this->providers as $provider) {
                $provider->boot();
            }

            // Load text domain for translations
            $this->loadTextDomain();

            // Hook into WordPress
            $this->registerWordPressHooks();

            $this->booted = true;

            // Fire custom action after boot
            do_action('ethileo_events_pro_booted', $this);
        } catch (\Exception $e) {
            throw new PluginException(
                'Failed to boot application: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Load plugin text domain for translations
     *
     * @return void
     */
    private function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'ethileo-events-pro',
            false,
            dirname(ETHILEO_EVENTS_PRO_BASENAME) . '/resources/lang'
        );
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function registerWordPressHooks(): void
    {
        // Enqueue assets
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);

        // Register custom post types and taxonomies
        add_action('init', [$this, 'registerPostTypes'], 0);

        // Register REST API routes
        add_action('rest_api_init', [$this, 'registerRestRoutes']);

        // Add custom capabilities
        add_action('admin_init', [$this, 'registerCapabilities']);
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // Only enqueue on plugin pages
        if (strpos($hook, 'ethileo-events') === false) {
            return;
        }

        wp_enqueue_style(
            'ethileo-events-pro-admin',
            ETHILEO_EVENTS_PRO_URL . 'assets/css/admin.css',
            [],
            ETHILEO_EVENTS_PRO_VERSION
        );

        wp_enqueue_script(
            'ethileo-events-pro-admin',
            ETHILEO_EVENTS_PRO_URL . 'assets/js/admin.js',
            ['jquery', 'wp-api'],
            ETHILEO_EVENTS_PRO_VERSION,
            true
        );

        wp_localize_script('ethileo-events-pro-admin', 'ethileoEventsProAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('ethileo-events/v1'),
            'nonce' => wp_create_nonce('ethileo-events-pro'),
            'i18n' => [
                'confirmDelete' => __('Are you sure you want to delete this?', 'ethileo-events-pro'),
                'error' => __('An error occurred. Please try again.', 'ethileo-events-pro'),
            ],
        ]);
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        wp_enqueue_style(
            'ethileo-events-pro-frontend',
            ETHILEO_EVENTS_PRO_URL . 'assets/css/frontend.css',
            [],
            ETHILEO_EVENTS_PRO_VERSION
        );

        wp_enqueue_script(
            'ethileo-events-pro-frontend',
            ETHILEO_EVENTS_PRO_URL . 'assets/js/frontend.js',
            ['jquery'],
            ETHILEO_EVENTS_PRO_VERSION,
            true
        );

        wp_localize_script('ethileo-events-pro-frontend', 'ethileoEventsPro', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('ethileo-events/v1'),
            'nonce' => wp_create_nonce('ethileo-events-pro'),
        ]);
    }

    /**
     * Register custom post types and taxonomies
     *
     * @return void
     */
    public function registerPostTypes(): void
    {
        do_action('ethileo_events_pro_register_post_types', $this->container);
    }

    /**
     * Register REST API routes
     *
     * @return void
     */
    public function registerRestRoutes(): void
    {
        do_action('ethileo_events_pro_register_rest_routes', $this->container);
    }

    /**
     * Register custom capabilities
     *
     * @return void
     */
    public function registerCapabilities(): void
    {
        do_action('ethileo_events_pro_register_capabilities', $this->container);
    }

    /**
     * Get the DI container
     *
     * @return Container
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Resolve a dependency from the container
     *
     * @template T
     * @param string|class-string<T> $name
     * @return mixed|T
     */
    public function make(string $name)
    {
        return $this->container->get($name);
    }

    /**
     * Check if application is booted
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function version(): string
    {
        return ETHILEO_EVENTS_PRO_VERSION;
    }

    /**
     * Get plugin path
     *
     * @param string $path
     * @return string
     */
    public function path(string $path = ''): string
    {
        return ETHILEO_EVENTS_PRO_PATH . ltrim($path, '/');
    }

    /**
     * Get plugin URL
     *
     * @param string $path
     * @return string
     */
    public function url(string $path = ''): string
    {
        return ETHILEO_EVENTS_PRO_URL . ltrim($path, '/');
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
