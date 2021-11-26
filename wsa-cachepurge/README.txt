=== WSA - Website Accelerator Cache Purge ===
Contributors: @astralinternet, @neutrall, @sleyeur
Tags: cache, purge, wsa, cpwsa, wsa-cachepurge, website acceleration
Requires at least: 3.5
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

On servers using the website accelerator by Astral Internet, the module offers the ability to automatically purge the cache when a page is modified.

== Description ==
The Website Accelerator by Astral Internet is a tool that allows you to place certain elements of a site in buffer memory (cache) inside the server. Once the elements are placed buffer of the server, they can be served much faster to people viewing a website.
The website accelerator maximizes the Nginx caching feature while offering granular configuration for each site. More information concerning the WSA module is available on this blog “[No more slow websites!]( https://www.astralinternet.com/blog/en/no-more-slow-websites/)”.

== Installation ==
1. Upload the plugin files to the /wp-content/plugins/ directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress

Upon installation, the default setting will purge the cache upon each page refresh. This cache be disactivated through the plugin interface in the tool’s menu.

== Frequently Asked Questions ==
= Will this plugin work for me =
This plugin will function on servers that have the Website Accelerator installed. If the acceleration module is not installed the module can still be installed and will not break any functionality of the website but will simply be useless.

== Screenshots ==
1. Main interface.


== Changelog ==

= 1.1.0 =
* Updated the WSA module class to the version 1.1.0 (was previouly on version 1.0.0)
* Change the method used to detect if the WSA module is installed. Work will previous version of WSA and with the newer versions 1.1.X
* Removed jQuery dependency, JS is now PureJS

= 1.0.9 =
* New name for the plugin.

= 1.0.8 =

* Add new namespace on class.

= 1.0.7 =
* Removed Swift Performance intergration since it is now included directly in Swift Performance.

= 1.0.6 =
* Fixed CloudFlare blocking curl request when using no UserAgent.

= 1.0.5 =
* Fixed string match when checking if the module is available on the server.

= 1.0.4 =
* Fixed array declaration in PHP 5.3.

= 1.0.3 =
* Fixed text domain error for translation.

= 1.0.2 =
* Fixed menu links to match the slug change from the official WordPress repo.
* Fixed path regex in wsa main class.
* Fixed jQuery call in interface.

= 1.0.1 =
* Hooked into popular existing cache plugin to clear server cache with those plugins
  * W3 Total Cache
  * Swift Performance
  * WP Super Cache
  * WP Fastest Cache
  * LiteSpeed Cache
  * Auto optimize
* Added "before" and "after" hookable event when puring the server cache

= 1.0 =
* First official version available.