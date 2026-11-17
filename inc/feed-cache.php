<?php
/**
 * Static podcast feeds.
 *
 * The Seriously Simple Podcasting feed renders every episode on every request
 * (398 items, ~2.5 MB, 14+ seconds, one DB round-trip per episode for series,
 * meta and media). Measured 2026-09-16: ~180 full renders/hour from Googlebot,
 * Podbean, Amazon Music, Spotify and others, which was the single largest
 * source of database load on the site.
 *
 * This file turns the feed into a static artefact:
 *   - Any change to a podcast post (save, delete, meta, terms) schedules ONE
 *     regeneration a minute later via WP-Cron. An hourly cron is the safety net.
 *   - Regeneration fetches each feed through a loopback request that carries a
 *     bypass header, so it gets the real dynamic render, and writes the XML to
 *     wp-content/uploads/cloudpod-feeds/ (the bind mount shared by both tasks).
 *   - Feed requests are answered from that file at `init`, before the main query
 *     runs, with a stable ETag/Last-Modified so pollers get 304s.
 *   - If the file is missing or older than CLOUDPOD_FEED_STALE_AFTER, the request
 *     falls through to the normal dynamic render. After 3 all-failed regenerations
 *     pollers stop scheduling retries and only the hourly cron keeps trying, so a
 *     broken render degrades to the old behaviour rather than amplifying it.
 *
 * @package CloudPod
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CLOUDPOD_FEED_CRON_HOOK', 'cloudpod_regenerate_feeds' );
define( 'CLOUDPOD_FEED_HOURLY_HOOK', 'cloudpod_regenerate_feeds_hourly' );
define( 'CLOUDPOD_FEED_BYPASS_HEADER', 'X-CloudPod-Feed-Regen' );
/** A cached feed older than this falls through to the dynamic render. */
define( 'CLOUDPOD_FEED_STALE_AFTER', 3 * HOUR_IN_SECONDS );
/** Debounce window: saves within this many seconds collapse into one regeneration. */
define( 'CLOUDPOD_FEED_DEBOUNCE', 60 );

/* -------------------------------------------------------------------------
 * Pure helpers (no WordPress calls) so they can be unit tested in plain PHP.
 * ---------------------------------------------------------------------- */

/**
 * Map a request path to a cache key, or '' if it is not a podcast feed path.
 *
 * Accepts /feed/podcast, /feed/podcast/, /feed/podcast/{series}/ with or
 * without trailing slash. Query strings must already be stripped.
 *
 * @param string $path Request path (no query string).
 * @return string e.g. 'feed-podcast', 'feed-podcast-tcp-talks', or ''.
 */
function cloudpod_feed_key_from_path( $path ) {
    if ( ! is_string( $path ) ) {
        return '';
    }
    $path = trim( $path );
    if ( ! preg_match( '#^/feed/podcast(?:/([a-z0-9][a-z0-9_-]{0,60}))?/?$#', $path, $m ) ) {
        return '';
    }
    return isset( $m[1] ) && '' !== $m[1] ? 'feed-podcast-' . $m[1] : 'feed-podcast';
}

/**
 * Decide whether a conditional request can be answered with 304.
 *
 * @param string   $etag           Current ETag (quoted).
 * @param int      $last_modified  Current Last-Modified as Unix time.
 * @param string   $if_none_match  Raw If-None-Match header value ('' if absent).
 * @param string   $if_mod_since   Raw If-Modified-Since header value ('' if absent).
 * @return bool
 */
function cloudpod_feed_is_not_modified( $etag, $last_modified, $if_none_match, $if_mod_since ) {
    if ( '' !== $if_none_match ) {
        foreach ( array_map( 'trim', explode( ',', $if_none_match ) ) as $candidate ) {
            // Weak comparison: ignore a leading W/ on either side.
            $candidate = preg_replace( '#^W/#i', '', $candidate );
            if ( '*' === $candidate || $candidate === preg_replace( '#^W/#i', '', $etag ) ) {
                return true;
            }
        }
        return false; // If-None-Match present but no match: must send 200 regardless of IMS.
    }
    if ( '' !== $if_mod_since ) {
        $since = strtotime( $if_mod_since );
        return false !== $since && $since >= $last_modified;
    }
    return false;
}

