<?php
/**
 * Template Name: TCP Talks
 *
 * @package CloudPod
 */

get_header();
?>

<div class="page-hero">
    <div class="container">
        <h1>TCP Talks</h1>
        <p class="lead">In-depth conversations with cloud computing leaders and innovators</p>
    </div>
</div>

<div class="container page-container">
    <div class="tcp-talks-content">
        <section class="talks-intro">
            <h2>About TCP Talks</h2>
            <p>TCP Talks is our special series featuring extended interviews with cloud computing experts, technology leaders, and industry innovators. These deep-dive conversations go beyond the headlines to explore the people, ideas, and technologies shaping the future of cloud computing.</p>
        </section>

        <section class="talks-episodes">
            <h2>Featured Interviews</h2>
            <?php
            // Query for TCP Talks category or custom post type
            $talks_query = new WP_Query( array(
                'post_type'      => 'podcast',
                'posts_per_page' => 12,
                'category_name'  => 'tcp-talks', // Adjust based on your taxonomy
                'orderby'        => 'date',
                'order'          => 'DESC',
            ) );

            if ( $talks_query->have_posts() ) :
                ?>
                <div class="podcast-grid">
                    <?php
                    while ( $talks_query->have_posts() ) :
                        $talks_query->the_post();
                        get_template_part( 'template-parts/content', 'podcast-card' );
                    endwhile;
                    ?>
                </div>
                <?php
                wp_reset_postdata();
            else :
                ?>
                <p><em>TCP Talks episodes coming soon! Check back for in-depth interviews with cloud computing leaders.</em></p>
                <p>In the meantime, explore our regular podcast episodes covering the latest cloud news and analysis.</p>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'podcast' ) ); ?>" class="btn-primary">Browse All Episodes</a>
            <?php endif; ?>
        </section>

        <section class="guest-suggestions">
            <h2>Suggest a Guest</h2>
            <p>Know someone who would be great for TCP Talks? We're always looking for interesting guests who can share valuable insights about cloud computing, DevOps, software architecture, and technology leadership.</p>
            <div class="guest-criteria">
                <h3>Ideal Guests Include:</h3>
                <ul>
                    <li>Cloud architects and engineers with unique perspectives</li>
                    <li>Technology leaders driving innovation</li>
                    <li>Authors and educators in the cloud space</li>
                    <li>Founders building cloud-native products</li>
                    <li>Researchers exploring emerging technologies</li>
                </ul>
            </div>
            <a href="/contact" class="btn-primary">Submit a Guest Suggestion</a>
        </section>

        <section class="be-a-guest">
            <div class="guest-cta-box">
                <h2>Want to be a guest on TCP Talks?</h2>
                <p>If you have an interesting story, unique expertise, or valuable insights to share with the cloud computing community, we'd love to hear from you.</p>
                <a href="mailto:guests@thecloudpod.net" class="btn-secondary">Pitch Your Story</a>
            </div>
        </section>
    </div>
</div>

<?php
get_footer();
