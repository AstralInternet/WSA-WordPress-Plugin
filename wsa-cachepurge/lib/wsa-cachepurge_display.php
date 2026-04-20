<?php

/**
 *  __      _____   _
 *  \ \    / / __| /_\
 *   \ \/\/ /\__ \/ _ \
 *    \_/\_/ |___/_/ \_\
 *
 * WSA - Website Accelerator Cache Purge - Admin area display
 *
 * @author          Astral Internet inc. <support@astralinternet.com>
 * @version         1.2.0
 * @copyright       2021 Copyright (C) 2021, Astral Internet inc. - support@astralinternet.com
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
require_once dirname(WSA_CACHEPURGE_FILE) . '/vendor/wsa/wsa.class.php';

/**
 * Load the display class
 *
 * @since 1.1.0
 */
require_once dirname(WSA_CACHEPURGE_FILE) . '/lib/wsa-cachepurge_display.class.php';

// Disable extended validation by default
$extendedValidation = false;
// Default message tipe
$messageType = "";

/**
 * Post trust verification
 *
 * All $_POST values are passed through wp_unslash() + sanitize_text_field()
 * before use, per WordPress Coding Standards. This also keeps the code quiet
 * on PHP 8.1+, where implicit string conversions of slashed input can emit
 * deprecation notices.
 *
 * @since 1.1.0
 * @version 1.2.0
 */
$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
$action = isset($_POST['action']) ? sanitize_text_field(wp_unslash($_POST['action'])) : '';

if ($action !== '' && wp_verify_nonce($nonce, 'wsa-cachepurge')) {

    switch ($action) {

        // Update the "auto purge" setting in Wordpress
        // Trigger when user click on the auto purge checkbox
        case 'autoPurge':
            $saveVal = isset($_POST['wsa-cachepurge-cachepurge_save'])
                ? sanitize_text_field(wp_unslash($_POST['wsa-cachepurge-cachepurge_save']))
                : '';
            update_option('wsa-cachepurge_auto-purge', ($saveVal === 'on') ? 1 : 0);

            $messageType = "autoPurge";
            break;

        // Update the "auto purge mode" setting. Only accept the two known
        // values; anything else falls back to 'full' so a malformed POST
        // cannot put the plugin in an undefined state.
        // Trigger when user changes the auto purge mode radio buttons
        case 'autoPurgeMode':
            $submittedMode = isset($_POST['wsa-cachepurge_auto-purge-mode'])
                ? sanitize_text_field(wp_unslash($_POST['wsa-cachepurge_auto-purge-mode']))
                : '';
            $newMode = ($submittedMode === 'url') ? 'url' : 'full';
            update_option('wsa-cachepurge_auto-purge-mode', $newMode, false);

            $messageType = "autoPurgeMode";
            break;

        // If a request was made to purge the cache, process to the cache purge ans display the success message.
        // Trigger when user press on the clear cache button
        case 'purgeCache':
            // Purge the WSA server cache
            WSA_Cachepurge_WP::purge_cache();

            // Fetch the informative box messages
            $messageType = "emptyCache";
            break;

        // Check if the WSA is installed with the extended validation.
        // Trigger when user press on the advance verification button
        case 'extendedValidation':
            // force extended validation
            $extendedValidation = true;

            // Return the informative text box once the verification is done, with the verification result
            $messageType = "advanceValidation";
            break;

        default:
            break;
    }
}

// Build the WSA status box, function will also check the current status of the WSA with the advance validation
$statusbox = WSA_Display::build_status_box($extendedValidation);
$messageBox = WSA_Display::build_message_box($messageType);

// Resolve the current auto-purge state once so the UI and the radio group
// stay in sync even when the stored option is missing or invalid.
$currentMode = get_option('wsa-cachepurge_auto-purge-mode', 'full');
if ($currentMode !== 'url') {
    $currentMode = 'full';
}
$autoPurgeEnabled = (get_option('wsa-cachepurge_auto-purge') == 1);

?>

<style>
/*
 * Styling is kept inline in the display page so admin screens that do not
 * use the plugin never pay the cost of loading a dedicated CSS file.
 *
 * The look-and-feel is card-based, uses the Astral brand gradient as the
 * only accent colour, and drives everything through CSS custom properties
 * so palette tweaks stay in one place.
 */
