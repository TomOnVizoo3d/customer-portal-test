<?php
if (is_user_logged_in()) {
    wp_safe_redirect(get_home_url(null, '/profile'), 302);
    exit();
} else {
    if (!empty($_POST['unsubscribe_mail_sent']) && $_POST['unsubscribe_mail_sent'] == '1') {
        if (wp_verify_nonce($_POST['unsubscribe_nonce'], 'unsubscribe_newsletter')) {
            $mail = $_POST['unsubscribe_mail'];

            if ($mail == '') {
                $err_msg = "Field cannot be empty.";
            } else {
                $user = get_user_by('email', $mail);

                if (!$user) {
                    $err_msg = 'The given mail address is not registered.';
                } else {
                    update_user_meta($user->ID, 'vizoo_newsletter', 'no');
                    $suc_msg = 'You were successfully unsubscribed from our newsletter.';
                }
            }
        } else {
            $err_msg = "Are you sure you want to do this?";
        }
    }
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body>
    <div class="unsubscribe-screen">
        <main>
            <?php
            while (have_posts()) : the_post();

                get_template_part('template-parts/content', 'page');

                // If comments are open or we have at least one comment, load up the comment template.
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;

            endwhile; // End of the loop.
if (!empty($err_msg)) : ?>
                <div id="unsubscribe_err"><?php echo $err_msg; ?></div>
            <?php else : ?>
                <div id="unsubscribe_suc"><?php echo $suc_msg; ?></div>
            <?php endif; ?>
            <form id="unsubscribe_form" method="POST">
                <label class="unsubscribe_mail_label" for="unsubscribe_mail">Please enter your mail address:</label>
                <input class="unsubscribe_mail_input" id="unsubscribe_mail" name="unsubscribe_mail" type="text" />
                <input type="hidden" id="unsubscribe_mail_sent" name="unsubscribe_mail_sent" value="1" />
                <?php wp_nonce_field('unsubscribe_newsletter', 'unsubscribe_nonce'); ?>
                <button id="unsubscribe_mail_btn" type="submit">Unsubscribe</button>
            </form>
        </main>
    </div>
</body>

</html>