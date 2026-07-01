<?php

/**
 * Template part for displaying attachements
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package vizoo
 */

$video_link = get_field('video_url');
parse_str(parse_url($video_link, PHP_URL_QUERY), $query_entries);

$thumb_file = WP_CONTENT_URL . '/uploads/thumbnails/' . $query_entries['v'] . '.jpg';
$link = "javascript:vizooOpenModal('" . $query_entries['v'] . "');";

?>

<?php if ($_GET['open'] == get_the_ID()) : ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function() {
            jQuery('html, body').scrollTop(jQuery('#post-<?php the_ID(); ?>').offset().top);
            vizooOpenModal('<?php echo $query_entries['v']; ?>');
        });
        //]]>
    </script>
<?php endif; ?>

<article id="post-<?php the_ID(); ?>" class="videotutorial-container">

    <?php
    if (!is_category()) {
        $link = esc_url(get_category_link(get_the_category()[0]->term_id) . '?open=' . get_the_ID());
    }
?>

    <?php if (!is_singular()) : ?>

        <span class="thumbnail-link">
            <span class="thumbnail-overlay">
                <a class="play-button" href="<?php echo $link; ?>">
                </a>
            </span>
            <img class="video-thumbnail" src="<?php echo $thumb_file; ?>" alt="thumbnail" />
        </span>
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

            <div class="video-entry-content">
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

            <div class="video-entry-content">
                <p>
                    <?php
                $description = get_field('description');
            echo wp_trim_words($description);
            ?>
                </p>
            </div><!-- .entry-content -->

        <?php endif;
if (!is_singular()) : ?>
        </div>
    <?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->