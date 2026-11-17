<?php
/**
 * Debug Episode 336 - Why isn't it appearing?
 *
 * Upload to WordPress root and visit in browser
 * DELETE after use
 */

require_once( dirname( __FILE__ ) . '/wp-load.php' );

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Access denied.' );
}

echo '<h1>Episode 336 Debug Report</h1>';
echo '<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #f5f5f5; }
pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style>';

// 1. Search for episode 336 by title pattern
echo '<h2>1. Searching for Episode 336</h2>';

$search_query = new WP_Query( array(
    's' => '336',
    'post_type' => 'podcast',
    'post_status' => 'any',
    'posts_per_page' => 10,
) );

if ( $search_query->have_posts() ) {
    echo '<p class="info">Found ' . $search_query->found_posts . ' episodes matching "336":</p>';
    echo '<table>';
    echo '<tr><th>ID</th><th>Title</th><th>Status</th><th>Date</th><th>Post Type</th></tr>';

    while ( $search_query->have_posts() ) {
        $search_query->the_post();
        echo '<tr>';
        echo '<td>' . get_the_ID() . '</td>';
        echo '<td>' . esc_html( get_the_title() ) . '</td>';
        echo '<td>' . esc_html( get_post_status() ) . '</td>';
        echo '<td>' . esc_html( get_the_date( 'Y-m-d H:i:s' ) ) . '</td>';
        echo '<td>' . esc_html( get_post_type() ) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    wp_reset_postdata();
} else {
    echo '<p class="error">✗ No posts found matching "336"</p>';
}

// 2. Get the most recent posts by date
echo '<h2>2. Most Recent Posts by Date (All Statuses)</h2>';

$recent_query = new WP_Query( array(
    'post_type' => 'podcast',
    'post_status' => 'any',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC',
) );

if ( $recent_query->have_posts() ) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Title</th><th>Status</th><th>Date (GMT)</th><th>Post Type</th></tr>';

    while ( $recent_query->have_posts() ) {
        $recent_query->the_post();
        echo '<tr>';
        echo '<td>' . get_the_ID() . '</td>';
        echo '<td>' . esc_html( get_the_title() ) . '</td>';
        echo '<td>' . esc_html( get_post_status() ) . '</td>';
        echo '<td>' . esc_html( get_the_date( 'Y-m-d H:i:s' ) ) . ' / ' . esc_html( get_post_field( 'post_date_gmt' ) ) . '</td>';
        echo '<td>' . esc_html( get_post_type() ) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    wp_reset_postdata();
}

// 3. Check if there's a post with publish date of Jan 12, 2026
echo '<h2>3. Posts Published on January 12, 2026</h2>';

$jan12_query = new WP_Query( array(
    'post_type' => 'podcast',
    'post_status' => 'any',
    'date_query' => array(
        array(
            'year'  => 2026,
            'month' => 1,
            'day'   => 12,
        ),
    ),
    'posts_per_page' => -1,
) );

if ( $jan12_query->have_posts() ) {
    echo '<p class="success">✓ Found ' . $jan12_query->found_posts . ' post(s) from Jan 12, 2026:</p>';
    echo '<table>';
    echo '<tr><th>ID</th><th>Title</th><th>Status</th><th>Date</th><th>Episode #</th><th>Audio File</th></tr>';

    while ( $jan12_query->have_posts() ) {
        $jan12_query->the_post();
        $episode_num = get_post_meta( get_the_ID(), 'itunes:episode', true );
        if ( empty( $episode_num ) ) {
            $episode_num = get_post_meta( get_the_ID(), 'episode_number', true );
        }
        $audio = get_post_meta( get_the_ID(), 'audio_file', true );

        echo '<tr>';
        echo '<td>' . get_the_ID() . '</td>';
        echo '<td>' . esc_html( get_the_title() ) . '</td>';
        echo '<td><strong>' . esc_html( get_post_status() ) . '</strong></td>';
        echo '<td>' . esc_html( get_the_date( 'Y-m-d H:i:s' ) ) . '</td>';
        echo '<td>' . esc_html( $episode_num ? $episode_num : 'N/A' ) . '</td>';
        echo '<td>' . ( $audio ? '✓' : '✗' ) . '</td>';
        echo '</tr>';

        // Show full details for this post
        echo '<tr><td colspan="6">';
        echo '<details><summary>Full Post Details (click to expand)</summary>';
        echo '<pre>';
        $post_obj = get_post( get_the_ID() );
        print_r( $post_obj );
        echo '</pre>';
        echo '<h4>All Post Meta:</h4>';
        echo '<pre>';
        $all_meta = get_post_meta( get_the_ID() );
        print_r( $all_meta );
        echo '</pre>';
        echo '</details>';
        echo '</td></tr>';
    }
    echo '</table>';
    wp_reset_postdata();
} else {
    echo '<p class="error">✗ No posts found from January 12, 2026</p>';
}

// 4. Check SSP settings
echo '<h2>4. Seriously Simple Podcasting Settings</h2>';

if ( class_exists( 'SeriouslySimplePodcasting' ) ) {
    echo '<p class="success">✓ Seriously Simple Podcasting is active</p>';

    // Check registered post types
    $ssp_post_types = get_option( 'ss_podcasting_use_post_types', array( 'podcast' ) );
    echo '<p><strong>Registered post types:</strong> ' . implode( ', ', (array) $ssp_post_types ) . '</p>';

    // Check feed settings
    $feed_series = get_option( 'ss_podcasting_data_series', array() );
    echo '<p><strong>Feed series:</strong></p><pre>' . print_r( $feed_series, true ) . '</pre>';
} else {
    echo '<p class="error">✗ Seriously Simple Podcasting is NOT active</p>';
}

// 5. Direct database query
echo '<h2>5. Direct Database Query</h2>';

global $wpdb;
$results = $wpdb->get_results(
    "SELECT ID, post_title, post_status, post_date, post_type, post_date_gmt
    FROM {$wpdb->posts}
    WHERE post_type = 'podcast'
    AND post_date LIKE '2026-01-12%'
    ORDER BY post_date DESC",
    ARRAY_A
);

if ( $results ) {
    echo '<p class="success">✓ Found ' . count( $results ) . ' podcast post(s) from Jan 12 in database:</p>';
    echo '<pre>';
    print_r( $results );
    echo '</pre>';
} else {
    echo '<p class="error">✗ No podcast posts from Jan 12 found in database</p>';
}

echo '<hr>';
echo '<h2>Diagnosis Summary</h2>';
echo '<ul>';
echo '<li>Check section 3 to see if episode 336 appears</li>';
echo '<li>If it shows up but status is not "publish", that\'s the problem</li>';
echo '<li>If it doesn\'t show up at all, check the post date/time in WordPress admin</li>';
echo '<li>Compare the GMT time vs local time - timezone issues can cause this</li>';
echo '<li><strong style="color: red;">DELETE THIS FILE after reviewing</strong></li>';
echo '</ul>';
