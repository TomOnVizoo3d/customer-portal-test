<?php


/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package vizoo
 */

get_header(); ?>

<div class="site-content">



    <div class="category-wrapper">

        <div class="category-overview">
            <?php
            /* Display sub-categories */
            $sub_cats_displayed = false;

if (is_category()) {
    $this_category = get_category($cat)->cat_ID;
    $category_childs = get_categories('orderby=name&parent=' . $this_category);
    if (!empty($category_childs)) { ?>
                    <div class="category-link-container">
                        <?php foreach ($category_childs as $child) :
                            $article_count = get_post_count($child->slug, false);
                            ?>
                            <a class="category-link" href="<?php echo get_category_link($child->term_id); ?>"><img class="category-icon" src="<?php echo get_field("category-icon", $child); ?>" alt=""><span class="category-name"><?php echo $child->cat_name; ?></span></a>

                        <?php $sub_cats_displayed = true;
                        endforeach; ?>
                    </div>

            <?php }
    } ?>
        </div>

        <?php
        if (have_posts()) : ?>
            <div class="category-content">
            <?php
    if ($sub_cats_displayed) {
        echo '<br style="clear: both;" /><hr style="margin-top: 1em; margin-bottom: 2em;" />';
    }

            /* Start the Loop */
            while (have_posts()) :
                the_post();

                if (get_post_type() === 'vizoo_videotutorial') {
                    get_template_part('template-parts/content', 'videotutorial');
                    continue;
                }

                /*
         * Include the Post-Format-specific template for the content.
         * If you want to override this in a child theme, then include a file
         * called content-___.php (where ___ is the Post Format name) and that will be used instead.
         */

                get_template_part('template-parts/content', get_field('display_post_as'));


            endwhile;
        else :

            if (!$sub_cats_displayed) {
                get_template_part('template-parts/content', 'none');
            }

        endif;
?>
            </div>



    </div>
</div>
<?php get_footer() ?>
</div>