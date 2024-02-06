jQuery(document).ready(function($) {
    let selectedProductId = null;
    let selectedProductName = "";

    // Auto-complete for product search
    $('#product-search').autocomplete({
        source: function(request, response) {
            console.log("Initiating search with term:", request.term);
            $.ajax({
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'search_products',
                    term: request.term
                },
                success: function(data) {
                    console.log("Search results:", data);
                    response($.map(data, function(item) {
                        return {
                            label: item.label,
                            value: item.value,
                            id: item.id
                        };
                    }));
                },
                error: function(xhr, status, error) {
                    console.error("Error in autocomplete AJAX: ", status, error);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            console.log("Product selected:", ui.item);
            selectedProductId = ui.item.id;
            selectedProductName = ui.item.value;
            $('#add-product-btn').prop('disabled', false);
        }
    });

    // Handle button click to add product
    $('#add-product-btn').click(function() {
        if (selectedProductId) {
            console.log("Adding product with ID:", selectedProductId);
            addProductToList(selectedProductId);
        } else {
            console.log("No product selected when 'Add Product' button clicked.");
        }
    });

    function addProductToList(productId) {
        console.log("Sending AJAX request to add product ID:", productId);
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'add_product_to_list',
                product_id: productId
            },
            success: function(response) {
                console.log("Response from server on adding product:", response);
                if (response.success) { // Check if the success property is true
                    updateProductList(response);
                    $('#product-search').val('');
                    $('#add-product-btn').prop('disabled', true);
                } else {
                    console.error("Failed to add product to the list.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error in add product AJAX: ", status, error);
            }
        });
    }
    function updateProductList(data) {
        console.log("Updating product list in UI with data:", data);
        $('#selected-products tbody').append('<tr><td>' + data.sku + '</td><td>' + data.name + '</td></tr>');
    }

    // Function to update the UI with products data
    function updateUIWithProducts(productsData) {
        // Clear existing data in UI
        $('#selected-products tbody').html('');
        // Populate UI with new data
        $.each(productsData, function(index, product) {
            $('#selected-products tbody').append(
                '<tr><td>' + product.sku + '</td><td>' + product.name + '</td><td>' + product.total_sales + '</td></tr>'
            );
        });
    }

    // Fetch and display all products on page load
    function fetchAllProducts() {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: 'get_all_products' },
            success: function(response) {
                var data = JSON.parse(response).data; // Ensure proper parsing of JSON
                updateUIWithProducts(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching all products: ", status, error);
            }
        });
    }


    fetchAllProducts();
});
