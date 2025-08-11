<?php

class WPOM_UpCloud
{
    /**
     * Constructor to initialize the plugin.
     */
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init_settings'], 20);
        add_action('plugins_loaded', [$this, 'init_tweaks'], 30);
        add_action('admin_notices', [$this, 'admin_notice_check_dependencies']);
    }

    /**
     * Get a setting value by name, with an optional default.
     */
    public static function get_setting($name, $default = '')
    {
        return defined($name) ? constant($name) : apply_filters(strtolower($name), $default);
    }

    /**
     * Initialize the settings for WP Offload Media - UpCloud.
     * This will define the AS3CF_SETTINGS constant with the UpCloud settings.
     */
    public function init_settings(): void
    {
        // Check if the required constant is defined, otherwise show an error notice
        if (!self::get_setting('AS3CF_UPCLOUD_ENDPOINT')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_constant_endpoint']);
        }

        // Check if WP Offload Media is active and the settings are not already defined
        if (defined('AS3CF_SETTINGS') || !is_plugin_active('amazon-s3-and-cloudfront-pro/amazon-s3-and-cloudfront-pro.php')) {
            return; // Settings already defined, no need to redefine, or WP Offload Media is not active
        }

        // Check if the required constants are defined, otherwise show an error notice
        if (!self::get_setting('AS3CF_UPCLOUD_REGION') || !self::get_setting('AS3CF_UPCLOUD_ACCESS_ID') || !self::get_setting('AS3CF_UPCLOUD_SECRET_KEY')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_constants']);
        }

        // use the host from the blog URL to set the bucket and domain if not already defined
        $host = wp_parse_url(get_bloginfo('url'), PHP_URL_HOST);

        // Define the AS3CF_SETTINGS constant with the UpCloud settings
        define('AS3CF_SETTINGS', serialize(apply_filters('as3cf_upcloud_settings', [
            // provider settings
            'provider'               => 'aws',
            'region'                 => self::get_setting('AS3CF_UPCLOUD_REGION'),
            'access-key-id'          => self::get_setting('AS3CF_UPCLOUD_ACCESS_ID'),
            'secret-access-key'      => self::get_setting('AS3CF_UPCLOUD_SECRET_KEY'),
            'bucket'                 => self::get_setting('AS3CF_UPCLOUD_BUCKET', sanitize_title($host)),
            #'block-public-access'       => false, // currently not supported in wp-config.php
            #'object-ownership-enforced' => false, // currently not supported in wp-config.php
            // storage settings
            'copy-to-s3'             => true,
            'enable-object-prefix'   => true,
            'object-prefix'          => 'wp-content/uploads/',
            'use-yearmonth-folders'  => true,
            'remove-local-file'      => !boolval(self::get_setting('AS3CF_UPCLOUD_DEBUG', true)),
            'object-versioning'      => false,
            // delivery settings
            'delivery-provider'      => 'storage',
            'serve-from-s3'          => true,
            'force-https'            => true,
            'enable-delivery-domain' => true, // normally not supported by 'storage' provider
            'delivery-domain'        => self::get_setting('AS3CF_UPCLOUD_DOMAIN', $host), // "s3." will be prefixed automatically
        ])));
    }

    /**
     * Register the UpCloud Provider class with WP Offload Media.
     */
    public function init_tweaks(): void
    {
        if (!is_plugin_active('amazon-s3-and-cloudfront-pro/amazon-s3-and-cloudfront-pro.php')) {
            return; // Ensure the WP Offload Media plugin is active before proceeding
        }

        // Include the UpCloud Tweaks class
        require_once plugin_dir_path(__FILE__) . 'wpom-upcloud-tweaks.php';

        // Register the UpCloud Tweaks
        new WPOM_UpCloud_Tweaks();
    }

    /**
     * Check if the WP Offload Media plugin is active and display an admin notice if not.
     */
    public function admin_notice_check_dependencies(): void
    {
        if (!is_plugin_active('amazon-s3-and-cloudfront-pro/amazon-s3-and-cloudfront-pro.php')) {
            echo '<div class="notice notice-error"><p>The "<b>WP Offload Media - UpCloud</b>" plugin requires the <b>WP Offload Media</b> plugin to be installed and activated.</p></div>';
        }
    }

    /**
     * Display an admin notice if the required constants are not defined.
     * This is used to inform the user that they need to define the constants in wp-config.php.
     */
    public function admin_notice_missing_constants(): void
    {
        echo '<div class="notice notice-error"><p>The "<b>WP Offload Media - UpCloud</b>" plugin requires the following constants to be defined in wp-config.php: <br><b>AS3CF_UPCLOUD_ENDPOINT</b>, <b>AS3CF_UPCLOUD_REGION</b>, <b>AS3CF_UPCLOUD_ACCESS_ID</b>, and <b>AS3CF_UPCLOUD_SECRET_KEY</b>.</p></div>';
    }

    /**
     * Display an admin notice if the AS3CF_UPCLOUD_ENDPOINT constant is not defined.
     */
    public function admin_notice_missing_constant_endpoint(): void
    {
        echo '<div class="notice notice-error"><p>The "<b>WP Offload Media - UpCloud</b>" plugin requires the <b>AS3CF_UPCLOUD_ENDPOINT</b> constant to be defined in wp-config.php</p></div>';
    }
}
