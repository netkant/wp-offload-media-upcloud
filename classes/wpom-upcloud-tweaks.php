<?php

use DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider;

class WPOM_UpCloud_Tweaks
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_filter('as3cf_storage_provider_classes', [$this, 'as3cf_storage_provider_classes']);
        add_filter('as3cf_aws_s3_client_args', [$this, 'as3cf_aws_s3_client_args']);
        add_filter('as3cf_aws_get_regions', [$this, 'as3cf_aws_get_regions']);
        add_filter('as3cf_aws_s3_bucket_in_path', '__return_true');
        add_filter('as3cf_aws_s3_domain', [$this, 'as3cf_aws_s3_domain']);
        add_filter('as3cf_get_item_url', [$this, 'as3cf_get_item_url']);
        add_filter('as3cf_aws_s3_console_url', [$this, 'as3cf_aws_s3_console_url']);
        add_filter('as3cf_allowed_mime_types', [$this, 'as3cf_allowed_mime_types']);
    }

    /**
     * Register the UpCloud Provider class.
     *
     * @param array $classes
     *
     * @return array
     */
    public function as3cf_storage_provider_classes(array $classes): array
    {
        if (
            class_exists('WPOM_UpCloud_Provider') ||
            !class_exists('DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider')
        ) {
            return $classes;
        }

        // Ensure the UpCloud Provider class is loaded
        require_once plugin_dir_path(__FILE__) . 'wpom-upcloud-provider.php';

        // Register the UpCloud Provider class
        return array_merge($classes, [
            AWS_Provider::get_provider_key_name() => WPOM_UpCloud_Provider::class,
        ]);
    }

    /**
     * Add UpCloud region to the list of available regions.
     *
     * @param array $regions
     *
     * @return array
     */
    public function as3cf_aws_get_regions(array $regions): array
    {
        global $as3cf;

        $region  = $as3cf->get_setting('region');
        $regions = [
            $region => sprintf(
                '%s (%s)',
                $region,
                __('UpCloud', 'wp-offload-media-upcloud')
            ),
        ];

        return $regions;
    }

    /**
     * Add UpCloud endpoint to the S3 client arguments.
     *
     * @param array $args
     *
     * @return array
     */
    public function as3cf_aws_s3_client_args(array $args): array
    {
        $endpoint = WPOM_UpCloud::get_setting('AS3CF_UPCLOUD_ENDPOINT');
        if (empty($endpoint)) {
            return $args;
        }

        $args['endpoint']                = $endpoint;
        $args['use_path_style_endpoint'] = true;

        return $args;
    }

    /**
     * Override the S3 domain to use the delivery domain if enabled.
     * Plugin will auto prefix "s3." to the domain
     *
     * @param string $domain
     *
     * @return string
     */
    public function as3cf_aws_s3_domain(string $domain): string
    {
        global $as3cf;

        if ($as3cf->get_setting('enable-delivery-domain')) {
            return $as3cf->get_setting('delivery-domain');
        }

        return $domain;
    }

    /**
     * Remove region from the URL, if it exists.
     * Mainly to fix delivery provider test in backend (I guess).
     *
     * @param string $url
     *
     * @return string
     */
    public function as3cf_get_item_url(string $url): string
    {
        global $as3cf;

        $region = $as3cf->get_setting('region');
        $host   = wp_parse_url($url, PHP_URL_HOST);
        if (strpos($host, '.' . $region) !== false) {
            $host = str_replace('.' . $region, '', $host);
            $url  = str_replace(wp_parse_url($url, PHP_URL_HOST), $host, $url);
        }

        return $url;
    }

    /**
     * Override the S3 console URL to use the delivery domain if enabled.
     * Makes no sense, but just to override the default console URL.
     *
     * @param string $url
     *
     * @return string
     */
    public function as3cf_aws_s3_console_url(string $url): string
    {
        global $as3cf;

        if ($as3cf->get_setting('enable-delivery-domain')) {
            return sprintf('https://s3.%s/', $as3cf->get_setting('delivery-domain'));
        }

        return $url; // return the default URL if delivery domain is not enabled
    }

    /**
     * This filter allows your limit specific mime types of files that
     * can be uploaded to the bucket. They will still be uploaded to the
     * WordPress media library but ignored from the offload process.
     *
     * @param array $types
     *
     * @return array
     */
    public function as3cf_allowed_mime_types(array $types): array
    {
        // Disallow offload of PDFs.
        // unset($types['pdf']);

        // Allow offload of PDFs.
        $types['pdf'] = 'application/pdf';

        // Disallow offload of SVGs.
        // unset($types['svg']);

        // Allow offload of SVGs.
        $types['svg'] = 'image/svg+xml';

        return $types;
    }
}
