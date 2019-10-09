<?php

/**
 * Cache purge for Website Accelerator (WSA) by Astral Internet
 * 
 * @author            Astral Internet inc.
 * @copyright         2019 Copyright (C) 2019, Astral Internet inc. - support@astralinternet.com
 * @license           http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 * 
 * @wordpress-plugin
 * Plugin Name: 		Cache purge for the Website Accelerator (WSA) by Astral Internet
 * Plugin URI:      	https://github.com/AstralInternet/WSA-WordPress-Plugin
 * Description:			Cette extension est conçue pour être utilisée sur un serveur exécutant le module d'accélération de site web créé par Astral Internet. Celle-ci sert à vider automatiquement la mémoire cache du serveur lorsqu'une page ou un article est modifié.
 * Version:         	1.0.0
 * Author:				Astral Internet inc.
 * Author URI:			https://www.astralinternet.com/fr
 * License:				GPL v3
 * License URI:			http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: 		wsa
 * Domain Path:     	/i18n
 * Requires at least:	3.5.0
 * Requires PHP:		5.2.2
 *
 * WSA : The Astral Internet Website Accelerator is a tool that allows you to place 
 * certain elements of a site in buffer memory (cache) inside the server. Once the 
 * elements are placed buffer of the server, they can be served much faster to people
 * viewing a website.
 * 
 * This module offers the ability to automatically purge the server cache when a page 
 * or a post is modified. The cache can also be purged manually from the administration 
 * menu bar.
 * 
 */

// Header Translation

__("Cette extension est conçue pour être utilisée sur un serveur exécutant le module d'accélération de site web créé par Astral Internet. Celle-ci sert à vider automatiquement la mémoire cache du serveur lorsqu'une page ou un article est modifié.", "wsa");
__("https://www.astralinternet.com/fr", "wsa");

// If this file is called directly, abort.
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Store the plugin version.
 *
 * @since 1.0.0
 */
define('WSA_VERSION', '1.0.0');

/**
 * Store the plugin name.
 *
 * @since 1.0.0
 */
define('WSA_NAME', 'wsa');

/**
 * Declare the main plugin file, if not alreay declared
 *
 * @since 1.0.0
 */
if (!defined('WSA_FILE')) {
    define('WSA_FILE', __FILE__);
}

/**
 * Include the core plugin class WP_WSA
 *
 * @since 1.0.0
 */
require_once plugin_dir_path(__FILE__) . 'lib/wsa_wp-module.class.php';

/**
 * Include the public WSA class
 *
 * @since 1.0.0
 */
require_once plugin_dir_path(__FILE__) . "vendor/wsa/wsa.class.php";

// Set module local setting
WP_WSA::set_locale();

// Register the activation hook
register_activation_hook(__FILE__, 'WP_WSA::activate');

// Register the uninstall hook
register_uninstall_hook(__FILE__, 'WP_WSA::uninstall');

// Register admin area top "Empty Cache" menu.
add_action('wp_before_admin_bar_render', 'WP_WSA::add_purge_top_admin_menu');

// Register admin area top "Empty Cache" menu.
add_action('admin_menu', 'WP_WSA::add_wsa_tools_menu');

// Register the hook on page preview
add_action('customize_preview_init', 'WP_WSA::purge_hooks');

// Register the hook on page updates and on new pages
add_action('save_post', 'WP_WSA::purge_hooks');

// Add the link for the plugin settings
add_filter('plugin_action_links_wsa/wsa.php', 'WP_WSA::add_settings_link');
