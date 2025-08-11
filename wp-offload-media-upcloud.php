<?php

/**
 * Plugin Name: WP Offload Media - UpCloud
 * Plugin URI:  https://netkant.com/
 * Author:      Netkant
 * Author URI:  https://netkant.com/
 * Description: Tweaks "WP Offload Media" to use UpCloud Object Storage, which works seamlessly with the Amazon S3 API.
 * Text Domain: wp-offload-media-upcloud
 * Version:     1.0.0
 * 
 * @wordpress-plugin
 * @author Henrik Urlund <henrik@netkant.com>
 * @license GPL-3.0+
 * @package WP Offload Media - UpCloud
 */

require_once plugin_dir_path(__FILE__) . 'classes/wpom-upcloud.php';

new WPOM_UpCloud();
