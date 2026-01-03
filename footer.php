    </main><!-- #primary -->

    <footer id="colophon" class="site-footer">
        <div class="container">
            <div class="footer-content">
                <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar( 'footer-1' ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar( 'footer-2' ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar( 'footer-3' ); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'cloudpod' ); ?></p>
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'footer',
                        'menu_id'        => 'footer-menu',
                        'depth'          => 1,
                        'container'      => false,
                    )
                );
                ?>
            </div>
        </div>
    </footer>

    <!-- Miniplayer -->
    <div class="miniplayer" id="miniplayer">
        <img src="" alt="" class="miniplayer-artwork" id="mini-artwork">
        <div class="miniplayer-info">
            <h4 class="miniplayer-title" id="mini-title"></h4>
            <p class="miniplayer-meta" id="mini-meta"></p>
        </div>
        <div class="miniplayer-controls">
            <button class="player-btn play-pause" id="mini-play-pause" aria-label="Play/Pause">
                <span id="mini-play-icon">▶</span>
            </button>
            <div class="progress-container">
                <span class="time-display" id="mini-current-time">0:00</span>
                <div class="progress-bar" id="mini-progress-bar">
                    <div class="progress-fill" id="mini-progress-fill"></div>
                </div>
                <span class="time-display" id="mini-duration">0:00</span>
            </div>
        </div>
        <button class="miniplayer-close" id="mini-close" aria-label="Close player">✕</button>
    </div>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
