<?php

/**
 *  __      _____   _
 *  \ \    / / __| /_\
 *   \ \/\/ /\__ \/ _ \
 *    \_/\_/ |___/_/ \_\
 *
 * Astral Internet - Website Acceleration class
 *
 * @author          Astral Internet inc. <support@astralinternet.com>
 * @version         1.2.0
 * @copyright       2021 Copyright (C) 2021, Astral Internet inc. -
 *                  support@astralinternet.com
 * @license         https://www.gnu.org/licenses/gpl-3.0.html GNU General
 *                  Public License, version 3 or higher
 * @link            https://github.com/AstralInternet/WSA-Website-Acceleration-PHP-class
 *
 * WSA-Cachepurge : The Website Accelerator by Astral Internet is a tool that allows you
 * to place certain elements of a site in buffer memory (cache) inside the
 * server. Once the elements are placed buffer of the server, they can be
 * served much faster to people viewing a website.
 *
 * This class will make it easier for anyone wanting to integrate the wsa
 * functionality directly into their website.
 *
 * ############################################################################
 * #                                 Usage                                    #
 * ############################################################################
 *
 * WSA::is_module_installed
 *  : Function that will try to determine if the WSA module is currently
 *  : installed inside the server. If the function can detect the module, it
 *  : will return true.
 *
 *  $p_extendedValidation : Default to false, When true, the module will look 
 *                          further than simple checking a page header 
 *                          response.
 *
 * WSA:purge_cache (bool $p_purgeAll = false, string $p_fullPath = null)
 *  : Function that will write the file being check by the WSA deamon in order
 *  : to initiate the cache purging procedure in the server. The function will
 *  : return true if it manage to write the file.
 *
 *  $p_purgeAll : Set to true if you want to empty the cache for all domains
 *                that belong to the user, otherwise the class will try to
 *                empty the cache for the domain in use only.
 *
 *  $p_fullPath  : Allow you to override the default ".wsa" folder. If for som
 *                 reason the class cannot find the path, it can be manually
 *                 specified.
 *
 * WSA::purge_url (string|string[] $p_urls, string $p_fullPath = null)
 *  : Page-level complement to purge_cache. Appends one line per URL to the
 *  : empty.me file in the format "URL:<url>, DOMAIN:<host>". The WSA daemon
 *  : picks up each line, validates the domain against the cPanel user, and
 *  : purges only the listed pages — leaving the rest of the cache intact.
 *
 *  $p_urls      : Single URL string or an array of URLs. Each entry must be
 *                 a fully qualified URL (http:// or https://). Malformed
 *                 entries are silently skipped so a bad URL does not abort
 *                 a batch request.
 *
 *  $p_fullPath  : Optional override for the ".wsa" folder location, same
 *                 semantics as in purge_cache.
 *
 */

namespace WSAHandler;

class WSA
{

    /**
     * Define the directory name used by the WSA caching module
     *
     * @since 1.0.0
     */
    const WSA_CACHE_PATH = '.wsa';

    /**
     * Maximum of time the script can move up the absolute path in order to
     * find the cache path.
     *
     * @since 1.0.0
     */
    const WSA_MAX_BACKTRACE = 5;

