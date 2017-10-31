=== Omise WooCommerce ===
Contributors: Omise
Tags: omise, payment, payment gateway, woocommerce plugin
Requires at least: 4.3.1
Tested up to: 4.5.3
Stable tag: 1.2.3
License: MIT
License URI: https://opensource.org/licenses/MIT

Omise plugin is a plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.

== Description ==

Accepting payment via credit or debit card straight from your WooCommerce site has never been easier.

You can seamlessly process single checkouts or recurring payments in a safe & secure
environment without having to get too technical!

Stay focused on your core business and what really matters and we'll shoulder the rest.

#### What's on the Dashboard

The dashboard provides realtime data visualisation of your account's balances. Here, you can expect to find your total and available balance and also the full list of your transfer history.

At the click of a button, you can make a transfer request to your connected bank account right away and keep posted on their current statuses.

#### The Settings Page

Our settings page controls the most basic configuration settings of Omise's features on your site. Here, you can enable or disable our payment module and other functionalities so to meet your requirements and specific needs.
New to the game? Choose the environment you'd like to work in, get familiar by enabling the sandbox mode or if you're ready, you could choose to go live.

If security is still a concern, rest easy. Here you can enable 3D Secure for your account.

#### During Checkout

Our standard checkout procedure is kept as simple as possible, having the whole payment process kept on one page.

Spare no chances of drop-offs as we keep your customers onsite with a checkout procedure that doesn't disconnect or redirect them to a third party payment processor at the critical point of sales.

We've built the ultimate backend provider so you can freely define your style and streamline payment under your own brand, look and feel.

Learn more about this plugin, [Features](https://www.omise.co/features) or [Pricing](https://www.omise.co/pricing).

== Installation ==

Please refer to our full [documentation page](https://www.omise.co/woocommerce-plugin).

#### Install using WordPress Plugin Store

1. In your WordPress admin panel, navigate to the *Plugins > Add New*.
2. Search for **Omise** and click "*Install now*".
3. Activate the plugin.
4. Config [your public/secret keys](https://www.omise.co/api-authentication) in the plugin settings page.

#### Configuring plugin

1. To enable Omise as Checkout option, form the WordPress admin panel: navigate to the *Omise -> Setting*.
2. Enable "Omise payment gateway" and save.
3. Add your API Keys there and click "*Save changes*"

== Screenshots ==
1. Omise Payment Gateway Dashboard
2. Omise Payment Gateway Setting Page
3. Omise Payment Gateway Checkout Form

== Changelog ==
= 1.2.3 =
(Added) Add a new feature, localization
(Added) Add a translation file for Japanese
(Changed) Change a page header from transactions history to charges history
(Removed) Remove a link, view detail, from each row of transactions and transfers history table
(Removed) Remove sub-tabs, charges and transfers
(Removed) Remove an unused setting, description

= 1.2.2 =
(Improved) Specify the display size of card brand image and allow customer to define their own style
(Removed) Remove an unused unit test of the library, omise-php

= 1.2.1 =
(Added) Configuration for card brand logo display
(Added) List of transfers
(Fixed) Changing page by specify the page number which is not functional

= 1.2.0 =
(Added) manual capture feature
(Added) supported JPY currency
(Added) shortcut menu to Omise's setting page
(Added) Included Omise-PHP 2.4.1 library to the project.
(Improved) Redesigned Omise Dashboard
(Improved) Re-ordered fields in Omise Setting page.
(Improved) Better handle error cases (error messages)
(Improved) Better handle WC order note to trace Omise's actions back.
(Improved) Revised PHP code to following the WordPress Coding Standards.
(Improved) Fixed/Improved various things.

= 1.1.1 =
Added Omise-Version into the cURL request header.

= 1.1.0 =
Adds support for 3-D Secure feature

== Upgrade Notice ==
= 1.2.3 =
(Added) Add a new feature, localization
(Added) Add a translation file for Japanese
(Changed) Change a page header from transactions history to charges history
(Removed) Remove a link, view detail, from each row of transactions and transfers history table
(Removed) Remove sub-tabs, charges and transfers
(Removed) Remove an unused setting, description

= 1.2.2 =
(Improved) Specify the display size of card brand image and allow customer to define their own style
(Removed) Remove an unused unit test of the library, omise-php

= 1.2.1 =
(Added) Configuration for card brand logo display
(Added) List of transfers
(Fixed) Changing page by specify the page number which is not functional

= 1.2.0 =
(Added) manual capture feature
(Added) supported JPY currency
(Added) shortcut menu to Omise's setting page
(Added) Included Omise-PHP 2.4.1 library to the project.
(Improved) Redesigned Omise Dashboard
(Improved) Re-ordered fields in Omise Setting page.
(Improved) Better handle error cases (error messages)
(Improved) Better handle WC order note to trace Omise's actions back.
(Improved) Revised PHP code to following the WordPress Coding Standards.
(Improved) Fixed/Improved various things.