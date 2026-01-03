<?php
/**
 * The main template file
 *
 * @package CloudPod
 */

get_header();
?>

<?php if ( is_home() && is_front_page() ) : ?>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1><?php bloginfo( 'name' ); ?></h1>
                <p><?php bloginfo( 'description' ); ?></p>
                <div class="hero-cta">
                    <a href="#latest-episodes" class="btn-primary">🎧 Listen to Latest Episode</a>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'podcast' ) ); ?>" class="btn-secondary">Browse All Episodes</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo cloudpod_get_episode_count(); ?>+</div>
                    <div class="stat-label">Episodes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">Weekly</div>
                    <div class="stat-label">New Content</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4</div>
                    <div class="stat-label">Cloud Platforms</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">5+</div>
                    <div class="stat-label">Years Running</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Links -->
    <section class="platforms-section">
        <div class="container">
            <h2>Listen On Your Favorite Platform</h2>
            <div class="platform-links">
                <a href="https://podcasts.apple.com/us/podcast/the-cloud-pod/id1447083316" class="platform-link" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2.182c5.423 0 9.818 4.395 9.818 9.818 0 5.423-4.395 9.818-9.818 9.818-5.423 0-9.818-4.395-9.818-9.818 0-5.423 4.395-9.818 9.818-9.818zM12 5.455a1.636 1.636 0 100 3.273 1.636 1.636 0 000-3.273zm0 4.909c-1.804 0-3.273 1.469-3.273 3.273v4.909a.818.818 0 101.636 0v-4.909c0-.904.733-1.637 1.637-1.637.904 0 1.637.733 1.637 1.637v4.909a.818.818 0 101.636 0v-4.909c0-1.804-1.469-3.273-3.273-3.273z"/></svg>
                    Apple Podcasts
                </a>
                <a href="https://open.spotify.com/show/414Xd3q3oqRdSuMa61cUZg" class="platform-link" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                    Spotify
                </a>
                <a href="https://www.thecloudpod.net/feed/podcast/" class="platform-link" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.503 20.752c0 1.794-1.456 3.248-3.251 3.248-1.796 0-3.252-1.454-3.252-3.248 0-1.794 1.456-3.248 3.252-3.248 1.795.001 3.251 1.454 3.251 3.248zm-6.503-12.572v4.811c6.05.062 10.96 4.966 11.022 11.009h4.817c-.062-8.71-7.118-15.758-15.839-15.82zm0-3.368C10.58 4.868 19.199 13.467 19.25 24h4.75C23.942 9.895 14.106.06 0 0v4.812z"/></svg>
                    RSS Feed
                </a>
                <a href="https://www.youtube.com/@thecloudpod" class="platform-link" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    YouTube
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="episodes-section" id="latest-episodes">
    <div class="container">
        <?php if ( have_posts() ) : ?>

            <?php if ( ! is_front_page() ) : ?>
                <header class="page-header">
                    <?php
                    if ( is_home() && ! is_front_page() ) :
                        ?>
                        <h1 class="page-title"><?php single_post_title(); ?></h1>
                    <?php else : ?>
                        <h1 class="page-title"><?php esc_html_e( 'Latest Episodes', 'cloudpod' ); ?></h1>
                    <?php endif; ?>
                </header>
            <?php else : ?>
                <div class="section-header">
                    <h2><?php esc_html_e( 'Latest Episodes', 'cloudpod' ); ?></h2>
                    <p><?php esc_html_e( 'Stay up to date with the latest cloud computing news and insights', 'cloudpod' ); ?></p>
                </div>
            <?php endif; ?>

            <div class="podcast-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content', 'podcast-card' );
                endwhile;
                ?>
            </div>

            <?php
            the_posts_pagination(
                array(
                    'mid_size'  => 2,
                    'prev_text' => __( '&laquo; Previous', 'cloudpod' ),
                    'next_text' => __( 'Next &raquo;', 'cloudpod' ),
                )
            );

        else :
            get_template_part( 'template-parts/content', 'none' );
        endif;
        ?>
    </div>
</section>

<?php
get_footer();
