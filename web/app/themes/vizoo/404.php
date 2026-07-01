<?php

/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package vizoo
 */

get_header(); ?>

<div id="primary" class="content-area full-width">
    <main id="main" class="site-main">
        <article>
            <header>
                <h2 class="entry-header">Oops! That page can&rsquo;t be found.</h2>
            </header>

            <div class="entry-content">
                <p>
                    We're sorry, but it seems there is nothing here. You can search the site with the search bar above or go back to the <a href="<?php echo esc_url(home_url()); ?>">homepage</a>.
                </p>
                <hr>
            </div>
        </article>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
