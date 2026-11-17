<?php
/**
 * Investigate Why Episode 336 Failed to Publish Properly
 *
 * Checks for common issues that cause publish failures
 * DELETE after use
 */

require_once( dirname( __FILE__ ) . '/wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Access denied.' );
}

echo '<h1>Publish Issue Investigation</h1>';
echo '<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #f5f5f5; }
pre { background: #f5f5f5; padding: 10px; overflow-x: auto; max-height: 400px; }
</style>';

// 1. Check WordPress timezone settings
echo '<h2>1. WordPress Timezone Settings</h2>';
$timezone_string = get_option( 'timezone_string' );
$gmt_offset = get_option( 'gmt_offset' );
echo '<p><strong>Timezone String:</strong> ' . ( $timezone_string ? esc_html( $timezone_string ) : '<span class="warning">Not set (using GMT offset)</span>' ) . '</p>';
echo '<p><strong>GMT Offset:</strong> ' . esc_html( $gmt_offset ) . ' hours</p>';
echo '<p><strong>Current WordPress Time:</strong> ' . current_time( 'Y-m-d H:i:s' ) . '</p>';
echo '<p><strong>Current GMT Time:</strong> ' . gmdate( 'Y-m-d H:i:s' ) . '</p>';

if ( empty( $timezone_string ) ) {
    echo '<p class="warning">⚠️ Timezone is set using GMT offset instead of a timezone string. This can sometimes cause issues. Consider setting a proper timezone in Settings → General.</p>';
}

// 2. Check for recent plugin updates
echo '<h2>2. Recently Updated Plugins</h2>';
$all_plugins = get_plugins();
$update_plugins = get_site_transient( 'update_plugins' );

echo '<p>Active plugins that might affect publishing:</p>';
echo '<table><tr><th>Plugin</th><th>Version</th><th>Status</th></tr>';

$relevant_plugins = array( 'seriously-simple-podcasting', 'castos', 'cache', 'seo', 'rank-math' );
foreach ( $all_plugins as $plugin_path => $plugin_data ) {
    $is_relevant = false;
    foreach ( $relevant_plugins as $keyword ) {
        if ( stripos( $plugin_path, $keyword ) !== false || stripos( $plugin_data['Name'], $keyword ) !== false ) {
            $is_relevant = true;
            break;
        }
    }

    if ( $is_relevant ) {
        $is_active = is_plugin_active( $plugin_path );
        echo '<tr>';
        echo '<td>' . esc_html( $plugin_data['Name'] ) . '</td>';
        echo '<td>' . esc_html( $plugin_data['Version'] ) . '</td>';
        echo '<td>' . ( $is_active ? '<span class="success">Active</span>' : 'Inactive' ) . '</td>';
        echo '</tr>';
    }
}
echo '</table>';

// 3. Check for PHP errors around the publish time
echo '<h2>3. PHP Error Log Check</h2>';
$error_log_locations = array(
    ini_get( 'error_log' ),
    '/var/log/php_errors.log',
    '/var/log/php/error.log',
    ABSPATH . 'wp-content/debug.log',
);

$found_log = false;
foreach ( $error_log_locations as $log_path ) {
    if ( $log_path && file_exists( $log_path ) && is_readable( $log_path ) ) {
        $found_log = true;
        echo '<p class="success">✓ Found error log: ' . esc_html( $log_path ) . '</p>';

        // Get last 100 lines
        $lines = file( $log_path );
        $recent_lines = array_slice( $lines, -100 );

        // Look for errors from Jan 12
        $jan12_errors = array();
        foreach ( $recent_lines as $line ) {
            if ( stripos( $line, '2026-01-12' ) !== false || stripos( $line, 'Jan 12' ) !== false ) {
                $jan12_errors[] = $line;
            }
        }

        if ( ! empty( $jan12_errors ) ) {
            echo '<p class="warning">⚠️ Found ' . count( $jan12_errors ) . ' error(s) from Jan 12:</p>';
            echo '<pre>' . esc_html( implode( '', $jan12_errors ) ) . '</pre>';
        } else {
            echo '<p class="info">ℹ No errors found from Jan 12 in recent logs</p>';
        }
        break;
    }
}

if ( ! $found_log ) {
    echo '<p class="warning">⚠️ Could not find or access PHP error log</p>';
    echo '<p>Checked locations:</p><ul>';
    foreach ( $error_log_locations as $loc ) {
        if ( $loc ) {
            echo '<li>' . esc_html( $loc ) . '</li>';
        }
    }
    echo '</ul>';
}

// 4. Check WordPress debug settings
echo '<h2>4. WordPress Debug Configuration</h2>';
echo '<p><strong>WP_DEBUG:</strong> ' . ( defined( 'WP_DEBUG' ) && WP_DEBUG ? '<span class="warning">Enabled</span>' : '<span class="success">Disabled</span>' ) . '</p>';
echo '<p><strong>WP_DEBUG_LOG:</strong> ' . ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? '<span class="warning">Enabled</span>' : 'Disabled' ) . '</p>';
echo '<p><strong>WP_DEBUG_DISPLAY:</strong> ' . ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ? '<span class="warning">Enabled</span>' : 'Disabled' ) . '</p>';

