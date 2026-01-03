<?php
/**
 * Template part for displaying podcast cards
 *
 * @package CloudPod
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'podcast-card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>">
            <?php the_post_thumbnail( 'cloudpod-thumbnail', array( 'class' => 'podcast-card-image' ) ); ?>
        </a>
    <?php else : ?>
        <div class="podcast-card-image"></div>
    <?php endif; ?>

    <div class="podcast-card-content">
        <h2 class="podcast-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

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

        <div class="podcast-excerpt">
            <?php the_excerpt(); ?>
        </div>

        <a href="<?php the_permalink(); ?>" class="btn-primary">
            <?php esc_html_e( 'Listen Now', 'cloudpod' ); ?>
        </a>
    </div>
</article>
