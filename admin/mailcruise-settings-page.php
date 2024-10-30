<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Create the settings page for the admin area
function mailcruise_settings_page() {
    ?>
    <div class="wrap">
        <h1>MailCruise Integration Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('mailcruise_settings_group'); ?>
            <?php do_settings_sections('mailcruise_settings_group'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">MailCruise API Token</th>
                    <td><input type="text" name="mailcruise_api_token" value="<?php echo esc_attr(get_option('mailcruise_api_token')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">MailCruise API Endpoint</th>
                    <td><input type="text" name="mailcruise_endpoint" value="<?php echo esc_attr(get_option('mailcruise_endpoint')); ?>" placeholder="e.g., https://console.mailcruise.glemad.com/api/v1" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">MailCruise List UID</th>
                    <td><input type="text" name="mailcruise_list_uid" value="<?php echo esc_attr(get_option('mailcruise_list_uid')); ?>" /></td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
