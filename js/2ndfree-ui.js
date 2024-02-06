(function($) {
    $(document).ready(function() {
        console.log("Document ready. Initializing extra deal functionality.");

        // Function to populate the dropdown with extra deal options
        function populateExtraDealDropdown(productId) {
            console.log("Populating extra deal dropdown for product ID:", productId);
            $.ajax({
                url: extraDealData.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_extra_deal_options',
                    product_id: productId,
                },
                success: function(response) {
                    console.log("Received extra deal options:", response);
                    if (response.success && response.data && response.data.options) {
                        var optionsHtml = response.data.options.map(function(option) {
                            return `<option value="${option.value}">${option.label} - $0</option>`;
                        }).join('');
                        $('.extradeal-variation-selector').html(optionsHtml);
                        console.log("Options populated:", optionsHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching extra deal options:", status, error);
                }
            });
        }

        // Function when "Add Both to Cart" button is clicked
        $('#add-both-to-cart').on('click', function(e) {
            e.preventDefault();
            var mainProductId = $('input[name="add-to-cart"]').val();
            var freeVariationId = $('.extradeal-variation-selector').val();
            console.log("Adding both main product and free product to cart:", mainProductId, freeVariationId);
            addBothProductsToCart(mainProductId, freeVariationId);
        });

        // Function to add both products to the cart
        function addBothProductsToCart(mainProductId, freeVariationId) {
            console.log("Sending AJAX to add both products - Main Product ID:", mainProductId, "Free Variation ID:", freeVariationId);

            $.ajax({
                url: extraDealData.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'add_both_products_to_cart',
                    main_product_id: mainProductId,
                    free_variation_id: freeVariationId, // Corrected parameter name
                },
                success: function(response) {
                    console.log("Successfully added both products:", response);
                    if (response.success) {
                        alert('Both products have been added to your cart.');
                    } else {
                        alert('Failed to add the free product to the cart. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Failed to add both products:", error);
                }
            });
        }

        populateExtraDealDropdown($('input[name="add-to-cart"]').val());
    });
})(jQuery);