    /**
     * The cache purge will be triggered automatically when the empty.me file
     * is found in “.wsa” folder. If the file contains a domain name that
     * belongs to the cPanel user, then only the cache of that domain will be
     * purged.
     *
     * This method will write the file in the appropriate folder. One can
     * manually provide the folder path if for some reason the script doesn’t
     * manage to find it itself.
     *
     * @param string  Full path where the .wsa folder is.
     * @param bool    Set to true to empty all the user cache, otherwise will
     *                empty only the crrent domain cache.
     *
     * @return bool  True if the file was properly created
     *
     * @since 1.0.0
     */
    public static function purge_cache($p_purgeAll = false, $p_fullPath = null)
    {
        // Set a return value in case the path wasn't found
        $pathFound = false;

        // Check the provided path (if provided)
        if ($p_fullPath != null) {

            // Build the path, trim start and end backslash to prevent error.
            $absPath = '/' . trim($p_fullPath, '/') . '/'
                . self::WSA_CACHE_PATH;

            // Set the valid flag if the file path is good.
            if (is_writable($absPath)) {
                $pathFound = true;
            }
        }

        // Try to find the absolute path if it wasn't provided or wasn't valid
        if (!$pathFound) {

            // Run the path discovery function
            $absPath = self::find_absolute_path_by_dir();

            // Set the valid flag if the file path was found
            if ($absPath != '') {
                // create folder if not exist
                if (!is_dir($absPath)) {
                    mkdir($absPath, 750);
                    $pathFound = true;
                } else {
                    if (is_writable($absPath)) {
                        $pathFound = true;
                    }
                }
            }
        }

        // Write the file for the cache purge if the path was found
        if ($pathFound) {

            // Add file name to the absolute path
            $absPath .= "empty.me";

            // Get the current domain name to place in the file purge
            if ($p_purgeAll) {

                /**
                 * If a string other than a user domain name is placed, all the
                 * user cache will be emptied.
                 */
                $domainName = 'Purge all my cache please!!!';
            } else {

                // Fetch the current domain name being used
                $domainName = self::fetch_current_domain(false);
            }

            /**
             * If for some reason the file existed prior the new file, delete
             * it.
             */
            $pathFound = self::clean_purge_carche_file($absPath);

            /**
             * Only try to create the cache purge file if the file is not
             * present.
             */
            if ($pathFound) {

                /**
                 * Change the return trigger to false if the file writing
                 * failed for some reason.
                 */
                if (!file_put_contents($absPath, $domainName)) {
                    $pathFound = false;
                }
            }
        }

        // Return the function result
        return $pathFound;
    }

    /**
     * Queue one or more specific URLs for cache purge.
     *
     * Each URL is appended to empty.me as a single line in the format:
     *   URL:<url>, DOMAIN:<host>
     *
     * The WSA daemon on the server side consumes the file, validates that
     * <host> belongs to the cPanel user, and purges only the matching cache
     * entries. This is the page-level complement to purge_cache(), which
     * targets a full domain or the whole user cache.
     *
     * If empty.me already exists (e.g. a previous call has not yet been
     * processed by the daemon) the new lines are appended instead of
     * overwriting, so concurrent purge requests never cancel each other.
     *
     * @param string|string[] $p_urls     Single URL or list of URLs. Each
     *                                    URL must start with http:// or
     *                                    https://. Non-string, empty, or
     *                                    malformed entries are skipped.
     * @param string|null     $p_fullPath Optional override for the .wsa
     *                                    folder location (same semantics
     *                                    as purge_cache).
     *
     * @return bool  True when at least one valid URL line was written,
     *               false when no URL validated, the .wsa folder could
     *               not be located, or the write itself failed.
     *
     * @since 1.2.0
     */
    public static function purge_url($p_urls, $p_fullPath = null)
    {
        // Accept a single URL as convenience; normalise to an array so the
        // rest of the method does not have to care about the input shape.
        if (is_string($p_urls)) {
            $p_urls = array($p_urls);
        }
        if (!is_array($p_urls) || empty($p_urls)) {
            return false;
        }

        // Validate each URL independently. We keep the survivors so a
        // single malformed entry never aborts a batch request.
        $validEntries = array();
        foreach ($p_urls as $url) {

            // Skip anything that is not a non-empty string up front.
            if (!is_string($url)) {
                continue;
            }
            $url = trim($url);
            if ($url === '') {
                continue;
            }

            // Require an explicit scheme: the server-side cache key is
            // built from $scheme, so "example.com/page" cannot resolve.
            if (!preg_match('#^https?://#i', $url)) {
                continue;
            }

            // Pull the host so the daemon can cross-check ownership
            // without re-parsing the URL itself.
            $parsed = parse_url($url);
            if (empty($parsed['host'])) {
                continue;
            }
            $host = strtolower($parsed['host']);

            // Only allow valid DNS characters to reach the daemon.
            if (!preg_match('/^[a-z0-9.\-]+$/', $host)) {
                continue;
            }

            $validEntries[] = 'URL:' . $url . ', DOMAIN:' . $host;
        }

        // Nothing survived validation — nothing to write.
        if (empty($validEntries)) {
            return false;
        }

        // Locate (and create if needed) the user's .wsa folder.
        $absPath = self::resolve_wsa_folder($p_fullPath);
        if ($absPath === '') {
            return false;
        }

        // One entry per line, always terminated so appends stay clean.
        $payload = implode("\n", $validEntries) . "\n";

        // Append rather than overwrite, so pending purges the daemon has
        // not yet consumed are preserved. LOCK_EX keeps concurrent writers
        // from interleaving partial lines.
        $filePath = $absPath . 'empty.me';
        $written  = @file_put_contents($filePath, $payload, FILE_APPEND | LOCK_EX);

        return $written !== false;
    }

