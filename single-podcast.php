<?php
/**
 * The template for displaying single podcast episodes
 *
 * @package CloudPod
 */

get_header();
?>

<div class="container">
    <?php
    while ( have_posts() ) :
        the_post();
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-podcast' ); ?>>
            <header class="entry-header">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                
                <div class="podcast-meta">
                    <span class="episode-date">
                        <?php echo get_the_date(); ?>
                    </span>
                    <?php
                    $duration = cloudpod_get_duration();
                    if ( $duration ) :
                        ?>
                        <span class="episode-duration">
                            <?php echo esc_html( $duration ); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <?php
            $audio_url = cloudpod_get_audio_url();
            if ( $audio_url ) :
                ?>
                <div class="podcast-player">
                    <div class="player-header">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <img src="<?php the_post_thumbnail_url( 'medium' ); ?>" alt="<?php the_title_attribute(); ?>" class="player-artwork">
                        <?php endif; ?>
                        <div class="player-info">
                            <h3><?php the_title(); ?></h3>
                            <div class="podcast-meta">
                                <span><?php echo get_the_date(); ?></span>
                                <?php if ( $duration ) : ?>
                                    <span><?php echo esc_html( $duration ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <audio id="main-audio" src="<?php echo esc_url( $audio_url ); ?>" preload="metadata"></audio>

                    <div class="player-controls">
                        <div class="player-buttons">
                            <button class="player-btn" id="rewind-btn" aria-label="Rewind 10 seconds" title="↶ 10s">
                                <span>↶ 10s</span>
                            </button>
                            <button class="player-btn play-pause" id="play-pause-btn" aria-label="Play/Pause">
                                <span id="play-icon">▶</span>
                            </button>
                            <button class="player-btn" id="forward-btn" aria-label="Forward 30 seconds" title="30s ↷">
                                <span>30s ↷</span>
                            </button>
                            <div class="speed-control">
                                <label for="playback-speed">Speed:</label>
                                <select id="playback-speed" class="speed-selector">
                                    <option value="0.5">0.5x</option>
                                    <option value="0.75">0.75x</option>
                                    <option value="1" selected>1x</option>
                                    <option value="1.25">1.25x</option>
                                    <option value="1.5">1.5x</option>
                                    <option value="1.75">1.75x</option>
                                    <option value="2">2x</option>
                                </select>
                            </div>
                        </div>

                        <div class="progress-container">
                            <span class="time-display" id="current-time">0:00</span>
                            <div class="progress-bar" id="progress-bar">
                                <div class="progress-fill" id="progress-fill"></div>
                            </div>
                            <span class="time-display" id="total-duration">0:00</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $audio_url = cloudpod_get_audio_url();
            $transcript_url = cloudpod_get_transcript_url();
            
            if ( $audio_url || $transcript_url ) :
                ?>
                <div class="podcast-downloads">
                    <h3><?php esc_html_e( 'Download & Resources', 'cloudpod' ); ?></h3>
                    <div class="download-buttons">
                        <?php if ( $audio_url ) : ?>
                            <a href="<?php echo esc_url( $audio_url ); ?>" download class="download-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                <?php esc_html_e( 'Download MP3', 'cloudpod' ); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ( $transcript_url ) : ?>
                            <a href="<?php echo esc_url( $transcript_url ); ?>" target="_blank" class="download-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <?php esc_html_e( 'View Transcript', 'cloudpod' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                the_content();

                wp_link_pages(
                    array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'cloudpod' ),
                        'after'  => '</div>',
                    )
                );
                ?>
            </div>

            <footer class="entry-footer">
                <?php
                $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'cloudpod' ) );
                if ( $tags_list ) {
                    printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'cloudpod' ) . '</span>', $tags_list );
                }
                ?>
            </footer>
        </article>

        <?php
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;

    endwhile;
    ?>
</div>

<?php
get_footer();
