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
 * @version         1.0.9
 * @copyright       2019 Copyright (C) 2019, Astral Internet inc. - support@astralinternet.com
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
     * Build the text for the box display and check for the extended validation
     *
     * @param boolean $extendedValidation true to use the extended validation 
     * @return array [status]/[information]/[styleColor]
     */
    public static function build_status_box($extendedValidation = false)
    {
        $lastCheck = get_option('wsa-cachepurge_wsa-installed');

        if ($lastCheck > (time() - 172800)) {
            $moduleInstalled = 1; // module still avalible since last check
        } else {
            $moduleInstalled = WSAHandler\WSA::is_module_installed($extendedValidation);
            if ($extendedValidation && $moduleInstalled == 1) { // extended validation success
                update_option('wsa-cachepurge_wsa-installed', time()); // update to current time
            } else { // extended validation failled
                if ($lastCheck != 0) {  // prevent update if value is already 0
                    update_option('wsa-cachepurge_wsa-installed', 0); // update the value to 0 
                }
            }
        }

        switch ($moduleInstalled) {
            case 1:
                $data['status'] = __("disponible", "wsa-cachepurge");
                $data['information'] = "";
                $data['styleColor'] = "good";
                break;

            case 2:
                $data['status'] = __("indéfini", "wsa-cachepurge");
                $data['information'] = __("Le serveur utilise Nginx sans la mention WSA, il est possible que WSA soit actif.", "wsa-cachepurge");
                $data['styleColor'] = "warning";
                break;

            case 3:
                $data['status'] = __("indéfini", "wsa-cachepurge");
                $data['information'] = __("Le site est derrière le proxy de Cloudflare, il est possible que WSA soit actif.", "wsa-cachepurge");
                $data['styleColor'] = "warning";
                break;

            default:
                $data['status'] = __("non disponible", "wsa-cachepurge");
                $data['information'] = "";
                $data['styleColor'] = "bad";
                break;
        }

        return $data;
    }

    /**
     * Build the the for the display message for multiple fonction
     *
     * @param string $message [emptyCache]/[AdvanceValidation]
     * @return array [title]/[information]/[styleColor]
     */
    public static function build_message_box($message)
    {
        switch ($message) {
            case 'emptyCache':
                $data['title'] = __("La cache a été vidée", "wsa-cachepurge");
                $data['message'] = __("* Un délai jusqu'à 60 secondes est requis pour la suppression de la cache.", "wsa-cachepurge");
                $data['styleColor'] = "good";
                $data['animation'] = true;
                break;

            case 'advanceValidation':
                $data['title'] = __("Vérification avancé complété.", "wsa-cachepurge");
                $data['animation'] = false;
                if (get_option('wsa-cachepurge_wsa-installed') != 0) {
                    $data['message'] = __("Le module WSA est bien disponible.", "wsa-cachepurge");
                    $data['styleColor'] = "good";
                } else {
                    $data['message'] = __("Le module WSA n'est pas disponible.", "wsa-cachepurge");
                    $data['styleColor'] = "bad";
                }
                break;

            default:
                break;
        }

        return $data;
    }
}
