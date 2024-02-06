<?php
/*
Plugin Name: extraDeal
Description: A plugin to add a second product for free on the product page.
Version: 1.5
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin paths
define('EXTRA_DEAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXTRA_DEAL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include dbjob.php for database operations
include_once(EXTRA_DEAL_PLUGIN_DIR . 'dbjob.php');

// Include AJAX handlers from the backend folder
include_once(EXTRA_DEAL_PLUGIN_DIR . 'backend/ajax-handlers.php');

// Add admin menu
add_action('admin_menu', 'extra_deal_admin_menu');

function extra_deal_admin_menu() {
    add_menu_page('extraDeal', 'extraDeal', 'manage_options', 'extra-deal', 'extra_deal_settings_page', 'dashicons-products', 6);
}

function extra_deal_settings_page() {
    include(EXTRA_DEAL_PLUGIN_DIR . 'backend/settings.php');
}

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'extra_deal_enqueue_admin_scripts');

function extra_deal_enqueue_admin_scripts() {
    $screen = get_current_screen();
    if ($screen->id !== "toplevel_page_extra-deal") {
        return;
    }

    // Enqueue the admin JavaScript file with a version number to avoid cache issues
    wp_enqueue_script(
        'extra-deal-admin-script',
        EXTRA_DEAL_PLUGIN_URL . 'js/admin.js',
        array('jquery', 'jquery-ui-autocomplete'),
        filemtime(EXTRA_DEAL_PLUGIN_DIR . 'js/admin.js'),  // Use file modification time as version
        true
    );

    // Enqueue the admin CSS file with a version number to avoid cache issues
    wp_enqueue_style(
        'extra-deal-admin-style',
        EXTRA_DEAL_PLUGIN_URL . 'css/admin.css',
        array(),
        filemtime(EXTRA_DEAL_PLUGIN_DIR . 'css/admin.css')  // Use file modification time as version
    );

    // Localize script for AJAX URL
    wp_localize_script('extra-deal-admin-script', 'extraDealAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}

function extra_deal_add_product_options() {
    global $product;

    // Check if the product is in the '2nd free' list
    if (extra_deal_is_product_eligible($product->get_id())) {
        // Include the updated template file from your plugin
        include EXTRA_DEAL_PLUGIN_DIR . 'frontend/2ndfree-ui.php';
    }
}

// Hook this function into the WooCommerce single product summary
add_action('woocommerce_single_product_summary', 'extra_deal_add_product_options', 25);

function extra_deal_enqueue_frontend_scripts() {
    if (is_product()) {
        wp_enqueue_script(
            'extra-deal-frontend-script',
            EXTRA_DEAL_PLUGIN_URL . 'js/2ndfree-ui.js',
            array('jquery'),
            filemtime(EXTRA_DEAL_PLUGIN_DIR . 'js/2ndfree-ui.js'),
            true
        );

        // Localize script to pass data from PHP to JavaScript
        wp_localize_script('extra-deal-frontend-script', 'extraDealData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            // Add any other data you need to pass to the script
        ));
    }
}
add_action('wp_enqueue_scripts', 'extra_deal_enqueue_frontend_scripts');

function extra_deal_is_product_eligible($product_id) {
    global $wpdb;
    $table_name = EXTRA_DEAL_TABLE_NAME; // This constant is defined in dbjob.php

    // Prepare the query to check if the product ID exists in the table
    $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE product_id = %d", $product_id);
    $is_eligible = $wpdb->get_var($query) > 0; // Executes the query and checks if the count is greater than 0

    return $is_eligible; // Returns true if eligible, false otherwise
}

function extra_deal_adjust_cart_items($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        // Check if this cart item is marked as a free product
        if (!empty($cart_item['free_product'])) {
            // Set the price to 0
            $cart_item['data']->set_price(0);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'extra_deal_adjust_cart_items');


