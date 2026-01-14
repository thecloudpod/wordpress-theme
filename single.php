<?php
/**
 * The template for displaying single posts
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

        <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>
            <header class="entry-header">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

                <div class="entry-meta">
                    <span class="posted-on">
                        <?php echo get_the_date(); ?>
                    </span>
                    <?php if ( get_the_author() ) : ?>
                        <span class="byline">
                            by <?php the_author(); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
            <?php endif; ?>

            <div class="single-post-layout">
                <div class="post-main-content">
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
                </div>

                <aside class="post-sidebar">
                    <div class="about-podcast-widget">
                        <h3>About The Cloud Pod</h3>
                        <div class="about-content">
                            <p>The Cloud Pod is your weekly source for AWS, Azure, Google Cloud, and Oracle Cloud news. We break down the latest cloud computing developments with expert analysis and industry insights.</p>
                            <p><strong>Perfect for:</strong></p>
                            <ul>
                                <li>Cloud engineers and architects</li>
                                <li>DevOps professionals</li>
                                <li>IT decision makers</li>
                                <li>Anyone working with cloud platforms</li>
                            </ul>
                            <p class="episode-count">Join <?php echo cloudpod_get_episode_count(); ?>+ episodes of cloud expertise</p>
                            <div class="about-actions">
                                <a href="<?php echo esc_url( home_url( '/subscribe' ) ); ?>" class="btn-primary btn-small">Subscribe</a>
                                <a href="<?php echo esc_url( home_url( '/about-us' ) ); ?>" class="btn-secondary btn-small">Learn More</a>
                            </div>
                        </div>
                    </div>
                </aside>
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
