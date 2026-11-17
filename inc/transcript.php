<?php
/**
 * Episode transcript support.
 *
 * Registers the `transcript_text` post meta on the podcast post type so it can
 * be written over the REST API (bolt-bot does this on publish), and renders the
 * stored text inline on the episode page so the transcript is indexed on this
 * domain rather than on the S3 bucket that hosts the .txt download.
 *
 * Contract for `transcript_text` (plain text, no HTML or Markdown):
 *   - one utterance per line, blank lines ignored
 *   - "[MM:SS] text" or "[MM:SS] Speaker Name: text"; minutes may exceed 59
 *   - lines without a leading timestamp are rendered as plain paragraphs
 *   - lines beginning with "#" (Markdown headings) are ignored
 *
 * Legacy episodes have no `transcript_text`; every function here returns
 * empty/echoes nothing in that case so the page falls back to the existing
 * S3 "Download Transcript" link only.
 *
 * @package CloudPod
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Meta key written by bolt-bot over REST. Not underscore-prefixed on purpose:
 *  protected meta cannot be written through the REST `meta` field. */
define( 'CLOUDPOD_TRANSCRIPT_META_KEY', 'transcript_text' );

/**
 * Register the transcript meta so the REST API accepts it.
 *
 * WordPress silently drops unregistered keys from the REST `meta` payload and
 * still returns 200, so this registration has to be live before bolt-bot
 * starts sending the field.
 */
function cloudpod_register_transcript_meta() {
    register_post_meta(
        'podcast',
        CLOUDPOD_TRANSCRIPT_META_KEY,
        array(
            'type'              => 'string',
            'description'       => 'Full episode transcript, plain text, one timestamped utterance per line.',
            'single'            => true,
            'default'           => '',
            'show_in_rest'      => true,
            'sanitize_callback' => 'cloudpod_sanitize_transcript_text',
            'auth_callback'     => 'cloudpod_transcript_meta_auth',
        )
    );
}
add_action( 'init', 'cloudpod_register_transcript_meta' );

/**
 * Only users who can edit the specific episode may write the transcript.
 *
 * @param bool   $allowed   Unused; WP default.
 * @param string $meta_key  Meta key being written.
 * @param int    $object_id Post ID.
 * @return bool
 */
function cloudpod_transcript_meta_auth( $allowed, $meta_key, $object_id ) {
    return current_user_can( 'edit_post', (int) $object_id );
}

/**
 * Normalise incoming transcript text: valid UTF-8, LF line endings, trimmed.
 *
 * Deliberately does NOT strip tags. PHP's strip_tags treats any "<" not
 * followed by whitespace as a tag opener and discards everything up to the
 * next ">", so a spoken "a<b", "<3" or "<VirtualHost>" would silently drop the
 * rest of a 100KB transcript. Safety comes from escaping at output time
 * (esc_html per line, wp_json_encode with JSON_HEX_TAG for the schema).
 *
 * @param mixed $value Raw meta value.
 * @return string
 */
function cloudpod_sanitize_transcript_text( $value ) {
    if ( ! is_string( $value ) ) {
        return '';
    }
    // Second arg = strip invalid bytes rather than returning '' for the whole string.
    $value = wp_check_invalid_utf8( $value, true );
    $value = str_replace( array( "\r\n", "\r" ), "\n", $value );
    return trim( $value );
}

/**
 * Get the stored transcript text for an episode, or '' if none.
 *
 * @param int|null $post_id Post ID, defaults to current post.
 * @return string
 */
function cloudpod_get_transcript_text( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    if ( ! $post_id ) {
        return '';
    }
    $text = get_post_meta( $post_id, CLOUDPOD_TRANSCRIPT_META_KEY, true );
    return is_string( $text ) ? trim( $text ) : '';
}

/**
 * Whether an episode has an inline transcript to render.
 *
 * @param int|null $post_id Post ID, defaults to current post.
 * @return bool
 */
function cloudpod_has_transcript_text( $post_id = null ) {
    return '' !== cloudpod_get_transcript_text( $post_id );
}

/**
 * Parse transcript text into structured lines.
 *
 * Pure function (no WordPress calls) so it can be unit tested in plain PHP.
 *
 * @param string $text Raw transcript text.
 * @return array<int, array{time:string, speaker:string, text:string}>
 */
function cloudpod_parse_transcript_lines( $text ) {
    $lines = array();
    if ( ! is_string( $text ) || '' === $text ) {
        return $lines;
    }

    // Optional "[M:SS]" / "[MM:SS]" / "[MMM:SS]" / "[H:MM:SS]" timestamp, then an
    // optional speaker label of 1-3 capitalised words, then the utterance.
    $pattern = '/^\[(\d{1,3}:\d{2}(?::\d{2})?)\]\s*(?:([A-Z][\w\'\-]*(?:\s[A-Z][\w\'\-]*){0,2}):\s+)?(.*)$/u';

    foreach ( explode( "\n", $text ) as $raw ) {
        $line = trim( $raw );
        if ( '' === $line || '#' === $line[0] ) {
            continue;
        }

        if ( preg_match( $pattern, $line, $m ) ) {
            $lines[] = array(
                'time'    => $m[1],
                'speaker' => isset( $m[2] ) ? $m[2] : '',
                'text'    => isset( $m[3] ) ? trim( $m[3] ) : '',
            );
        } else {
            $lines[] = array(
                'time'    => '',
                'speaker' => '',
                'text'    => $line,
            );
        }
    }

    return $lines;
}

/**
 * Render the collapsed inline transcript for the current episode.
 *
 * Echoes nothing when the episode has no `transcript_text` (legacy posts).
 * The markup is plain HTML in the page at load time, no JavaScript, so it is
 * indexed at full weight even though it is collapsed by default.
 *
 * @param int|null $post_id Post ID, defaults to current post.
 */
