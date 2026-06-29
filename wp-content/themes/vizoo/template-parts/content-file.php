<?php

/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package vizoo
 */

?>


<article id="post-<?php the_ID(); ?>" class="file-post-container">
    <header class="file-header">
        <?php
        $url = vizoo_file_direct_download(vizoo_mime_sniffing($post)) ? wp_get_attachment_url(get_the_ID()) : get_permalink();

if (is_category()) :
    the_title('<h2 class="entry-title file-title"><a href="' . esc_url($url) . '" rel="bookmark">', '</a></h2>');
elseif (!is_single()) :
    the_title('<h2 class="entry-title"><a href="' . esc_url($url) . '" rel="bookmark">', '</a></h2>');
else :
    the_title('<h2 class="entry-title">', '</h2>');
endif;

if (!is_category() && !is_single()) : ?>

            <div class="entry-meta">
                <?php vizoo_posted_on(); ?>
            </div><!-- .entry-meta -->

        <?php endif; ?>

        <?php if (is_single()) :
            echo '<a class="button-medium sw-download-button" href="' . esc_url(wp_get_attachment_url(get_the_ID())) . '">Download</a>';
        endif; ?>

    </header><!-- .entry-header -->

    <div>
        <?php
        if (is_category()) : ?>
            <p class="file-description">
            <?php else : ?>
            <p>
            <?php endif; ?>
            <?php echo get_the_content(); ?>
            </p>
    </div><!-- .entry-content -->
    <span class="post-separator"></span>
</article><!-- #post-<?php the_ID(); ?> -->