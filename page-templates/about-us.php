<?php
/**
 * Template Name: About Us
 *
 * @package CloudPod
 */

get_header();
?>

<div class="page-hero">
    <div class="container">
        <h1>About The Cloud Pod</h1>
        <p class="lead">Your trusted source for cloud computing news and insights since 2019</p>
    </div>
</div>

<div class="container page-container">
    <div class="about-content">
        <section class="about-section">
            <h2>Who We Are</h2>
            <p>The Cloud Pod is a weekly podcast dedicated to bringing you the latest news, analysis, and insights from the world of cloud computing. We cover AWS, Microsoft Azure, Google Cloud Platform, and Oracle Cloud, providing expert commentary on the services, features, and trends shaping the industry.</p>
            <p>With over 335 episodes and counting, we've built a community of cloud professionals, enthusiasts, and decision-makers who rely on us to stay informed about the rapidly evolving cloud landscape.</p>
        </section>

        <section class="about-section">
            <h2>What We Cover</h2>
            <div class="coverage-grid">
                <div class="coverage-item">
                    <h3>☁️ Multi-Cloud News</h3>
                    <p>Weekly updates from AWS, Azure, GCP, and Oracle Cloud</p>
                </div>
                <div class="coverage-item">
                    <h3>🔒 Security & Compliance</h3>
                    <p>Cloud security best practices and compliance updates</p>
                </div>
                <div class="coverage-item">
                    <h3>💡 Expert Analysis</h3>
                    <p>In-depth discussions on cloud architecture and strategy</p>
                </div>
                <div class="coverage-item">
                    <h3>🚀 Industry Trends</h3>
                    <p>Emerging technologies and market insights</p>
                </div>
            </div>
        </section>

        <section class="about-section">
            <h2>Meet The Hosts</h2>
            <div class="hosts-grid">
                <div class="host-card">
                    <h3>Justin Brodley</h3>
                    <p>Co-host & Cloud Architect</p>
                </div>
                <div class="host-card">
                    <h3>Jonathan Baker</h3>
                    <p>Co-host & Cloud Engineer</p>
                </div>
                <div class="host-card">
                    <h3>Ryan Lucas</h3>
                    <p>Co-host & Cloud Specialist</p>
                </div>
                <div class="host-card">
                    <h3>Matthew Kohn</h3>
                    <p>Co-host & Cloud Expert</p>
                </div>
            </div>
        </section>

        <section class="about-section">
            <h2>Our Mission</h2>
            <p>We're committed to making cloud computing news accessible, understandable, and actionable for professionals at all levels. Whether you're a seasoned cloud architect or just starting your cloud journey, The Cloud Pod provides valuable insights to help you succeed.</p>
        </section>

        <section class="about-section cta-section">
            <h2>Join Our Community</h2>
            <p>Subscribe to The Cloud Pod and never miss an episode. Available on all major podcast platforms.</p>
            <div class="cta-buttons">
                <a href="<?php echo home_url('/subscribe'); ?>" class="btn-primary">Subscribe Now</a>
                <a href="<?php echo home_url('/contact'); ?>" class="btn-secondary">Get In Touch</a>
            </div>
        </section>
    </div>
</div>

<?php
get_footer();
