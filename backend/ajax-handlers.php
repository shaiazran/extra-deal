<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly2
}

add_action('wp_ajax_search_products', 'extra_deal_search_products');
add_action('wp_ajax_add_product_to_list', 'extra_deal_add_product_to_list');
add_action('wp_ajax_get_all_products', 'extra_deal_get_all_products');
// Hook the function to WordPress AJAX
add_action('wp_ajax_check_eligible_product', 'extra_deal_check_eligible_product');
add_action('wp_ajax_nopriv_check_eligible_product', 'extra_deal_check_eligible_product'); // If needed for non-logged-in users
add_action('wp_ajax_get_extra_deal_options', 'extra_deal_get_extra_deal_options');
add_action('wp_ajax_nopriv_get_extra_deal_options', 'extra_deal_get_extra_deal_options'); // For guest users
// Hook the new AJAX action for logged-in users
add_action('wp_ajax_add_both_products_to_cart', 'extra_deal_add_both_products_to_cart');
// Hook the new AJAX action for guests
add_action('wp_ajax_nopriv_add_both_products_to_cart', 'extra_deal_add_both_products_to_cart');

function extra_deal_add_both_products_to_cart() {
    check_ajax_referer('extra_deal_nonce', 'security');
    $mainProductId = isset($_POST['main_product_id']) ? intval($_POST['main_product_id']) : 0;
    $freeVariationId = isset($_POST['free_variation_id']) ? intval($_POST['free_variation_id']) : 0;

    error_log('Adding Products to Cart - Main Product ID: ' . $mainProductId . ', Free Variation ID: ' . $freeVariationId);

    $mainProductAdded = WC()->cart->add_to_cart($mainProductId, 1);
    error_log($mainProductAdded ? 'Main product added successfully' : 'Failed to add main product');

    $freeProductAdded = false;
    if ($freeVariationId > 0) {
        $freeProductAdded = WC()->cart->add_to_cart($mainProductId, 1, $freeVariationId, array(), array('is_free_product' => true));
        error_log($freeProductAdded ? 'Free product added successfully' : 'Failed to add free product');
    }

    if ($mainProductAdded && $freeProductAdded) {
        error_log('Both products added to cart successfully');
        wp_send_json_success(['message' => 'Both products added to cart successfully.']);
    } else {
        error_log('Failed to add both products to cart');
        wp_send_json_error(['message' => 'Failed to add products to cart.']);
    }
    wp_die();
}




// Hook into WooCommerce cart loading to adjust the price of the free product
add_action('woocommerce_before_calculate_totals', 'adjust_free_product_price', 10, 1);
function adjust_free_product_price($cart_obj) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    // Check if we have a cart item key to adjust
    $free_product_cart_id = WC()->session->get('free_product_cart_id');
    if (!$free_product_cart_id) return;

    foreach ($cart_obj->get_cart() as $cart_item_key => $cart_item) {
        // Adjust the price of the product identified by the cart item key
        if ($cart_item_key === $free_product_cart_id) {
            $cart_item['data']->set_price(0);
        }
    }

    // Clear the session to prevent adjusting prices on future cart loads
    WC()->session->__unset('free_product_cart_id');
}

function extra_deal_get_extra_deal_options() {
    // Ensure a product ID is provided
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        wp_send_json_error('Product ID is missing.');
        wp_die();
    }

    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);

    if (!$product || !$product->is_type('variable')) {
        wp_send_json_error('Invalid product.');
        wp_die();
    }

    $variations = $product->get_available_variations();
    $options = [];

    foreach ($variations as $variation) {
        $options[] = [
            'value' => $variation['variation_id'],
            'label' => implode(' / ', $variation['attributes']) . ' - ' . wc_price($variation['display_price']),
        ];
    }

    wp_send_json_success(['options' => $options]);
    wp_die();
}


// Function to search products for auto-complete
function extra_deal_search_products() {
    global $wpdb;

    $term = isset($_GET['term']) ? '%' . $wpdb->esc_like($_GET['term']) . '%' : '';
    $query = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND (post_title LIKE %s OR ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s))";
    $products = $wpdb->get_results($wpdb->prepare($query, $term, $term));

    $response = array();
    foreach ($products as $product) {
        $sku = get_post_meta($product->ID, '_sku', true);
        $response[] = array(
            'id' => $product->ID,
            'label' => $sku . ' - ' . $product->post_title,
            'value' => $product->post_title
        );
    }

    echo json_encode($response);
    wp_die();
}

// Function to add product to '2nd Free' list
function extra_deal_get_all_products() {
    global $wpdb;
    $table_name = EXTRA_DEAL_TABLE_NAME;

    $query = "SELECT product_id, sku FROM $table_name";
    $products = $wpdb->get_results($query, ARRAY_A);

    $response = array();
    foreach ($products as $product) {
        $product_id = $product['product_id'];
        $sku = $product['sku'];
        $name = get_the_title($product_id);
        $total_sales = get_post_meta($product_id, 'total_sales', true);

        $response[] = array(
            'sku' => $sku,
            'name' => $name,
            'total_sales' => $total_sales
        );
    }

    echo json_encode(array('data' => $response));
    wp_die();
}

// Function to check if a product is eligible for an extra deal
function extra_deal_check_eligible_product() {
    global $wpdb;

    // Security check - ensure a valid product ID is received
    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        wp_send_json_error(array('message' => 'Invalid product ID.'));
        wp_die();
    }

    $product_id = intval($_POST['product_id']);
    $table_name = EXTRA_DEAL_TABLE_NAME;

    // Check if the product ID exists in the database
    $is_eligible = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE product_id = %d",
            $product_id
        )) > 0;

    // Send back a JSON response
    wp_send_json_success(array('is_eligible' => $is_eligible));
    wp_die();
}