    /**
     * Locate the user's ".wsa" folder using the same rules as purge_cache:
     * honour the caller-supplied path first, otherwise fall back to the
     * cPanel-style directory probe. The folder is created on first use
     * when the caller did not supply an explicit path.
     *
     * Always returns a path that ends with "/" so callers can concatenate
     * a filename directly.
     *
     * @param string|null $p_fullPath  Optional override.
     * @return string  Absolute path ending with "/" on success, "" on failure.
     *
     * @since 1.2.0
     */
    private static function resolve_wsa_folder($p_fullPath = null)
    {
        // Try the caller-provided path first. Trailing slash is enforced
        // here so downstream concatenation is safe.
        if ($p_fullPath != null) {
            $absPath = '/' . trim($p_fullPath, '/') . '/' . self::WSA_CACHE_PATH . '/';
            if (is_writable($absPath)) {
                return $absPath;
            }
        }

        // Fall back to the directory-walking discovery used by purge_cache.
        $absPath = self::find_absolute_path_by_dir();
        if ($absPath === '') {
            return '';
        }

        // Create the folder on first use. 0750 keeps it readable by the
        // cPanel user and the nginx group without exposing it world-wide.
        if (!is_dir($absPath)) {
            if (!@mkdir($absPath, 0750)) {
                return '';
            }
            return $absPath;
        }

        return is_writable($absPath) ? $absPath : '';
    }

    /**
     * Function that will try to determine if the WSA module is currently
     * installed inside the server. If the function can detect the module, it
     * will return :
     * 0 - not installed
     * 1 - installed
     * 2 - could be installed (needed since WSA 1.1)
     * 3 - could be installed, behind CloudFlare proxy (needed since WSA 1.1)
     *
     * @param bool Default to false, When true, the module will look further 
     *             than simple checking a page header response.
     * 
     * @return int
     *
     * @since 1.0.0
     * @version 1.1.0
     */
    public static function is_module_installed($p_extendedValidation = false)
    {

        // Define the default return value
        $moduleActivated = 0;

        // Return the extended validation result and exit.
        if ($p_extendedValidation) {
            return self::is_module_installed__extended_validation();
        }

        // Start by getting the current domain name
        $siteURL = self::fetch_current_domain();

        // Fetch the site header with cURL
        $pageHeader = self::get_page_header($siteURL);

        /**
         * Check if the module Powered-By string is present in the file header.
         * No need for '===' since the Powered-By will never by at position 0.
         */
        if (strpos($pageHeader, 'Nginx for WHM/cPanel by Astral Internet')) {
            $moduleActivated = 1;
        } else if (strpos($pageHeader, 'nginx')) {
            $moduleActivated = 2;
        } else if (strpos($pageHeader, 'cloudflare')) {
            $moduleActivated = 3;
        }

        // return response
        return $moduleActivated;
    }

    /**
     * Function that check if WSA interact with the clear file.
     * If it's not the case, the module is not active.
     *
     * @return int 0 - not installed
     *             1 - installed
     *
     * @since 1.1.0
     */
    private static function is_module_installed__extended_validation()
    {
        // Run the path discovery function
        $absPath = self::find_absolute_path_by_dir();
        // Add the file name
        $absPath .= "empty.me";

        // Purge cache to create empty file
        self::purge_cache(true, $absPath);

        // wait for the file to be removed
        sleep(1);

        // return 0 if the file is still there and 1 if the file was removed
        return file_exists($absPath) ? 0 : 1;
    }

    /**
     * Function used to return a webpage header request.
     * Used by WSA::is_module_installed
     *
     * @param string $p_pageUrl Url to request
     * @return string Webpage header
     *
     * @since 1.0.0
     */
    private static function get_page_header($p_pageUrl)
    {

        // Create curl request to fetch the page header
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $p_pageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        // return the page header request
        return $output;
    }

