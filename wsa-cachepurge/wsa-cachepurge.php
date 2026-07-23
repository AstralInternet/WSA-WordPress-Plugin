<?php

/**
 * WSA - Website Accelerator Cache Purge
 * 
 * @author            Astral Internet inc.
 * @copyright         2021 Copyright (C) 2021, Astral Internet inc. - support@astralinternet.com
 * @license           https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 * 
 * @wordpress-plugin
 * Plugin Name: 		WSA - Website Accelerator Cache Purge
 * Plugin URI:      	https://github.com/AstralInternet/WSA-WordPress-Plugin
 * Description:			Automatically purges the Astral Internet Website Accelerator server cache when WordPress content changes.
 * Version:         	1.2.1
 * Author:				Astral Internet inc.
 * Author URI:			https://www.astralinternet.com/fr
 * License:				GPL v3 or later
 * License URI:			https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: 		wsa-cachepurge
 * Domain Path:     	/i18n
 * Requires at least:	5.0
 * Tested up to:		7.0
 * Requires PHP:		7.4
 *
 * wsa-cachepurge : The Astral Internet Website Accelerator is a tool that allows you to place 
 * certain elements of a site in buffer memory (cache) inside the server. Once the 
 * elements are placed buffer of the server, they can be served much faster to people 
 * viewing a website.
 * 
 * This module offers the ability to automatically purge the server cache when a page 
 * or a post is modified. The cache can also be purged manually from the administration 
 * menu bar.
 * 
 */

// If this file is called directly, abort.
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Store the plugin version.
 *
 * @since 1.0.0
 */
define('WSA_CACHEPURGE_VERSION', '1.2.1');

/**
 * Store the plugin name.
 *
 * @since 1.0.0
 */
define('WSA_CACHEPURGE_NAME', 'Website Accelerator Cache purge');

/**
 * Declare the main plugin file, if not alreay declared
 *
 * @since 1.0.0
 */
if (!defined('WSA_CACHEPURGE_FILE')) {
    define('WSA_CACHEPURGE_FILE', __FILE__);
}

/**
 * Include the core plugin class WSA_Cachepurge_WP
 *
 * @since 1.0.0
 */
require_once plugin_dir_path(__FILE__) . 'lib/wsa-cachepurge_wp-module.class.php';

/**
 * Include the public WSA class
 *
 * @since 1.0.0
 */
require_once plugin_dir_path(__FILE__) . "vendor/wsa/wsa.class.php";

// Set module local setting
WSA_Cachepurge_WP::set_locale();

// Register the activation hook
register_activation_hook(__FILE__, 'WSA_Cachepurge_WP::activate');

// Register the uninstall hook
register_uninstall_hook(__FILE__, 'WSA_Cachepurge_WP::uninstall');

// Register admin area top "Empty Cache" menu.
add_action('wp_before_admin_bar_render', 'WSA_Cachepurge_WP::add_purge_top_admin_menu');

// Register admin area top "Empty Cache" menu.
add_action('admin_menu', 'WSA_Cachepurge_WP::add_tools_menu');

// Register the hook on page preview
add_action('customize_preview_init', 'WSA_Cachepurge_WP::purge_hooks');

// Register the hook on page updates and on new pages
add_action('save_post', 'WSA_Cachepurge_WP::purge_hooks');

// Add the link for the plugin settings
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'WSA_Cachepurge_WP::add_settings_link');

/**
 * Hook into other Cache plugin to clear server cache at the same time
 * has the other plugins
 * 
 * @since 1.0.1
 */
WSA_Cachepurge_WP::Add_Cache_Plugins_Hooks();
