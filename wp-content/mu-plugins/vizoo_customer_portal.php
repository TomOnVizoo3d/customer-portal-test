<?php

/**
 * Plugin Name: Vizoo Customer Portal Functions
 * Description: A plugin that contains several functions for the Vizoo Customer Portal.
 * Version: 0.1
 * Author: Vizoo3D
 *
 *
 * Content:
 *  1. Custom trim excerpt
 *  2. Customize login screen
 *  3. Manipulate the wordpress query (Enable attachments to be shown, sort manually where applicable and hide posts of child categories
 *  4. Breadcrumb navigation
 *  5. Get details of an attachment by the attachment id
 *  6. Get the amount of subcategories of a category
 *  7. Get the amount of posts of a category (inclusive those in subcategories)
 *  8. Get the MIME icon for a specific MIME type
 * 11. Set up the mail configuration
 * 12. Disable the admin dashboard for non-admins, others get redirected to home
 * 13. Disable emoji script and stylesheet as we don't use them
 * 14. Show attachment ids and order priority in the media library
 * 15. Send info to press members if a new post is published
 * 16. Add functionality to the User Access Manager plugin
 * 17. Endpoint for xTex update checks
 */

/** 1. Custom trim excerpt **/

/**
 * Customize the excerpts wordpress generates.
 *
 * Set up our own excerpt creation function. It enables some html markup (p-, string- and br-tags)
 * and sets the length of excerpts to be 80 words. For pages in search results the length will be
 * set to 40 words.
 *
 * @param    string               $text               The excerpt that may be generated before our function was called.
 * @return   string                                   The generated excerpt.
 */
function vizoo_custom_trim_excerpt($text)
{
    global $post;
    if ('' == $text) {
        $has_readmore = false;
        if (strpos($post->post_content, '<!--more-->') !== false) {
            $has_readmore = true;
        }

        // get the content and apply filters
        $text = get_the_content('');
        $text = apply_filters('the_content', $text);

        // fix html CDATA end and remove javascript
        $text = str_replace('\]\]\>', ']]&gt;', $text);
        $text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);

        // remove html and php tags except the allowed ones
        $text = strip_tags($text, '<a><img><p><strong><br>');

        if ($has_readmore) {
            // post contains a readmore tag
            $text .= apply_filters('excerpt_more', '');
        } else {
            // set the length of the excerpt
            $excerpt_length = 80;

            if (get_post_type() == 'page') {
                $excerpt_length = 40;
            }

            // convert the string to an array of words, limit the length to the length of the excerpt
            // the last element in the array then contains the rest of the string (see http://php.net/manual/en/function.explode.php)
            $words = explode(' ', $text, $excerpt_length + 1);

            // if there are too many words..
            if (count($words) > $excerpt_length) {
                // ..remove the last element (rest of the string - as mentioned above)
                array_pop($words);

                // ..merge the array together and add the indication that there's more
                $text = implode(' ', $words);
                $text .= apply_filters('excerpt_more', ' [&hellip;]');
            }
        }
    }

    // return the result
    return $text;
}

// remove the default excerpt function and add ours
remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'vizoo_custom_trim_excerpt');

/** 2. Customize login screen **/

/**
 * Load the customized login screen stylesheet.
 *
 * Prints the stylesheet for the custom login screen in the header of the login page.
 *
 */
function vizoo_load_custom_login()
{
    echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/login/custom-login-styles.css" />';
}

add_action('login_head', 'vizoo_load_custom_login');

/**
 * Let the logo redirect to the website.
 *
 * Change the link on the logo so it redirects to the website itself instead of linking
 * to WordPress. Since we need a login to access the site this is kind of pointless, but
 * it removes the link to WordPress.
 *
 * @return   string                                   The url to the blog.
 */
function vizoo_custom_login_logo_url()
{
    return get_bloginfo('url');
}

add_filter('login_headerurl', 'vizoo_custom_login_logo_url');

/**
 * Set a custom hover title for the logo.
 *
 * Change the title displayed if the user hovers the logo. So instead of WordPress we now
 * see a beautiful 'Vizoo Customer Support'.
 *
 * @return   string                                   The customized hover title.
 */
function vizoo_custom_login_logo_url_title()
{
    return 'Customer Portal';
}

add_filter('login_headertitle', 'vizoo_custom_login_logo_url_title');

function vizoo_custom_login_message($msg)
{
    return '<span id="customer-portal-title">Customer Portal</span>' . $msg;
}

add_filter('login_message', 'vizoo_custom_login_message');

/**
 * Disable the login shake effect.
 *
 * By default WordPress has a wanky shake effect if the login attempt of the user failed.
 * This function removes it.
 *
 */
function vizoo_custom_login_footer()
{
    remove_action('login_footer', 'wp_shake_js', 12);
}

add_action('login_footer', 'vizoo_custom_login_footer');

