<?php

/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package vizoo
 */

get_header(); ?>
<div class="landingpage-wrapper">
    <div id="super-categories" class="super-category-wrapper">
        <a href="<?php echo esc_url(get_category_link(get_cat_ID('Knowledge Base'))); ?>" class="super-category">
            <img class="category-icon" src="./app/themes/vizoo/img/book-solid.svg" alt="">
            <span class="category-name">
                <h2>Knowledge Base</h2>
            </span>
        </a>
        <a href="<?php echo esc_url(get_category_link(get_cat_ID('Downloads'))); ?>" class="super-category">
            <img class="category-icon" src="./app/themes/vizoo/img/download-solid.svg" alt="">
            <span class="category-name">
                <h2>Downloads</h2>
            </span>
        </a>
        <a href="<?php echo esc_url(get_category_link(get_cat_ID('News'))); ?>" class="super-category">
            <img class="category-icon" src="./app/themes/vizoo/img/newspaper-solid.svg" alt="">
            <span class="category-name">
                <h2>News</h2>
            </span>
        </a>
        <?php
        $userrole = get_user_meta(wp_get_current_user()->ID, 'vizoo_customerrole', true);
if ($userrole == "LicenseManager") :
    ?>
            <a href="<?php echo get_permalink(get_page_by_title('Licenses')); ?>" class="super-category">
                <img class="category-icon" src="./app/themes/vizoo/img/key-solid.svg" alt="">
                <span class="category-name">
                    <h2>Licenses</h2>
                </span>
            </a>
        <?php endif; ?>
    </div>
    <div class="latest-news-container">
        <h3>Latest News:</h3>
        <?php $the_query = new WP_Query([
        "category_name" => "Announcements",
        "post_status" => "publish",
        "posts_per_page" => 1,
    ]);

if ($the_query->have_posts()): ?>
            <span><?php while ($the_query->have_posts()) : $the_query->the_post();
                echo the_title() ?></span>
        <?php endwhile; ?>
    <?php endif ?>
    <a href="<?php echo get_permalink(the_post()) ?>" class="button-medium" target="_blank">Read article<i class="fa-solid fa-arrow-right" style="color: #ffffff;"></i></a>
    </div>
</div>
<?php get_footer() ?>
</div>