    /**
     * This function will return the domain name currently being browse
     * including the URL prefix.
     * Used by WSA::purge_cache & WSA::is_module_installed
     *
     * @param bool     Whether to return the URL prefix being used (HTTP or
     *                 HTTPS) in the URL
     * @return string  Return the site URL
     *
     * @since 1.0.0
     */
    private static function fetch_current_domain($p_getPrefix = true)
    {
        // Prepare the URL prefix
        if ($p_getPrefix) {
            $urlPrefix = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://');
        } else {
            $urlPrefix = "";
        }

        // Return a usable site url, using HTTP_HOST if defined
        return $urlPrefix . (isset($_SERVER['HTTP_HOST']) ?
            $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
    }

    /**
     * Tries to find the absolute path base on the current working directory or
     * by working up the folders.
     * Used by WSA::purge_cache
     *
     * @return string Return the empty if the path cannot be set
     */
    private static function find_absolute_path_by_dir()
    {
        // Set a return value in case the path wasn't found
        $pathFound = false;

        /**
         * Split the current path into two groups to find the username. cPanel
         * usually use the following pattern: /$WEBFILES/$USER Groups are
         * separated by / character and names can be any char, number or
         * underscore ([a-zA-Z0-9_]).
         */
        preg_match(
            '/\/([a-zA-Z0-9_]{1,})\/([a-zA-Z0-9_]{1,})\//',
            __DIR__,
            $splittedFolders
        );

        // Build the assume absolute path based on folder
        $absPath = '/' . $splittedFolders[1] . '/' . $splittedFolders[2] . '/'
            . self::WSA_CACHE_PATH . '/';

        /**
         * If the build path is not valid, try to find a valid path by working
         * up the directory hierarchy.
         */
        if (!file_exists($absPath)) {

            /**
             * Remove the backslash at the beginning and end of the current
             * path.
             */
            $cleaneadPath = trim(__DIR__, "/");

            /**
             * Start by counting the amount of sub directory in the current
             * path to prevent moving up too far.
             */
            $subfolders = count(explode('/', $cleaneadPath));

            /**
             * Set the maximum of back trace allowed based on the define limit
             * in the class.
             */
            $maxBacktrace = ($subfolders > self::WSA_MAX_BACKTRACE ?
                self::WSA_MAX_BACKTRACE : $subfolders);

            // Set the start back trace path variable.
            $currentBacktracePath = self::WSA_CACHE_PATH;

            // Define the loop stopper.
            self::backtrace_folder_search(
                $currentBacktracePath,
                $maxBacktrace,
                $pathFound
            );

            if ($pathFound) {

                /**
                 * Set the absolute path if the recursive function manages to
                 * find it.
                 */
                $absPath = $currentBacktracePath;
            } else {

                // Define empty path on failure.
                $absPath = '';
            }
        }

        // Return the assumed absolute path or empty string.
        return $absPath;
    }

    /**
     * Private function to try to find the module folder path by working up
     * recursively into the folders hierarchy.
     *
     * @param string  Current folder that the function is looking at.
     * @param int     Maximum recursion allowed to prevent going to far or
     *                infinite loop
     * @param bool    Will be set to true if the path if found since the sub
     *                can exit wihout finding the path.
     * @param int     Current number of recursion done by the sub.
     */
    private static function backtrace_folder_search(
        &$p_currentBacktracePath,
        $p_maxBatrace,
        &$p_found = false,
        &$p_currentPass = 0
    ) {
        // Proceed only if we didn't reach the maximum of recursion.
        if ($p_currentPass < $p_maxBatrace) {

            /**
             * If the folder exists, trigger the found variable and exit
             * otherwise call itself.
             */
            if (file_exists($p_currentBacktracePath)) {
                $p_found = true;
            } else {

                // Set the new back trace (by adding '../' to move up a folder)
                $p_currentBacktracePath = '../' . $p_currentBacktracePath;

                // Increment the number of pass made.
                $p_currentPass += 1;

                // Recursive function call.
                self::backtrace_folder_search(
                    $p_currentBacktracePath,
                    $p_maxBatrace,
                    $p_found,
                    $p_currentPass
                );
            }
        }
    }

    /**
     * Delete purge cache if file already exists. To prevent error, we make
     * sure the cache file didn't exist prior creating a new file.
     *
     * @param string full path with file name for the empty.me file
     * @return bool  Return false if the file exists but can't be deleted.
     */
    private static function clean_purge_carche_file($p_filePath)
    {
        // Set default return value.
        $result = true;

        // First check if the file already exists.
        if (file_exists($p_filePath)) {

            // Delete the existing file.
            if (!unlink($p_filePath)) {

                // Set the error flag if the file couldn't be deleted.
                $result = false;
            }
        }

        // Return the function result.
        return $result;
    }
}