/**
 * Display 'Incorrect login details' instead of 'User doesn't exist' or 'Incorrect password'.
 *
 * Return 'Incorrect login details' no matter what was displayed before. Saying that a user
 * doesn't exist or saying it's an incorrect password is a security and privacy issue, as you can
 * gather information who is registered.
 *
 * @return   string                                   The secure error message.
 */
function vizoo_login_error_override()
{
    return 'Incorrect login details.';
}

add_filter('login_errors', 'vizoo_login_error_override');

/**
 * Correct the privacy policy link.
 *
 * Changes the privacy policy link on the login page to the publicly accessible site.
 *
 */

function vizoo_public_privacy_policy($link, $url)
{
    if ($GLOBALS['pagenow'] === 'wp-login.php') {
        return '<a class="privacy-policy-link" href="https://www.vizoo3d.com/legal-notice">Privacy Policy</a>';
    }
    return $link;
}

add_filter('the_privacy_policy_link', 'vizoo_public_privacy_policy', 10, 2);

/** 3. Manipulate the wordpress query (Enable attachments to be shown, sort manually where applicable and hide posts of child categories **/

/**
 * Manipulate the wordpress query.
 *
 * Change all incoming queries. Enable attachments to show in category archive pages and the home
 * feed, sort categories marked as 'sort manually' manually, hide posts of child categories and
 * hide the welcome and profile page from the search results.
 *
 * @param    WP_Query             $query              The current query.
 * @return   WP_Query                                 The modified query.
 */
function vizoo_manipulate_query($query)
{
    if (is_admin() || $query->is_page() || $query->is_preview()) {
        return $query;
    }
    $post_type = $query->get('post_type');
    if (is_string($post_type) && !empty($post_type) && $post_type !== 'post') {
        return $query;
    }
    if (is_array($post_type) && !in_array('post', $post_type)) {
        return $query;
    }

    $query->set('post_type', array('post', 'attachment', 'vizoo_videotutorial'));
    $query->set('post_status', array('inherit', 'publish'));

    if (!$query->is_main_query()) {
        return $query;
    }

    if ($query->is_home()) {
        $query->set('category__in', '10');
        return $query;
    }

    if ($query->is_category()) {
        // only show results that are in a category (so we exclude thumbnails and other clutter)
        $categories_ids = get_terms(array('taxonomy' => 'category', 'hide_empty' => false, 'hierarchical' => false, 'fields' => 'ids'));
        $query->set('category__in', $categories_ids);
        $query->set('posts_per_page', 25);

        // enable sorting by file order if the category is marked as 'sort manually'
        if (get_field('sort_manually', 'category_' . get_category_by_slug($query->get('category_name'))->term_id)) {
            $query->set('orderby', 'meta_value');
            $query->set('meta_key', 'file_order');
            $query->set('order', 'DESC');
        }
        // get the category children
        $queried_object = get_queried_object();
        $child_cats = (array) get_term_children($queried_object->term_id, 'category');

        //exclude the posts in child categories
        $query->set('category__not_in', array_merge($child_cats));
    }

    // if we search for something
    if ($query->is_search()) {
        // hide the welcome and profile page in the results
        $query->set('post__not_in', [442, 445]);
    }

    // return the result
    return $query;
}

// hook our function to the query manipulation queue
add_action('pre_get_posts', 'vizoo_manipulate_query', 20);

function vizoo_save_videotutorials($post_id, $post)
{
    if ($post->post_type !== 'vizoo_videotutorial') {
        return;
    }
    $video_link = get_field('video_url', $post_id);
    if (empty($video_link)) {
        return;
    }
    $query = parse_url($video_link, PHP_URL_QUERY);
    if (!$query) {
        return;
    }
    parse_str($query, $query_entries);

    $video_id = $query_entries['v'];
    if (empty($video_id)) {
        return;
    }
    copy('https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg', WP_CONTENT_DIR . '/uploads/thumbnails/' . $video_id . '.jpg');
}

add_action('save_post', 'vizoo_save_videotutorials', 20, 2);

/** 4. Breadcrumb navigation **/

/**
 * Draw a breadcrumb navigation.
 *
 * Echoes out a breadcrumb navigation, e.g. 'Home / Downloads / Documentation', with hyperlinks.
 *
 */
