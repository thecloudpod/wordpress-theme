<?php
/**
 * The Cloud Pod Theme Functions
 *
 * @package CloudPod
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme Setup
 */
function cloudpod_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title.
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 800, 450, true );

    // Add custom image sizes
    add_image_size( 'cloudpod-featured', 1200, 675, true );
    add_image_size( 'cloudpod-thumbnail', 400, 300, true );
    add_image_size( 'cloudpod-hero', 600, 600, true );

    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'cloudpod' ),
        'footer'  => __( 'Footer Menu', 'cloudpod' ),
    ) );

    // Switch default core markup to output valid HTML5.
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style',
    ) );

    // Add theme support for selective refresh for widgets.
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Add support for custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 60,
        'width'       => 240,
        'flex-width'  => true,
        'flex-height' => true,
    ) );

    // Add theme support for Elementor
    add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'cloudpod_setup' );

/**
 * Set the content width in pixels
 */
function cloudpod_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'cloudpod_content_width', 1200 );
}
add_action( 'after_setup_theme', 'cloudpod_content_width', 0 );

/**
 * Enqueue scripts and styles
 */
function cloudpod_scripts() {
    // Main stylesheet
    wp_enqueue_style( 'cloudpod-style', get_stylesheet_uri(), array(), '1.1.1' );

    // Custom JavaScript with defer loading
    wp_enqueue_script( 'cloudpod-audio-player', get_template_directory_uri() . '/js/audio-player.js', array(), '1.1.1', true );
    wp_script_add_data( 'cloudpod-audio-player', 'defer', true );
    
    wp_enqueue_script( 'cloudpod-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '1.1.1', true );
    wp_script_add_data( 'cloudpod-navigation', 'defer', true );
    
    // Homepage player
    if ( is_front_page() ) {
        wp_enqueue_script( 'cloudpod-homepage-player', get_template_directory_uri() . '/js/homepage-player.js', array(), '1.1.1', true );
        wp_script_add_data( 'cloudpod-homepage-player', 'defer', true );
    }

    // Localize script with episode data
    if ( is_singular( 'podcast' ) ) {
        global $post;
        $audio_file = get_post_meta( $post->ID, 'audio_file', true );
        $episode_data = array(
            'audioUrl' => $audio_file,
            'title' => get_the_title(),
            'artwork' => get_the_post_thumbnail_url( $post->ID, 'medium' ),
        );
        wp_localize_script( 'cloudpod-audio-player', 'episodeData', $episode_data );
    }

    // Comment reply script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'cloudpod_scripts' );

/**
 * Enable lazy loading for images
 */
function cloudpod_add_lazy_loading( $attr, $attachment, $size ) {
    $attr['loading'] = 'lazy';
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'cloudpod_add_lazy_loading', 10, 3 );

/**
 * Add WebP support
 */
function cloudpod_enable_webp_upload( $mimes ) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter( 'mime_types', 'cloudpod_enable_webp_upload' );

/**
 * Generate responsive image sizes
 */
function cloudpod_responsive_images() {
    add_image_size( 'cloudpod-mobile', 600, 400, true );
    add_image_size( 'cloudpod-tablet', 900, 600, true );
}
add_action( 'after_setup_theme', 'cloudpod_responsive_images' );

/**
 * Register widget areas
 */
function cloudpod_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'cloudpod' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Add widgets here to appear in your sidebar.', 'cloudpod' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 1', 'cloudpod' ),
        'id'            => 'footer-1',
        'description'   => __( 'Add widgets here to appear in your footer.', 'cloudpod' ),
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 2', 'cloudpod' ),
        'id'            => 'footer-2',
        'description'   => __( 'Add widgets here to appear in your footer.', 'cloudpod' ),
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 3', 'cloudpod' ),
        'id'            => 'footer-3',
        'description'   => __( 'Add widgets here to appear in your footer.', 'cloudpod' ),
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'cloudpod_widgets_init' );

/**
 * Seriously Simple Podcasting Integration
 */
