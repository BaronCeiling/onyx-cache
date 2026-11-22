<?php
/**
 * Uninstall routine.
 *
 * @package Onyx Cache
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

foreach (array('oc_stats', 'oc_stats_url', 'oc_report_at', 'oc_install_at', 'oc_settings', 'oc_consent', 'oc_consent_secrets', 'oc_report_v', 'oc_touch', 'oc_metrics_due') as $option) {
    delete_option($option);
}

foreach (array('oc_seen', 'oc_due', 'oc_sent', 'oc_since', 'oc_env_check', 'oc_cloud', 'oc_policy') as $key) {
    delete_transient($key);
}

foreach (array('oc_cache_expire', 'oc_cache_preload', 'oc_db_maintenance') as $hook) {
    wp_clear_scheduled_hook($hook);
}

global $wpdb;
$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'onyx-cache_redirects');
delete_option('oc_redirects_db');

$dir = WP_CONTENT_DIR . '/cache/onyx-cache';

foreach ((array) glob($dir . '/*') as $file) {
    if (is_file($file)) {
        @unlink($file);
    }
}

if (is_dir($dir)) {
    @rmdir($dir);
}