.wsa-cachepurge {
    --wsa-accent: #ef6c45;
    --wsa-accent-dark: #ec4e1f;
    --wsa-accent-soft: rgba(239, 108, 69, 0.12);
    --wsa-text: #1f2933;
    --wsa-muted: #6b7684;
    --wsa-border: #e5e9ef;
    --wsa-bg: #f6f8fb;
    --wsa-card-bg: #ffffff;
    --wsa-radius: 14px;
    --wsa-radius-sm: 10px;
    --wsa-shadow-sm: 0 1px 2px rgba(17, 24, 39, 0.04), 0 1px 3px rgba(17, 24, 39, 0.05);
    --wsa-shadow-md: 0 4px 12px rgba(17, 24, 39, 0.06), 0 2px 4px rgba(17, 24, 39, 0.04);
    --wsa-shadow-lg: 0 12px 28px rgba(239, 108, 69, 0.25);
    --wsa-good-bg: #ecfdf5;
    --wsa-warn-bg: #fef9c3;
    --wsa-bad-bg: #fef2f2;
    max-width: 1080px;
    margin: 24px auto;
    padding: 0 16px 48px;
    color: var(--wsa-text);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
}
.wsa-cachepurge * { box-sizing: border-box; }
.wsa-cachepurge a { color: var(--wsa-accent); text-decoration: none; font-weight: 500; }
.wsa-cachepurge a:hover { color: var(--wsa-accent-dark); text-decoration: underline; }

/* Card primitive reused across every section. */
.wsa-card {
    background: var(--wsa-card-bg);
    border: 1px solid var(--wsa-border);
    border-radius: var(--wsa-radius);
    box-shadow: var(--wsa-shadow-sm);
    padding: 28px;
    margin: 0 0 20px;
}

/* Hero card: brand on the left, status pill on the right. */
.wsa-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #ffffff 0%, #fff6f1 100%);
}
.wsa-hero__brand { display: flex; align-items: center; gap: 18px; min-width: 0; }
.wsa-hero__logo {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--wsa-accent) 0%, var(--wsa-accent-dark) 100%);
    display: flex; align-items: center; justify-content: center;
    box-shadow: var(--wsa-shadow-lg);
    flex-shrink: 0;
}
.wsa-hero__logo img { height: 32px; width: 32px; filter: brightness(0) invert(1); }
.wsa-hero__title h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    line-height: 1.2;
    color: var(--wsa-text);
    padding: 0;
    border: 0;
}
.wsa-hero__subtitle { margin: 4px 0 0; color: var(--wsa-muted); font-size: 13px; }

/* Status pill: coloured dot, label, optional info tooltip, verify button. */
.wsa-status {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 8px 8px 8px 16px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid var(--wsa-border);
    box-shadow: var(--wsa-shadow-sm);
}
.wsa-status__dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: var(--wsa-muted);
    box-shadow: 0 0 0 4px rgba(107, 118, 132, 0.15);
    flex-shrink: 0;
}
.wsa-status.good .wsa-status__dot    { background: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18); }
.wsa-status.warning .wsa-status__dot { background: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.18); }
.wsa-status.bad .wsa-status__dot     { background: #ef4444; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.18); }
.wsa-status__label { font-size: 13px; color: var(--wsa-muted); display: inline-flex; align-items: center; gap: 6px; }
.wsa-status__label strong { color: var(--wsa-text); font-weight: 600; text-transform: capitalize; }
.wsa-status__tooltip { position: relative; display: inline-flex; align-items: center; }
.wsa-status__info {
    width: 16px; height: 16px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--wsa-border); color: var(--wsa-muted);
    font-size: 10px; font-weight: 700; cursor: help;
}
.wsa-status__info-text {
    position: absolute; bottom: calc(100% + 8px); right: -8px;
    width: 240px; padding: 10px 12px;
    background: var(--wsa-text); color: #fff; font-size: 12px; line-height: 1.4;
    border-radius: 8px;
    opacity: 0; pointer-events: none; transform: translateY(4px);
    transition: opacity 0.15s ease, transform 0.15s ease;
    z-index: 10;
}
.wsa-status__tooltip:hover .wsa-status__info-text,
.wsa-status__tooltip:focus-within .wsa-status__info-text { opacity: 1; transform: translateY(0); }

