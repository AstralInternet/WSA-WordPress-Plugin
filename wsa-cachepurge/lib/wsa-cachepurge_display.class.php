<?php

/**
 *  __      _____   _
 *  \ \    / / __| /_\
 *   \ \/\/ /\__ \/ _ \
 *    \_/\_/ |___/_/ \_\
 *
 * WSA - Website Accelerator Cache Purge - Admin area display logic
 *
 * @author          Astral Internet inc. <support@astralinternet.com>
 * @version         1.1.1
 * @copyright       2021 Copyright (C) 2021, Astral Internet inc. - support@astralinternet.com
 * @license         https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 * @link            https://www.astralinternet.com/en Astral Internet inc.
 *
 * WSA : The Astral Internet Website Accelerator is a tool that allows you to place
 * certain elements of a site in buffer memory (cache) inside the server. Once the
 * elements are placed buffer of the server, they can be served much faster to people
 * viewing a website.
 *
 * This page is the logic for the display of the module in the admin area. It
 * provides information about the caching module and give the option to purge the
 * cache immediately.
 *
 */

// If this file is called directly, abort.
defined('ABSPATH') or die('No script kiddies please!');

class WSA_Display
{
    /**
     * Amount of seconds a valid check is kept before returning into a "maybe"
     * status. Current value is 48 hours.
     *
     * @since 1.1.0
     */
    const VALID_CHECK_PERIOD = 172800;

    /**
     * Build the text array needed for the module status box in the admin area
     *
     * @param bool Defaul false, force the WSA class to use the extented
     *             validation mode.
     *
     * @return array Status bos texte. [status]/[information]/[styleColor]
     *
     * @since    1.1.0
     */
    public static function build_status_box($p_extendedValidation = false)
    {
        // Fetch the last time we got a positive check
        $lastCheck = get_option('wsa-cachepurge_wsa-installed');

        // Set to  "enable" status is the last valid check is within the autorized period.
        if ($lastCheck > (time() - self::VALID_CHECK_PERIOD)) {

            // Define the module has enabled.
            $moduleInstalled = 1;

            // Procedd to a new verification if the last chack period has expired or was never set
        } else {

            // Validate the module installation
            $moduleInstalled = WSAHandler\WSA::is_module_installed($p_extendedValidation);

            // If the module installation is valid, update the last check time.
            if ($moduleInstalled === 1) {

                // Update the check time to "now" in the WP option table.
                update_option('wsa-cachepurge_wsa-installed', time());

                // If the module is not installed
            } else {

                // prevent update if value is already 0, redure MySQL call
                if ($lastCheck != 0) {

                    // Set the last time check to 0.
                    update_option('wsa-cachepurge_wsa-installed', 0);
                }
            }
        }

        // Build the array containing the message for the module
        switch ($moduleInstalled) {

            // If module is installe
            case 1:
                $data['status'] = __("disponible", "wsa-cachepurge");
                $data['information'] = "";
                $data['styleColor'] = "good";
                break;

            // Module could be installed, header only shows behind Nginx
            case 2:
                $data['status'] = __("indéfini", "wsa-cachepurge");
                $data['information'] = __("Le serveur utilise Nginx sans la mention WSA, il est possible que WSA soit actif.", "wsa-cachepurge");
                $data['styleColor'] = "warning";
                break;

            // Module could be installed, header shows behind CloudFlare
            case 3:
                $data['status'] = __("indéfini", "wsa-cachepurge");
                $data['information'] = __("Le site est derrière le proxy de Cloudflare, il est possible que WSA soit actif.", "wsa-cachepurge");
                $data['styleColor'] = "warning";
                break;

            // Module id not installed on server
            default:
                $data['status'] = __("non disponible", "wsa-cachepurge");
                $data['information'] = "";
                $data['styleColor'] = "bad";
                break;
        }

        // Return the message array
        return $data;
    }

    /**
     * Build the the for the informative box that display after a user action
     *
     * @param string A string corresponding to the type of message to be returned
     *               emptyCache : Message once that cache has been cleared.
     *               AdvanceValidation : Message whe nthe user trigger the advance
     *               module validation.
     *
     * @return array [title]/[information]/[styleColor]/[animation]
     *
     * @since    1.1.0
     */
    public static function build_message_box($p_message)
    {

        // Message enable by default
        $data['gotMessage'] = true;

        // Build the message array based on the require task.
        switch ($p_message) {

            // Message when the cache got cleared
            case 'emptyCache':
                $data['title'] = __("La cache a été vidée", "wsa-cachepurge");
                $data['message'] = __("* Un délai jusqu'à 60 secondes est requis pour la suppression de la cache.", "wsa-cachepurge");
                $data['styleColor'] = "good";
                $data['animation'] = true;
                break;

            // Message when the user trigger the advance validation
            case 'advanceValidation':
                $data['title'] = __("Vérification avancé complété.", "wsa-cachepurge");
                $data['animation'] = false;

                // Check if the WSA module was installd by reading the WP-options left by the advance check.
                if (get_option('wsa-cachepurge_wsa-installed') != 0) {
                    $data['message'] = __("Le module WSA est bien disponible.", "wsa-cachepurge");
                    $data['styleColor'] = "good";
                } else {
                    $data['message'] = __("Le module WSA n'est pas disponible.", "wsa-cachepurge");
                    $data['styleColor'] = "bad";
                }
                break;

            case 'autoPurge':
                if (get_option('wsa-cachepurge_auto-purge') == 1) {
                    $data['title'] = __("Le nettoyage automatique a été activé", "wsa-cachepurge");
                    $data['message'] = __("La cache se videra automatiquement lors de la modification d'une page ou d'un article.", "wsa-cachepurge");
                } else {
                    $data['title'] = __("Le nettoyage automatique a été désactivé", "wsa-cachepurge");
                    $data['message'] = __("La cache se se videra plus automatiquement lors de la modification d'une page ou d'un article.", "wsa-cachepurge");
                }

                $data['styleColor'] = "good";
                $data['animation'] = false;
                break;

            // Return empty array when invalid value is passed
            default:
                // disable message
                $data['gotMessage'] = false;
                break;
        }

        // Return the message array
        return $data;
    }
}
