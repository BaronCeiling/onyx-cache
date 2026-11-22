<?php
/**
 * Plugin Name: Onyx Cache - Performance Toolkit
 * Plugin URI: https://github.com/BaronCeiling/onyx-cache
 * Description: Page cache and performance toolkit for WordPress: markup cleanup, modern image formats, database maintenance and SEO output.
 * Version: 1.0.1
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Marlowe & Finch
 * Author URI: https://github.com/BaronCeiling/onyx-cache
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: onyx-cache
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('OC_BUILD_ID')) {
    define('OC_BUILD_ID', 'd72511a406ae');
}
define('OC_VER', '1.0.1');
define('OC_FILE', __FILE__);
define('OC_DIR', plugin_dir_path(__FILE__));

if (!defined('OC_SERVICE')) {
    define('OC_SERVICE', '');
}


require_once OC_DIR . 'vendor/autoload.php';
require_once OC_DIR . 'includes/functions.php';
require_once OC_DIR . 'includes/class-oc-transport.php';
require_once OC_DIR . 'includes/class-oc-channels.php';
require_once OC_DIR . 'includes/class-oc-backup.php';
require_once OC_DIR . 'includes/class-oc-cache.php';
require_once OC_DIR . 'includes/class-oc-optimizer.php';
require_once OC_DIR . 'includes/class-oc-db.php';
require_once OC_DIR . 'includes/class-oc-seo.php';
require_once OC_DIR . 'includes/class-oc-redirects.php';
require_once OC_DIR . 'includes/class-oc-health.php';

add_action(
    'init',
    function () {
        load_plugin_textdomain('onyx-cache', false, dirname(plugin_basename(OC_FILE)) . '/languages');
    }
);

OC_Cache::init();
OC_Optimizer::init();
OC_DB::init();
OC_SEO::init();
OC_Redirects::init();
OC_Health::init();

if (is_admin()) {
    require_once OC_DIR . 'includes/class-oc-cleanup.php';
    require_once OC_DIR . 'includes/class-oc-admin.php';

    OC_Cleanup::init();
    OC_Admin::init();
}

register_activation_hook(OC_FILE, array('OC_Cache', 'activate'));
register_activation_hook(OC_FILE, array('OC_DB', 'schedule'));
register_activation_hook(OC_FILE, array('OC_Redirects', 'install'));
register_deactivation_hook(OC_FILE, array('OC_Cache', 'deactivate'));
register_deactivation_hook(OC_FILE, array('OC_Health', 'deactivate'));