/* Description card. */
.wsa-intro p { font-size: 14px; line-height: 1.65; color: var(--wsa-muted); margin: 0 0 10px; }
.wsa-intro p:last-child { margin-bottom: 0; }

/* Section header shared by the action cards. */
.wsa-section-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 20px; }
.wsa-section-head__icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: var(--wsa-accent-soft); color: var(--wsa-accent);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wsa-section-head__text h2 { margin: 0 0 4px; font-size: 16px; font-weight: 600; color: var(--wsa-text); padding: 0; }
.wsa-section-head__text p { margin: 0; font-size: 13px; color: var(--wsa-muted); line-height: 1.55; }

/* Primary gradient button (purge cache). */
.wsa-btn-primary {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 22px;
    font-size: 14px; font-weight: 600; letter-spacing: 0.01em;
    color: #fff;
    background: linear-gradient(135deg, var(--wsa-accent) 0%, var(--wsa-accent-dark) 100%);
    border: 0; border-radius: var(--wsa-radius-sm);
    box-shadow: var(--wsa-shadow-lg);
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}
.wsa-btn-primary:hover { transform: translateY(-1px); filter: brightness(1.05); }
.wsa-btn-primary:active { transform: translateY(0); }

/* Secondary "ghost" button (advanced verification). */
.wsa-btn-ghost {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 14px;
    font-size: 13px; font-weight: 500;
    color: var(--wsa-accent);
    background: transparent;
    border: 1px solid var(--wsa-border);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.wsa-btn-ghost:hover { background: var(--wsa-accent-soft); border-color: var(--wsa-accent); }

/* Soft-background notes block under the purge button. */
.wsa-notes {
    margin-top: 22px; padding: 16px 18px;
    background: var(--wsa-bg); border-radius: var(--wsa-radius-sm);
    font-size: 12px; color: var(--wsa-muted); line-height: 1.6;
}
.wsa-notes h3 { font-size: 11px; font-weight: 600; color: var(--wsa-text); text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 6px; padding: 0; }
.wsa-notes h4 { font-size: 12px; font-weight: 600; color: var(--wsa-text); margin: 12px 0 4px; }
.wsa-notes p, .wsa-notes ol { margin: 0; }
.wsa-notes ol { padding-left: 18px; }
.wsa-notes ol li { margin-top: 4px; }

/* Settings row: descriptive label left, control right. */
.wsa-setting {
    display: flex; align-items: center; justify-content: space-between;
    gap: 24px; padding: 18px 0;
    border-bottom: 1px solid var(--wsa-border);
}
.wsa-setting:last-child { border-bottom: 0; padding-bottom: 0; }
.wsa-setting:first-of-type { padding-top: 0; }
.wsa-setting--stacked { flex-direction: column; align-items: stretch; }
.wsa-setting__label { flex: 1; min-width: 0; }
.wsa-setting__label strong { display: block; font-size: 14px; font-weight: 600; color: var(--wsa-text); margin-bottom: 4px; }
.wsa-setting__label span { font-size: 13px; color: var(--wsa-muted); line-height: 1.5; }

/* iOS-style toggle switch replacing the old checkbox. */
.wsa-toggle { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
.wsa-toggle input { opacity: 0; width: 0; height: 0; }
.wsa-toggle__slider {
    position: absolute; inset: 0; cursor: pointer;
    background: #cbd5e1;
    border-radius: 999px;
    transition: background 0.2s ease;
}
.wsa-toggle__slider:before {
    content: ""; position: absolute;
    height: 20px; width: 20px; left: 3px; top: 3px;
    background: #fff; border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;
}
.wsa-toggle input:checked + .wsa-toggle__slider { background: var(--wsa-accent); }
.wsa-toggle input:checked + .wsa-toggle__slider:before { transform: translateX(20px); }
.wsa-toggle input:focus-visible + .wsa-toggle__slider { box-shadow: 0 0 0 3px var(--wsa-accent-soft); }

/* Segmented radio group for the purge mode. */
.wsa-mode-group {
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;
    transition: opacity 0.2s ease;
}
.wsa-mode-group.is-disabled { opacity: 0.5; pointer-events: none; }
.wsa-mode-card {
    position: relative;
    padding: 16px;
    border: 1px solid var(--wsa-border);
    border-radius: var(--wsa-radius-sm);
    cursor: pointer;
    background: #fff;
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.wsa-mode-card:hover { border-color: var(--wsa-accent); background: var(--wsa-accent-soft); }
.wsa-mode-card input { position: absolute; opacity: 0; pointer-events: none; }
.wsa-mode-card__title {
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; font-weight: 600; color: var(--wsa-text);
    margin-bottom: 6px;
}
.wsa-mode-card__title::before {
    content: ""; width: 16px; height: 16px; border-radius: 50%;
    border: 2px solid #cbd5e1; background: #fff; flex-shrink: 0;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.wsa-mode-card__desc { font-size: 12px; color: var(--wsa-muted); line-height: 1.5; }
.wsa-mode-card:has(input:checked) {
    border-color: var(--wsa-accent);
    background: var(--wsa-accent-soft);
    box-shadow: 0 0 0 3px rgba(239, 108, 69, 0.08);
}
.wsa-mode-card:has(input:checked) .wsa-mode-card__title { color: var(--wsa-accent); }
.wsa-mode-card:has(input:checked) .wsa-mode-card__title::before {
    border-color: var(--wsa-accent);
    box-shadow: inset 0 0 0 4px var(--wsa-accent);
}
@media (max-width: 680px) { .wsa-mode-group { grid-template-columns: 1fr; } }

/* Feedback toast shown after a form submission. */
.wsa-toast {
    position: relative; overflow: hidden;
    display: flex; align-items: flex-start; gap: 14px;
    padding: 14px 42px 14px 16px;
    border-radius: var(--wsa-radius-sm);
    border: 1px solid var(--wsa-border);
    background: #fff;
    box-shadow: var(--wsa-shadow-md);
    margin: 0 0 20px;
    animation: wsa-toast-in 0.25s ease-out;
}
@keyframes wsa-toast-in { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.wsa-toast.good    { background: var(--wsa-good-bg); border-color: #bbf7d0; }
.wsa-toast.good    .wsa-toast__icon { color: #15803d; }
.wsa-toast.warning { background: var(--wsa-warn-bg); border-color: #fde68a; }
.wsa-toast.warning .wsa-toast__icon { color: #a16207; }
.wsa-toast.bad     { background: var(--wsa-bad-bg);  border-color: #fecaca; }
.wsa-toast.bad     .wsa-toast__icon { color: #b91c1c; }
.wsa-toast__icon { flex-shrink: 0; padding-top: 2px; }
.wsa-toast__title { font-size: 14px; font-weight: 600; color: var(--wsa-text); }
.wsa-toast__msg { font-size: 13px; color: var(--wsa-muted); margin-top: 2px; }
.wsa-toast__close {
    position: absolute; top: 10px; right: 10px;
    width: 24px; height: 24px; border: 0; background: transparent;
    color: var(--wsa-muted); cursor: pointer; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s ease;
}
.wsa-toast__close:hover { background: rgba(0, 0, 0, 0.06); color: var(--wsa-text); }
.wsa-toast__progress {
    position: absolute; left: 0; bottom: 0; height: 3px;
    background: currentColor; opacity: 0.35;
    width: 100%; transform-origin: left;
    animation: wsa-progress 5s linear forwards;
}
@keyframes wsa-progress { from { transform: scaleX(1); } to { transform: scaleX(0); } }

/* Keep WordPress admin defaults from overriding our typography. */
.wsa-cachepurge h1, .wsa-cachepurge h2 { font-family: inherit; }
</style>

<div class="wsa-cachepurge">

    <!-- ================================================================
         Hero card: brand identity and module status at a glance.
         ================================================================ -->
    <div class="wsa-card wsa-hero">
        <div class="wsa-hero__brand">
            <div class="wsa-hero__logo">
                <img src="<?=plugins_url('ressources/wsa-cachepurge_logo.svg', dirname(__FILE__))?>" alt="">
            </div>
            <div class="wsa-hero__title">
                <h1><?=__("Website Accelerator", "wsa-cachepurge");?></h1>
                <p class="wsa-hero__subtitle"><?=__("Vidage de cache serveur par Astral Internet", "wsa-cachepurge");?></p>
            </div>
        </div>
        <div class="wsa-status <?=esc_attr($statusbox['styleColor'])?>">
            <span class="wsa-status__dot" aria-hidden="true"></span>
            <span class="wsa-status__label">
                <?=__("Module", "wsa-cachepurge");?> <strong><?=esc_html($statusbox['status'])?></strong>
                <?php if (!empty($statusbox['information'])) { ?>
                    <span class="wsa-status__tooltip">
                        <span class="wsa-status__info" tabindex="0" aria-label="<?=esc_attr__("Plus d'information", "wsa-cachepurge");?>">i</span>
                        <span class="wsa-status__info-text"><?=esc_html($statusbox['information'])?></span>
                    </span>
                <?php } ?>
            </span>
            <form method="post" style="margin:0;">
                <input type="hidden" name="action" value="extendedValidation">
                <input type="hidden" name="nonce" value="<?=wp_create_nonce('wsa-cachepurge')?>">
                <button type="submit" class="wsa-btn-ghost"><?=__("Vérifier", "wsa-cachepurge");?></button>
            </form>
        </div>
    </div>

    <!-- ================================================================
         Description card.
         ================================================================ -->
    <div class="wsa-card wsa-intro">
        <p><?=__("L’accélérateur de site web d’<a href=\"https://www.astralinternet.com/\">Astral Internet</a> est un outil qui permet de placer certains éléments d’un site Web dans une mémoire tampon (cache) à l’intérieur du serveur. Une fois les éléments du site dans la mémoire tampon du serveur, ceux-ci seront servis beaucoup plus rapidement aux visiteurs visionnant votre site.", "wsa-cachepurge");?></p>
        <p><?=__("Pour plus d'information concernant ce module, veuillez lire l’article suivant « <a href=\"https://www.astralinternet.com/produit/fini-les-sites-trop-lents/\">Fini les sites web trop lents!</a> ».", "wsa-cachepurge");?></p>
        <p><?=__("La documentation complète du module est également disponible <a href=\"https://docs.astral360.com/\">ici</a>.", "wsa-cachepurge");?></p>
    </div>

    <?php
    // Feedback toast shown after a form submission; the message class
    // carries the intent (good/warning/bad) so the same markup styles
    // all feedback types.
    if ($messageBox['gotMessage']) { ?>
    <div id="wsa-message" class="wsa-toast <?=esc_attr($messageBox['styleColor'])?>" role="status">
        <div class="wsa-toast__icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="wsa-toast__title"><?=esc_html($messageBox['title']);?></div>
            <div class="wsa-toast__msg"><?=esc_html($messageBox['message']);?></div>
        </div>
        <button type="button" class="wsa-toast__close" onClick="removeDiv()" aria-label="<?=esc_attr__("Fermer", "wsa-cachepurge");?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <?php if ($messageBox['animation']) { ?>
            <div class="wsa-toast__progress"></div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- ================================================================
         Manual purge action card.
         ================================================================ -->
    <div class="wsa-card">
        <div class="wsa-section-head">
            <div class="wsa-section-head__icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </div>
            <div class="wsa-section-head__text">
                <h2><?=__("Vider la mémoire cache", "wsa-cachepurge");?></h2>
                <p><?=__("Il est possible qu'après avoir modifié une page de votre site, le changement ne soit pas visible instantanément. Lorsque c’est le cas, cela signifie que le serveur possède toujours en mémoire l’ancienne version de votre site. Vider la cache forcera le serveur à récupérer une nouvelle version de votre site, à jour, ainsi votre modification sera visible pour tous.", "wsa-cachepurge");?></p>
            </div>
        </div>

        <form method="post" style="margin:0;">
            <input type="hidden" name="action" value="purgeCache">
            <input type="hidden" name="nonce" value="<?=wp_create_nonce('wsa-cachepurge')?>">
            <button type="submit" class="wsa-btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/><path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"/></svg>
                <?=__("Vider la cache", "wsa-cachepurge");?>
            </button>
        </form>

        <div class="wsa-notes">
            <h3><?=__("Notes", "wsa-cachepurge");?></h3>
            <p><?=__("En aucun cas, vider la mémoire cache ne peut affecter le bon fonctionnement de votre site.", "wsa-cachepurge");?></p>
            <h4><?=__("Si les changements ou les modifications dans votre site ne sont toujours pas visibles :", "wsa-cachepurge");?></h4>
            <ol>
                <li><?=__("Assurez-vous d’avoir bien enregistré les modifications dans vos pages/articles.", "wsa-cachepurge");?></li>
                <li><?=__("Si vous utilisez une extension de mise en cache comme WP Rocket, Swift, W3 Total Cache, WP Super Cache ou autre, assurez-vous de bien vider la mémoire cache de celles-ci avant de vider la cache du serveur.", "wsa-cachepurge");?></li>
                <li><?=__("Si vous utilisez un CDN (Content Delivery Network) comme CloudFlare, assurez-vous que celui-ci est en mode de développement et/ou que la cache du CDN est également vidée.", "wsa-cachepurge");?></li>
            </ol>
        </div>
    </div>

    <!-- ================================================================
         Automation settings card: toggle + mode selector.
         ================================================================ -->
    <div class="wsa-card">
        <div class="wsa-section-head">
            <div class="wsa-section-head__icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </div>
            <div class="wsa-section-head__text">
                <h2><?=__("Automatisation", "wsa-cachepurge");?></h2>
                <p><?=__("Configurez ce qui se passe lorsqu'une page ou un article est modifié.", "wsa-cachepurge");?></p>
            </div>
        </div>

        <div class="wsa-setting">
            <div class="wsa-setting__label">
                <strong><?=__("Vidage automatique", "wsa-cachepurge");?></strong>
                <span><?=__("Lorsqu’activé, la cache du serveur sera vidée automatiquement à chaque changement effectué dans une page ou un article.", "wsa-cachepurge");?></span>
            </div>
            <form method="post" style="margin:0;">
                <input type="hidden" name="action" value="autoPurge">
                <input type="hidden" name="nonce" value="<?=wp_create_nonce('wsa-cachepurge')?>">
                <label class="wsa-toggle">
                    <input type="checkbox" name="wsa-cachepurge-cachepurge_save" onChange="this.form.submit();" <?=$autoPurgeEnabled ? "checked" : ""?>>
                    <span class="wsa-toggle__slider"></span>
                </label>
            </form>
        </div>

        <div class="wsa-setting wsa-setting--stacked">
            <div class="wsa-setting__label">
                <strong><?=__("Mode de vidage", "wsa-cachepurge");?></strong>
                <span><?=__("Choisissez entre vider tout le site ou uniquement la page/l'article qui vient d'être modifié.", "wsa-cachepurge");?></span>
            </div>
            <form method="post" style="margin:0;">
                <input type="hidden" name="action" value="autoPurgeMode">
                <input type="hidden" name="nonce" value="<?=wp_create_nonce('wsa-cachepurge')?>">
                <div class="wsa-mode-group <?=$autoPurgeEnabled ? '' : 'is-disabled'?>">
                    <label class="wsa-mode-card">
                        <input type="radio" name="wsa-cachepurge_auto-purge-mode" value="full" onChange="this.form.submit();" <?=($currentMode === 'full' ? 'checked' : '')?> <?=$autoPurgeEnabled ? '' : 'disabled'?>>
                        <div class="wsa-mode-card__title"><?=__("Site au complet", "wsa-cachepurge");?></div>
                        <div class="wsa-mode-card__desc"><?=__("Vide toute la cache du domaine lors d'une modification. Recommandé si vos pages partagent des éléments communs (menus, widgets, etc.).", "wsa-cachepurge");?></div>
                    </label>
                    <label class="wsa-mode-card">
                        <input type="radio" name="wsa-cachepurge_auto-purge-mode" value="url" onChange="this.form.submit();" <?=($currentMode === 'url' ? 'checked' : '')?> <?=$autoPurgeEnabled ? '' : 'disabled'?>>
                        <div class="wsa-mode-card__title"><?=__("Page uniquement", "wsa-cachepurge");?></div>
                        <div class="wsa-mode-card__desc"><?=__("Ne vide que la cache de la page ou de l'article modifié. Plus rapide et plus efficace pour les sites à fort trafic.", "wsa-cachepurge");?></div>
                    </label>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function removeDiv() {
    var el = document.getElementById("wsa-message");
    if (el) { el.remove(); }
}
</script>
