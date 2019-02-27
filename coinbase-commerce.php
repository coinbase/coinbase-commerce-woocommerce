<?php
/*
Plugin Name:  Coinbase Commerce
Plugin URI:   https://github.com/coinbase/coinbase-commerce-woocommerce/
Description:  A payment gateway that allows your customers to pay with cryptocurrency via Coinbase Commerce (https://commerce.coinbase.com/)
Version:      1.1.1
Author:       Coinbase Commerce
Author URI:   https://commerce.coinbase.com/
License:      GPLv3+
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:  coinbase
Domain Path:  /languages

WC requires at least: 3.0.9
WC tested up to: 3.4.3

Coinbase Commerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Coinbase Commerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Coinbase WooCommerce. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

function cb_init_gateway() {
	// If WooCommerce is available, initialise WC parts.
	// phpcs:ignore
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		require_once 'class-wc-gateway-coinbase.php';
		add_action( 'init', 'cb_wc_register_blockchain_status' );
		add_filter( 'woocommerce_valid_order_statuses_for_payment', 'cb_wc_status_valid_for_payment', 10, 2 );
		add_action( 'cb_check_orders', 'cb_wc_check_orders' );
		add_filter( 'woocommerce_payment_gateways', 'cb_wc_add_coinbase_class' );
		add_filter( 'wc_order_statuses', 'cb_wc_add_status' );
		add_action( 'woocommerce_admin_order_data_after_order_details', 'cb_order_meta_general' );
		add_action( 'woocommerce_order_details_after_order_table', 'cb_order_meta_general' );
		add_filter( 'woocommerce_email_order_meta_fields', 'cb_custom_woocommerce_email_order_meta_fields', 10, 3 );
	}
}
add_action( 'plugins_loaded', 'cb_init_gateway' );


// Setup cron job.

function cb_activation() {
	if ( ! wp_next_scheduled( 'cb_check_orders' ) ) {
		wp_schedule_event( time(), 'hourly', 'cb_check_orders' );
	}
}
register_activation_hook( __FILE__, 'cb_activation' );

function cb_deactivation() {
	wp_clear_scheduled_hook( 'cb_check_orders' );
}
register_deactivation_hook( __FILE__, 'cb_deactivation' );


// WooCommerce

function cb_wc_add_coinbase_class( $methods ) {
	$methods[] = 'WC_Gateway_Coinbase';
	return $methods;
}

function cb_wc_check_orders() {
	$gateway = WC()->payment_gateways()->payment_gateways()['coinbase'];
	return $gateway->check_orders();
}

/**
 * Register new status with ID "wc-blockchainpending" and label "Blockchain Pending"
 */
function cb_wc_register_blockchain_status() {
	register_post_status( 'wc-blockchainpending', array(
		'label'                     => __( 'Blockchain Pending', 'coinbase' ),
		'public'                    => true,
		'show_in_admin_status_list' => true,
		/* translators: WooCommerce order count in blockchain pending. */
		'label_count'               => _n_noop( 'Blockchain pending <span class="count">(%s)</span>', 'Blockchain pending <span class="count">(%s)</span>' ),
	) );
}

/**
 * Register wc-blockchainpending status as valid for payment.
 */
function cb_wc_status_valid_for_payment( $statuses, $order ) {
	$statuses[] = 'wc-blockchainpending';
	return $statuses;
}

/**
 * Add registered status to list of WC Order statuses
 * @param array $wc_statuses_arr Array of all order statuses on the website.
 */
function cb_wc_add_status( $wc_statuses_arr ) {
	$new_statuses_arr = array();

	// Add new order status after payment pending.
	foreach ( $wc_statuses_arr as $id => $label ) {
		$new_statuses_arr[ $id ] = $label;

		if ( 'wc-pending' === $id ) {  // after "Payment Pending" status.
			$new_statuses_arr['wc-blockchainpending'] = __( 'Blockchain Pending', 'coinbase' );
		}
	}

	return $new_statuses_arr;
}


/**
 * Add order Coinbase meta after General and before Billing
 *
 * @see: https://rudrastyh.com/woocommerce/customize-order-details.html
 *
 * @param WC_Order $order WC order instance
 */
function cb_order_meta_general( $order )
{
    if ($order->get_payment_method() == 'coinbase') {
        ?>

        <br class="clear"/>
        <h3>Coinbase Commerce Data</h3>
        <div class="">
            <p>Coinbase Commerce Reference # <?php echo esc_html($order->get_meta('_coinbase_charge_id')); ?></p>
        </div>

        <?php
    }
}


/**
 * Add Coinbase meta to WC emails
 *
 * @see https://docs.woocommerce.com/document/add-a-custom-field-in-an-order-to-the-emails/
 *
 * @param array    $fields indexed list of existing additional fields.
 * @param bool     $sent_to_admin If should sent to admin.
 * @param WC_Order $order WC order instance
 *
 */
function cb_custom_woocommerce_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
    if ($order->get_payment_method() == 'coinbase') {
        $fields['coinbase_commerce_reference'] = array(
            'label' => __( 'Coinbase Commerce Reference #' ),
            'value' => $order->get_meta( '_coinbase_charge_id' ),
        );
    }

    return $fields;
}
