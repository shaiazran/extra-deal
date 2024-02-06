<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Admin interface layout
?>

<div class="wrap">
    <h1>extraDeal Settings</h1>
    <h2 class="nav-tab-wrapper">
        <a href="#2nd-free" class="nav-tab nav-tab-active">2nd Free</a>
        <a href="#2nd-discount" class="nav-tab">2nd % Discount</a>
        <a href="#bundles" class="nav-tab">Bundles</a>
    </h2>

    <!-- Content for 2nd Free tab -->
    <div id="2nd-free">
        <!-- Search Bar -->
        <input type="text" id="product-search" placeholder="Search for products" />
        <button id="add-product-btn" disabled>Add Product</button> <!-- Add Product Button -->

        <!-- Table for showing selected products -->
        <table id="selected-products" style="margin-top: 20px;">
            <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Total Sales</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <!-- Products will be dynamically added here via JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Content for 2nd % Discount tab -->

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Function to append messages to the debugging console
        function appendToDebugConsole(message) {
            // Optionally implement a console in the HTML and append messages to it
        }

        // Example: appendToDebugConsole("Settings page loaded.");
    });
</script>