function cloudpod_render_transcript( $post_id = null ) {
    $text = cloudpod_get_transcript_text( $post_id );
    if ( '' === $text ) {
        return;
    }

    $lines = cloudpod_parse_transcript_lines( $text );
    if ( empty( $lines ) ) {
        return;
    }
    ?>
    <section class="episode-transcript" aria-labelledby="transcript-heading">
        <details class="transcript-details">
            <summary class="transcript-summary">
                <h2 id="transcript-heading" class="transcript-heading"><?php esc_html_e( 'Full Transcript', 'cloudpod' ); ?></h2>
                <span class="transcript-toggle-hint" aria-hidden="true"></span>
            </summary>
            <div id="transcript" class="transcript-body">
                <p class="transcript-disclaimer">
                    <?php esc_html_e( 'This transcript was generated automatically and has not been fully reviewed. Timestamps are approximate and wording may contain errors.', 'cloudpod' ); ?>
                </p>
                <?php
                // Built as strings rather than templated markup: a 17k-word
                // transcript would otherwise carry ~150 bytes of indentation per line.
                foreach ( $lines as $line ) {
                    $html = '<p class="transcript-line">';
                    if ( '' !== $line['time'] ) {
                        $html .= '<span class="transcript-time">' . esc_html( $line['time'] ) . '</span> ';
                    }
                    if ( '' !== $line['speaker'] ) {
                        $html .= '<span class="transcript-speaker">' . esc_html( $line['speaker'] ) . ':</span> ';
                    }
                    $html .= '<span class="transcript-text">' . esc_html( $line['text'] ) . '</span></p>';
                    echo $html . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- every dynamic part escaped above.
                }
                ?>
            </div>
        </details>
    </section>
    <script>
    // Fallback for browsers that do not auto-reveal <details> on fragment
    // navigation: open it when the page loads on, or navigates to, #transcript.
    (function () {
        function reveal() {
            if (location.hash !== '#transcript') { return; }
            var body = document.getElementById('transcript');
            var details = body && body.closest('details');
            if (details && !details.open) { details.open = true; }
        }
        reveal();
        window.addEventListener('hashchange', reveal);
    })();
    </script>
    <?php
}

/**
 * Convert an "HH:MM:SS" or "MM:SS" duration string to ISO 8601 (PT1H5M33S).
 *
 * Pure function. Returns '' if the input is not a recognised clock string.
 *
 * @param string $clock Duration as stored by Seriously Simple Podcasting.
 * @return string
 */
function cloudpod_duration_to_iso8601( $clock ) {
    if ( ! is_string( $clock ) || ! preg_match( '/^(?:(\d{1,2}):)?(\d{1,3}):(\d{2})$/', trim( $clock ), $m ) ) {
        return '';
    }
    $hours   = '' !== $m[1] ? (int) $m[1] : 0;
    $minutes = (int) $m[2];
    $seconds = (int) $m[3];

    if ( $minutes >= 60 ) {
        $hours  += intdiv( $minutes, 60 );
        $minutes = $minutes % 60;
    }

    $iso = 'PT';
    if ( $hours > 0 ) {
        $iso .= $hours . 'H';
    }
    if ( $minutes > 0 ) {
        $iso .= $minutes . 'M';
    }
    $iso .= $seconds . 'S';
    return $iso;
}

/**
 * Emit PodcastEpisode JSON-LD carrying the transcript on its AudioObject.
 *
 * Rank Math already emits Article/WebPage/Breadcrumb schema on these pages
 * (verified on the live site, 2026-09-16) but nothing audio-specific, so this
 * is a separate graph rather than an injection into Rank Math's. Only runs on
 * single podcast pages that actually have transcript text.
 */
function cloudpod_transcript_schema() {
    if ( ! is_singular( 'podcast' ) ) {
        return;
    }

    $post_id = get_queried_object_id();
    $text    = cloudpod_get_transcript_text( $post_id );
    if ( '' === $text ) {
        return;
    }

    $audio_url = cloudpod_get_audio_url( $post_id );
    if ( empty( $audio_url ) ) {
        return;
    }

    $audio = array(
        '@type'          => 'AudioObject',
        'contentUrl'     => $audio_url,
        'encodingFormat' => 'audio/mpeg',
        'name'           => get_the_title( $post_id ),
        'transcript'     => $text,
    );

    $duration = cloudpod_duration_to_iso8601( (string) cloudpod_get_duration( $post_id ) );
    if ( '' !== $duration ) {
        $audio['duration'] = $duration;
    }

    $episode = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'PodcastEpisode',
        '@id'             => get_permalink( $post_id ) . '#episode',
        'url'             => get_permalink( $post_id ),
        'name'            => get_the_title( $post_id ),
        'datePublished'   => get_the_date( 'c', $post_id ),
        'associatedMedia' => $audio,
        'partOfSeries'    => array(
            '@type' => 'PodcastSeries',
            'name'  => get_bloginfo( 'name' ),
            'url'   => home_url( '/' ),
        ),
    );

    $episode_number = get_post_meta( $post_id, 'itunes_episode_number', true );
    if ( '' !== $episode_number && null !== $episode_number ) {
        $episode['episodeNumber'] = (string) $episode_number;
    }

    // JSON_HEX_TAG/AMP encode < > & as \uXXXX so neither "</script>" nor an
    // "<!--<script" sequence in the transcript can affect the HTML tokenizer.
    echo '<script type="application/ld+json" class="cloudpod-episode-schema">' . wp_json_encode( $episode, JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n";
}
add_action( 'wp_head', 'cloudpod_transcript_schema', 20 );
