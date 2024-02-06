<?php
// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // Hook into WooCommerce to add our custom UI for selecting the free product variation
    add_action( 'woocommerce_after_single_variation', 'extradeal_custom_free_variation_ui' );

    function extradeal_custom_free_variation_ui() {
        // Get the global product object
        global $product;

        // Ensure we're on a variable product page
        if ( $product->is_type( 'variable' ) ) {
            // Get available variations
            $variations = $product->get_available_variations();

            // Output the custom UI
            ?>
            <div id="extradeal-free-product" class="extradeal-custom-ui">
                <h3>Choose your free product variation:</h3>
                <form id="extradeal-free-selection-form">
                    <select name="extradeal_free_variation" class="extradeal-variation-selector">
                        <option value="">Select Variation</option>
                        <?php foreach ( $variations as $variation ) : ?>
                            <option value="<?php echo esc_attr( $variation['variation_id'] ); ?>">
                                <?php echo implode( ' / ', $variation['attributes'] ); ?> - <?php echo wc_price($variation['display_price']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <?php
        }
    }
}
