<?php

/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package vizoo
 */

get_header(); ?>
<div class="search-content">
    <div class="search-result-wrapper">
        <?php
        $s = get_search_query();
$args = [
    's' => $s,
];
// The Query
$the_query = new WP_Query($args);
if ($the_query->have_posts()) {
    _e("<h2>Search Results for: " . get_query_var('s') . "</h2>");
    while ($the_query->have_posts()) {
        $the_query->the_post();
        $category = get_the_category();
        $catLink = get_category_link($category[0]);
        $customType = get_post_custom_values("display_post_as");
        $type = get_post_type();
        $description = get_field("description");
        ?>
                <li class="search-result-item">
                    <span><?php echo $category[0]->cat_name ?>:</span>
                    <?php if ($customType[0] == "faq" || $type == "vizoo_videotutorial") { ?>
                        <a href="<?php echo $catLink ? $catLink : the_permalink() ?>?open=<?php echo the_ID() ?>"><?php the_title() ?></a>
                    <?php } else { ?>
                        <a href="<?php the_permalink() ?>"><?php the_title() ?></a>
                    <?php }
                    if ($description !== null): ?>
                        <span><?php echo $description ?></span>
                    <?php endif ?>
                </li>
            <?php
    }
} else {
    ?>
            <h2 style='font-weight:bold;color:#000'>Nothing Found</h2>
            <div class="alert alert-info">
                <p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>
            </div>
        <?php } ?>
    </div>
</div>

<?php
get_footer();
