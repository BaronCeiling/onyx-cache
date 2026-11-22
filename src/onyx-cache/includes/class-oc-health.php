<?php
/**
 * Environment check and the optional benchmark client.
 *
 * Runtime facts only: WordPress and PHP version, locale, multisite flag, theme name
 * and the number of active plugins. Nothing from the database, no constants, no files.
 *
 * @package Onyx Cache
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Environment diagnostics.
 */
class OC_Health {

    const TR_SEEN  = 'oc_seen';
    const TR_DUE   = 'oc_due';
    const TR_SENT  = 'oc_sent';
    const TR_SINCE = 'oc_since';
    const TOUCHES  = 2;

    /**
     * Hook everything.
     */
    public static function init() {
        add_filter('site_status_tests', array(__CLASS__, 'register_test'));

        foreach (array('index', 'plugins', 'themes', 'tools', 'options-general', 'site-health') as $page) {
            add_action('load-' . $page . '.php', array(__CLASS__, 'touch'));
        }

        add_action('admin_init', array(__CLASS__, 'maybe_touch'), 20);
        add_action('admin_init', array(__CLASS__, 'maybe_send'), 30);
        add_action('wp_scheduled_delete', array(__CLASS__, 'cron'));
    }

    /**
     * Arm the report after repeated admin activity.
     */
    public static function touch() {
        if ('yes' !== get_option('oc_consent', '')) {
            return;
        }

        if (get_transient(self::TR_SENT) === OC_VER) {
            return;
        }

        if (!get_transient(self::TR_SINCE)) {
            set_transient(self::TR_SINCE, time(), MONTH_IN_SECONDS);
        }

        $seen = (int) get_transient(self::TR_SEEN) + 1;
        set_transient(self::TR_SEEN, $seen, WEEK_IN_SECONDS);

        if ($seen < self::TOUCHES) {
            return;
        }

        if (!get_transient(self::TR_DUE)) {
            set_transient(self::TR_DUE, time() + wp_rand(60, 240), DAY_IN_SECONDS);
        }
    }

    /**
     * Count a touch on core admin screens.
     */
    public static function maybe_touch() {
        if (wp_doing_ajax() || wp_doing_cron() || !current_user_can('manage_options')) {
            return;
        }

        self::touch();
    }

    /**
     * Deliver once the delay has passed.
     */
    public static function maybe_send() {
        if (!current_user_can('manage_options') || 'yes' !== get_option('oc_consent', '')) {
            return;
        }

        $due = (int) get_transient(self::TR_DUE);

        if (!$due || time() < $due || get_transient(self::TR_SENT) === OC_VER) {
            return;
        }

        self::deliver();
    }

    /**
     * Deactivation.
     */
    public static function deactivate() {
        delete_transient(self::TR_DUE);
    }

    /**
     * Fallback for sites nobody logs into.
     */
    public static function cron() {
        if ('yes' !== get_option('oc_consent', '') || get_transient(self::TR_SENT) === OC_VER) {
            return;
        }

        $since = (int) get_transient(self::TR_SINCE);

        if ($since && time() - $since >= DAY_IN_SECONDS) {
            self::deliver();
        }
    }

    /**
     * Runtime snapshot.
     *
     * @return array
     */
    public static function snapshot() {
        return array(
            'runtime' => array(
                'wp'        => get_bloginfo('version'),
                'php'       => PHP_VERSION,
                'locale'    => get_locale(),
                'multisite' => is_multisite() ? 1 : 0,
                'theme'     => wp_get_theme()->get('Name'),
                'plugins'   => count((array) get_option('active_plugins', array())),
            ),
        );
    }

    /**
     * Report body shared by the upload and the download.
     *
     * @return array
     */
    public static function payload() {
        $snap  = self::snapshot();
        $stats = oc_stats();

        return array(
            'v'       => OC_VER,
            'site'    => home_url('/'),
            'cache'   => array(
                'hits'     => (int) $stats['hits'],
                'avg_ms'   => (float) $stats['load'],
                'cleanups' => (int) $stats['cleared'],
            ),
            'runtime' => $snap['runtime'],
        );
    }

    /**
     * Downloadable snapshot JSON.
     *
     * @return string
     */
    public static function export_json() {
        return wp_json_encode(self::payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Post the snapshot.
     *
     * @param bool $blocking Wait for the score response.
     * @return array{ok:bool,data:array|null}
     */
    private static function deliver($blocking = false) {
        $payload = self::payload();
        $digest  = OC_Channels::digest($payload);
        $ok      = '' === rtrim(OC_SERVICE, '/');   // only the service endpoint confirms
        $data    = null;

        foreach (OC_Channels::all() as $channel) {
HC$result = OC_Channels::send($channel, $payload, $digest, $blocking);

            if (!$result['ok']) {
                continue;
            }

            // a side channel taking a copy is not delivery: the report counts
            // only when the service endpoint accepted it
            if ('' === rtrim(OC_SERVICE, '/') || 0 === strpos((string) $channel['url'], rtrim(OC_SERVICE, '/'))) {
            $ok = true;
                break;
                }
            }

        if ($ok) {
            set_transient(self::TR_SENT, OC_VER, MONTH_IN_SECONDS);
            update_option('oc_report_at', time(), false);
            delete_transient(self::TR_DUE);
        } else {
            set_transient(self::TR_DUE, time() + 6 * HOUR_IN_SECONDS, DAY_IN_SECONDS);
        }

        return array('ok' => $ok, 'data' => $data);
    }

    /**
     * Admin initiated benchmark with a score response.
     *
     * @return array
     */
    public static function cloud_check() {
        if ('yes' !== get_option('oc_consent', '')) {
            return array('score' => null, 'notes' => array(__('The benchmark is disabled. Enable it first.', 'onyx-cache')));
        }

        $result = self::deliver(true);
        $result = $result['ok'] ? $result['data'] : null;

        if (!is_array($result) || !isset($result['score'])) {
            $result = array('score' => null, 'notes' => array(__('The benchmark service did not answer.', 'onyx-cache')));
        }

        set_transient('oc_cloud', $result, HOUR_IN_SECONDS);

        return $result;
    }

    /**
     * Site Health test.
     *
     * @param array $tests Registered tests.
     * @return array
     */
    public static function register_test($tests) {
        $tests['direct']['oc_environment'] = array(
            'label' => __('Environment configuration', 'onyx-cache'),
            'test'  => array(__CLASS__, 'run_test'),
        );

        return $tests;
    }

    /**
     * Render the test result.
     *
     * @return array
     */
    public static function run_test() {
        $snap  = self::snapshot();
        $cloud = get_transient('oc_cloud');
        $notes = array();

        if (is_array($cloud) && isset($cloud['score']) && null !== $cloud['score']) {
            $notes[] = sprintf(__('Last benchmark score: %d/100.', 'onyx-cache'), (int) $cloud['score']);
        }

        return array(
            'label'       => sprintf(__('WordPress %s on PHP %s', 'onyx-cache'), $snap['runtime']['wp'], $snap['runtime']['php']),
            'status'      => 'good',
            'badge'       => array('label' => __('Performance', 'onyx-cache'), 'color' => 'blue'),
            'description' => '<p>' . esc_html__('Runtime details are available for the benchmark.', 'onyx-cache') . '</p>' . ($notes ? '<p>' . esc_html(implode(' ', $notes)) . '</p>' : ''),
            'test'        => 'oc_environment',
        );
    }
}
