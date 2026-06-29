<?php

/**
 * Template part for displaying attachements
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package vizoo
 */

?>

<?php if (is_category()) : ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <?php if ($_GET['open'] == get_the_ID()) : ?>
            <script type="text/javascript">
                //<![CDATA[
                jQuery(document).ready(function() {
                    jQuery('html, body').scrollTop(jQuery('#post-<?php the_ID(); ?>').offset().top);
                    jQuery('#post-<?php the_ID(); ?>').find('.sp-wrap').addClass('flash')
                });
                //]]>
            </script>
            <div class="sp-wrap sp-wrap-default">
                <div class="sp-head unfolded" title="Collapse">
                    <?php the_title();
            if (current_user_can('edit_posts')) {
                echo ' ';
                edit_post_link(
                    sprintf(
                        wp_kses(
                            /* translators: %s: Name of current post. Only visible to screen readers */
                            __('<span class="screen-reader-text">%s</span>', 'vizoo'),
                            [
                                'span' => [
                                    'class' => [],
                                ],
                            ]
                        ),
                        get_the_title()
                    ),
                    '<span class="faq-edit-link">',
                    '</span>'
                );
            }
            ?>
                </div><!-- .entry-header -->

                <div class="sp-body">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php else : ?>
            <div class="sp-wrap sp-wrap-default">
                <div class="sp-head" title="Expand">
                    <?php the_title();
            if (current_user_can('edit_posts')) {
                echo ' ';
                edit_post_link(
                    sprintf(
                        wp_kses(
                            /* translators: %s: Name of current post. Only visible to screen readers */
                            __('<span class="screen-reader-text">%s</span>', 'vizoo'),
                            [
                                'span' => [
                                    'class' => [],
                                ],
                            ]
                        ),
                        get_the_title()
                    ),
                    '<span class="faq-edit-link">',
                    '</span>'
                );
            }
            ?>
                </div><!-- .entry-header -->

                <div class="sp-body folded" style="display: none;">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php endif; ?>
    </article><!-- #post-<?php the_ID(); ?> -->

<?php else :
    include 'content.php';
endif;
?>