<?php
// Hooks for WooCommerce actions

function at_hook_into_wc_actions() {
    // Order-related actions
    add_action('woocommerce_order_status_changed', function ($order_id, $old_status, $new_status, $order) {
        at_track_actions('woocommerce_order_status_changed', $order_id, "Status changed from $old_status to $new_status");
    }, 10, 4);

    add_action('woocommerce_new_order', function ($order_id) {
        at_track_actions('woocommerce_new_order', $order_id);
    });

    // Product-related actions
    add_action('woocommerce_duplicate_product', function ($duplicate, $product) {
        at_track_actions('woocommerce_product_duplicated', $duplicate->get_id(), $product->get_title());
    }, 10, 2);

    add_action('woocommerce_update_product', function ($product_id) {
        at_track_actions('woocommerce_product_updated', $product_id);
    });

    add_action('woocommerce_delete_product', function ($product_id) {
        at_track_actions('woocommerce_product_deleted', $product_id);
    });

    // Coupon-related actions
    add_action('woocommerce_coupon_created', function ($coupon_id) {
        at_track_actions('woocommerce_coupon_created', $coupon_id);
    });

    add_action('woocommerce_coupon_updated', function ($coupon_id) {
        at_track_actions('woocommerce_coupon_updated', $coupon_id);
    });

    add_action('woocommerce_coupon_deleted', function ($coupon_id) {
        at_track_actions('woocommerce_coupon_deleted', $coupon_id);
    });

    // Customer-related actions
    add_action('woocommerce_created_customer', function ($customer_id, $new_customer_data, $password_generated) {
        at_track_actions('woocommerce_customer_created', $customer_id);
    });

    add_action('woocommerce_update_customer', function ($customer_id) {
        at_track_actions('woocommerce_customer_updated', $customer_id);
    });

    add_action('woocommerce_delete_customer', function ($customer_id) {
        at_track_actions('woocommerce_customer_deleted', $customer_id);
    });

    // Shipping-related actions
    add_action('woocommerce_shipping_zone_added', function ($zone_id) {
        at_track_actions('woocommerce_shipping_zone_added', $zone_id);
    });

    add_action('woocommerce_shipping_zone_updated', function ($zone_id) {
        at_track_actions('woocommerce_shipping_zone_updated', $zone_id);
    });

    add_action('woocommerce_shipping_zone_deleted', function ($zone_id) {
        at_track_actions('woocommerce_shipping_zone_deleted', $zone_id);
    });

    // Tax-related actions
    add_action('woocommerce_tax_rate_added', function ($tax_rate_id) {
        at_track_actions('woocommerce_tax_rate_added', $tax_rate_id);
    });

    add_action('woocommerce_tax_rate_updated', function ($tax_rate_id) {
        at_track_actions('woocommerce_tax_rate_updated', $tax_rate_id);
    });

    add_action('woocommerce_tax_rate_deleted', function ($tax_rate_id) {
        at_track_actions('woocommerce_tax_rate_deleted', $tax_rate_id);
    });
}
add_action('woocommerce_init', 'at_hook_into_wc_actions');
