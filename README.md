# Coinbase Commerce for WooCommerce

A WooCommerce payment gateway that allows your customers to pay with cryptocurrency via Coinbase Commerce

## Installation

### From this repository

Within the Github repository, click the Clone or Download button and Download a zip file of the repository, or clone it directly via command line.

Within your WordPress administration panel, go to Plugins > Add New and click the Upload Plugin button on the top of the page.

Alternatively, you can move the zip file into the `wp-content/plugins` folder of your website and unzip.

You will then need to go to your WordPress administration Plugins page, and activate the plugin.

## Configuring Coinbase Commerce

You will need to set up an account on [Coinbase Commerce].

Within the WordPress administration area, go to the WooCommerce > Settings > Payments page and you will see Coinbase in the table of payment gateways.

Clicking the Manage button on the right hand side will take you into the settings page, where you can configure the plugin for your store.

**Note: If you are running version of WooCommerce older than 3.4.x your Coinbase Commerce tab will be underneath the WooCommerce > Settings > Checkout tab**

## Settings

### Enable / Disable

Turn the Coinbase Commerce payment method on / off for visitors at checkout.

### Title

Title of the payment method on the checkout page

### Description

Description of the payment method on the checkout page

### API Key

Your Coinbase Commerce API key. Available within the [Coinbase Commerce settings page].

Using an API key allows your website to periodically check Coinbase Commerce for payment confirmation.

### Webhook Shared Secret

Your webhook shared secret. Available within the [Coinbase Commerce settings page].

Using webhooks allows Coinbase Commerce to send payment confirmation messages to the website. To fill this out:

1. In your Coinbase Commerce settings page, scroll to the 'Webhook subscriptions' section
2. Click 'Add an endpoint' and paste the URL from within your settings page.
3. Make sure to select "Send me all events", to receive all payment updates.
4. Click "Show shared secret" and paste into the box within your settings page.

### Debug log

Whether or not to store debug logs.

If this is checked, these are saved within your `wp-content/uploads/wc-logs/` folder in a .log file prefixed with `coinbase-`

## Prerequisites

To use this plugin with your WooCommerce store you will need:

* [WordPress] (tested up to 4.9.7)
* [WooCommerce] (tested up to 3.4.3)

## Frequently Asked Questions

**What cryptocurrencies does the plugin support?**

The plugin supports all cryptocurrencies available at [Coinbase Commerce]

## License

This project is licensed under the Apache 2.0 License

## Changelog

## 1.0.1 ##
* Tested against WordPress 4.9.7
* Tested against WooCommerce 3.4.3
* Updated README.md
* Updated plugin meta in coinbase-commerce.php

## 1.0.0 ##
* Coinbase Commerce

[//]: # (Comments for storing reference material in. Stripped out when processing the markdown)

[Coinbase Commerce]: <https://commerce.coinbase.com/>
[Coinbase Commerce settings page]: <https://commerce.coinbase.com/dashboard/settings/>
[WooCommerce]: <https://woocommerce.com/>
[WordPress]: <https://wordpress.org/>