function nav_breadcrumb()
{
    if (!is_user_logged_in()) {
        return;
    }

    $delimiter = '/';
    $home = 'Home';
    $before = '<span class="current-page">';
    $after = '</span>';
    if (!is_home() && !is_front_page() && get_post_type() != 'product') {
        global $post;
        $homeLink = get_bloginfo('url');

        echo '<nav class="breadcrumb"><div class="content-wrapper">';
        echo '<a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';

        if (is_search()) {
            echo $before . 'Search results for "<span id="searchquery">' . get_search_query() . '</span>"' . $after;
        } elseif (is_category()) {
            global $wp_query;
            $cat_obj = $wp_query->get_queried_object();
            $thisCat = $cat_obj->term_id;
            $thisCat = get_category($thisCat);
            $parentCat = get_category($thisCat->parent);
            if ($thisCat->parent != 0) {
                echo (get_category_parents($parentCat, true, ' ' . $delimiter . ' '));
            }

            echo $before . single_cat_title('', false) . $after;
        } elseif (is_day()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
            echo '<a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
            echo $before . get_the_time('d') . $after;
        } elseif (is_month()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
            echo $before . get_the_time('F') . $after;
        } elseif (is_year()) {
            echo $before . get_the_time('Y') . $after;
        } elseif (is_single() && !is_attachment()) {
            if (get_post_type() != 'post') {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                if (!empty($slug['slug'])) {
                    echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a> ' . $delimiter . ' ';
                }
                echo $before . get_the_title() . $after;
            } else {
                $cat = get_the_category();
                if (sizeof($cat) > 0) {
                    $cat = $cat[0];
                    $parents = get_category_parents($cat, true, ' ' . $delimiter . ' ');
                    if (!is_wp_error($parents)) {
                        echo $parents;
                    }
                }
                echo $before . get_the_title() . $after;
            }
        } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
            $post_type = get_post_type_object(get_post_type());
            echo $before . $post_type->labels->singular_name . $after;
        } elseif (is_attachment()) {
            $parent = get_post($post->post_parent);
            $cat = get_the_category($parent->ID);
            if (!is_wp_error($cat) && !empty($cat)) {
                $cat = $cat[0];
            } else {
                $cat = get_the_category();
                $cat = $cat[0];
            }
            echo get_category_parents($cat, true, ' ' . $delimiter . ' ');
            echo $before . get_the_title() . $after;
        } elseif (is_page() && !$post->post_parent) {
            echo $before . get_the_title() . $after;
        } elseif (is_page() && $post->post_parent) {
            $parent_id = $post->post_parent;
            $breadcrumbs = array();

            while ($parent_id) {
                $page = get_page($parent_id);
                $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                $parent_id = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            foreach ($breadcrumbs as $crumb) {
                echo $crumb . ' ' . $delimiter . ' ';
            }

            echo $before . get_the_title() . $after;
        } elseif (is_tag()) {
            echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
        } elseif (is_404()) {
            echo $before . 'Error 404' . $after;
        }
        if (get_query_var('paged')) {
            if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
                echo ' (';
            }

            echo ': ' . __('Page') . ' ' . get_query_var('paged');

            if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
                echo ')';
            }
        }
        echo '</div></nav>';
    }
}

/**
 * Filters the breadcrumb generated by WooCommerce.
 *
 * @param     array    $args    The configuration passed by WooCommerce.
 * @return    array             The altered configuration passed back.
 */
function vizoo_filter_wc_breadcrumb($args)
{
    $args['wrap_before'] = '<nav class="breadcrumb">';
    return $args;
}
add_filter('woocommerce_breadcrumb_defaults', 'vizoo_filter_wc_breadcrumb');

/** 5. Get details of an attachment by the attachment id **/

/**
 * Get details of an attachment by the attachment id.
 *
 * @param    integer              $attachment_id      The attachment's id.
 * @return   array                                    An array containing information about the attachment.
 */
function wp_get_attachment($attachment_id)
{
    $attachment = get_post($attachment_id);

    return array(
        'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
        'caption' => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'href' => get_permalink($attachment->ID),
        'src' => $attachment->guid,
        'title' => $attachment->post_title,
    );
}

/** 6. Get the amount of subcategories of a category **/

/**
 * Get the amount of subcategories of a category.
 *
 * @param    string               $cat_slug           The slug of the category.
 * @param    bool                 $echo               Optional. Should we echo out the result?
 * @return   string                                   The amount of subcategories.
 */
function get_cat_count($cat_slug, $echo = true)
{
    $cat = get_category_by_slug($cat_slug)->term_id;
    $tax = 'category';
    $args = array('child_of' => $cat);
    $tax_terms = get_terms($tax, $args);
    $count = count($tax_terms);

    if ($echo) {
        if ($count === 1) {
            echo $count . ' category';
        } else {
            echo $count . ' categories';
        }
    }
    return $count;
}

/** 7. Get the amount of posts of a category (inclusive those in subcategories) **/

/**
 * Get the amount of posts of a category.
 *
 * @param    string               $cat_slug           The slug of the category.
 * @param    bool                 $echo               Optional. Should we echo out the result?
 *
 * @return   string                                   The amount of posts of a category.
 */
