=== Coinbase Commerce Payment Gateway for WooCommerce ===
Contributors: pragbarrett, eddhurst, omidahourai, robbybarton
Plugin URL: https://commerce.coinbase.com/
Tags: coinbase, woocommerce, ethereum, commerce, crypto
Requires at least: 3.0
Requires PHP: 8.1+
Tested up to: 6.5.3
Stable tag: 1.4.1
License: GPLv2 or later

== Description ==

Accept cryptocurrencies through Coinbase Commerce such as USDC, Ethereum, and Matic on your WooCommerce store.

== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'coinbase commerce'
3. Activate Coinbase Commerce from your Plugins page.

= From WordPress.org =

1. Download Coinbase Commerce.
2. Upload to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate Coinbase Commerce from your Plugins page.

= Once Activated =

1. Go to WooCommerce > Settings > Payments
2. Configure the plugin for your store

= Configuring Coinbase Commerce =

* You will need to set up an account on https://commerce.coinbase.com/
* Within the WordPress administration area, go to the WooCommerce > Settings > Payments page and you will see Coinbase in the table of payment gateways.
* Clicking the Manage button on the right hand side will take you into the settings page, where you can configure the plugin for your store.

**Note: If you are running version of WooCommerce older than 3.4.x your Coinbase Commerce tab will be underneath the WooCommerce > Settings > Checkout tab**

= Enable / Disable =

Turn the Coinbase Commerce payment method on / off for visitors at checkout.

= Title =

Title of the payment method on the checkout page

= Description =

Description of the payment method on the checkout page

= API Key =

Your Coinbase Commerce API key. Available within the https://commerce.coinbase.com/dashboard/settings/

Using an API key allows your website to periodically check Coinbase Commerce for payment confirmation.

= Webhook Shared Secret =

Your webhook shared secret. Available within the https://commerce.coinbase.com/dashboard/settings/

Using webhooks allows Coinbase Commerce to send payment confirmation messages to the website. To fill this out:

1. In your Coinbase Commerce settings page, scroll to the 'Webhook subscriptions' section
2. Click 'Add an endpoint' and paste the URL from within your settings page.
3. Make sure to select "Send me all events", to receive all payment updates.
4. Click "Show shared secret" and paste into the box within your settings page.

= Debug log =

Whether or not to store debug logs.

If this is checked, these are saved within your `wp-content/uploads/wc-logs/` folder in a .log file prefixed with `coinbase-`


== Frequently Asked Questions ==

= What cryptocurrencies does the plugin support?

The plugin supports all cryptocurrencies available at https://commerce.coinbase.com/

= Prerequisites=

To use this plugin with your WooCommerce store you will need:
* WooCommerce plugin



== Upgrade Notice ==

This plugin replaces the previous deprecated coinbase-woocommerce plugin.


== Screenshots ==

1. Admin panel
2. Coinbase Commerce payment gateway on checkout page
3. Cryptocurrency payment screen


== Changelog ==

= 1.4.1 =
* Tested against WordPress 6.5.3
* Tested against WooCommerce 8.9.1

= 1.4 =
* Declare HPOS Compatibility
* Remove deprecated Charge status mappings
* Fix order_id incorrect format error
* Update coinbase_charge_id to be charge.id

= 1.3 =
* Adds HPOS support

= 1.2 =
* Tested against WordPress 6.0
* Tested against WooCommerce 6.5.1

= 1.1.4 =
* Fix to send order emails when transitioning from "New" to "Processing"

= 1.1.3 =
* Tested against WordPress 5.2
* Tested against WooCommerce 3.6.3

= 1.1.2 =
* Add support for USDC
* Do not cancel pending orders when charges expire

= 1.1.1 =
* Add support for OVERPAID
* Update Woo order statuses
* Add Coinbase meta data to backend order details

= 1.1 =
* Added support for charge cancel url.
* Handle cancelled events from API.
* Add option to disable icons on checkout page.
* Add Coinbase Commerce transaction ID to WooCommerce order output (Admin order page, Customer order page, email confirmation).
* Updated README.md

= 1.0.1 =
* Tested against WordPress 4.9.7
* Tested against WooCommerce 3.4.3
* Updated README.md
* Updated plugin meta in coinbase-commerce.php

= 1.0.0 =
* Coinbase Commerce