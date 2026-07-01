<?php

/**
 * Template part for displaying attachements
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package vizoo
 */

?>

<article id="post-<?php the_ID(); ?>" class="videotutorial-container">

    <?php $link = esc_url(get_permalink());
?>

    <?php if (!is_singular() && !is_front_page()) :

        $thumb_path_parts = pathinfo(array_values(get_children('post_parent=' . get_the_ID() . '&post_type=attachment&post_mime_type=image&numberposts=1'))[0]->guid);
        $thumb_dirname = $thumb_path_parts['dirname'];
        $thumb_filename = $thumb_path_parts['filename'];
        $thumb_extension = $thumb_path_parts['extension'];
        $thumb_file = $thumb_dirname . '/' . $thumb_filename . '-300x169.' . $thumb_extension;
        ?>

        <a class="thumbnail-link" href="<?php echo $link; ?>">
            <img class="video-thumbnail" src="<?php echo $thumb_file; ?>" alt="thumbnail" width="240" height="auto" />
        </a>



        <div class="content video-content">

        <?php endif; ?>

        <header class="entry-header">
            <?php
                if (!is_singular()) :
                    the_title('<h2 class="entry-title video-title"><a href="' . $link . '" rel="bookmark">', '</a></h2>');
                    ?>
                <div class="entry-meta">
                    <?php vizoo_posted_on(); ?>
                </div><!-- .entry-meta -->
            <?php endif; ?>
        </header><!-- .entry-header -->

        <?php if (is_singular()) : ?>

            <div class="entry-content">
                <?php
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
                'after' => '</div>',
            ]);
            ?>
            </div><!-- .entry-content -->

        <?php else : ?>

            <div class="entry-content">
                <p>
                    <?php
                $attachment_meta = wp_get_attachment(get_the_ID());
            echo wp_trim_words($attachment_meta['description']);
            ?>
                </p>
            </div><!-- .entry-content -->

        <?php endif;
if (!is_singular() && !is_front_page()) : ?>
        </div>
    <?php endif; ?>


    <footer class="entry-footer" style="clear: both;">
        <hr />
    </footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->