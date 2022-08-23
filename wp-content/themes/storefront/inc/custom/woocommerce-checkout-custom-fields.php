<?php

define('WCC_CUSTOM_FIELD_COUNT', 3);
define('WCC_CUSTOM_FIELD_KEY', 'wcc_custom_field_key_%s');
define('WCC_CUSTOM_FIELD_LABEL', 'Custom Field %s');

// Create Fields
function wcc_custom_fields_create($checkout)
{
    echo '<h3>Custom Fields</h3>';

    // add three fields
    for ($i=1; $i <= WCC_CUSTOM_FIELD_COUNT; $i++) {
        woocommerce_form_field(sprintf(WCC_CUSTOM_FIELD_KEY, $i), array(
            'type' => 'text',
            'label' => __(sprintf(WCC_CUSTOM_FIELD_LABEL, $i)),
            'required' => true,
        ), $checkout->get_value(sprintf(WCC_CUSTOM_FIELD_KEY, $i)));
    }
}
add_filter( 'woocommerce_after_order_notes' , 'wcc_custom_fields_create' );

// Field Validation
function wcc_custom_fields_process()
{
    for ($i=1; $i <= WCC_CUSTOM_FIELD_COUNT; $i++) {
        if ( ! $_POST[sprintf(WCC_CUSTOM_FIELD_KEY, $i)] ) {
            wc_add_notice(__( sprintf(WCC_CUSTOM_FIELD_LABEL . ' is required', $i)), 'error' );
        }
    }
}
add_action('woocommerce_checkout_process', 'wcc_custom_fields_process');

// Save Custom fields to order meta data
function wcc_custom_fields_save_order_meta( $order, $data )
{
    for ($i=1; $i <= WCC_CUSTOM_FIELD_COUNT; $i++) {
        if ( $_POST[sprintf(WCC_CUSTOM_FIELD_KEY, $i)] ) {
            $order->update_meta_data( sprintf(WCC_CUSTOM_FIELD_KEY, $i), $_POST[sprintf(WCC_CUSTOM_FIELD_KEY, $i)] );
        }
    }
}
add_action('woocommerce_checkout_create_order', 'wcc_custom_fields_save_order_meta', 22, 2 );

// Display custom fields in admin order
function wcc_custom_fields_display_admin_order($order)
{
    $loop = 1;

    while (true) {
        $wcc_custom_field_name = get_post_meta($order->id, sprintf(WCC_CUSTOM_FIELD_KEY, $loop), true);
        if (! $wcc_custom_field_name) {
            break;
        }

        echo '<p><strong>' . __(sprintf(WCC_CUSTOM_FIELD_LABEL, $loop)) . ':</strong> ' . get_post_meta($order->id, sprintf(WCC_CUSTOM_FIELD_KEY, $loop), true) . '</p>';

        $loop++;
    }
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'wcc_custom_fields_display_admin_order', 10, 1 );

// Custom field order view
function wcc_custom_fields_order_view($order_id, $plain_text = false)
{
    $loop = 1;
    $display_html = [];
    $display_plain = [];

    while (true) {
        $wcc_custom_field_name = get_post_meta($order_id, sprintf(WCC_CUSTOM_FIELD_KEY, $loop), true);
        if (! $wcc_custom_field_name) {
            break;
        }

        $display_html[] = '<li><strong>' . __(sprintf(WCC_CUSTOM_FIELD_LABEL, $loop)) . ':</strong> ' . get_post_meta($order_id, sprintf(WCC_CUSTOM_FIELD_KEY, $loop), true) . '</li>';
        $display_plain[] =  __(sprintf(WCC_CUSTOM_FIELD_LABEL, $loop)) . ': ' . get_post_meta($order_id, sprintf(WCC_CUSTOM_FIELD_KEY, $loop), true);

        $loop++;
    }

    if ($plain_text === false) {
        echo '<h2>Custom Fields</h2>';
        echo '<ul>';
        echo join('', $display_html);
        echo '</ul>';
    } else {
        echo "Custom Fields\n";
        echo join("\n", $display_plain);
    }
}

// Display custom fields in email order
function wcc_custom_fields_display_email_order($order_obj, $sent_to_admin, $plain_text)
{
    wcc_custom_fields_order_view($order_obj->get_order_number(), $plain_text);
}
add_action( 'woocommerce_email_order_meta', 'wcc_custom_fields_display_email_order', 10, 3 );

// Display custom fields in order view
function wcc_custom_fields_display_view_order($order)
{
    wcc_custom_fields_order_view($order);
}
add_action( 'woocommerce_thankyou','wcc_custom_fields_display_view_order', 10);
add_action( 'woocommerce_view_order', 'wcc_custom_fields_display_view_order', 10 );