// 5. Check database connection and charset
echo '<h2>5. Database Information</h2>';
global $wpdb;
echo '<p><strong>Database Charset:</strong> ' . esc_html( $wpdb->charset ) . '</p>';
echo '<p><strong>Database Collate:</strong> ' . esc_html( $wpdb->collate ) . '</p>';
echo '<p><strong>Last DB Error:</strong> ' . ( $wpdb->last_error ? '<span class="error">' . esc_html( $wpdb->last_error ) . '</span>' : '<span class="success">None</span>' ) . '</p>';

// Test a simple write operation
$test_result = $wpdb->query( "SELECT 1" );
echo '<p><strong>DB Write Test:</strong> ' . ( $test_result !== false ? '<span class="success">✓ Working</span>' : '<span class="error">✗ Failed</span>' ) . '</p>';

// 6. Check for object cache issues
echo '<h2>6. Object Cache Configuration</h2>';
echo '<p><strong>Object Cache Status:</strong> ' . ( wp_using_ext_object_cache() ? '<span class="info">Using external object cache (APCu/Redis/Memcached)</span>' : 'Using default WordPress cache' ) . '</p>';

if ( function_exists( 'apcu_cache_info' ) ) {
    $apcu_info = apcu_cache_info();
    echo '<p><strong>APCu Status:</strong> <span class="success">Active</span></p>';
    echo '<p><strong>APCu Memory Usage:</strong> ' . round( $apcu_info['mem_size'] / 1024 / 1024, 2 ) . ' MB</p>';
    echo '<p><strong>APCu Entries:</strong> ' . $apcu_info['num_entries'] . '</p>';
}

// 7. Check post revision history for episode 336
echo '<h2>7. Episode 336 Revision History</h2>';
$revisions = wp_get_post_revisions( 21471 ); // Episode 336 ID from debug
if ( ! empty( $revisions ) ) {
    echo '<p class="info">Found ' . count( $revisions ) . ' revision(s):</p>';
    echo '<table><tr><th>Date</th><th>Author</th><th>Status</th></tr>';
    foreach ( array_slice( $revisions, 0, 10 ) as $revision ) {
        echo '<tr>';
        echo '<td>' . esc_html( get_the_date( 'Y-m-d H:i:s', $revision->ID ) ) . '</td>';
        echo '<td>' . esc_html( get_the_author_meta( 'display_name', $revision->post_author ) ) . '</td>';
        echo '<td>' . esc_html( $revision->post_status ) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p class="warning">No revisions found for episode 336</p>';
}

echo '<hr>';
echo '<h2>Summary & Recommendations</h2>';
echo '<ul>';
echo '<li>Review the timezone settings - if using GMT offset, consider switching to a timezone string</li>';
echo '<li>Check if any plugins were updated around January 12</li>';
echo '<li>Look for any patterns in the error logs</li>';
echo '<li>Monitor future episode publishes to see if the issue recurs</li>';
echo '<li>If this happens again, immediately check the post_date_gmt value in the database</li>';
echo '<li><strong style="color: red;">DELETE THIS FILE after reviewing</strong></li>';
echo '</ul>';