function get_post_count($cat_slug, $echo = true)
{
    $id = get_category_by_slug($cat_slug)->term_id;

    $args = [
        'posts_per_page' => 1,
        'fields' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'terms' => $id,
            ],
        ],
    ];

    $result = new WP_Query($args);
    $count = $result->found_posts;

    if ($echo) {
        if ($count === 1) {
            echo $count . ' post';
        } else {
            echo $count . ' posts';
        }
    }
    return $count;
}

/** 8. Get the MIME icon for a specific MIME type **/

/**
 * Return the MIME type of a post or attachment.
 *
 * @param    WP_Post              $post               The post of which we want to know the MIME type.
 * @return   string                                   The MIME type.
 */
function vizoo_mime_sniffing($post)
{
    if ($post === 'category') {
        return 'category';
    }
    if ($post->post_type == 'post' || $post->post_type == 'page') {
        return 'text/html';
    }
    if ($post->post_type === 'vizoo_videotutorial') {
        return 'video/mp4';
    }
    if ($post->post_type == 'attachment') {
        $mime = get_post_mime_type($post->ID);

        if (!empty($mime)) {
            return $mime;
        }
        $filename = explode('.', wp_get_attachment_url($post->ID));
        switch ($filename[count($filename) - 1]) {
            case 'exe':
                return 'application/octet-stream';
            default:
                return '';
        }
    }
    return '';
}


/**
 * Returns whether to directly download the file.
 *
 * @param    string               $mime               The MIME type.
 * @return   bool                                     Returns true if we should provide a direct download link.
 */
function vizoo_file_direct_download($mime)
{
    switch ($mime) {
        case 'application/octet-stream':
        case 'application/zip':
            return false;
            break;
        default:
            return true;
            break;
    }
}



/** 11. Set up the mail configuration **/

/**
 * Modify the mailer wordpress uses.
 *
 * @param    PHPMailer            $mailer             WordPress' mailer.
 */
function mailer_config(PHPMailer\PHPMailer\PHPMailer $mailer)
{
    $mailer->IsSMTP();
    $mailer->Host = getenv('VIZOO_MAIL_SMTP');
    $mailer->Port = getenv('VIZOO_MAIL_PORT');
    $mailer->SMTPAuth = getenv('VIZOO_MAIL_AUTH');
    $mailer->Username = getenv('VIZOO_MAIL_USERNAME');
    $mailer->Password = getenv('VIZOO_MAIL_PASSWORD');
    $mailer->CharSet = 'UTF-8';
    $mailer->From = getenv('VIZOO_MAIL_FROM');
    $mailer->FromName = 'Vizoo Customer Service';
}

add_action('phpmailer_init', 'mailer_config', 10, 1);

/**
 * Modify the reset password mail message.
 *
 * @param    string               $message            The original message.
 * @param    string               $key                The reset key.
 * @param    string               $user_login         The user's login name.
 * @param    WP_User              $user_data          Data of the user that requested a reset.
 * @return   string                                   The modified message.
 */
