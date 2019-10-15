<?php

/**
 *  __      _____   _  
 *  \ \    / / __| /_\  
 *   \ \/\/ /\__ \/ _ \ 
 *    \_/\_/ |___/_/ \_\
 * 
 * Cache purge for Website Accelerator - Plugin class functions
 * 
 * @author          Astral Internet inc. <support@astralinternet.com>
 * @version         1.0.0
 * @copyright       2019 Copyright (C) 2019, Astral Internet inc. - support@astralinternet.com
 * @license         https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 * @link            https://www.astralinternet.com/en Astral Internet inc.
 * 
 * WSA : The Astral Internet Website Accelerator is a tool that allows you to place 
 * certain elements of a site in buffer memory (cache) inside the server. Once the 
 * elements are placed buffer of the server, they can be served much faster to people
 * viewing a website.
 * 
 * Class to handle most actions required by Wordpress. This class oversees loading 
 * the internationalization settings, registering the menus, action to take upon 
 * activation/uninstall and setting the actions hooks.
 *
 */

// If this file is called directly, abort.
defined('ABSPATH') or die('No script kiddies please!');

class WP_WSA
{

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the wsa_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public static function set_locale()
	{

		/**
		 *  sub-function that will load the language (i18n) file into the wordpress admin area
		 * 
		 * @since    1.0.0
	 	 * @return void
		 */
		function wsa_load_plugin_textdomain()
		{
			// Define the plugin path
			$plugin_rel_path = dirname(dirname(plugin_basename(__FILE__))) . '/i18n';

			// Set the language path for wordPress to find it.
			load_plugin_textdomain('wsa', false, $plugin_rel_path);
		}

		// Add load the language files upon loading the module
		add_action('plugins_loaded', 'wsa_load_plugin_textdomain');
	}

	/**
	 * function to register the an "Empty Cache" option in the top admin area menu.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public static function add_purge_top_admin_menu()
	{
		global $wp_admin_bar;

		// Build top menu url with nonce protection
        $url = add_query_arg(
            [
                'page' => 'wsa/lib/wsa_display.php',
                'purge'   => 'vide_moi_tout',
                'nonce'  => wp_create_nonce('wsa_purge-cache'),
            ],
            admin_url()
        );

		$wp_admin_bar->add_menu(array(
			'id' => 'wsa-menu',
			'parent' => false,
			'title' => __("Vider la cache", "wsa"),
			//'href' => admin_url('admin.php?page=wsa%2Flib%2Fwsa_display.php&purge=vide_moi_tout'),
			'href' => esc_url($url),
		));
	}

	/**
	 * Function to register the an the plugin page in the tools menu of wordpress.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public static function add_wsa_tools_menu()
	{
		add_management_page(
			__('Module de cache', 'wsa'),
			'Website Accelerator',
			'manage_options',
			'wsa/lib/wsa_display.php',
			''
		);
	}

	/**
	 * Function to add setting link to the plugins page.
	 *
	 * @since    1.0.0
	 * @param array $links
	 * @return array
	 */
	public static function  add_settings_link($links)
	{
		array_unshift($links, '<a href="tools.php?page=wsa%2Flib%2Fwsa_display.php">Settings</a>');
		return $links;
	}

	/**
	 * Function to start the purge cache procedure if the option for automatic
	 * purging is activated in the plugin.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public static function purge_hooks()
	{
		if (get_option('wsa_auto-purge') == "on") {
			
			//purge the user cache
			WSA::purge_cache();
		}
	}

	/**
	 * Upon plugin activation, will create a new entry in the option table for
	 * the automatic purge trigger.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public static function activate()
	{
		// Check if the option already exist 
		if (!get_option('wsa_auto-purge')) {

			// Add the options with the default value
			update_option('wsa_auto-purge', "on");
		}
	}

	/**
	 * Remove the options added by the plugin from the option table in the database.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public static function uninstall()
	{
		// Check if the option already exist 
		if (get_option('wsa_auto-purge')) {

			// remove the option we added in the db
			delete_option('wsa_auto-purge', "on");
		}
	}
}