function cloudpod_ssp_integration() {
    // Check if Seriously Simple Podcasting is active
    if ( ! class_exists( 'SeriouslySimplePodcasting' ) ) {
        return;
    }

    // Add custom meta box for transcript upload
    add_action( 'add_meta_boxes', 'cloudpod_add_transcript_meta_box' );
    add_action( 'save_post', 'cloudpod_save_transcript_meta_box' );
}
add_action( 'init', 'cloudpod_ssp_integration' );

/**
 * Add transcript meta box
 */
function cloudpod_add_transcript_meta_box() {
    add_meta_box(
        'cloudpod_transcript',
        __( 'Episode Transcript', 'cloudpod' ),
        'cloudpod_transcript_meta_box_callback',
        'podcast',
        'normal',
        'default'
    );
}

/**
 * Transcript meta box callback
 */
function cloudpod_transcript_meta_box_callback( $post ) {
    wp_nonce_field( 'cloudpod_transcript_nonce', 'cloudpod_transcript_nonce' );
    
    $transcript_url = get_post_meta( $post->ID, '_transcript_url', true );
    ?>
    <p>
        <label for="transcript_url"><?php _e( 'Transcript URL:', 'cloudpod' ); ?></label><br>
        <input type="text" id="transcript_url" name="transcript_url" value="<?php echo esc_attr( $transcript_url ); ?>" style="width: 100%;" />
    </p>
    <p class="description"><?php _e( 'Enter the URL to the episode transcript file (PDF, TXT, or DOC).', 'cloudpod' ); ?></p>
    <?php
}

/**
 * Save transcript meta box
 */
function cloudpod_save_transcript_meta_box( $post_id ) {
    if ( ! isset( $_POST['cloudpod_transcript_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['cloudpod_transcript_nonce'], 'cloudpod_transcript_nonce' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['transcript_url'] ) ) {
        update_post_meta( $post_id, '_transcript_url', sanitize_text_field( $_POST['transcript_url'] ) );
    }
}

/**
 * Get podcast audio URL from Seriously Simple Podcasting
 */
function cloudpod_get_audio_url( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    // Try SSP's meta field first
    $audio_file = get_post_meta( $post_id, 'audio_file', true );
    
    if ( empty( $audio_file ) ) {
        // Fallback to enclosure meta
        $audio_file = get_post_meta( $post_id, 'enclosure', true );
    }

    return $audio_file;
}

/**
 * Get episode duration
 */
function cloudpod_get_duration( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $duration = get_post_meta( $post_id, 'duration', true );
    
    if ( empty( $duration ) ) {
        $duration = get_post_meta( $post_id, 'itunes:duration', true );
    }

    return $duration;
}

/**
 * Get transcript URL
 * Supports both theme custom field and Seriously Simple Transcripts plugin
 */
function cloudpod_get_transcript_url( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    
    // Check for Seriously Simple Transcripts plugin field first
    $transcript_file = get_post_meta( $post_id, 'transcript_file', true );
    if ( ! empty( $transcript_file ) ) {
        return $transcript_file;
    }
    
    // Fallback to theme custom field
    $transcript_url = get_post_meta( $post_id, '_transcript_url', true );
    if ( ! empty( $transcript_url ) ) {
        return $transcript_url;
    }
    
    // Additional fallbacks
    $alt_transcript = get_post_meta( $post_id, 'episode_transcript', true );
    if ( ! empty( $alt_transcript ) ) {
        return $alt_transcript;
    }
    
    return '';
}

/**
 * Format duration from seconds to HH:MM:SS
 */
function cloudpod_format_duration( $seconds ) {
    if ( empty( $seconds ) ) {
        return '';
    }

    $hours = floor( $seconds / 3600 );
    $minutes = floor( ( $seconds % 3600 ) / 60 );
    $seconds = $seconds % 60;

    if ( $hours > 0 ) {
        return sprintf( '%02d:%02d:%02d', $hours, $minutes, $seconds );
    } else {
        return sprintf( '%02d:%02d', $minutes, $seconds );
    }
}

/**
 * Custom excerpt length
 */
function cloudpod_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'cloudpod_excerpt_length' );

/**
 * Custom excerpt more
 */
function cloudpod_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'cloudpod_excerpt_more' );

/**
 * Get total episode count
 */
function cloudpod_get_episode_count() {
    $count = wp_count_posts( 'podcast' );
    return $count->publish;
}

