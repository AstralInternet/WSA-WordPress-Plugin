<?php

/**
 *  __      _____   _  
 *  \ \    / / __| /_\  
 *   \ \/\/ /\__ \/ _ \ 
 *    \_/\_/ |___/_/ \_\
 * 
 * Website Accelerator Cache purge - Admin area display
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
 * This page is the actual on-screen display of the module in the admin area. It 
 * provides information about the caching module and give the option to purge the 
 * cache immediately. 
 * 
 * You may also disable the automatic cache purge from this page.
 * 
 */

 // If this file is called directly, abort.
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Load the WSA class
 *
 * @since 1.0.0
 */
 require_once dirname( CPWSA_FILE ) . '/vendor/wsa/wsa.class.php';

// Update the "auto purge" setting in Wordpress
if ( isset($_POST['hookForm']) && isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'cpwsa_set-auto-purge') ) {

    update_option('cpwsa_auto-purge', isset($_POST['cpwsa_save']) ? "on" : "off");
}

?>

<style>
/* 
The styling has been place in the main display page to reduce the amount of items being loaded each time the backend pages are loaded
 */
.cpwsa {max-width: 1000px; margin: 0 auto;transition: all ease 0.3s;padding:0 20px; position:relative}
.cpwsa #wsa-message {color: #155724; background-color: #b6ecc3; border:1px solid #c3e6cb;padding: .75rem 1.25rem; font-size: 20px; text-align: center; display:none}
.cpwsa h1 {font-size: 36px;line-height: 1.1; border-left: 4px solid #ef6c45;font-weight: lighter;padding: 0 0 0 50px;}
.cpwsa p {text-align:justify; font-size: 14px;}
.cpwsa h1 img{height: 42px; left: 30px; position: absolute;}
.cpwsa a {color: #ef6c45; text-decoration: none;}
.cpwsa a:hover {color: #ec4e1f;}
.cpwsa .flex_base {display:flex;justify-content:flex-start;align-items:center;}
.cpwsa .clearcache_button {color: #fff; background-color: #ef6c45; position: relative; padding: 5px 15px; border: 0; border-radius: 2px;font-size: 20px}
.cpwsa .clearcache_button:hover {background-color: #ec4e1f; cursor: pointer;}
.cpwsa .clearcache_button img {height: 20px; margin-right: 10px;}
.cpwsa .wsa_status_box, .cpwsa .white_box {background-color: #fff; border: 1px solid #ccc; padding: 15px;}
.cpwsa .white_box {display: flex; flex-direction: column; align-items: center; margin: 20px 0;}
.cpwsa .wsa_status_box p {font-weight: 600; padding: 15px; font-size: 18px; padding: 0; margin: 0;}
.cpwsa .wsa_status_box p a {color: #caced1;text-decoration: none;}
.cpwsa .wsa_status_box p a:hover {color: #989da1;}
.cpwsa .exceptions {color: #989da1;padding: 10px; font-size: 12px;}
.cpwsa .exceptions h3, .cpwsa .exceptions p, .cpwsa .exceptions ol {margin: 0;}
.cpwsa .exceptions h4 {margin-bottom: 0;}
.cpwsa .options_grp {width: 90%; text-align:left; padding:10px 0; align-items: baseline;}
.cpwsa .options_grp .options_check {min-width: 50px; text-align: center;}
.cpwsa .options_check input[type=checkbox] {visibility: hidden;}
.cpwsa .options_check label {display: block;position: relative; cursor: pointer;}
.cpwsa .options_check .checkmark {position: absolute; top: 0; left: 0; height: 20px; width: 20px; border: 1px solid #ccc; border-radius: 2px; background-color: #fff;}
.cpwsa .options_check label:hover input ~ .checkmark {background-color: #efcdc3;} 
.cpwsa .options_check label input:checked ~ .checkmark {background-color: #ef6c45;} 
.cpwsa .options_check .checkmark:after {content: "";  position: absolute; display: none;} 
.cpwsa .options_check label input:checked ~ .checkmark:after {display: block;} 
.cpwsa .options_check label .checkmark:after {left: 7px; bottom: 5px; width: 4px; height: 8px; border: solid white; border-width: 0 3px 3px 0; -webkit-transform: rotate(45deg); -ms-transform: rotate(45deg); transform: rotate(45deg)} 
#wsa-progress {transition: all 5s ease; position: absolute; z-index: 1; top: 0; right: 0; border: solid #d4edda; border-right-width: 0px; border-top-width: 58px;}
#wsa-close {position: absolute; top: 2px; right: 2px; height: 18px; width: 18px; z-index: 4; cursor: pointer; font-size: 16px;}
#wsa-close:hover { background-color: #b0d2a8;}
</style>

<div class="cpwsa">
    <div class="flex_base" style="justify-content:space-between">
        <h1><img src="<?=plugins_url( 'ressources/cpwsa_logo.svg', dirname(__FILE__) )?>"><?=__("Website Accelerator - Vidage de cache", "cpwsa");?></h1>
        <div class="wsa_status_box"> <!-- START status box -->
            <? if (WSAHandler\WSA::is_module_installed()) { ?>
                <p><?=__("Le module est", "cpwsa")?> <span style="color: #359944;"> <?=__("disponible", "cpwsa");?></span></p>
            <? } else { ?>
                <p><?=__("Le module est", "cpwsa")?> <span style="color: #D85454;"> <?=__("non disponible", "cpwsa");?></span></p>
                <p style="font-weight: lighter;font-size: 12px; color: #989da1;"><a href="#"><?=__("Appuyer ici pour plus d'information", "cpwsa");?></a></p>
            <? } ?>
        </div> <!-- END status box -->
    </div>

    <p><?=__("L’accélérateur de site web d’<a href=\"https://www.astralinternet.con/fr\">Astral Internet</a> est un outil qui permet de placer certains éléments d’un site Web dans une mémoire tampon (cache) à l’intérieur du serveur. Une fois les éléments du site dans la mémoire tampon du serveur, ceux-ci seront servis beaucoup plus rapidement aux visiteurs visionnant votre site.", "cpwsa");?></p>
    <p><?=__("Pour plus d'information concernant ce module, veuillez lire l’article suivant « <a href=\"https://www.astralinternet.com/blog/fr/fini-les-sites-web-trop-lents/\">Fini les sites web trop lents!</a> ».", "cpwsa");?></p>
    <p><?=__("La documentation complète du module est également disponible <a href=\"https://clients.astralinternet.com/index.php/knowledgebase/1096/Accelerateur-de-site-web.html?language=french\">ici</a>.", "cpwsa");?></p>

    <?php
    // If a request was made to purge the cache, process to the cache purge ans display the success message.
    if ( isset($_REQUEST['purge']) && isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'cpwsa_purge-cache') ) {
        CPWSA_WP::purge_cache(); ?>
        <div id="wsa-message" style="display:flex;z-index:0;position:relative;flex-direction: column;">
            <div id="wsa-close" onClick="removeDiv()">&#x274E;</div>
            <div id="wsa-progress"></div>
            <div style="display:block;z-index:2;">
                <?_e("La cache a été vidée", "cpwsa");?>
            </div>
            <div style="display:block;z-index:2;font-size:small;">
                <?_e("* Un délai jusqu'à 60 secondes est requis pour la suppression de la cache.", "cpwsa");?>
            </div>
    </div> 
        
        
        <?php
    }
    ?>

    <div class="white_box">
        <h2><?=__("Vider la mémoire cache", "cpwsa");?></h2>
        <p><?=__("Il est possible qu'après avoir modifié une page de votre site, le changement ne soit pas visible instantanément. Lorsque c’est le cas, cela signifie que le serveur possède toujours en mémoire l’ancienne version de votre site. Vider la cache forcera le serveur à récupérer une nouvelle version de votre site, à jour, ainsi votre modification sera visible pour tous.", "cpwsa");?></p>


        <form method="post">
            <input type="hidden" name="purge" value="yes_please">
            <input type="hidden" name="nonce" value="<?=wp_create_nonce('cpwsa_purge-cache')?>">
            <button class="flex_base clearcache_button">
                <div><img src="<?=plugins_url( 'ressources/clear-single-user-cache-white.png', dirname(__FILE__) )?>"></div>
                <div><?=__("Vider la cache", "cpwsa");?></div>
            </button>
        </form>
        <div class="exceptions">
            <h3><?=__("Notes :", "cpwsa");?></h3>
            <p><?=__("En aucun cas, vider la mémoire cache ne peut affecter le bon fonctionnement de votre site.", "cpwsa");?></p>

            <h4><?=__("Si les changements ou les modifications dans votre site ne sont toujours pas visibles :", "cpwsa");?></h4>
            <ol>
                <li><?=__("Assurez-vous d’avoir bien enregistré les modifications dans vos pages/articles.", "cpwsa");?></li>
                <li><?=__("Si vous utilisez une extension de mise en cache comme WP Rocket, Swift, W3 Total Cache, WP Super Cache ou autre, assurez-vous de bien vider la mémoire cache de celles-ci avant de vider la cache du serveur.", "cpwsa");?></li>
                <li><?=__("Si vous utilisez un CDN (Content Delivery Network) comme CloudFlare, assurez-vous que celui-ci est en mode de développement et/ou que la cache du CDN est également vidée.", "cpwsa");?></li>
            </ol>
        </div>
    </div>

    <div class="white_box">
        <div class="options_grp flex_base">
            <div class="options_check">
                <label>
                    <form method="post">
                        <input type="hidden" name="hookForm" value="1">
                        <input type="hidden" name="nonce" value="<?=wp_create_nonce('cpwsa_set-auto-purge')?>">
                        <input type="checkbox" name="cpwsa_save" onChange="submit();" <?=get_option('cpwsa_auto-purge') == "on" ? "checked" : "" ?>> 
                        <span class="checkmark"></span> 
                    </form>
                </label>
            </div>
            <div class=""><?=__("Lorsqu’activée, la cache du serveur sera vidée automatiquement à chaque changement effectué dans une page ou un article.", "cpwsa");?></div>
        </div>
    </div>
</div>
<script> 

// Fade the "success" message once the cache has been purged.
var popupBloc = document.getElementById("wsa-message");
var popupMessage = document.getElementById("wsa-progress");
var popupMessageSize = popupBloc.offsetWidth - 4;
popupMessage.style.borderRightWidth = popupMessageSize + "px";

function removeDiv(){
    jQuery('#wsa-message').remove();
}
</script>
