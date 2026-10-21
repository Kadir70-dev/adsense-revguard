<?php
/**
 * Plugin Name: KadirSlot Ad Placement
 * Description: Lightweight Google AdSense placement with three ad slots, automatic placements and shortcodes.
 * Version: 1.1.2
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: Abdul Kadir
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kadirslot-ad-placement
 */

if (!defined('ABSPATH')) { exit; }

/*
 * All functions live in includes/plugin.php under the KadirSlotAdPlacement namespace, so this file declares nothing
 * that can collide with the former RevGuard Free plugin (namespace RevGuardFree) or with a second copy of this plugin.
 * A second copy loads nothing and shows an admin notice instead of causing a "Cannot redeclare" fatal error.
 */
if (defined('KADIRSLOT_AD_PLACEMENT_FILE')) {
    add_action('admin_notices', static function (): void {
        if (!current_user_can('activate_plugins')) { return; }
        echo '<div class="notice notice-error"><p>' . esc_html__('Another copy of KadirSlot Ad Placement is already active. Deactivate the duplicate copy.', 'kadirslot-ad-placement') . '</p></div>';
    });
    return;
}

define('KADIRSLOT_AD_PLACEMENT_FILE', __FILE__);
require_once __DIR__ . '/includes/plugin.php';

add_action('plugins_loaded', 'KadirSlotAdPlacement\\boot');
register_activation_hook(__FILE__, 'KadirSlotAdPlacement\\activate');
