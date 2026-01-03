<?php
/**
 * Template Name: Contact Us
 *
 * @package CloudPod
 */

get_header();
?>

<div class="page-hero">
    <div class="container">
        <h1>Contact Us</h1>
        <p class="lead">We'd love to hear from you!</p>
    </div>
</div>

<div class="container page-container">
    <div class="contact-content">
        <div class="contact-intro">
            <p>Have a question, feedback, or want to collaborate? Reach out to The Cloud Pod team. We're always excited to connect with our listeners and the cloud computing community.</p>
        </div>

        <div class="contact-grid">
            <div class="contact-methods">
                <h2>Get In Touch</h2>
                
                <div class="contact-method">
                    <h3>📧 Email</h3>
                    <p><a href="mailto:hello@thecloudpod.net">hello@thecloudpod.net</a></p>
                    <p class="contact-desc">For general inquiries, feedback, or collaboration opportunities</p>
                </div>

                <div class="contact-method">
                    <h3>💬 Slack</h3>
                    <p><a href="/slack">Join our Slack Community</a></p>
                    <p class="contact-desc">Connect with fellow listeners and chat with the hosts</p>
                </div>

                <div class="contact-method">
                    <h3>𝕏 Social Media</h3>
                    <p><a href="https://twitter.com/thecloudpod" target="_blank" rel="noopener">@thecloudpod</a></p>
                    <p class="contact-desc">Follow us for updates and behind-the-scenes content</p>
                </div>

                <div class="contact-method">
                    <h3>💼 LinkedIn</h3>
                    <p><a href="https://www.linkedin.com/company/the-cloud-pod" target="_blank" rel="noopener">The Cloud Pod</a></p>
                    <p class="contact-desc">Connect with us professionally</p>
                </div>
            </div>

            <div class="contact-form-section">
                <h2>Send Us a Message</h2>
                <p><em>Contact form integration placeholder</em></p>
                <p>Install a contact form plugin like Contact Form 7, WPForms, or Gravity Forms to enable the contact form here.</p>
                
                <div class="form-placeholder">
                    <p><strong>Typical form fields:</strong></p>
                    <ul>
                        <li>Name</li>
                        <li>Email</li>
                        <li>Subject</li>
                        <li>Message</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="contact-cta">
            <h2>Want to be a guest on the show?</h2>
            <p>We're always looking for interesting guests to join us on The Cloud Pod. If you have expertise in cloud computing, DevOps, or related fields, we'd love to hear from you!</p>
            <a href="mailto:guests@thecloudpod.net" class="btn-primary">Pitch Your Topic</a>
        </div>
    </div>
</div>

<?php
get_footer();
