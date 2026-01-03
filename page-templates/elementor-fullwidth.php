<?php
/**
 * Template Name: Elementor Full Width
 * Template Post Type: page
 *
 * @package CloudPod
 */

get_header();
?>

<div class="elementor-page-content">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php
get_footer();
