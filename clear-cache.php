<?php
/**
 * Emergency Cache Clear Script
 *
 * Upload to your WordPress root directory and visit in browser to clear all caches
 * DELETE THIS FILE after use for security
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Access denied. You must be logged in as an administrator.' );
}

echo '<h1>Cache Clearing Utility</h1>';
echo '<style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>';

// 1. Clear APCu cache
echo '<h2>1. APCu Object Cache</h2>';
if ( function_exists( 'apcu_clear_cache' ) ) {
    if ( apcu_clear_cache() ) {
        echo '<p class="success">✓ APCu cache cleared successfully</p>';
    } else {
        echo '<p class="error">✗ Failed to clear APCu cache</p>';
    }
} else {
    echo '<p class="info">ℹ APCu not available or not enabled</p>';
}

// 2. Clear WordPress object cache
echo '<h2>2. WordPress Object Cache</h2>';
if ( wp_cache_flush() ) {
    echo '<p class="success">✓ WordPress object cache flushed</p>';
} else {
    echo '<p class="error">✗ Failed to flush WordPress object cache</p>';
}

// 3. Clear all transients
echo '<h2>3. Transients</h2>';
global $wpdb;
$result = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'" );
echo '<p class="success">✓ Cleared ' . $result . ' transient records</p>';

// 4. Clear rewrite rules (can affect feeds)
echo '<h2>4. Rewrite Rules</h2>';
flush_rewrite_rules();
echo '<p class="success">✓ Rewrite rules flushed</p>';

// 5. Check latest podcast episodes
echo '<h2>5. Latest Podcast Episodes (Verification)</h2>';
$episodes = new WP_Query( array(
    'post_type'      => 'podcast',
    'posts_per_page' => 10,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
) );

if ( $episodes->have_posts() ) {
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
    echo '<tr><th>Episode #</th><th>Title</th><th>Status</th><th>Date</th><th>Audio File</th></tr>';

    while ( $episodes->have_posts() ) {
        $episodes->the_post();
        $episode_num = get_post_meta( get_the_ID(), 'itunes:episode', true );
        if ( empty( $episode_num ) ) {
            $episode_num = get_post_meta( get_the_ID(), 'episode_number', true );
        }
        $audio_file = get_post_meta( get_the_ID(), 'audio_file', true );

        echo '<tr>';
        echo '<td>' . esc_html( $episode_num ? $episode_num : 'N/A' ) . '</td>';
        echo '<td>' . esc_html( get_the_title() ) . '</td>';
        echo '<td>' . esc_html( get_post_status() ) . '</td>';
        echo '<td>' . esc_html( get_the_date() ) . '</td>';
        echo '<td>' . ( $audio_file ? '✓ Yes' : '✗ No' ) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    wp_reset_postdata();
} else {
    echo '<p class="error">No podcast episodes found!</p>';
}

// 6. Check for cached feed
echo '<h2>6. Feed Information</h2>';
$feed_url = home_url( '/feed/podcast/tcp-show/' );
echo '<p>Feed URL: <a href="' . esc_url( $feed_url ) . '" target="_blank">' . esc_html( $feed_url ) . '</a></p>';
echo '<p class="info">ℹ Visit the feed URL now to see if episode 336 appears</p>';

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ol>';
echo '<li>Check the table above to verify episode 336 is listed</li>';
echo '<li>Visit your homepage and RSS feed to confirm episode 336 now appears</li>';
echo '<li>If still not showing, check if episode 336 has an audio file attached</li>';
echo '<li><strong style="color: red;">DELETE THIS FILE (clear-cache.php) for security</strong></li>';
echo '</ol>';
