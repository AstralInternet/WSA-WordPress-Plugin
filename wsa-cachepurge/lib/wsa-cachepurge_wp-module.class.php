<?php

/**
 *  __      _____   _  
 *  \ \    / / __| /_\  
 *   \ \/\/ /\__ \/ _ \ 
 *    \_/\_/ |___/_/ \_\
 * 
 * WSA - Website Accelerator Cache Purge - Plugin class functions
 * 
 * @author          Astral Internet inc. <support@astralinternet.com>
 * @version         1.1.1
 * @copyright       2021 Copyright (C) 2021, Astral Internet inc. - support@astralinternet.com
 * @license         https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 * @link            https://www.astralinternet.com/en Astral Internet inc.
 * 
 * WSA-Cachepurge : The Astral Internet Website Accelerator is a tool that 
 * allows you to place certain elements of a site in buffer memory (cache) 
 * inside the server. Once the elements are placed buffer of the server, they 
 * can be served much faster to peopleviewing a website.
 * 
 * Class to handle most actions required by Wordpress. This class oversees 
 * loading the internationalization settings, registering the menus, action to
 * take upon activation/uninstall and setting the actions hooks.
 *
 */

// If this file is called directly, abort.
defined('ABSPATH') or die('No script kiddies please!');

class WSA_Cachepurge_WP
{

    /**
     * Define the module options used for the activate and deactivate function
     *
     * @since 1.1.0
     */
	const WP_OPTIONS_LIST = array('wsa-cachepurge_wsa-installed', 'wsa-cachepurge_auto-purge');

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WSA_Cachepurge_i18n class in order to set the domain and to register the
	 * hook with WordPress.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public static function set_locale()
	{

		/**
		 * sub-function that will load the language (i18n) file into the 
		 * wordpress admin area
		 * 
		 * @since    1.0.0
		 * @return void
		 */
		function wsa_cachepurge_load_plugin_textdomain()
		{
			// Define the plugin path
			$plugin_rel_path = dirname(dirname(plugin_basename(__FILE__))) .
				'/i18n';

			// Set the language path for wordPress to find it.
			load_plugin_textdomain('wsa-cachepurge', false, $plugin_rel_path);
		}

		// Add load the language files upon loading the module
		add_action('plugins_loaded', 'wsa_cachepurge_load_plugin_textdomain');
	}

	/**
	 * function to register the an "Empty Cache" option in the top admin area 
	 * menu.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public static function add_purge_top_admin_menu()
	{

		global $wp_admin_bar;

		// Build top menu url with nonce protection
		$url = add_query_arg(
			array(
				'page' => 'wsa-cachepurge/lib/wsa-cachepurge_display.php',
				'purge'   => 'empty_me',
				'nonce'  => wp_create_nonce('wsa-cachepurge_purge-cache'),
			),
			admin_url() . "admin.php"
		);

		$wp_admin_bar->add_menu(array(
			'id' => 'wsa-cachepurge-menu',
			'parent' => false,
			'title' => __("Vider la cache", "wsa-cachepurge"),
			'href' => esc_url($url),
		));
	}

	/**
	 * Function to register the an the plugin page in the tools menu of 
	 * wordpress.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public static function add_tools_menu()
	{

		add_management_page(
			__('Module de cache', 'wsa-cachepurge'),
			WSA_CACHEPURGE_NAME,
			'manage_options',
			'wsa-cachepurge/lib/wsa-cachepurge_display.php',
			''
		);
	}

	/**
	 * Function to add setting link to the plugins page.
	 *
	 * @since    1.0.0
	 * @last_update 
	 * @param array $links
	 * @return array
	 */
	public static function add_settings_link($links)
	{

		$linkToAdd = '<a href="tools.php?' .
			'page=wsa-cachepurge/lib/wsa-cachepurge_display.php">' .
			__("Réglages", "wsa-cachepurge") . '</a>';

		array_unshift($links, $linkToAdd);
		return $links;
	}

	/**
	 * Function to start the purge cache procedure if the option for automatic
	 * purging is activated in the plugin.
	 * 
	 * @since    1.0.0
	 * @version  1.1.2
	 * @return void
	 */
	public static function purge_hooks()
	{

		// Fetch the auto purge setting
		$autoPurge = get_option('wsa-cachepurge_auto-purge');

		// Clear the WSA cache
		if ($autoPurge) {
			WSAHandler\WSA::purge_cache();
		}
	}