/**
 * Register Elementor locations
 */
function cloudpod_register_elementor_locations( $elementor_theme_manager ) {
    $elementor_theme_manager->register_location( 'header' );
    $elementor_theme_manager->register_location( 'footer' );
}
add_action( 'elementor/theme/register_locations', 'cloudpod_register_elementor_locations' );

/**
 * Rank Math SEO Compatibility
 * Ensures Rank Math can set custom meta for homepage and custom page templates
 */
function cloudpod_rankmath_support() {
    // Add Rank Math SEO support
    add_theme_support( 'rank-math-breadcrumbs' );
    
    // Ensure front-page.php is recognized as the homepage for SEO
    add_filter( 'rank_math/frontend/title', 'cloudpod_rankmath_homepage_title', 10, 1 );
    add_filter( 'rank_math/frontend/description', 'cloudpod_rankmath_homepage_description', 10, 1 );
}
add_action( 'after_setup_theme', 'cloudpod_rankmath_support' );

/**
 * Allow Rank Math to customize homepage title
 */
function cloudpod_rankmath_homepage_title( $title ) {
    if ( is_front_page() ) {
        $custom_title = get_post_meta( get_option( 'page_on_front' ), 'rank_math_title', true );
        if ( ! empty( $custom_title ) ) {
            return $custom_title;
        }
    }
    return $title;
}

/**
 * Allow Rank Math to customize homepage description
 */
function cloudpod_rankmath_homepage_description( $description ) {
    if ( is_front_page() ) {
        $custom_description = get_post_meta( get_option( 'page_on_front' ), 'rank_math_description', true );
        if ( ! empty( $custom_description ) ) {
            return $custom_description;
        }
    }
    return $description;
}

/**
 * Add Rank Math meta box to custom page templates
 */
function cloudpod_rankmath_post_types( $post_types ) {
    $post_types[] = 'podcast';
    return $post_types;
}
add_filter( 'rank_math/metabox/post_types', 'cloudpod_rankmath_post_types' );

/**
 * Additional Performance Optimizations
 */

// Remove WordPress emoji scripts
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Disable WordPress embeds
function cloudpod_disable_embeds() {
    wp_dequeue_script( 'wp-embed' );
}
add_action( 'wp_footer', 'cloudpod_disable_embeds' );

// Remove query strings from static resources
function cloudpod_remove_query_strings( $src ) {
    if ( strpos( $src, '?ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
add_filter( 'script_loader_src', 'cloudpod_remove_query_strings', 15, 1 );
add_filter( 'style_loader_src', 'cloudpod_remove_query_strings', 15, 1 );

// Preload key resources
function cloudpod_preload_resources() {
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">';
}
add_action( 'wp_head', 'cloudpod_preload_resources', 1 );

// Optimize image loading with fetchpriority
function cloudpod_add_fetchpriority( $attr, $attachment ) {
    // Add high priority to first image (typically hero)
    static $image_count = 0;
    $image_count++;
    
    if ( $image_count === 1 ) {
        $attr['fetchpriority'] = 'high';
        unset( $attr['loading'] ); // Remove lazy loading from first image
    }
    
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'cloudpod_add_fetchpriority', 20, 2 );

// Defer CSS loading for non-critical stylesheets
function cloudpod_defer_css( $html, $handle ) {
    // Don't defer main stylesheet
    if ( $handle === 'cloudpod-style' ) {
        return $html;
    }
    
    // Defer other stylesheets
    $html = str_replace( "rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html );
    return $html;
}
add_filter( 'style_loader_tag', 'cloudpod_defer_css', 10, 2 );

// Add width and height attributes to images
function cloudpod_add_image_dimensions( $image, $attachment_id, $size ) {
    if ( strpos( $image, ' width=' ) === false ) {
        $image_meta = wp_get_attachment_metadata( $attachment_id );
        if ( ! empty( $image_meta['width'] ) && ! empty( $image_meta['height'] ) ) {
            $image = str_replace( '<img ', '<img width="' . $image_meta['width'] . '" height="' . $image_meta['height'] . '" ', $image );
        }
    }
    return $image;
}
add_filter( 'post_thumbnail_html', 'cloudpod_add_image_dimensions', 10, 3 );
