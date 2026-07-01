<?php

/**
 * The header for the Vizoo theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">.
 *
 * @link https://customers.vizoo3d.com
 *
 * @package vizoo
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div id="page" class="site">
        <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'vizoo'); ?></a>
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <a class="custom-logo-link" href="<?php echo esc_url(get_home_url()); ?>" rel="home" itemprop="url">
                    <svg class="custom-logo" alt="Vizoo" itemprop="logo" data-name="Layer 2" viewBox="0 0 96.000001 39" version="1.1" width="96" height="39" xmlns="http://www.w3.org/2000/svg">
                        <defs id="defs4">
                            <style id="style2">
                                .cls-1 {
                                    fill: #00bfbf
                                }
                            </style>
                        </defs>
                        <path class="cls-1" d="M92.675266.27318691H80.108061c-1.836271 0-3.324734 1.48874079-3.324734 3.32501179V35.402079c0 1.835993 1.488463 3.324734 3.324734 3.324734h12.567205C94.511537 38.726813 96 37.238072 96 35.402079V3.5981987c0-1.836271-1.488463-3.32501179-3.324734-3.32501179ZM91.217917 32.682398c0 .667002-.540603 1.207605-1.207605 1.207605h-7.237297c-.667003 0-1.207605-.540603-1.207605-1.207605V6.3176021c0-.6670025.540602-1.2076051 1.207605-1.2076051h7.237297c.667002 0 1.207605.5406026 1.207605 1.2076051z" id="path6" style="stroke-width:.0277802" />
                        <path class="cls-1" d="M69.250449.27318691H56.683243c-1.836271 0-3.324734 1.48874079-3.324734 3.32501179V35.402079c0 1.835993 1.488463 3.324734 3.324734 3.324734h12.567206c1.836271 0 3.324733-1.488741 3.324733-3.324734V3.5981987c0-1.836271-1.488462-3.32501179-3.324733-3.32501179zM67.793099 32.682398c0 .667002-.540602 1.207605-1.207605 1.207605h-7.237297c-.667002 0-1.207605-.540603-1.207605-1.207605V6.3176021c0-.6670025.540603-1.2076051 1.207605-1.2076051h7.237297c.667003 0 1.207605.5406026 1.207605 1.2076051z" id="path8" style="stroke-width:.0277802" />
                        <path class="cls-1" d="M22.263302.27582622h.766177c2.224361 0 4.030074 1.80599058 4.030074 4.03007318V38.724174h-.932304c-2.132685 0-3.864225-1.731539-3.864225-3.864225V.27582622z" id="path10" style="stroke-width:.0277802" />
                        <path class="cls-1" d="M30.880163 4.4172977v.6892267h13.098363L30.936279 34.61076c-.856186 1.936835.561993 4.114247 2.679678 4.114247h11.494334c2.226027 0 4.030629-1.804602 4.030629-4.030629v-.80757l-12.63999.0011L49.647908 4.1856108c.814516-1.840438-.533102-3.91061827-2.545777-3.91061827H35.022468c-2.287699 0-4.142305 1.85460597-4.142305 4.14230517z" id="path12" style="stroke-width:.0277802" />
                        <path class="cls-1" d="M18.50853 4.5340787 13.106393 35.507886c-.747565 4.286763-6.9011561 4.286763-7.648999 0L.05497909 4.5340787C-.33255465 2.311663 1.3778721.27704137 3.6339019.27704137h.7622886L9.2802268 28.912034l.00167.0092.00167-.0092L14.167319.27704137h.762289c2.256029 0 3.966456 2.03462163 3.578922 4.25703733z" id="path14" style="stroke-width:.0277802" />
                    </svg>
                </a>
            </div><!-- .site-branding -->
            <?php function get_attachment_url_by_slug($slug)
            {
                $args = [
                    'post_type' => 'attachment',
                    'name' => sanitize_title($slug),
                    'posts_per_page' => 1,
                    'post_status' => 'inherit',
                ];
                $_header = get_posts($args);
                $header = $_header ? array_pop($_header) : null;
                return $header ? wp_get_attachment_url($header->ID) : '';
            }
if (is_user_logged_in()) : ?>
                <nav id="site-navigation" class="main-navigation">
                    <ul class="menu">
                        <li class="your-info-container">
                            <span class="dropdown-head">
                                <span>Categories</span>
                                <img src=<?php echo get_attachment_url_by_slug("plus-solid") ?> alt="plus icon">
                            </span>
                            <ul class="dropdown-links">
                                <li>
                                    <a href="<?php echo esc_url(get_category_link(get_cat_ID('Knowledge Base'))); ?>" class="super-category">Knowledge Base</a>
                                </li>
                                <li>
                                    <a href="<?php echo esc_url(get_category_link(get_cat_ID('Downloads'))); ?>" class="super-category">Downloads</a>
                                </li>
                                <li>
                                    <a href="<?php echo esc_url(get_category_link(get_cat_ID('News'))); ?>" class="super-category">News</a>
                                </li>
                                <li>
                                    <?php
                        $userrole = get_user_meta(wp_get_current_user()->ID, 'vizoo_customerrole', true);
    if ($userrole == "LicenseManager") :
        ?>
                                        <a href="<?php echo get_permalink(get_page_by_title('Licenses')); ?>" class="super-category">Licenses</a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </li>

                        <li class="your-info-container">
                            <span class="dropdown-head">
                                <span>Profile</span>
                                <img src=<?php echo get_attachment_url_by_slug("plus-solid") ?> alt="plus icon">
                            </span>
                            <ul class="dropdown-links">
                                <li>
                                    <a href="<?php echo get_permalink(get_page_by_title('Profile')); ?>">Your Info</a>
                                </li>
                                <?php if (current_user_can("edit_pages")) { ?>
                                    <li>
                                        <a href="<?php echo admin_url(); ?>">Dashboard</a>
                                    </li>
                                <?php } ?>
                                <li>
                                    <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
                                </li>
                            </ul>
                        </li>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'menu-1',
                            'menu_id'        => 'primary-menu',
                        ]);
    ?>
                    </ul>
                </nav><!-- #site-navigation -->
            <?php else : ?>
                <nav id="site-navigation" class="main-navigation">
                    <div class="menu-navigation-menu-container">
                    </div>
                </nav><!-- #site-navigation -->
            <?php endif; ?>
            <button onclick="toggleDropdown()" class="toggle-dropdown-mobile-button">
                <i class="fa-solid fa-bars fa-2x toggle-dropdown-mobile-icon" aria-hidden="true"></i>
            </button>
        </header><!-- #masthead -->
        <div>
            <?php if (is_front_page()) : ?>
                <div id=" super-search" class="magazine">
                    <h1 class="super-category-name">Customer Portal</h1>
                    <span class="category-description">
                        <p>Resources for your Vizoo products.</p>
                    </span>
                    <?php echo do_shortcode('[wpdreams_ajaxsearchpro id=1]'); ?>
                </div>
                <?php else :
                    if (is_category()) :
                        ?>
                    <div id="super-search" class="magazine">
                        <h1 class="super-category-name"><?php $category = get_category($cat);
                        echo $category->cat_name; ?></h1>
                        <?php the_archive_description('<span class="category-description">', '</span>') ?>
                        <?php echo do_shortcode('[wpdreams_ajaxsearchpro id=1]'); ?>
                    </div>
                    <?php else :
                        if (is_search()) : ?>
                        <div id="super-search" class="magazine">
                            <h1 class="super-category-name">Search Results:</h1>
                            <?php the_archive_description('<span class="category-description">', '</span>') ?>
                            <?php echo do_shortcode('[wpdreams_ajaxsearchpro id=1]'); ?>
                        </div>
                    <?php else: ?>
                        <div id="super-search" class="magazine">
                            <?php the_title('<h1 class="super-category-name">', '</h1>'); ?>
                            <span class="category-description">
                                <p>
                                    <?php echo get_field("page-description"); ?>
                                </p>
                            </span>
                            <?php echo do_shortcode('[wpdreams_ajaxsearchpro id=1]'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="breadcrumb-container">
            <?php nav_breadcrumb() ?>
        </div>