/**
 * Strip volatile parts of a feed body before comparing old and new content.
 * SSP's lastBuildDate is get_lastpostmodified(), which moves on ANY post edit
 * (blog posts included); without this every hourly run would rewrite the file,
 * bump the ETag and make every poller re-download 2.5 MB.
 *
 * @param string $body Feed XML.
 * @return string
 */
function cloudpod_feed_normalize_for_compare( $body ) {
    return (string) preg_replace( '#<lastBuildDate>[^<]*</lastBuildDate>#i', '', (string) $body );
}

/**
 * Minimal sanity check that a body looks like the podcast RSS feed.
 *
 * @param string $body Response body.
 * @return bool
 */
function cloudpod_feed_body_looks_valid( $body ) {
    return cloudpod_feed_body_is_rss( $body ) && false !== stripos( $body, '<item>' );
}

/**
 * Whether a body is a complete RSS document (possibly with zero items).
 *
 * @param string $body Response body.
 * @return bool
 */
function cloudpod_feed_body_is_rss( $body ) {
    if ( ! is_string( $body ) || strlen( $body ) < 200 ) {
        return false;
    }
    $head = substr( ltrim( $body ), 0, 200 );
    return 0 === strpos( $head, '<?xml' ) && false !== stripos( $body, '<rss' ) && false !== strpos( rtrim( $body ), '</rss>' );
}

/* -------------------------------------------------------------------------
 * Storage
 * ---------------------------------------------------------------------- */

/**
 * Directory holding the static feed files, created on demand.
 *
 * @return string Absolute path without trailing slash, or '' on failure.
 */
function cloudpod_feed_dir() {
    $upload = wp_upload_dir( null, false );
    if ( ! empty( $upload['error'] ) ) {
        return '';
    }
    $dir = trailingslashit( $upload['basedir'] ) . 'cloudpod-feeds';
    if ( ! is_dir( $dir ) ) {
        if ( ! wp_mkdir_p( $dir ) ) {
            return '';
        }
        // The XML is public anyway, but keep directory listings off.
        @file_put_contents( $dir . '/index.html', '' );
    }
    return $dir;
}

/**
 * Path of the XML file for a cache key.
 *
 * @param string $key Cache key from cloudpod_feed_key_from_path().
 * @return string
 */
function cloudpod_feed_file( $key ) {
    $dir = cloudpod_feed_dir();
    return $dir ? $dir . '/' . $key . '.xml' : '';
}

/**
 * Shared secret for the loopback bypass header. Derived from the site's salts
 * so it is stable across both tasks and never stored.
 *
 * @return string
 */
function cloudpod_feed_bypass_token() {
    return hash_hmac( 'sha256', 'cloudpod-feed-regen', wp_salt( 'auth' ) );
}

/**
 * Feed paths to prerender: the bare podcast feed plus one per series term.
 *
 * @return string[] Paths beginning with '/'.
 */
function cloudpod_feed_paths() {
    $paths = array( '/feed/podcast/' );
    if ( taxonomy_exists( 'series' ) ) {
        $terms = // hide_empty must stay false: SSP's default series ('the-cloud-pod') has no
        // episodes assigned yet renders the full default feed, and the bare
        // /feed/podcast/ URL redirects there. Truly empty feeds are skipped at fetch time.
        get_terms( array( 'taxonomy' => 'series', 'hide_empty' => false, 'fields' => 'slugs' ) );
        if ( is_array( $terms ) ) {
            foreach ( $terms as $slug ) {
                if ( '' !== cloudpod_feed_key_from_path( '/feed/podcast/' . $slug . '/' ) ) {
                    $paths[] = '/feed/podcast/' . $slug . '/';
                }
            }
        }
    }
    return array_values( array_unique( $paths ) );
}

