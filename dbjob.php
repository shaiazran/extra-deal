<?php
// dbjob.php

function extra_deal_define_constants() {
    global $wpdb;
    define('EXTRA_DEAL_DB_VERSION', '1.0');
    define('EXTRA_DEAL_TABLE_NAME', $wpdb->prefix . 'extradeal_2nd_free');
}

add_action('init', 'extra_deal_define_constants');

// Function to create the database table
function extra_deal_install() {
    global $wpdb;
    $table_name = EXTRA_DEAL_TABLE_NAME;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id mediumint(9) NOT NULL,
        sku varchar(255) NOT NULL,
        date_added datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('extra_deal_db_version', EXTRA_DEAL_DB_VERSION);
}

// Hook the install function to plugin activation
register_activation_hook(__FILE__, 'extra_deal_install');
