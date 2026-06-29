<?php
if (!defined('ABSPATH')) {
    exit();
}

get_header();

if (is_user_logged_in()) {
    $user = wp_get_current_user();

    if (function_exists('vizoo_pipedrive_get_current_user_organization')) {
        $organization = vizoo_pipedrive_get_current_user_organization();
    }

    if (isset($_POST['vizoo_new_pw_nonce']) && isset($_POST['vizoo_new_pw1']) && isset($_POST['vizoo_new_pw2'])) {
        $nonce = $_POST['vizoo_new_pw_nonce'];
        $pw1 = $_POST['vizoo_new_pw1'];
        $pw2 = $_POST['vizoo_new_pw2'];
        if (wp_verify_nonce($nonce, 'new_password_set')) {
            if (!empty($pw1) && !empty($pw2)) {
                if ($pw1 === $pw2) {
                    $user_id = wp_update_user(['ID' => $user->ID, 'user_pass' => $pw1]);
                    if (!($user_id instanceof WP_Error)) {
                        $vizoo_new_pw_suc = 'Your password was successfully updated.';
                    } else {
                        $vizo_new_pw_err = 'There was an internal error when editing your password. Please contact our support.';
                    }
                } else {
                    $vizoo_new_pw_err = 'Passwords didn\'t match.';
                }
            } else {
                $vizoo_new_pw_err = 'Passwords can\'t be empty.';
            }
        } else {
            $vizoo_new_pw_err = 'Are you sure you want to do this?';
        }
    }

    ?>
    <div id="primary" class="profile-wrapper">
        <main id="main" class="profile-content">
            <section class="page-single-profile-section">
                <h2>User information</h2>
                <span class="user-details">
                    <span><i class="fa fas fa-fw fa-user"></i>&nbsp;<?= $user->display_name ?></span>
                    <span><i class="fa fas fa-fw fa-envelope"></i>&nbsp;<?= $user->user_email ?></span>
                    <span>
                        <?php if (is_a($organization, 'Vizoo_Pipedrive_Organization')) : ?>
                            <i class="fa fas fa-fw fa-briefcase"></i>&nbsp;<?= $organization->name ?>
                    </span>
                    <span>
                        <?php if (!empty($organization->website)) : ?>
                            <i class="fa fas fa-fw fa-globe"></i>&nbsp;<?= $organization->website ?>
                        <?php endif; ?>
                    <?php else : ?>
                        <i class="fa fas fa-fw fa-briefcase"></i>&nbsp;No organization
                    <?php endif; ?>
                    </span>
                </span>
            </section>
            <section class="page-single-profile-section">
                <h2>Change password</h2>
                <form id="password-set-form" method="POST" class="password-input-wrapper">
                    <span class="single-input-wrapper">
                        <label class="password-input-label" for="password-set-1">Set new password</label>
                        <input class="password-input" id="password-set-1" name="vizoo_new_pw1" type="password" />
                    </span>
                    <span class="single-input-wrapper">
                        <label class="password-input-label" for="password-set-2">Confirm new password</label>
                        <input class="password-input" id="password-set-2" name="vizoo_new_pw2" type="password" />
                    </span>
                    <?php wp_nonce_field('new_password_set', 'vizoo_new_pw_nonce'); ?>
                    <?php if (isset($vizoo_new_pw_err)) : ?>
                        <div class="vizoo-err-msg-box"><?= $vizoo_new_pw_err; ?></div>
                    <?php elseif (isset($vizoo_new_pw_suc)) : ?>
                        <div class="vizoo-suc-msg-box"><?= $vizoo_new_pw_suc; ?></div>
                    <?php endif; ?>
                    <button class="button-medium save-password-button" id="password-set-btn" type="submit">Save</button>
                </form>
            </section>
        </main>
    </div>
<?php
} else {
    ?>
    <div id="primary" class="content-area full-width">
        <main id="main" class="site-main">
            <article>
                <div class="entry-content">
                    <header>
                        <h2>Not logged in</h2>
                    </header>
                    <p>You need to be logged in in order to see this page.</p>
                </div>
            </article>
        </main>
    </div>
<?php
}
get_footer();
?>