/* -------------------------------------------------------------------------
 * Regeneration
 * ---------------------------------------------------------------------- */

/**
 * Regenerate every feed file. Runs from WP-Cron, never inside a page request.
 *
 * @return array<string, string> key => 'ok' | error description.
 */
function cloudpod_regenerate_feeds() {
    $results = array();
    $token   = cloudpod_feed_bypass_token();
    $state   = get_option( 'cloudpod_feeds_last_regen', array() );
    $fails   = isset( $state['consecutive_failures'] ) ? (int) $state['consecutive_failures'] : 0;
    $any_ok  = false;
    $any_bad = false;

    foreach ( cloudpod_feed_paths() as $path ) {
        $key  = cloudpod_feed_key_from_path( $path );
        $file = cloudpod_feed_file( $key );
        if ( '' === $key || '' === $file ) {
            $results[ $path ] = 'no storage';
            continue;
        }

        $response = wp_remote_get(
            home_url( $path ),
            array(
                // Under the ALB's 60 s idle timeout so a doomed render fails cheaply.
                'timeout'     => 55,
                'redirection' => 3,
                'headers'     => array(
                    CLOUDPOD_FEED_BYPASS_HEADER => $token,
                    'Accept'                    => 'application/rss+xml, application/xml',
                ),
                'user-agent'  => 'CloudPod-Feed-Regen/1.0 (+' . home_url( '/' ) . ')',
            )
        );

        if ( is_wp_error( $response ) ) {
            $results[ $key ] = 'fetch error: ' . $response->get_error_message();
            $any_bad         = true;
            error_log( '[cloudpod-feeds] ' . $key . ': ' . $response->get_error_message() ); // phpcs:ignore
            continue;
        }

        // If the bypass header was lost somewhere, we just fetched our own static
        // file. Treat that as a failure rather than "unchanged", or the cache
        // would silently refresh from itself forever.
        if ( 'static' === wp_remote_retrieve_header( $response, 'x-cloudpod-feed' ) ) {
            $results[ $key ] = 'bypass header not honoured: received the static file';
            $any_bad         = true;
            error_log( '[cloudpod-feeds] ' . $key . ': ' . $results[ $key ] ); // phpcs:ignore
            continue;
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        if ( 200 === $code && cloudpod_feed_body_is_rss( $body ) && ! cloudpod_feed_body_looks_valid( $body ) ) {
            // A well-formed feed with no episodes (an unused series). Not an error;
            // drop any stale file so the request falls through to the dynamic render.
            if ( is_file( $file ) ) {
                @unlink( $file );
            }
            $results[ $key ] = 'skipped (feed has no items)';
            continue;
        }
        if ( 200 !== $code || ! cloudpod_feed_body_looks_valid( $body ) ) {
            $results[ $key ] = 'bad response: HTTP ' . $code . ', ' . strlen( (string) $body ) . ' bytes';
            $any_bad         = true;
            error_log( '[cloudpod-feeds] ' . $key . ': ' . $results[ $key ] ); // phpcs:ignore
            continue;
        }

        // Unchanged content (ignoring lastBuildDate) keeps its old mtime and ETag
        // so pollers keep getting 304s.
        if ( is_readable( $file ) && md5( cloudpod_feed_normalize_for_compare( (string) file_get_contents( $file ) ) ) === md5( cloudpod_feed_normalize_for_compare( $body ) ) ) {
            $results[ $key ] = 'ok (unchanged)';
            $any_ok          = true;
            continue;
        }

        $tmp = $file . '.' . uniqid( '', true ) . '.tmp';
        if ( false === file_put_contents( $tmp, $body, LOCK_EX ) || ! rename( $tmp, $file ) ) {
            @unlink( $tmp );
            $results[ $key ] = 'write failed';
            $any_bad         = true;
            error_log( '[cloudpod-feeds] ' . $key . ': write failed to ' . $file ); // phpcs:ignore
            continue;
        }
        $results[ $key ] = 'ok (' . strlen( $body ) . ' bytes)';
        $any_ok          = true;
    }

    // Consecutive-failure counter drives the back-off in cloudpod_schedule_feed_regen().
    $fails = ( $any_bad && ! $any_ok ) ? $fails + 1 : 0;
    update_option(
        'cloudpod_feeds_last_regen',
        array( 'time' => time(), 'results' => $results, 'consecutive_failures' => $fails ),
        false
    );
    return $results;
}

/** After this many all-failed runs, only the hourly cron retries (pollers stop scheduling). */
define( 'CLOUDPOD_FEED_MAX_FAILURES', 3 );

/**
 * Whether regeneration is currently backing off after repeated failures.
 *
 * @return bool
 */
function cloudpod_feed_backing_off() {
    $state = get_option( 'cloudpod_feeds_last_regen', array() );
    return isset( $state['consecutive_failures'] ) && (int) $state['consecutive_failures'] >= CLOUDPOD_FEED_MAX_FAILURES;
}
add_action( CLOUDPOD_FEED_CRON_HOOK, 'cloudpod_regenerate_feeds' );
add_action( CLOUDPOD_FEED_HOURLY_HOOK, 'cloudpod_regenerate_feeds' );

/**
 * Schedule one regeneration shortly, collapsing bursts of saves.
 */
function cloudpod_schedule_feed_regen() {
    if ( get_transient( 'cloudpod_feed_regen_pending' ) ) {
        return;
    }
    set_transient( 'cloudpod_feed_regen_pending', 1, CLOUDPOD_FEED_DEBOUNCE );
    if ( ! wp_next_scheduled( CLOUDPOD_FEED_CRON_HOOK ) ) {
        wp_schedule_single_event( time() + CLOUDPOD_FEED_DEBOUNCE, CLOUDPOD_FEED_CRON_HOOK );
    }
}

/**
 * A change to a PUBLISHED podcast post invalidates the feeds. Drafts and
 * pending posts are not in the feed, so their churn is ignored.
 */
function cloudpod_feed_on_post_change( $post_id ) {
    if ( 'podcast' === get_post_type( $post_id ) && 'publish' === get_post_status( $post_id ) ) {
        cloudpod_schedule_feed_regen();
    }
}

/**
 * Status transitions into or out of publish change the item list. This fires
 * on every save (including REST), so it replaces a save_post hook.
 */
function cloudpod_feed_on_status_change( $new_status, $old_status, $post ) {
    if ( $post instanceof WP_Post && 'podcast' === $post->post_type && ( 'publish' === $new_status || 'publish' === $old_status ) ) {
        cloudpod_schedule_feed_regen();
    }
}
add_action( 'transition_post_status', 'cloudpod_feed_on_status_change', 10, 3 );
add_action( 'before_delete_post', 'cloudpod_feed_on_post_change' );

/**
 * Meta writes that bypass save_post (SSP's Castos sync, REST meta-only updates).
 * Editor bookkeeping keys are excluded: the block editor heartbeat rewrites
 * _edit_lock every 15-60 s while an episode is open, which would otherwise keep
 * the cron re-rendering continuously.
 */
function cloudpod_feed_on_meta_change( $meta_id, $object_id, $meta_key ) {
    static $ignored = array( '_edit_lock', '_edit_last', '_pingme', '_encloseme', '_wp_old_slug', '_wp_old_date', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug' );
    if ( in_array( (string) $meta_key, $ignored, true ) || 0 === strpos( (string) $meta_key, '_oembed' ) || 0 === strpos( (string) $meta_key, 'rank_math' ) ) {
        return;
    }
    cloudpod_feed_on_post_change( $object_id );
}
add_action( 'added_post_meta', 'cloudpod_feed_on_meta_change', 10, 3 );
add_action( 'updated_post_meta', 'cloudpod_feed_on_meta_change', 10, 3 );
add_action( 'deleted_post_meta', 'cloudpod_feed_on_meta_change', 10, 3 );

/**
 * Series assignment changes which feed an episode appears in.
 */
function cloudpod_feed_on_terms_change( $object_id, $terms, $tt_ids, $taxonomy ) {
    if ( 'series' === $taxonomy ) {
        cloudpod_feed_on_post_change( $object_id );
    }
}
add_action( 'set_object_terms', 'cloudpod_feed_on_terms_change', 10, 4 );

/**
 * Hourly safety net so a missed hook can never leave the feed stale for long.
 */
function cloudpod_feed_ensure_hourly_cron() {
    if ( ! wp_next_scheduled( CLOUDPOD_FEED_HOURLY_HOOK ) ) {
        wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'hourly', CLOUDPOD_FEED_HOURLY_HOOK );
    }
}
add_action( 'init', 'cloudpod_feed_ensure_hourly_cron' );

/**
 * Manual trigger for admins: /wp-admin/?cloudpod_regen_feeds=1&_wpnonce=...
 * Four full renders take longer than the ALB's 60 s timeout, so this schedules
 * the cron event immediately and redirects back; the notice shows the last result.
 */
function cloudpod_feed_admin_trigger() {
    if ( ! isset( $_GET['cloudpod_regen_feeds'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    check_admin_referer( 'cloudpod_regen_feeds' );
    delete_transient( 'cloudpod_feed_regen_pending' );
    if ( ! wp_next_scheduled( CLOUDPOD_FEED_CRON_HOOK ) ) {
        wp_schedule_single_event( time() - 1, CLOUDPOD_FEED_CRON_HOOK );
    }
    spawn_cron();
    wp_safe_redirect( add_query_arg( 'cloudpod_feeds', 'scheduled', admin_url() ) );
    exit;
}
add_action( 'admin_init', 'cloudpod_feed_admin_trigger' );

/**
 * Admin notice: confirmation of scheduling plus the last regeneration result.
 */
function cloudpod_feed_admin_notice() {
    if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['cloudpod_feeds'] ) ) {
        return;
    }
    $state = get_option( 'cloudpod_feeds_last_regen', array() );
    $when  = isset( $state['time'] ) ? human_time_diff( (int) $state['time'] ) . ' ago' : 'never';
    echo '<div class="notice notice-info"><p><strong>Podcast feed regeneration scheduled</strong> (runs via WP-Cron within a minute or two). Last completed run: ' . esc_html( $when ) . '.</p>';
    if ( ! empty( $state['results'] ) && is_array( $state['results'] ) ) {
        echo '<ul>';
        foreach ( $state['results'] as $k => $v ) {
            echo '<li><code>' . esc_html( $k ) . '</code>: ' . esc_html( $v ) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}
add_action( 'admin_notices', 'cloudpod_feed_admin_notice' );

/**
 * Link in the admin bar so the trigger is discoverable.
 */
function cloudpod_feed_admin_bar( $bar ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $bar->add_node(
        array(
            'id'    => 'cloudpod-regen-feeds',
            'title' => 'Regenerate podcast feeds',
            'href'  => wp_nonce_url( admin_url( '?cloudpod_regen_feeds=1' ), 'cloudpod_regen_feeds' ),
        )
    );
}
add_action( 'admin_bar_menu', 'cloudpod_feed_admin_bar', 100 );

/* -------------------------------------------------------------------------
 * Serving
 * ---------------------------------------------------------------------- */

/**
 * Answer podcast feed requests from the static file, before the main query.
 */
function cloudpod_serve_static_feed() {
    if ( is_admin() || ! isset( $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'] ) ) {
        return;
    }
    if ( ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'HEAD' ), true ) ) {
        return;
    }
    $path = (string) wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    $key  = cloudpod_feed_key_from_path( $path );
    if ( '' === $key ) {
        return;
    }

    // The regeneration loopback must reach the real render.
    $header = 'HTTP_' . strtoupper( str_replace( '-', '_', CLOUDPOD_FEED_BYPASS_HEADER ) );
    if ( isset( $_SERVER[ $header ] ) && hash_equals( cloudpod_feed_bypass_token(), (string) $_SERVER[ $header ] ) ) {
        return;
    }

    $file = cloudpod_feed_file( $key );
    if ( '' === $file || ! is_readable( $file ) ) {
        return;
    }
    $mtime = (int) filemtime( $file );
    $size  = (int) filesize( $file );
    if ( $size < 1024 || ( time() - $mtime ) > CLOUDPOD_FEED_STALE_AFTER ) {
        // Stale: fall through to the dynamic render. Only re-schedule if
        // regeneration is not already failing repeatedly (the hourly cron
        // keeps retrying either way).
        if ( ! cloudpod_feed_backing_off() ) {
            cloudpod_schedule_feed_regen();
        }
        return;
    }

    $etag = '"' . md5( $mtime . ':' . $size ) . '"';
    header( 'X-CloudPod-Feed: static' );
    header( 'Content-Type: application/rss+xml; charset=UTF-8' );
    header( 'ETag: ' . $etag );
    header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $mtime ) . ' GMT' );
    // No Cache-Control here on purpose: Apache in this container appends its own
    // max-age to script-set values (the live feed already shows two), and a
    // third directive would only add ambiguity.
    header( 'Vary: Accept-Encoding' );

    // wp_magic_quotes() has already slashed $_SERVER by init, so the quoted ETag
    // arrives as \"...\". Core's own feed 304 logic unslashes it too (class-wp.php).
    $inm = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? wp_unslash( (string) $_SERVER['HTTP_IF_NONE_MATCH'] ) : '';
    $ims = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? wp_unslash( (string) $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : '';
    if ( cloudpod_feed_is_not_modified( $etag, $mtime, $inm, $ims ) ) {
        status_header( 304 );
        exit;
    }

    status_header( 200 );
    // PHP's zlib.output_compression does not strip a script-set Content-Length,
    // so only send it when PHP is not compressing (mod_deflate handles its own).
    if ( ! filter_var( ini_get( 'zlib.output_compression' ), FILTER_VALIDATE_BOOLEAN ) ) {
        header( 'Content-Length: ' . $size );
    }
    if ( 'HEAD' !== $_SERVER['REQUEST_METHOD'] ) {
        readfile( $file );
    }
    exit;
}
add_action( 'init', 'cloudpod_serve_static_feed', 0 );

/* -------------------------------------------------------------------------
 * robots.txt
 * ---------------------------------------------------------------------- */

/**
 * Keep general web crawlers off the RSS feeds. Googlebot alone fetched the
 * podcast feed ~100 times/hour (2026-09-16); it gets nothing from it. Podcast
 * apps and directories do not read robots.txt, so they are unaffected.
 */
function cloudpod_robots_disallow_feeds( $output ) {
    $rules = "Disallow: /feed/\nDisallow: /*/feed/\n";
    // Keep the rules inside the User-agent group: insert before the first
    // Sitemap: line (Rank Math appends one) rather than after it.
    $pos = stripos( $output, "\nSitemap:" );
    if ( false !== $pos ) {
        // Attach directly to the last rule: a blank line before the new rules
        // could be read as the end of the group.
        return rtrim( substr( $output, 0, $pos ) ) . "\n" . $rules . substr( $output, $pos );
    }
    return rtrim( $output ) . "\n" . $rules;
}
add_filter( 'robots_txt', 'cloudpod_robots_disallow_feeds', 20 );
