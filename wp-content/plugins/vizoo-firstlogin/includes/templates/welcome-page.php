<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
    <link rel="stylesheet" type="text/css" href="/wp-content/plugins/vizoo-firstlogin/includes/css/style.css" />
</head>

<body>
    <div class="welcome-screen">
        <main class="firstlogin-body">
            <section class="textarea">
                <img class="logo" src="./logo.svg" alt="Vizoo Logo">
                <h1>Welcome&nbsp;to&nbsp;the Vizoo&nbsp;Customer&nbsp;Portal</h1>
                <span class="description">
                    Please set a new password before entering the portal for the first time:
                </span>
            </section>
            <?php if (isset($vizoo_firstlogin_err)) : ?>
                <div id="password-set-err"><?= $vizoo_firstlogin_err; ?></div>
            <?php endif; ?>
            <form id="password-set-form" method="POST">
                <label class="password-set-label" for="password-set-1">Set new password</label>
                <input class="password-set" id="password-set-1" name="vizoo_firstlogin_pw1" type="password" />
                <label class="password-set-label" for="password-set-2">Confirm new password</label>
                <input class="password-set" id="password-set-2" name="vizoo_firstlogin_pw2" type="password" />
                <input type="hidden" id="password-set-sent" name="vizoo_firstlogin_set" value="1" />
                <?php wp_nonce_field('initial_password_set', 'vizoo_firstlogin_nonce'); ?>
                <button id="password-set-btn" type="submit">Continue</button>
            </form>
            <a class="privacy-policy-link" href="https://customers.vizoo3d.com/privacy-policy">Privacy Policy</a>
        </main>
    </div>
</body>

</html>