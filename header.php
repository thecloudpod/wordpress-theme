<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <!-- Resource Hints for Performance -->
    <link rel="preconnect" href="https://feeds.castos.com" crossorigin>
    <link rel="dns-prefetch" href="https://feeds.castos.com">
    <link rel="preconnect" href="https://cdn.thecloudpod.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.thecloudpod.net">
    
    <!-- Critical CSS for Above-the-Fold Content -->
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--cloud-blue:#0A7AFF;--cloud-dark:#1A1F36;--cloud-light:#FFFFFF;--shadow-sm:0 2px 8px rgba(10,122,255,.1)}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;font-size:18px;line-height:1.7;color:var(--cloud-dark);background:var(--cloud-light);-webkit-font-smoothing:antialiased}
        .site{display:flex;flex-direction:column;min-height:100vh}
        .site-header{background:var(--cloud-light);box-shadow:var(--shadow-sm);position:sticky;top:0;z-index:999}
        .site-header .container{display:flex;justify-content:space-between;align-items:center;padding:1.5rem;max-width:1200px;margin:0 auto}
        .site-content{flex:1}
        img{max-width:100%;height:auto}
        @media(max-width:768px){.site-header .container{flex-wrap:wrap;padding:1rem}}
    </style>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <header id="masthead" class="site-header">
        <div class="container">
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    ?>
                    <div>
                        <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
                        <?php
                        $description = get_bloginfo( 'description', 'display' );
                        if ( $description || is_customize_preview() ) :
                            ?>
                            <p class="site-description"><?php echo $description; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>
            </div>

            <nav id="site-navigation" class="main-navigation">
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'cloudpod' ); ?></span>
                    ☰
                </button>
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'container'      => false,
                    )
                );
                ?>
            </nav><!-- #site-navigation -->
            
            <div class="header-search">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input type="search" class="search-field" placeholder="Search episodes..." value="<?php echo get_search_query(); ?>" name="s" />
                    <button type="submit" class="search-submit" aria-label="<?php esc_attr_e( 'Search', 'cloudpod' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main id="primary" class="site-content">
