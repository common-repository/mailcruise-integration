<?php
/**
 * Plugin Name: MailCruise Integration
 * Plugin URI: https://glemad.com
 * Description: A WordPress plugin to integrate with Glemad MailCruise email marketing and automation
 * Version: 1.1.0
 * Author: Glemad Inc
 * Author URI: https://glemad.com/contact
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: mailcruise-integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('MAILCRUISE_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include admin settings page
include_once(MAILCRUISE_PLUGIN_PATH . 'admin/mailcruise-settings-page.php');

// Register plugin settings
function mailcruise_register_settings() {
    register_setting('mailcruise_settings_group', 'mailcruise_api_token', 'sanitize_text_field');
    register_setting('mailcruise_settings_group', 'mailcruise_endpoint', 'esc_url_raw');
    register_setting('mailcruise_settings_group', 'mailcruise_list_uid', 'sanitize_text_field');
}
add_action('admin_init', 'mailcruise_register_settings');

// Add admin menu
function mailcruise_add_admin_menu() {
    add_menu_page(
        'MailCruise Settings',      // Page title
        'MailCruise',               // Menu title
        'manage_options',           // Capability
        'mailcruise-settings',      // Menu slug
        'mailcruise_settings_page', // Callback function
        'dashicons-email-alt',      // Icon
        110                         // Position
    );
}
add_action('admin_menu', 'mailcruise_add_admin_menu');

// Handle subscriber sending on user registration
function mailcruise_send_subscriber_on_registration($user_id) {
    $user_info = get_userdata($user_id);
    $email = $user_info->user_email;
    $first_name = $user_info->first_name;
    $last_name = $user_info->last_name;

    // Get API settings
    $api_token = get_option('mailcruise_api_token');
    $endpoint = get_option('mailcruise_endpoint');
    $list_uid = get_option('mailcruise_list_uid');

    // Send data to MailCruise
    $response = wp_remote_post("$endpoint/subscribers", array(
        'body' => array(
            'api_token'  => $api_token,
            'list_uid'   => $list_uid,
            'EMAIL'      => $email,
            'FIRST_NAME' => $first_name,
            'LAST_NAME'  => $last_name,
            'status'     => 'subscribed'
        )
    ));

    // Handle response
    if (is_wp_error($response)) {
        error_log('MailCruise Error: ' . $response->get_error_message());
    }
}
add_action('user_register', 'mailcruise_send_subscriber_on_registration');

// Handle subscriber sending on Contact Form 7 submission
function mailcruise_send_subscriber_on_cf7_submission($contact_form) {
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
        $data = $submission->get_posted_data();

        // Get email and name
        $email = isset($data['email']) ? $data['email'] : '';
        $first_name = isset($data['your_name']) ? $data['your_name'] : '';
        $last_name = '';

        if (!is_email($email)) {
            error_log('Invalid email: ' . $email);
            return;
        }

        // Get API settings
        $api_token = get_option('mailcruise_api_token');
        $endpoint = get_option('mailcruise_endpoint');
        $list_uid = get_option('mailcruise_list_uid');

        // Send data to MailCruise
        $response = wp_remote_post("$endpoint/subscribers", array(
            'body' => array(
                'api_token'  => $api_token,
                'list_uid'   => $list_uid,
                'EMAIL'      => $email,
                'FIRST_NAME' => $first_name,
                'LAST_NAME'  => $last_name,
                'status'     => 'subscribed'
            )
        ));

        if (is_wp_error($response)) {
            error_log('MailCruise CF7 Error: ' . $response->get_error_message());
        } else {
            error_log('MailCruise CF7 Response: ' . wp_remote_retrieve_body($response));
        }
    }
}

if (class_exists('WPCF7_Submission')) {
    add_action('wpcf7_mail_sent', 'mailcruise_send_subscriber_on_cf7_submission');
}

// WooCommerce registration hook
add_action('woocommerce_created_customer', 'mailcruise_send_subscriber_on_registration');

// Elementor form submission integration
function mailcruise_elementor_form_submission($record, $handler) {
    $form_data = $record->get_formatted_data();

    $email = isset($form_data['email']) ? $form_data['email'] : '';
    $first_name = isset($form_data['first_name']) ? $form_data['first_name'] : '';
    $last_name = isset($form_data['last_name']) ? $form_data['last_name'] : '';

    $api_token = get_option('mailcruise_api_token');
    $endpoint = get_option('mailcruise_endpoint');
    $list_uid = get_option('mailcruise_list_uid');

    $response = wp_remote_post("$endpoint/subscribers", array(
        'body' => array(
            'api_token'  => $api_token,
            'list_uid'   => $list_uid,
            'EMAIL'      => $email,
            'FIRST_NAME' => $first_name,
            'LAST_NAME'  => $last_name,
            'status'     => 'subscribed'
        )
    ));

    if (is_wp_error($response)) {
        error_log('MailCruise Elementor Form Error: ' . $response->get_error_message());
    }
}
add_action('elementor_pro/forms/new_record', 'mailcruise_elementor_form_submission', 10, 2);
