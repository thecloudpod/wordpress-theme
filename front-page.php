<?php
/**
 * The front page template file
 * 
 * Two column layout: Recent Episodes | Blog Posts
 *
 * @package CloudPod
 */

get_header();
?>

<!-- Hero Section with Miniplayer -->
<section class="hero-section hero-compact">
    <div class="container">
        <div class="hero-content">
            <h1><?php bloginfo( 'name' ); ?></h1>
            <p>Your weekly deep dive into AWS, Azure, Google Cloud, and Oracle Cloud news. Expert analysis, industry insights, and the cloud computing stories that matter. Join thousands of cloud professionals staying ahead of the curve.</p>
        </div>
        
        <?php
        // Latest Episode Miniplayer in Hero
        $latest_episode = new WP_Query( array(
            'post_type'      => 'podcast',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
        
        if ( $latest_episode->have_posts() ) :
            while ( $latest_episode->have_posts() ) :
                $latest_episode->the_post();
                $audio_url = cloudpod_get_audio_url();
                if ( $audio_url ) :
                    ?>
                    <div class="hero-miniplayer">
                        <div class="hero-player-content">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <img src="<?php the_post_thumbnail_url( 'thumbnail' ); ?>" alt="<?php the_title_attribute(); ?>" class="hero-player-artwork">
                            <?php endif; ?>
                            
                            <div class="hero-player-info">
                                <div class="hero-player-label">Latest Episode</div>
                                <div class="hero-player-title"><?php the_title(); ?></div>
                            </div>
                            
                            <audio id="homepage-audio" src="<?php echo esc_url( $audio_url ); ?>" preload="metadata"></audio>
                            
                            <div class="hero-player-controls">
                                <button class="player-btn rewind-btn" id="homepage-rewind-btn" title="Rewind 10s">
                                    ↶ 10s
                                </button>
                                <button class="player-btn play-btn-large" id="homepage-play-btn">
                                    <span id="homepage-play-icon">▶</span>
                                </button>
                                <button class="player-btn forward-btn" id="homepage-forward-btn" title="Forward 30s">
                                    30s ↷
                                </button>
                                
                                <div class="hero-progress-container">
                                    <span class="time-display" id="homepage-current">0:00</span>
                                    <div class="progress-bar" id="homepage-progress">
                                        <div class="progress-fill" id="homepage-fill"></div>
                                    </div>
                                    <span class="time-display" id="homepage-duration">0:00</span>
                                </div>
                                
                                <div class="volume-control">
                                    <button class="player-btn volume-btn" id="homepage-volume-btn">🔊</button>
                                    <input type="range" id="homepage-volume" min="0" max="100" value="100" class="volume-slider">
                                </div>
                            </div>
                            
                            <div class="hero-player-actions">
                                <a href="<?php the_permalink(); ?>" class="hero-player-link">Show Notes</a>
                                
                                <?php
                                $transcript_url = cloudpod_get_transcript_url();
                                if ( $transcript_url ) :
                                    ?>
                                    <a href="<?php echo esc_url( $transcript_url ); ?>" target="_blank" class="hero-download-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        Transcript
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ( $audio_url ) : ?>
                                    <a href="<?php echo esc_url( $audio_url ); ?>" download class="hero-download-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        Download
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                endif;
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
    </div>
</section>

<!-- Two Column Layout -->
<div class="home-two-column">
    <div class="container">
        <div class="two-column-grid">
            
            <!-- Left Column: Recent Episodes -->
            <div class="column-episodes">
                <div class="section-header">
                    <h2><?php esc_html_e( 'Recent Episodes', 'cloudpod' ); ?></h2>
                </div>

                <?php
                // Query recent podcast episodes
                $episodes_query = new WP_Query( array(
                    'post_type'      => 'podcast',
                    'posts_per_page' => 10,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ) );

                if ( $episodes_query->have_posts() ) :
                    ?>
                    <div class="episode-list">
                        <?php
                        while ( $episodes_query->have_posts() ) :
                            $episodes_query->the_post();
                            ?>
                            <article class="episode-list-item">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="episode-thumbnail">
                                        <?php the_post_thumbnail( 'thumbnail' ); ?>
                                    </a>
                                <?php else : ?>
                                    <a href="<?php the_permalink(); ?>" class="episode-thumbnail episode-thumbnail-placeholder">
                                        <span>☁️</span>
                                    </a>
                                <?php endif; ?>

                                <div class="episode-list-content">
                                    <h3 class="episode-list-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    <div class="podcast-meta">
                                        <span><?php echo get_the_date(); ?></span>
                                        <?php
                                        $duration = cloudpod_get_duration();
                                        if ( $duration ) :
                                            ?>
                                            <span><?php echo esc_html( $duration ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php
                                    $audio_url = cloudpod_get_audio_url();
                                    if ( $audio_url ) :
                                        ?>
                                        <a href="<?php the_permalink(); ?>" class="episode-play-btn">
                                            ▶ Listen Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <div class="view-all-center">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'podcast' ) ); ?>" class="btn-primary">
                            View All Episodes
                        </a>
                    </div>
                    <?php
                    wp_reset_postdata();
                else :
                    ?>
                    <p><?php esc_html_e( 'No episodes found.', 'cloudpod' ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Right Column: Blog Posts -->
            <div class="column-blog">
                <div class="section-header">
                    <h2><?php esc_html_e( 'Latest News', 'cloudpod' ); ?></h2>
                </div>

                <?php
                // Query blog posts (regular posts)
                $blog_query = new WP_Query( array(
                    'post_type'      => 'post',
                    'posts_per_page' => 5,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ) );

                if ( $blog_query->have_posts() ) :
                    ?>
                    <div class="blog-list">
                        <?php
                        while ( $blog_query->have_posts() ) :
                            $blog_query->the_post();
                            ?>
                            <article class="blog-list-item">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="blog-thumbnail">
                                        <?php the_post_thumbnail( 'medium' ); ?>
                                    </a>
                                <?php endif; ?>

                                <div class="blog-list-content">
                                    <h3 class="blog-list-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    <div class="blog-meta">
                                        <span><?php echo get_the_date(); ?></span>
                                    </div>
                                    <div class="blog-excerpt">
                                        <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                                    </div>
                                    <a href="<?php the_permalink(); ?>" class="read-more">
                                        Read More →
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <div class="view-all-center">
                        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="btn-secondary">
                            View All Posts
                        </a>
                    </div>
                    <?php
                    wp_reset_postdata();
                else :
                    ?>
                    <p><?php esc_html_e( 'No posts found.', 'cloudpod' ); ?></p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- Platform Links Section -->
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
            <a href="https://feeds.castos.com/kqk1" class="platform-link" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.503 20.752c0 1.794-1.456 3.248-3.251 3.248-1.796 0-3.252-1.454-3.252-3.248 0-1.794 1.456-3.248 3.252-3.248 1.795.001 3.251 1.454 3.251 3.248zm-6.503-12.572v4.811c6.05.062 10.96 4.966 11.022 11.009h4.817c-.062-8.71-7.118-15.758-15.839-15.82zm0-3.368C10.58 4.868 19.199 13.467 19.25 24h4.75C23.942 9.895 14.106.06 0 0v4.812z"/></svg>
                RSS Feed
            </a>
            <a href="https://www.youtube.com/channel/UCySg2OyO6KNIqraYoP3CoCQ" class="platform-link" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                YouTube
            </a>
            <a href="https://www.linkedin.com/company/thecloudpod" class="platform-link" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                LinkedIn
            </a>
        </div>
    </div>
</section>

<?php 
// EmailOctopus popup form - renders as side popover
echo do_shortcode('[emailoctopus form_id="c8fa39e2-e877-11f0-8e2b-a394fe7edc92"]'); 
?>

<?php
get_footer();
