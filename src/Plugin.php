<?php

declare(strict_types=1);

namespace Woogostores\Plugin;

use Woogostores\Plugin\Inc\DependencyInjection\Container;
use Woogostores\Plugin\Inc\EventManagement\EventManager;
use Woogostores\Plugin\Modules\Woocommerce\CleanupSubscriber;
use Woogostores\Plugin\Modules\Woogo\ConfigSubscriber;
use Woogostores\Plugin\Modules;

class Plugin
{
    /**
     * The plugin's dependency injection container.
     *
     * @var Container
     */
    private $container;


    /**
     * The file path of the plugin.
     *
     * @var string
     */
    private $filePath;


    public function __construct(string $filePath)
    {
        if (!defined('ABSPATH')) {
            throw new \RuntimeException('"ABSPATH" constant isn\'t defined');
        }

        $rootDirectory = ABSPATH;

        if ('/wp/' === substr($rootDirectory, -4)) {
            $rootDirectory = substr($rootDirectory, 0, -3);
        }

        $this->container = new Container([
            'root_directory' => $rootDirectory,
            'plugin_name' => basename($filePath, '.php'),
            'file_path' => $filePath,
        ]);

        $this->container['plugin_basename'] = fn ($c) => plugin_basename($c['file_path']);
        $this->container['plugin_dir_path'] = fn ($c) => plugin_dir_path($c['file_path']);
        $this->container['plugin_dir_url'] = fn ($c) => plugin_dir_url($c['file_path']);
        $this->container['plugin_relative_path'] = fn ($c) => '/'.trim(str_replace($c['root_directory'], '', plugin_dir_path($c['file_path'])), '/');

        $this->container['event_manager'] = fn ($c) => new EventManager();

        $this->container['event_manager']->addSubscriber(new CleanupSubscriber());

        $this->container['event_manager']->addSubscriber(new ConfigSubscriber());
        $this->container['event_manager']->addSubscriber(new Modules\Woogo\AdminStyleSubscriber($this->container->get('plugin_dir_url')));
        $this->container['event_manager']->addSubscriber(new Modules\Plugins\PDFConfigSubscriber());
        $this->container['event_manager']->addSubscriber(new Modules\Plugins\ElementorConfigSubscriber());



        $this->loaded = false;
    }

    /**
     * Get the plugin's dependency injection container.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Checks if the plugin is loaded.
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Loads the plugin into WordPress.
     */
    public function load()
    {
        if ($this->isLoaded()) {
            return;
        }

        // $this->container['plugin_basename'] = plugin_basename($this->container['file_path']);
        // $this->container['plugin_dir_path'] = plugin_dir_path($this->container['file_path']);
        // $this->container['plugin_dir_url'] = plugin_dir_url($this->container['file_path']);
        // $this->container['plugin_relative_path'] = '/'.trim(str_replace($this->container['root_directory'], '', plugin_dir_path($this->container['file_path'])), '/');

        // foreach ($this->container['local_commands'] as $command) {
        //     $this->registerCommand($command);
        // }

        // foreach ($this->container['priority_subscribers'] as $subscriber) {
        //     $this->container['event_manager']->addSubscriber($subscriber);
        // }

        // foreach ($this->container['subscribers'] as $subscriber) {
        //     $this->container['event_manager']->addSubscriber($subscriber);
        // }

        // foreach ($this->container['commands'] as $command) {
        //     $this->registerCommand($command);
        // }

        $this->loaded = true;
    }

    /**
     * Register the given command with WP-CLI.
     */
    private function registerCommand(CommandInterface $command)
    {
        if (!$this->container['is_wp_cli'] || !class_exists('\WP_CLI')) {
            return;
        }

        \WP_CLI::add_command($command::getName(), $command, [
            'shortdesc' => $command::getDescription(),
            'synopsis' => $command::getSynopsis(),
        ]);
    }
}