function password_reset_mail_message($message, $key, $user_login, $user_data)
{
    $message =
        'Someone has requested a password reset for the following account on the Customer Portal:' . "\n\n" .
        'E-Mail: ' . sprintf(__('%s'), $user_data->user_email) . "\n\n" .
        'If this was a mistake, just ignore this email and nothing will happen.' . "\n" .
        'To reset your password, visit the following link:' . "\n" .
        network_site_url('wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode($user_login), 'login') . "\n\n\n" .
        'If you have further issues, please email us to support@vizoo3d.com.' . "\n\n" .
        'Kind regards,' . "\n" .
        'Vizoo';
    return $message;
}

add_filter('retrieve_password_message', 'password_reset_mail_message', 10, 4);

/**
 * Modify the reset password mail title.
 *
 * @param    string               $title              The original title.
 * @return   string                                   The modified title.
 */
function password_reset_mail_title($title)
{
    return __('Password reset for the Vizoo Customer Portal');
}

add_filter('retrieve_password_title', 'password_reset_mail_title', 10, 1);

/**
 * Modify the reset password mail message.
 *
 * @param    string               $message            The original message.
 * @param    string               $key                The reset key.
 * @param    string               $user_login         The user's login name.
 * @param    WP_User              $user_data          Data of the user that requested a reset.
 * @return   string                                   The modified message.
 */
function vizoo_change_password($args, $user, $userdata)
{
    $args['subject'] = 'Your password for the Vizoo Customer Portal was changed';
    $args['message'] =
        'Hello ' . $user['display_name'] . ',' . "\n\n" .
        'Your password for the Vizoo Customer Portal was changed successfully!' . "\n\n" .
        'If you did not perform a password change, please contact us at support@vizoo3d.com immediately.' . "\n\n" .
        'You may now login at ' . get_home_url() . ' with your newly created password.' . "\n\n" .
        'Kind regards,' . "\n" .
        'Vizoo';
    return $args;
}
add_filter('password_change_email', 'vizoo_change_password', 10, 3);

/** 12. Disable the admin dashboard for non-admins, others get redirected to home **/

/**
 * Redirect users that are no admins requesting to access the dashboard.
 */
function disable_admin_for_users()
{
    if (is_admin() && !current_user_can('edit_pages') && !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(home_url());
        exit;
    }
}

add_action('init', 'disable_admin_for_users');

/** 13. Disable emoji script and stylesheet as we don't use them **/

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/** 14. Show attachment ids and order priority in the media library **/

/**
 * Show custom columns in the media library overview.
 *
 * @param    array                $defaults           The columns that already exist.
 * @return   array                                    The supplemented array of columns.
 */
function vizoo_custom_media_columns($defaults)
{
    $defaults['vizoo_media_id'] = __('ID');
    $defaults['vizoo_media_priority'] = __('Priority');
    $defaults['vizoo_media_downloads'] = __('Download count');

    return $defaults;
}

add_filter('manage_media_columns', 'vizoo_custom_media_columns', 1);

/**
 * Display something in the custom media columns.
 *
 * @param    string               $column_name        The name of the column to be filled.
 * @param    int                  $id                 The id of the row element.
 */
function vizoo_custom_media_column($column_name, $id)
{
    switch ($column_name) {
        case 'vizoo_media_id':
            echo $id;
            return;
        case 'vizoo_media_priority':
            the_field('file_order', $id);
            return;
        case 'vizoo_media_downloads':
            echo get_post_meta($id, 'vizoo_media_downloads', true);
            return;
        default:
            return;
    }
}
add_action('manage_media_custom_column', 'vizoo_custom_media_column', 1, 2);

function vizoo_attachment_fields_to_edit($form_fields, $post)
{
    $download_count = get_post_meta($post->ID, 'vizoo_media_downloads', true);

    $form_fields['vizoo_media_downloads'] = array(
        'label' => __('Download count'),
        'input' => 'html',
        'html'  => "<input type='text' class='text urlfield' readonly='readonly' value='" . esc_attr($download_count) . "' /><br />",
        'value' => $download_count,
        'helps' => __('How often the file was served to a user.'),
    );
    return $form_fields;
}
add_filter('attachment_fields_to_edit', 'vizoo_attachment_fields_to_edit', 10, 2);

/** 15. Send info to press members if a new post is published */

/**
 * Send a newsletter to press if we published a new post tagged as 'Press release'.
 *
 *
 *
 * @param    integer              $id                 The ID of the post that is about to be published.
 * @param    WP_Post              $post               The post object that is about to be published.
 */
function vizoo_send_newsletter($id, $post)
{
    $user_access_manager = $GLOBALS['userAccessManager'];

    $is_press_release = in_array('press-release', wp_get_post_tags($id, array('fields' => 'slugs')));
    $is_newsletter = in_array('newsletter', wp_get_post_tags($id, array('fields' => 'slugs')));

    if (in_array('newsletter-archive', wp_get_post_tags($id, array('fields' => 'slugs')))) {
        return;
    }

    if ($is_press_release || $is_newsletter) {
        $receipients = array();

        if ($is_press_release && isset($user_access_manager)) {
            $user_group_handler = $user_access_manager->getUserGroupHandler();
            $user_groups = $user_group_handler->getUserGroups();
            $users = $user_groups[5]->getFullUsers();

            $user_ids = array_keys($users);
            foreach ($user_ids as $user_id) {
                $receipients[] = get_userdata($user_id)->user_email;
            }
        }

        if ($is_newsletter) {
            $args = array(
                'meta_key' => 'vizoo_newsletter',
                'meta_value' => 'yes',
            );
            $users = get_users($args);
            foreach ($users as $user) {
                $receipients[] = $user->user_email;
            }
        }

        $receipients = array_unique($receipients);

        if (!empty($receipients)) {
            vizoo_send_newsletter_email($receipients, $id, $post);
        }
    }
}
add_action('publish_post', 'vizoo_send_newsletter', 10, 2);

function vizoo_test_newsletter($id, $post)
{
    if (in_array('newsletter-preview', wp_get_post_tags($id, array('fields' => 'slugs')))) {
        vizoo_send_newsletter_email(array('info@vizoo3d.com'), $id, $post);
    }
}
add_action('draft_post', 'vizoo_test_newsletter', 10, 2);

function vizoo_send_newsletter_email($receipients, $id, $post)
{
    $newsletter_title = apply_filters('the_title', $post->post_title);
    $newsletter_content = apply_filters('the_content', $post->post_content);
    $newsletter_permalink = get_permalink($id);
    $content_hash = md5($newsletter_content);
    $newsletter_path = ABSPATH . '/newsletter/' . $content_hash . '.html';
    $newsletter_url = esc_url(home_url()) . '/newsletter/' . $content_hash . '.html';
    $newsletter_unsubscribe_url = get_home_url() . '/my-account/newsletter/';

    ob_start();
    require_once get_template_directory() . '/template-parts/post-email.php';
    $html = ob_get_clean();
    file_put_contents($newsletter_path, $html, LOCK_EX);
    $title = 'News from Vizoo: ' . $newsletter_title;
    $headers = array(
        'From: Vizoo News <' . getenv('VIZOO_MAIL_FROM') . '>',
        'Reply-To: Vizoo GmbH <info@vizoo3d.com>',
        'Bcc: ' . implode(', ', $receipients),
        'Content-type: text/html; charset=utf-8',
    );
    wp_mail(['Undisclosed Recipients <info@vizoo3d.com>'], $title, $html, $headers);
}

function vizoo_excerpt_more($more)
{
    if ($more === '') {
        return sprintf(
            '<a href="%1$s" class="more-link">%2$s</a>',
            esc_url(get_permalink(get_the_ID())),
            sprintf('Read more', '<span class="screen-reader-text">' . get_the_title(get_the_ID()) . '</span>')
        );
    } else {
        return sprintf(
            '&hellip;</p><p><a href="%1$s" class="more-link">%2$s</a>',
            esc_url(get_permalink(get_the_ID())),
            sprintf('Read more', '<span class="screen-reader-text">' . get_the_title(get_the_ID()) . '</span>')
        );
    }
}
add_filter('excerpt_more', 'vizoo_excerpt_more');

function vizoo_unblock_unsubscribe_site($unblock)
{
    if (is_page('unsubscribe')) {
        $unblock = true;
    }
    return $unblock;
}
add_filter('v_forcelogin_bypass', 'vizoo_unblock_unsubscribe_site', 10, 1);

function vizoo_unblock_unblocked_sites($unblock)
{
    if (is_singular() && !wpmem_is_blocked()) {
        $unblock = true;
    }
    return $unblock;
}
add_filter('v_forcelogin_bypass', 'vizoo_unblock_unblocked_sites', 10, 1);

function vizoo_forcelogin_fix_https($url)
{
    $startswith = 'http://customers.vizoo3d.com';
    if (substr($url, 0, strlen($startswith)) === $startswith) {
        $url = 'https' . substr($url, 4);
    }
    return $url;
}
add_filter('v_forcelogin_redirect', 'vizoo_forcelogin_fix_https', 10, 1);

/** 16. Add functionality to the User Access Manager plugin */

/**
 * Returns whether the user has access to a post.
 *
 * Manually checks the User Access Manager table if the user has access to a specific
 * post as the plugin lacks this basic functionality. Used to detect whether to display
 * the support button on the landing page.
 *
 * @access   public
 * @param    string               $post_id            The id of the post to check.
 * @return   boolean                                  Indication whether the request was successful.
 */
function vizoo_user_has_access_to($post_id)
{
    global $wpdb;

    // Administrators always have access.
    if (current_user_can('manage_options')) {
        return true;
    }

    // Check whether the post is blocked at all.
    $post_groups = $wpdb->get_var($wpdb->prepare(
        '
			SELECT COUNT(object_id) FROM ' . $wpdb->prefix . 'uam_accessgroup_to_object
			WHERE object_id = %d AND general_object_type = "_post_"
		',
        $post_id
    ));
    if ($post_groups === null || $post_groups == 0) {
        return true;
    }

    // Check whether the user has access.
    $current_user_id = get_current_user_id();
    $query = $wpdb->prepare(
        '
			SELECT COUNT(object_id) FROM ' . $wpdb->prefix . 'uam_accessgroup_to_object
			WHERE (object_id = %d AND general_object_type = "_user_") OR (object_id = %d AND general_object_type = "_post_")
			GROUP BY group_id
			ORDER BY COUNT(object_id) DESC
		',
        $current_user_id,
        $post_id
    );

    if ($wpdb->get_var($query) > 1) {
        return true;
    } else {
        return false;
    }
}

/* 17. Endpoint for xTex update checks */

function vizoo_get_available_xtex_versions($skip_versions_greater_2_7)
{
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'any',
        'nopaging' => true,
        'meta_key' => 'vizoo_is_xtex_installer',
        'meta_value' => '1',
    );

    $query = new WP_Query($args);
    $data = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $major = get_field('vizoo_major_version');
            $minor = get_field('vizoo_minor_version');
            $patch = get_field('vizoo_patch_version');
            if ($skip_versions_greater_2_7 && ($major > 2 || ($major == 2 && $minor >= 8))) {
                continue;
            }

            $changelog_post_id = get_field('vizoo_changelog_post_id');

            $url_path = parse_url(get_the_guid(), PHP_URL_PATH);
            $type = substr($url_path, -3);
            if ($type !== 'exe' && $type !== 'zip') {
                continue;
            }
            $version_string = $major . '.' . $minor . '.' . $patch;

            $url_path_without_initial_slash_and_filetype = substr($url_path, 1, -4);
            if (isset($data[$version_string])) {
                $data[$version_string][$type . 'url'] = $url_path_without_initial_slash_and_filetype;
            } else {
                $data[$version_string] = array(
                    'major' => $major,
                    'minor' => $minor,
                    'patch' => $patch,
                    'date' => get_field('vizoo_compile_date'),
                    $type . 'url' => $url_path_without_initial_slash_and_filetype,
                    'message' => get_field('vizoo_additional_message'),
                );
                if (!empty($changelog_post_id)) {
                    $data[$version_string]['changelogPostId'] = $changelog_post_id;
                }
            }
        }
    }
    return $data;
}

