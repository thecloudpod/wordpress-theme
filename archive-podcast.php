<?php
/**
 * The template for displaying podcast archives
 *
 * @package CloudPod
 */

get_header();
?>

<div class="container">
    <?php if ( have_posts() ) : ?>

        <header class="page-header">
            <h1 class="page-title"><?php esc_html_e( 'All Episodes', 'cloudpod' ); ?></h1>
            <?php
            $description = get_the_archive_description();
            if ( $description ) :
                ?>
                <div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
            <?php endif; ?>
        </header>

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

<?php
get_footer();