	/**
	 * Upon plugin activation, will create a new entry in the option table for
	 * the automatic purge trigger.
	 * 
	 * @since    1.0.0
	 * @version  1.1.0
	 * @return void
	 */
	public static function activate()
	{
		// Parse each options used by the module
		foreach (WSA_Cachepurge_WP::WP_OPTIONS_LIST as $singleOptions) {

			// Check if the option already exist 
			if (!get_option($singleOptions)) {

				// Define the option value
				switch ($singleOptions) {

					// enable auto-purge by default
					case 'wsa-cachepurge_auto-purge':
						$value=1;
						break;

					default:
						$value=0;
						break;
				}

				// add/update tthe option in the WP database
				update_option($singleOptions, $value);
			}
		}
	}

	/**
	 * Remove the options added by the plugin from the option table in the 
	 * database.
	 * 
	 * @since    1.0.0
	 * @version  1.1.0
	 * @return void
	 */
	public static function uninstall()
	{
		// Parse each options used by the module
		foreach (WSA_Cachepurge_WP::WP_OPTIONS_LIST as $singleOptions) {

			// First check if the options exist
			if (get_option($singleOptions)) {

				// remove the option from the DB
				delete_option($singleOptions);
			}
		}
	}

	/**
	 * Function to purge the cache with WP hooks included
	 * 
	 * @since    1.0.1
	 * @version  1.1.2
	 * @return void
	 */
	public static function purge_cache()
	{

		// Event hook before purging the cache
		do_action('wsa-cachepurge_before_cache_purge');

		// Call the purge cache function from the WSA class
		WSAHandler\WSA::purge_cache();

		// Event Hook after purging the cache
		do_action('wsa-cachepurge_after_cache_purge');
	}

	/**
	 * Hook itself into the other cache purge plugins
	 * 
	 * @since    1.0.1
	 * @return void
	 */
	public static function Add_Cache_Plugins_Hooks()
	{

		// Add W3 Total Cache
		self::add_W3_Total_Cache_Hooks();

		// Add WP Super Cache
		self::add_WP_Super_Cache_Hooks();

		// Add WP fastest Cache
		self::add_WP_Fastest_Cache_Hooks();

		// Add Auto Optimize
		self::add_Auto_Optimize_Hooks();

		// Add LiteSpeed
		self::add_LiteSpeed_Cache_Hooks();
	}



	/**
	 * Hook itself into W3 Total Cache extension
	 * 
	 * @since 1.0.1
	 * @return void
	 */
	private static function add_W3_Total_Cache_Hooks()
	{

		// On clear all cache
		add_action(
			'w3tc_flush_all',
			'WSA_Cachepurge_WP::purge_cache();'
		);

		// On purge all post
		add_action(
			'w3tc_flush_posts',
			'WSA_Cachepurge_WP::purge_cache();'
		);

		// On browser cache purge
		add_action(
			'w3tc_flush_after_browsercache',
			'WSA_Cachepurge_WP::purge_cache();'
		);

		// On minify object cache purge
		add_action(
			'w3tc_flush_after_minify',
			'WSA_Cachepurge_WP::purge_cache();'
		);

		// After Object cache flush
		add_action(
			'w3tc_flush_after_objectcache',
			'WSA_Cachepurge_WP::purge_cache();'
		);
	}

	/**
	 * Hook itself into WP Super Cache
	 * 
	 * @since 1.0.1
	 * @return void
	 */
	private static function add_WP_Super_Cache_Hooks()
	{

		// On clear all cache
		add_action('wp_cache_cleared', 'WSA_Cachepurge_WP::purge_cache();');
	}

	/**
	 * Hook itself into WP Fastest Cache
	 * 
	 * @since 1.0.1
	 * @return void
	 */
	private static function add_WP_Fastest_Cache_Hooks()
	{

		// On clear cache
		add_action('wpfc_delete_cache', 'WSA_Cachepurge_WP::purge_cache();');

		// On clear all cache
		add_action('wpfc_clear_all_cache', 'WSA_Cachepurge_WP::purge_cache();');
	}

	/**
	 * Hook itself into WP Fastest Cache
	 * 
	 * @since 1.0.1
	 * @return void
	 */
	private static function add_Auto_Optimize_Hooks()
	{

		// Clear page cache
		add_action(
			'autoptimize_action_cachepurged',
			'WSA_Cachepurge_WP::purge_cache();'
		);

		// On clear all cache
		add_action('cachify_flush_cache', 'WSA_Cachepurge_WP::purge_cache();');
	}

	/**
	 * Hook itself into LiteSpeed
	 * 
	 * @since 1.0.1
	 * @return void
	 */
	private static function add_LiteSpeed_Cache_Hooks()
	{

		// On clear all cache
		add_action('litespeed_cache_api_purge', 'WSA_Cachepurge_WP::purge_cache();');
	}
}