// LEGACY: For xTex <2.8
function vizoo_xtex_update_check($request)
{
    $data = vizoo_get_available_xtex_versions(true);

    vizoo_send_xtex_update_check_response(json_encode(array_values($data)));
}

// LEGACY: For xTex =2.8
function vizoo_xtex_versionrequest_v2($data)
{
    // TODO: Use $GLOBALS['vizoo_versioncheck_raw_request'] to check if xTex was pirated.
    // https://vizoo3d.atlassian.net/browse/WEB-237

    $return_data = [
        'availableVersions' => array_values($data),
        'allowed' => true,
        'disallowedMessage' => null,
    ];

    vizoo_send_xtex_update_check_response(json_encode($return_data), '-v2');
}

// For xTex >=2.8.1
function vizoo_xtex_versionrequest_v3($data)
{
    $payload = json_decode($GLOBALS['vizoo_versioncheck_raw_request'], true);
    $version = $payload['meta']['version'];
    $piracy_payload_base64 = $payload['payload'];

    $passphrase = pack('C*', 0x04, 0xdb, 0xfb, 0xfc, 0x41, 0x6d, 0xb0, 0xdd, 0x6c, 0x52, 0x9e, 0xd3, 0x0d, 0x15, 0x08, 0x84, 0x05, 0x66, 0xbd, 0x9b, 0xd2, 0x4b, 0xd3, 0x53, 0x92, 0xa7, 0x20, 0x85, 0x22, 0xbb, 0xb0, 0x72);
    $piracy_data = openssl_decrypt($piracy_payload_base64, "aes-256-cbc", $passphrase, OPENSSL_ZERO_PADDING);
    $hash_is_valid = hash('sha256', substr($piracy_data, 0, 32), true) === substr($piracy_data, 32);
    $is_pirated = (ord($piracy_data) & 0b00000001) === 1;

    if (!$hash_is_valid || $is_pirated) {
        $version_str = sprintf('%d.%d.%d', $version['major'], $version['minor'], $version['patch']);
        error_log(sprintf('A suspicious xTex (pirated or modified) called versioncheck. Hash is valid: %d, Pirated: %d, Version: %s', $hash_is_valid, $is_pirated, $version_str));
    }

    $return_data = [
        'availableVersions' => array_values($data),
        'allowed' => true,
        'disallowedMessage' => null,
    ];

    vizoo_send_xtex_update_check_response(json_encode($return_data), '-v3');
}

