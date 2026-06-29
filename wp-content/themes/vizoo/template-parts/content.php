<?php

/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package vizoo
 */

?>

<article id="post-<?php the_ID(); ?>" class="<?php echo get_field("post-css-class") ?>">
    <header class="entry-header">
        <?php
        if (is_singular()) :
            the_title('<h1 class="entry-title">', '</h1>');
        else :
            if (get_field('display_post_as') !== 'faq') :
                the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
            else :
                the_title('<h2 class="entry-title"><a href="' . esc_url(get_category_link(get_the_category()[0]->term_id)) . '?open=' . get_the_ID() . '" rel="bookmark">', '</a></h2>');
            endif;
        endif;

if ('post' === get_post_type() && is_user_logged_in()) : ?>
            <div class="entry-meta">
                <?php vizoo_posted_on(); ?>
            </div><!-- .entry-meta -->
        <?php
endif; ?>
    </header><!-- .entry-header -->

    <div class="entry-content">
        <div class="entry-content-details">
            <?php
    if (is_single() || !is_main_query()) {
        the_content(sprintf(
            wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
                __('Read more<span class="screen-reader-text"> "%s"</span>', 'vizoo'),
                [
                    'span' => [
                        'class' => [],
                    ],
                ]
            ),
            get_the_title()
        ));

        wp_link_pages([
            'before' => '<div class="page-links">' . esc_html__('Pages:', 'vizoo'),
            'after'  => '</div>',
        ]);
    } else {
        the_excerpt();
    }
?>
        </div>
    </div><!-- .entry-content -->
    <footer class="entry-footer">
        <?php if (is_single()) :
            edit_post_link(
                sprintf(
                    wp_kses(
                        /* translators: %s: Name of current post. Only visible to screen readers */
                        __('Edit <span class="screen-reader-text">%s</span>', 'vizoo'),
                        [
                            'span' => [
                                'class' => [],
                            ],
                        ]
                    ),
                    get_the_title()
                ),
                '<span class="edit-link">',
                '</span>'
            );
        else : ?>
            <span class="post-separator"></span>
        <?php endif; ?>
    </footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->