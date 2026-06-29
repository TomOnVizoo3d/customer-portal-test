<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package vizoo
 */

?>

</div><!-- #content-wrapper -->
</div><!-- #content -->

<footer id="colophon" class="site-footer">
    <div class="copyright">
        Copyright &copy; 2013-<?= date("Y") ?> Vizoo GmbH
    </div>
    <div class="imprint">
        <?php if (is_user_logged_in()) : ?>
            <a href="https://customers.vizoo3d.com/privacy-policy">Privacy Policy</a> | <a href="https://customers.vizoo3d.com/terms-of-service">Terms of Service</a>
        <?php else : ?>
            <a href="https://www.vizoo3d.com/legal-notice">Legal Notice &amp; Privacy Policy</a>
        <?php endif; ?>
    </div>
</footer><!-- #colophon -->
</div><!-- #page -->
<div class="video-modal-container" style="display: none;" tabindex="-1">
    <div class="video-modal-content">
        <iframe class="vizoo-yt-iframe" src="" frameborder="0" style="border: none;" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen"></iframe>
    </div>
</div>

<?php wp_footer(); ?>

</body>

</html>