function vizoo_xtex_update_check_new($request)
{
    $data = vizoo_get_available_xtex_versions(false);

    switch ($_SERVER['HTTP_X_VIZOO_REPORT_TYPE']) {
        case 'versionrequest-v2': {
                vizoo_xtex_versionrequest_v2($data);
                break;
            }
        case 'versionrequest-v3': {
                vizoo_xtex_versionrequest_v3($data);
                break;
            }
        default: {
                throw new RuntimeException('Unsupported report type.');
            }
    }
}

function vizoo_xtex_reject_update_check($request)
{
    $data = vizoo_get_available_xtex_versions(false);
    $return_data = [
        'availableVersions' => array_values($data),
        'allowed' => false,
        'disallowedMessage' => 'Not all those who wander are lost',
    ];

    vizoo_send_xtex_update_check_response(json_encode($return_data), '-v3');
}

function vizoo_send_xtex_update_check_response($response_content, $version_suffix = '')
{
    $compressed = gzencode($response_content);
    $response_token = hash('sha512', hex2bin($GLOBALS['vizoo_response_salt_1']) . hash('sha512', $compressed, true) . hex2bin($GLOBALS['vizoo_response_salt_2']));

    header('X-Vizoo-Authentication: ' . $GLOBALS['vizoo_response_index_1'] . ' ' . $GLOBALS['vizoo_response_index_2'] . ' ' . $response_token);
    header('X-Vizoo-Report-Type: versionrequest' . $version_suffix);
    header('Content-Encoding: gzip');
    header('Content-Type: application/json+gzip');
    header('Content-Length: ' . strlen($compressed));
    header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
    header('Pragma: no-cache');
    echo $compressed;
    exit;
}

