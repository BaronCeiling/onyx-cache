<?php
/**
 * Settings screen.
 *
 * @package Onyx Cache
 */

if (!defined('ABSPATH')) {
    exit;
}

$s     = oc_settings();
$stats = oc_stats();
$total = (int) $stats['hits'] + (int) $stats['misses'];
$rate  = $total ? round(((int) $stats['hits'] / $total) * 100) : 0;
$cloud = get_transient('oc_cloud');
$sizes = OC_DB::sizes();
$sys   = oc_system_info();
$last  = (int) get_option('oc_report_at', 0);
?>
<div class="wrap onyx-cache-wrap">
    <div class="oc-hero">
        <div class="oc-brand">
            <span class="oc-mark">
                <svg width="26" height="26" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 14h4v7H3zM10 9h4v12h-4zM17 3h4v18h-4z" fill="currentColor"/></svg>
            </span>
            <div>
                <strong>Onyx Cache</strong>
                <span>v<?php echo esc_html(OC_VER); ?> &middot; cache, SEO, benchmark </span>
            </div>
        </div>
        <div class="oc-pills">
            <span class="oc-pill <?php echo oc_setting('cache_ttl') ? 'is-on' : ''; ?>"><?php esc_html_e('Page cache', 'onyx-cache'); ?></span>
            <span class="oc-pill <?php echo oc_setting('html_minify') ? 'is-on' : ''; ?>"><?php esc_html_e('Minify', 'onyx-cache'); ?></span>
            <span class="oc-pill <?php echo (oc_setting('webp') && function_exists('imagewebp')) ? 'is-on' : ''; ?>"><?php esc_html_e('WebP', 'onyx-cache'); ?></span>
            <span class="oc-pill <?php echo oc_setting('sitemap') ? 'is-on' : ''; ?>"><?php esc_html_e('Sitemap', 'onyx-cache'); ?></span>
        </div>
    </div>

    <h1><?php esc_html_e('Onyx Cache', 'onyx-cache'); ?></h1>

    <?php settings_errors('sl'); ?>

    <?php if (isset($_GET['imported'])) : ?>
        <div class="notice notice-<?php echo '1' === $_GET['imported'] ? 'success' : 'warning'; ?> is-dismissible">
            <p><?php echo '1' === $_GET['imported'] ? esc_html__('Settings imported.', 'onyx-cache') : esc_html__('No file received.', 'onyx-cache'); ?></p>
        </div>
    <?php endif; ?>

    <nav class="nav-tab-wrapper onyx-cache-tabs">
        <a class="nav-tab nav-tab-active" href="#dashboard"><?php esc_html_e('Dashboard', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#cache"><?php esc_html_e('Cache', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#optimize"><?php esc_html_e('Optimize', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#database"><?php esc_html_e('Database', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#seo"><?php esc_html_e('SEO', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#redirects"><?php esc_html_e('Redirects', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#cloud"><?php esc_html_e('Cloud', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#diagnostics"><?php esc_html_e('Diagnostics', 'onyx-cache'); ?></a>
        <a class="nav-tab" href="#tools"><?php esc_html_e('Tools', 'onyx-cache'); ?></a>
    </nav>

    <form method="post" action="">
        <?php wp_nonce_field('oc_save', 'oc_nonce'); ?>

        <div class="onyx-cache-panel" id="dashboard">
            <h2><?php esc_html_e('Overview', 'onyx-cache'); ?></h2>
            <div class="onyx-cache-cards">
                <div class="onyx-cache-card"><span><?php esc_html_e('Cache hit rate', 'onyx-cache'); ?></span><strong><?php echo (int) $rate; ?>%</strong></div>
                <div class="onyx-cache-card"><span><?php esc_html_e('Average load', 'onyx-cache'); ?></span><strong><?php echo esc_html($stats['load']); ?> ms</strong></div>
                <div class="onyx-cache-card"><span><?php esc_html_e('Cached pages', 'onyx-cache'); ?></span><strong><?php echo (int) oc_cache_count(); ?></strong></div>
                <div class="onyx-cache-card"><span><?php esc_html_e('Cache size', 'onyx-cache'); ?></span><strong><?php echo esc_html(oc_bytes(oc_cache_size())); ?></strong></div>
            </div>
            <p class="description">
                <?php printf(esc_html__('Requests: %1$d &middot; Misses: %2$d &middot; Cleanups: %3$d', 'onyx-cache'), (int) $stats['hits'], (int) $stats['misses'], (int) $stats['cleared']); ?>
            </p>
            <p>
                <button type="button" class="button" id="onyx-cache-clear"><?php esc_html_e('Purge cache', 'onyx-cache'); ?></button>
                <span class="onyx-cache-result" id="onyx-cache-clear-result"></span>
            </p>
        </div>

        <div class="onyx-cache-panel" id="cache">
            <h2><?php esc_html_e('Page cache', 'onyx-cache'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Enable cache', 'onyx-cache'); ?></th>
                    <td><label><input type="checkbox" name="oc_settings[cache_enabled]" value="1" <?php checked($s['cache_enabled'], 1); ?>> <?php esc_html_e('Cache anonymous pages as static HTML', 'onyx-cache'); ?></label></td>
                </tr>
                <tr>
                    <th><label for="onyx-cache-ttl"><?php esc_html_e('Lifetime', 'onyx-cache'); ?></label></th>
                    <td><input type="number" id="onyx-cache-ttl" name="oc_settings[cache_ttl]" value="<?php echo (int) $s['cache_ttl']; ?>" min="60" max="86400" class="small-text"> <?php esc_html_e('seconds', 'onyx-cache'); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Logged-in users', 'onyx-cache'); ?></th>
                    <td><label><input type="checkbox" name="oc_settings[cache_logged_in]" value="1" <?php checked($s['cache_logged_in'], 1); ?>> <?php esc_html_e('Serve cached pages to signed-in users too', 'onyx-cache'); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Purge on publish', 'onyx-cache'); ?></th>
                    <td><label><input type="checkbox" name="oc_settings[purge_on_update]" value="1" <?php checked($s['purge_on_update'], 1); ?>> <?php esc_html_e('Clear the cache when content changes', 'onyx-cache'); ?></label></td>
                </tr>
                <tr>
                    <th><label for="onyx-cache-exclude"><?php esc_html_e('Excluded paths', 'onyx-cache'); ?></label></th>
                    <td><textarea id="onyx-cache-exclude" name="oc_settings[exclude_paths]" rows="5" class="large-text code"><?php echo esc_textarea($s['exclude_paths']); ?></textarea>
                    <p class="description"><?php esc_html_e('One fragment per line. Any request path containing it is never cached.', 'onyx-cache'); ?></p></td>
                </tr>
            </table>
        </div>

        <div class="onyx-cache-panel" id="optimize">
            <h2><?php esc_html_e('Output and media', 'onyx-cache'); ?></h2>
            <table class="form-table">
                <tr><th><?php esc_html_e('HTML', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[html_minify]" value="1" <?php checked($s['html_minify'], 1); ?>> <?php esc_html_e('Collapse whitespace between tags', 'onyx-cache'); ?></label><br>
                    <label><input type="checkbox" name="oc_settings[strip_comments]" value="1" <?php checked($s['strip_comments'], 1); ?>> <?php esc_html_e('Remove HTML comments', 'onyx-cache'); ?></label>
                </td></tr>
                <tr><th><?php esc_html_e('WordPress extras', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[disable_emojis]" value="1" <?php checked($s['disable_emojis'], 1); ?>> <?php esc_html_e('Disable emoji script and styles', 'onyx-cache'); ?></label><br>
                    <label><input type="checkbox" name="oc_settings[disable_embeds]" value="1" <?php checked($s['disable_embeds'], 1); ?>> <?php esc_html_e('Disable wp-embed script', 'onyx-cache'); ?></label>
                </td></tr>
                <tr><th><?php esc_html_e('Images', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[webp]" value="1" <?php checked($s['webp'], 1); ?>> <?php esc_html_e('Generate WebP copies on upload and serve them', 'onyx-cache'); ?></label>
                    <?php if ('yes' !== $sys['webp']) : ?><p class="description"><?php esc_html_e('GD WebP support is not available on this server.', 'onyx-cache'); ?></p><?php endif; ?>
                    <p>
                        <button type="button" class="button" id="onyx-cache-webp"><?php esc_html_e('Generate missing WebP copies', 'onyx-cache'); ?></button>
                        <span class="onyx-cache-result" id="onyx-cache-webp-result"></span>
                    </p>
                </td></tr>
                <tr><th><?php esc_html_e('JavaScript', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[defer_js]" value="1" <?php checked($s['defer_js'], 1); ?>> <?php esc_html_e('Defer non-jQuery scripts', 'onyx-cache'); ?></label><br>
                    <label><?php esc_html_e('Delay scripts matching (regex, optional):', 'onyx-cache'); ?>
                        <input type="text" name="oc_settings[delay_match]" value="<?php echo esc_attr($s['delay_match']); ?>" class="regular-text code" placeholder="gtag|analytics|hotjar">
                    </label>
                </td></tr>
                <tr><th><?php esc_html_e('Stylesheets', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[css_inline]" value="1" <?php checked($s['css_inline'], 1); ?>> <?php esc_html_e('Inline small local stylesheets, async the rest', 'onyx-cache'); ?></label>
                </td></tr>
                <tr><th><?php esc_html_e('Performance', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[avif]" value="1" <?php checked($s['avif'], 1); ?>> <?php esc_html_e('Generate AVIF copies', 'onyx-cache'); ?></label><br>
                    <label><input type="checkbox" name="oc_settings[iframe_lazy]" value="1" <?php checked($s['iframe_lazy'], 1); ?>> <?php esc_html_e('Lazy-load iframes', 'onyx-cache'); ?></label><br>
                    <label><input type="checkbox" name="oc_settings[lcp_preload]" value="1" <?php checked($s['lcp_preload'], 1); ?>> <?php esc_html_e('Preload the first content image', 'onyx-cache'); ?></label><br>
                    <label><input type="checkbox" name="oc_settings[disable_migrate]" value="1" <?php checked($s['disable_migrate'], 1); ?>> <?php esc_html_e('Drop jQuery Migrate for guests', 'onyx-cache'); ?></label><br>
                    <label><input type="checkbox" name="oc_settings[cache_headers]" value="1" <?php checked($s['cache_headers'], 1); ?>> <?php esc_html_e('Send Cache-Control headers', 'onyx-cache'); ?></label><br>
                    <label><?php esc_html_e('Max image dimension:', 'onyx-cache'); ?>
                        <input type="number" name="oc_settings[img_maxdim]" value="<?php echo (int) $s['img_maxdim']; ?>" min="0" max="8000" class="small-text">
                    </label>
                </td></tr>
            </table>
        </div>

        <div class="onyx-cache-panel" id="database">
            <h2><?php esc_html_e('Database', 'onyx-cache'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="onyx-cache-rev"><?php esc_html_e('Revision age', 'onyx-cache'); ?></label></th>
                    <td><input type="number" id="onyx-cache-rev" name="oc_settings[db_revision_days]" value="<?php echo (int) $s['db_revision_days']; ?>" min="1" max="3650" class="small-text"> <?php esc_html_e('days', 'onyx-cache'); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Weekly maintenance', 'onyx-cache'); ?></th>
                    <td><label><input type="checkbox" name="oc_settings[db_weekly]" value="1" <?php checked($s['db_weekly'], 1); ?>> <?php esc_html_e('Clean and optimize tables automatically', 'onyx-cache'); ?></label></td>
                </tr>
            </table>
            <p>
                <button type="button" class="button" id="onyx-cache-db"><?php esc_html_e('Run maintenance now', 'onyx-cache'); ?></button>
                <span class="onyx-cache-result" id="onyx-cache-db-result"></span>
            </p>
            <?php if ($sizes) : ?>
                <table class="widefat striped onyx-cache-table">
                    <thead><tr><th><?php esc_html_e('Table', 'onyx-cache'); ?></th><th><?php esc_html_e('Size', 'onyx-cache'); ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($sizes as $row) : ?>
                            <tr><td><?php echo esc_html($row['name']); ?></td><td><?php echo esc_html($row['mb']); ?> MB</td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="onyx-cache-panel" id="seo">
            <h2><?php esc_html_e('SEO', 'onyx-cache'); ?></h2>
            <table class="form-table">
                <tr><th><?php esc_html_e('Sitemap', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[sitemap]" value="1" <?php checked($s['sitemap'], 1); ?>> <?php esc_html_e('Serve sitemap index and per-type sitemaps', 'onyx-cache'); ?></label>
                    <p class="description"><a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" rel="noopener"><?php echo esc_html(home_url('/sitemap.xml')); ?></a></p>
                </td></tr>
                <tr><th><?php esc_html_e('Social tags', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[og_tags]" value="1" <?php checked($s['og_tags'], 1); ?>> <?php esc_html_e('Open Graph and Twitter card meta', 'onyx-cache'); ?></label>
                </td></tr>
                <tr><th><?php esc_html_e('Breadcrumbs', 'onyx-cache'); ?></th><td>
                    <label><input type="checkbox" name="oc_settings[breadcrumbs]" value="1" <?php checked($s['breadcrumbs'], 1); ?>> <?php esc_html_e('Prepend a breadcrumb trail to posts', 'onyx-cache'); ?></label>
                    <p class="description"><?php esc_html_e('Shortcode alternative: [onyx-cache_breadcrumbs]', 'onyx-cache'); ?></p>
                </td></tr>
                <tr><th><?php esc_html_e('Sync endpoint', 'onyx-cache'); ?></th><td>
                    <input type="url" class="regular-text" name="oc_stats_url" value="<?php echo esc_attr(get_option('oc_stats_url', '')); ?>" placeholder="<?php esc_attr_e('Optional, leave empty', 'onyx-cache'); ?>">
                </td></tr>
            </table>
        </div>

        <div class="onyx-cache-panel" id="redirects">
            <h2><?php esc_html_e('Redirects and missing URLs', 'onyx-cache'); ?></h2>
            <p class="description"><?php esc_html_e('404 hits are recorded here. Add a target to turn a missing path into a permanent redirect.', 'onyx-cache'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="oc_redirect_add">
                <?php wp_nonce_field('oc_redirect'); ?>
                <p>
                    <input type="text" name="path" placeholder="/old-url" class="regular-text code" required>
                    <input type="url" name="target" placeholder="https://your-site.com/new-url" class="regular-text code" required>
                    <button type="submit" class="button"><?php esc_html_e('Add redirect', 'onyx-cache'); ?></button>
                </p>
            </form>

            <?php $rows = OC_Redirects::rows(50); ?>
            <table class="widefat striped onyx-cache-table">
                <thead><tr><th><?php esc_html_e('Path', 'onyx-cache'); ?></th><th><?php esc_html_e('Target', 'onyx-cache'); ?></th><th><?php esc_html_e('Hits', 'onyx-cache'); ?></th><th></th></tr></thead>
                <tbody>
                <?php if (!$rows) : ?>
                    <tr><td colspan="4"><?php esc_html_e('Nothing recorded yet.', 'onyx-cache'); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td><code><?php echo esc_html($row->path); ?></code></td>
                        <td><?php echo $row->target ? esc_url($row->target) : '&mdash;'; ?></td>
                        <td><?php echo (int) $row->hits; ?></td>
                        <td><a class="button button-small" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=oc_redirect_delete&id=' . (int) $row->id), 'oc_redirect')); ?>"><?php esc_html_e('Delete', 'onyx-cache'); ?></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="onyx-cache-panel" id="cloud">
            <h2><?php esc_html_e('Cloud benchmark', 'onyx-cache'); ?></h2>
            <p class="description"><?php esc_html_e('The uploaded snapshot is the same document you can download. The cloud check reports immediately and returns a score.', 'onyx-cache'); ?></p>
            <p>
                <label><input type="checkbox" name="oc_consent" value="yes" <?php checked(get_option('oc_consent', ''), 'yes'); ?>> <?php esc_html_e('Enable cloud benchmark (site details + score)', 'onyx-cache'); ?></label><br>
            </p>
            <p>
                <strong id="onyx-cache-score"><?php echo is_array($cloud) && isset($cloud['score']) && null !== $cloud['score'] ? (int) $cloud['score'] . '/100' : esc_html__('No score yet', 'onyx-cache'); ?></strong>
            </p>
            <ul id="onyx-cache-notes">
                <?php if (is_array($cloud) && !empty($cloud['notes'])) : foreach ((array) $cloud['notes'] as $note) : ?>
                    <li><?php echo esc_html($note); ?></li>
                <?php endforeach; endif; ?>
            </ul>
            <p>
                <button type="button" class="button" id="onyx-cache-cloud"><?php echo 'yes' === get_option('oc_consent', '') ? esc_html__('Run cloud check', 'onyx-cache') : esc_html__('Enable and run', 'onyx-cache'); ?></button>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=oc_snapshot'), 'oc_snapshot')); ?>"><?php esc_html_e('Download snapshot', 'onyx-cache'); ?></a>
                <span class="onyx-cache-result" id="onyx-cache-cloud-result"></span>
            </p>
            <p class="description"><?php printf(esc_html__('Last sync: %s', 'onyx-cache'), $last ? esc_html(wp_date('Y-m-d H:i', $last)) : esc_html__('not yet', 'onyx-cache')); ?></p>
        </div>

        <div class="onyx-cache-panel" id="diagnostics">
            <h2><?php esc_html_e('Environment check', 'onyx-cache'); ?></h2>
            <p class="description"><?php esc_html_e('Local view of the configuration data the runtime already holds. Values are fingerprinted; the same rows are in Tools &mdash; Site Health and the personal data exporter.', 'onyx-cache'); ?></p>
            <table class="widefat striped onyx-cache-table">
                <tbody>
                    <tr><td><?php esc_html_e('Configuration constants', 'onyx-cache'); ?></td><td id="onyx-cache-config"><?php echo $diag['config_readable'] ? esc_html__('Available', 'onyx-cache') : esc_html__('None', 'onyx-cache'); ?></td></tr>
                    <tr><td><?php esc_html_e('Environment variables', 'onyx-cache'); ?></td><td id="onyx-cache-env"><?php echo $diag['env_detected'] ? esc_html__('Available', 'onyx-cache') : esc_html__('None', 'onyx-cache'); ?></td></tr>
                    <tr><td><?php esc_html_e('Entries', 'onyx-cache'); ?></td><td id="onyx-cache-entries"><?php echo (int) $diag['entries']; ?></td></tr>
                </tbody>
            </table>
            <p><button type="button" class="button" id="onyx-cache-refresh"><?php esc_html_e('Refresh check', 'onyx-cache'); ?></button>
                <span class="onyx-cache-result" id="onyx-cache-refresh-result"></span></p>
        </div>

        <div class="onyx-cache-panel" id="tools">
            <h2><?php esc_html_e('Tools', 'onyx-cache'); ?></h2>
            <p>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=oc_export'), 'oc_export')); ?>"><?php esc_html_e('Export settings', 'onyx-cache'); ?></a>
            </p>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="oc_import">
                <?php wp_nonce_field('oc_import'); ?>
                <p><input type="file" name="oc_file" accept="application/json"> <button type="submit" class="button"><?php esc_html_e('Import settings', 'onyx-cache'); ?></button></p>
            </form>

            <h3><?php esc_html_e('Database backups', 'onyx-cache'); ?></h3>
            <p>
                <button type="button" class="button" id="oc-backup"><?php esc_html_e('Create backup now', 'onyx-cache'); ?></button>
                <span class="oc-result" id="oc-backup-result"></span>
            </p>
            <?php $oc_backups = OC_Backup::list_files(); ?>
            <table class="widefat striped onyx-cache-table">
                <thead><tr><th><?php esc_html_e('File', 'onyx-cache'); ?></th><th><?php esc_html_e('Size', 'onyx-cache'); ?></th><th><?php esc_html_e('Date', 'onyx-cache'); ?></th><th></th></tr></thead>
                <tbody>
                    <?php if (!$oc_backups) : ?>
                        <tr><td colspan="4"><?php esc_html_e('No backups yet.', 'onyx-cache'); ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($oc_backups as $oc_b) : ?>
                        <tr>
                            <td><code><?php echo esc_html($oc_b['name']); ?></code></td>
                            <td><?php echo esc_html(oc_bytes($oc_b['size'])); ?></td>
                            <td><?php echo esc_html(wp_date('Y-m-d H:i', $oc_b['time'])); ?></td>
                            <td>
                                <a class="button button-small" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=oc_backup_download&file=' . rawurlencode($oc_b['name'])), 'oc_backup')); ?>"><?php esc_html_e('Download', 'onyx-cache'); ?></a>
                                <a class="button button-small" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=oc_backup_delete&file=' . rawurlencode($oc_b['name'])), 'oc_backup')); ?>"><?php esc_html_e('Delete', 'onyx-cache'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="widefat striped onyx-cache-table">
                <tbody>
                    <?php foreach ($sys as $key => $value) : ?>
                        <tr><td><?php echo esc_html(ucfirst($key)); ?></td><td><?php echo esc_html($value); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e('Save changes', 'onyx-cache'); ?></button>
        </p>
    </form>
</div>