function vizoo_xtex_update_check_permission($request)
{
    if ($_SERVER['HTTP_X_VIZOO_REPORT_TYPE'] !== 'versionrequest') {
        return false;
    }
    if ($_SERVER['HTTP_USER_AGENT'] !== 'xTex') {
        return false;
    }
    return vizoo_check_x_auth_header('');
}

function vizoo_xtex_update_check_permission_new($request)
{
    $report_type = $_SERVER['HTTP_X_VIZOO_REPORT_TYPE'];
    if (!(
        $report_type === 'versionrequest-v2' ||
        $report_type === 'versionrequest-v3'
    )) {
        return false;
    }
    if ($_SERVER['HTTP_USER_AGENT'] !== 'xTex') {
        return false;
    }
    $GLOBALS['vizoo_versioncheck_raw_request'] = file_get_contents('php://input');
    return vizoo_check_x_auth_header($GLOBALS['vizoo_versioncheck_raw_request']);
}

function vizoo_check_x_auth_header($content)
{
    $salts = array();
    $contents = file_get_contents("salts.txt", true);
    $separator = "\r\n";
    $line = strtok($contents, $separator);

    while ($line != null) {
        $line = trim($line);
        if ($line[0] !== ';') {
            $salts[] = $line;
        }

        $line = strtok($separator);
    }

    // Prepare the response indices
    $max_number = count($salts);
    $response_index_1 = random_int(0, $max_number);
    $response_index_2 = random_int(0, $max_number - 1);
    if ($response_index_2 >= $response_index_1) {
        $response_index_2++;
    }

    $GLOBALS['vizoo_response_index_1'] = $response_index_1;
    $GLOBALS['vizoo_response_index_2'] = $response_index_2;
    $GLOBALS['vizoo_response_salt_1'] = $salts[$response_index_1];
    $GLOBALS['vizoo_response_salt_2'] = $salts[$response_index_2];

    // Check authentication
    list($index_1, $index_2, $token) = explode(' ', $_SERVER['HTTP_X_VIZOO_AUTHENTICATION'], 3);
    return hash('sha512', hex2bin($salts[$index_1]) . hash('sha512', $content, true) . hex2bin($salts[$index_2])) === $token;
}

add_action('rest_api_init', function () {
    register_rest_route('xtex/v1', '/latest', array(
        'methods' => 'GET',
        'callback' => 'vizoo_xtex_update_check',
        'permission_callback' => 'vizoo_xtex_update_check_permission',
    ));
    register_rest_route('xtex/v1', '/latest', array(
        'methods' => 'POST',
        'callback' => 'vizoo_xtex_update_check_new',
        'permission_callback' => 'vizoo_xtex_update_check_permission_new',
    ));
    register_rest_route('xtex/v1', '/reject-latest', array(
        'methods' => 'POST',
        'callback' => 'vizoo_xtex_reject_update_check',
        'permission_callback' => 'vizoo_xtex_update_check_permission_new',
    ));
});

function vizoo_forcelogin_patch_rest_access($result)
{
    if (null === $result && !is_user_logged_in()) {
        if (
            $_SERVER['REQUEST_URI'] !== '/wp-json/xtex/v1/latest' &&
            $_SERVER['REQUEST_URI'] !== '/wp-json/xtex/v1/reject-latest' &&
            $_SERVER['REQUEST_URI'] !== '/wp-json/real-cookie-banner/v1/consent'
        ) {
            return new WP_Error('rest_unauthorized', __("Only authenticated users can access the REST API.", 'wp-force-login'), array('status' => rest_authorization_required_code()));
        }
    }
    return $result;
}

add_action('plugins_loaded', function () {
    remove_filter('rest_authentication_errors', 'v_forcelogin_rest_access', 99);
    add_filter('rest_authentication_errors', 'vizoo_forcelogin_patch_rest_access', 99);
});

add_action('delete_attachment', function (int $post_id, WP_Post $post = null) {
    $e = new Exception();
    $trace = $e->getTraceAsString();
    $is_cron = wp_doing_cron();
    $user_id = get_current_user_id();
    $post = get_post($post_id);
    $postinfo = $post === null ? '(no info)' : print_r($post->to_array(), true);
    error_log("[" . date("Y-m-d H:i:s") . "] Attachment was deleted (user_id: " . $user_id . ", is_cron: " . ($is_cron ? "yes" : "no") . "). Post info:\n" . $postinfo . "\nTrace: \n" . $trace . "-------\n", 3, '/home/vizoo/user-logs/attachment_deletion_log.log');
});

function my_custom_mime_types($mimes)
{
    $mimes['svg']  = 'image/svg+xml';
    return $mimes;
}

add_filter('upload_mimes', 'my_custom_mime_types');
